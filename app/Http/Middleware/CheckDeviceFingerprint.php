<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\DeviceFingerprint;
use Illuminate\Support\Facades\Hash;

class CheckDeviceFingerprint
{
    /**
     * Handle an incoming request.
     * 
     * This middleware checks if the current device matches the registered device fingerprint.
     * If device binding is enabled and a fingerprint exists, access is denied for mismatched devices.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip device check for guest users (they'll be checked after login)
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Skip device check for super admins to prevent lockout
        // Super admins can access from any device but events are still logged
        if ($user->hasRole('super-admin') || $user->hasRole('Super Admin')) {
            return $next($request);
        }

        // Check if device_fingerprints table exists (migration may not have been run yet)
        try {
            // Try to check if table exists by attempting a simple query
            \DB::select('SELECT 1 FROM device_fingerprints LIMIT 1');
        } catch (\Exception $e) {
            // Table doesn't exist yet - skip device check
            \Log::info('Device fingerprints table not found, skipping device check', [
                'error' => $e->getMessage(),
            ]);
            return $next($request);
        }

        // Get device fingerprint from request header or input
        // Sanitize input to prevent injection attacks
        $deviceFingerprint = $request->header('X-Device-Fingerprint') 
            ?? $request->input('_device_fingerprint');

        // Sanitize fingerprint if present
        if ($deviceFingerprint) {
            $deviceFingerprint = trim($deviceFingerprint);
            // Remove null bytes and control characters
            $deviceFingerprint = preg_replace('/[\x00-\x1F\x7F]/', '', $deviceFingerprint);
        }

        if (!$deviceFingerprint) {
            // If no fingerprint is sent, check if user has any registered device
            try {
                $hasRegisteredDevice = DeviceFingerprint::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->exists();

                if ($hasRegisteredDevice) {
                    // User has a registered device but didn't send fingerprint
                    // This is a security concern - deny access
                    \Log::warning('Device fingerprint missing for authenticated user with registered device', [
                        'user_id' => $user->id,
                        'ip' => $request->ip(),
                    ]);

                    // Deny access for security - fingerprint is required
                    return $this->denyAccess($request, 'Device fingerprint is required. Please refresh the page and try again.');
                }
            } catch (\Exception $e) {
                // Table might not exist or other database error - deny access for security
                \Log::warning('Error checking device fingerprint', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'ip' => $request->ip(),
                ]);
                // Deny access if we can't verify device
                return $this->denyAccess($request, 'Unable to verify device. Please contact your administrator.');
            }

            return $next($request);
        }

        try {
            // Validate and sanitize device fingerprint input
            if (!$this->isValidFingerprint($deviceFingerprint)) {
                \Log::warning('Invalid device fingerprint format received', [
                    'user_id' => $user->id,
                    'ip' => $request->ip(),
                    'fingerprint_length' => strlen($deviceFingerprint ?? ''),
                ]);
                return $this->denyAccess($request, 'Invalid device fingerprint. Please refresh the page and try again.');
            }

            // Hash the incoming fingerprint for comparison
            $fingerprintHash = hash('sha256', $deviceFingerprint);

            // Check if user has an active device fingerprint
            $registeredDevice = DeviceFingerprint::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if (!$registeredDevice) {
                // No device registered yet - allow access (device will be registered on login)
                return $next($request);
            }

            // Compare fingerprints using constant-time comparison to prevent timing attacks
            if (!hash_equals($registeredDevice->fingerprint_hash, $fingerprintHash)) {
                // Device mismatch - deny access
                // Don't log full hashes for security (only log partial hash for debugging)
                \Log::warning('Device fingerprint mismatch detected', [
                    'user_id' => $user->id,
                    'expected_hash_prefix' => substr($registeredDevice->fingerprint_hash, 0, 8) . '...',
                    'received_hash_prefix' => substr($fingerprintHash, 0, 8) . '...',
                    'ip' => $request->ip(),
                ]);

                return $this->denyAccess($request, 'Device mismatch. Access is restricted to your registered device.');
            }

            // Device matches - update last used timestamp
            $registeredDevice->touchLastUsed();
        } catch (\Exception $e) {
            // Database error - log and deny access for security
            \Log::error('Error validating device fingerprint', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            
            // For security, deny access if we can't validate the device
            // This prevents bypassing device check through errors
            return $this->denyAccess($request, 'Unable to verify device. Please contact your administrator.');
        }

        return $next($request);
    }

    /**
     * Validate device fingerprint format and length.
     */
    private function isValidFingerprint(?string $fingerprint): bool
    {
        if (empty($fingerprint)) {
            return false;
        }

        // Check length (should be reasonable - not too short, not too long)
        $length = strlen($fingerprint);
        if ($length < 10 || $length > 10000) {
            return false;
        }

        // Check for suspicious patterns (SQL injection attempts, script tags, etc.)
        $suspiciousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/union\s+select/i',
            '/drop\s+table/i',
            '/delete\s+from/i',
            '/insert\s+into/i',
            '/update\s+.*\s+set/i',
            '/exec\s*\(/i',
            '/eval\s*\(/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $fingerprint)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Deny access and return appropriate response.
     */
    private function denyAccess(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'DEVICE_MISMATCH',
            ], 403);
        }

        // Logout the user and redirect to login with error message
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors(['device' => $message])
            ->with('device_error', true);
    }
}
