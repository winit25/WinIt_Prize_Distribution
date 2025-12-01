<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        // Regenerate session token on page load to prevent 419 errors
        // This ensures fresh CSRF token for new device sessions
        if (!$request->session()->has('_token')) {
            $request->session()->regenerateToken();
        }
        
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Get user before session regeneration
        $user = Auth::user();

        // Validate device fingerprint BEFORE allowing login
        $deviceValidation = $this->validateDeviceFingerprint($request, $user);
        if (!$deviceValidation['allowed']) {
            // Logout the user immediately
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirect back with error message
            return redirect()->route('login')
                ->withErrors(['device' => $deviceValidation['message']])
                ->with('device_error', true)
                ->withInput($request->only('email', 'remember'));
        }

        $request->session()->regenerate();

        // Log the login activity
        try {
            app(\App\Services\ActivityLoggingService::class)->logUserLogin($user);
        } catch (\Exception $e) {
            \Log::warning('Failed to log login activity', ['error' => $e->getMessage()]);
        }

        // IMPORTANT: Device fingerprint registration happens ONLY when user accesses dashboard
        // This ensures fingerprint is only activated after successful login AND password change (if required)
        // Do NOT register device fingerprint here - it will be registered in DashboardController

        // Check if user must change password on first login
        if ($user->must_change_password) {
            return redirect()->route('password.change')
                ->with('warning', 'Welcome! Please change your password to continue.');
        }

        // Always redirect to dashboard after login
        return redirect()->route('dashboard');
    }

    /**
     * Validate device fingerprint before allowing login.
     * Returns array with 'allowed' boolean and 'message' string.
     */
    private function validateDeviceFingerprint(Request $request, $user): array
    {
        try {
            // Allow super admins to bypass device check on first login
            // This ensures super admins can always access the system
            $isSuperAdmin = $user->hasRole('super-admin') || $user->hasRole('Super Admin');
            
            // Check if device_fingerprints table exists
            try {
                \DB::select('SELECT 1 FROM device_fingerprints LIMIT 1');
            } catch (\Exception $e) {
                // Table doesn't exist yet - allow login
                return ['allowed' => true, 'message' => ''];
            }

            // Get device fingerprint from request
            $deviceFingerprint = $request->header('X-Device-Fingerprint') 
                ?? $request->input('_device_fingerprint');

            // Sanitize fingerprint if present
            if ($deviceFingerprint) {
                $deviceFingerprint = trim($deviceFingerprint);
                // Remove null bytes and control characters
                $deviceFingerprint = preg_replace('/[\x00-\x1F\x7F]/', '', $deviceFingerprint);
            }

            // Check if user has any registered device
            $registeredDevice = \App\Models\DeviceFingerprint::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            // If no device is registered, allow login (first time login)
            // Super admins can always login even without device registered
            if (!$registeredDevice) {
                if ($isSuperAdmin) {
                    \Log::info('Super admin login - no device registered yet, allowing login', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                }
                return ['allowed' => true, 'message' => ''];
            }
            
            // Super admins: if fingerprint missing but device registered, still allow (grace period)
            // This prevents super admin lockout
            if ($isSuperAdmin && !$deviceFingerprint) {
                \Log::warning('Super admin login - fingerprint missing but device registered, allowing login', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                ]);
                return ['allowed' => true, 'message' => ''];
            }

            // If no fingerprint provided but device is registered, deny login
            if (!$deviceFingerprint) {
                \Log::warning('Device fingerprint missing for user with registered device', [
                    'user_id' => $user->id,
                    'ip' => $request->ip(),
                ]);

                return [
                    'allowed' => false,
                    'message' => 'Device fingerprint is required. Please use your registered device to login.'
                ];
            }

            // Validate fingerprint format
            if (!$this->isValidFingerprint($deviceFingerprint)) {
                \Log::warning('Invalid device fingerprint format during login', [
                    'user_id' => $user->id,
                    'ip' => $request->ip(),
                ]);

                return [
                    'allowed' => false,
                    'message' => 'Invalid device fingerprint. Please refresh the page and try again.'
                ];
            }

            // Hash the incoming fingerprint
            $fingerprintHash = hash('sha256', $deviceFingerprint);

            // Compare fingerprints using constant-time comparison to prevent timing attacks
            if (!hash_equals($registeredDevice->fingerprint_hash, $fingerprintHash)) {
                // Device mismatch - handle differently for super admins
                if ($isSuperAdmin) {
                    // Super admins: allow login but log the event
                    \Log::warning('Super admin login from different device - allowing with warning', [
                        'user_id' => $user->id,
                        'expected_hash_prefix' => substr($registeredDevice->fingerprint_hash, 0, 8) . '...',
                        'received_hash_prefix' => substr($fingerprintHash, 0, 8) . '...',
                        'registered_device' => $registeredDevice->device_name,
                        'ip' => $request->ip(),
                    ]);
                    
                    // Still notify super admins about the security event
                    $this->notifySuperAdminsOfDeviceMismatch($user, $request, $registeredDevice);
                    
                    // Allow super admin to login but log the event
                    return ['allowed' => true, 'message' => ''];
                }
                
                // Regular users: deny login
                \Log::warning('Login attempt from different device blocked', [
                    'user_id' => $user->id,
                    'expected_hash_prefix' => substr($registeredDevice->fingerprint_hash, 0, 8) . '...',
                    'received_hash_prefix' => substr($fingerprintHash, 0, 8) . '...',
                    'registered_device' => $registeredDevice->device_name,
                    'ip' => $request->ip(),
                ]);

                // Notify super admins about the security event
                $this->notifySuperAdminsOfDeviceMismatch($user, $request, $registeredDevice);

                return [
                    'allowed' => false,
                    'message' => 'Access denied. This account is tied to another device. Please use your original registered device to login. If you need to change your device, please contact your administrator.'
                ];
            }

            // Device matches - allow login
            return ['allowed' => true, 'message' => ''];

        } catch (\Exception $e) {
            // On error, log and deny login for security
            \Log::error('Error validating device fingerprint during login', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            
            // For security, deny login if we can't validate the device
            // This prevents bypassing device check through errors
            return [
                'allowed' => false,
                'message' => 'Unable to verify device. Please contact your administrator.'
            ];
        }
    }

    /**
     * Register or update device fingerprint for the authenticated user.
     * This method is public so it can be called from DashboardController.
     */
    public function registerDeviceFingerprint(Request $request, $user): void
    {
        try {
            // Check if device_fingerprints table exists
            try {
                \DB::select('SELECT 1 FROM device_fingerprints LIMIT 1');
            } catch (\Exception $e) {
                // Table doesn't exist yet - skip registration
                \Log::info('Device fingerprints table not found, skipping device registration', [
                    'error' => $e->getMessage(),
                ]);
                return;
            }

            // Try to get fingerprint from header first (for AJAX), then from form input
            $deviceFingerprint = $request->header('X-Device-Fingerprint') 
                ?? $request->input('_device_fingerprint');

            // Sanitize fingerprint if present
            if ($deviceFingerprint) {
                $deviceFingerprint = trim($deviceFingerprint);
                // Remove null bytes and control characters
                $deviceFingerprint = preg_replace('/[\x00-\x1F\x7F]/', '', $deviceFingerprint);
            }
            
            if (!$deviceFingerprint) {
                \Log::warning('Device fingerprint not provided during login', [
                    'user_id' => $user->id,
                ]);
                return;
            }

            // Validate fingerprint before processing
            if (!$this->isValidFingerprint($deviceFingerprint)) {
                \Log::warning('Invalid device fingerprint format during registration', [
                    'user_id' => $user->id,
                    'ip' => $request->ip(),
                ]);
                return;
            }

            $fingerprintHash = hash('sha256', $deviceFingerprint);
            $userAgent = $request->userAgent();
            $ipAddress = $request->ip();

            // Parse device name from user agent
            $deviceName = $this->parseDeviceName($userAgent);

            // Check if this fingerprint already exists for this user
            $existingDevice = \App\Models\DeviceFingerprint::where('user_id', $user->id)
                ->where('fingerprint_hash', $fingerprintHash)
                ->first();

            if ($existingDevice) {
                // Update existing device (same device logging in again)
                $existingDevice->update([
                    'last_used_at' => now(),
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'is_active' => true,
                ]);
            } else {
                // This is a new device - but validation already passed, so this is first-time registration
                // Create new device fingerprint (only happens on first login)
                \App\Models\DeviceFingerprint::create([
                    'user_id' => $user->id,
                    'fingerprint_hash' => $fingerprintHash,
                    'device_name' => $deviceName,
                    'user_agent' => $userAgent,
                    'ip_address' => $ipAddress,
                    'last_used_at' => now(),
                    'is_active' => true,
                ]);

                \Log::info('New device registered for user (first login)', [
                    'user_id' => $user->id,
                    'device_name' => $deviceName,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to register device fingerprint', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw exception - allow login to proceed even if device registration fails
        }
    }

    /**
     * Parse device name from user agent string.
     */
    private function parseDeviceName(string $userAgent): string
    {
        // Extract browser and OS information
        $browser = 'Unknown';
        $os = 'Unknown';

        // Detect browser
        if (preg_match('/Chrome/i', $userAgent) && !preg_match('/Edg|OPR/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/OPR/i', $userAgent)) {
            $browser = 'Opera';
        }

        // Detect OS
        if (preg_match('/Windows NT/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac OS X/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $os = 'iOS';
        }

        return "{$browser} on {$os}";
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
     * Notify super admins about device mismatch security event.
     */
    private function notifySuperAdminsOfDeviceMismatch($user, Request $request, $registeredDevice): void
    {
        try {
            // Get all super admin users
            $superAdmins = \App\Models\User::whereHas('roles', function($query) {
                $query->where('name', 'super-admin')
                      ->orWhere('name', 'Super Admin');
            })->get();

            if ($superAdmins->isEmpty()) {
                \Log::warning('No super admin users found to notify about device mismatch');
                return;
            }

            // Parse device info from current request - sanitize user agent
            $userAgent = $request->userAgent() ?? 'Unknown';
            $currentDeviceName = $this->parseDeviceName($userAgent);
            $attemptedIp = $request->ip() ?? 'Unknown';

            // Sanitize user data to prevent XSS in notifications
            $userName = htmlspecialchars($user->name ?? 'Unknown', ENT_QUOTES, 'UTF-8');
            $userEmail = htmlspecialchars($user->email ?? 'Unknown', ENT_QUOTES, 'UTF-8');
            $registeredDeviceName = htmlspecialchars($registeredDevice->device_name ?? 'Unknown', ENT_QUOTES, 'UTF-8');
            $attemptedDeviceName = htmlspecialchars($currentDeviceName, ENT_QUOTES, 'UTF-8');

            // Create activity log entry for each super admin to see
            foreach ($superAdmins as $admin) {
                try {
                    app(\App\Services\ActivityLoggingService::class)->log(
                        'device_mismatch_security_alert',
                        "Security Alert: User '{$userName}' ({$userEmail}) attempted to login from a different device. Registered device: {$registeredDeviceName}. Attempted from: {$attemptedDeviceName} (IP: {$attemptedIp})",
                        $user, // Subject is the user who attempted login
                        [
                            'user_id' => $user->id,
                            'user_name' => $userName,
                            'user_email' => $userEmail,
                            'registered_device' => $registeredDeviceName,
                            'registered_device_id' => $registeredDevice->id,
                            'attempted_device' => $attemptedDeviceName,
                            'attempted_ip' => $attemptedIp,
                            'attempted_user_agent' => substr($userAgent, 0, 200), // Limit length
                            'alert_type' => 'security',
                            'severity' => 'high',
                        ],
                        $admin // Causer is the super admin (so they see it in their notifications)
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to create security alert notification for super admin', [
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            \Log::info('Security alert notifications sent to super admins', [
                'user_id' => $user->id,
                'super_admin_count' => $superAdmins->count(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to notify super admins of device mismatch', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Log the logout activity
        if ($user) {
            try {
                app(\App\Services\ActivityLoggingService::class)->logUserLogout($user);
            } catch (\Exception $e) {
                \Log::warning('Failed to log logout activity', ['error' => $e->getMessage()]);
            }
        }

        return redirect('/');
    }
}
