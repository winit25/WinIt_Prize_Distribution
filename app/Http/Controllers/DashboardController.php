<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BatchUpload;
use App\Models\Recipient;
use App\Models\Transaction;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\DeviceFingerprint;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // IMPORTANT: Register device fingerprint ONLY when user accesses dashboard
        // This ensures fingerprint is activated after:
        // 1. Successful login
        // 2. Password change (if required for first-time users)
        // 3. User successfully reaches dashboard
        $user = auth()->user();
        if ($user) {
            // Only register if user doesn't have must_change_password flag
            // (meaning they've completed password change if it was required)
            if (!$user->must_change_password) {
                $this->registerDeviceFingerprint($request, $user);
            }
        }
        
        // Cache dashboard statistics for 2 minutes to improve performance
        $cacheKey = 'dashboard_stats_' . auth()->id();
        
        $stats = Cache::remember($cacheKey, 120, function () {
            // Get comprehensive BuyPower metrics with live data
            $totalTransactions = Transaction::count();
            $successfulTransactions = Transaction::where('status', 'success')->count();
            $totalAmount = Transaction::where('status', 'success')->sum('amount');
            $totalUnits = Transaction::where('status', 'success')->whereNotNull('units')->sum(DB::raw('CAST(units AS DECIMAL(10,2))'));
            
            // Recipient statistics
            $uniqueRecipients = Recipient::distinct('phone_number')->count();
            $recipientsWithTokens = Transaction::where('status', 'success')->distinct('recipient_id')->count();
            
            // Batch statistics
            $completedBatches = BatchUpload::where('status', 'completed')->count();
            $processingBatches = BatchUpload::where('status', 'processing')->count();
            $failedBatches = BatchUpload::where('status', 'failed')->count();
            
            // Disco distribution
            $discoStats = Transaction::join('recipients', 'transactions.recipient_id', '=', 'recipients.id')
                ->where('transactions.status', 'success')
                ->selectRaw('recipients.disco, COUNT(*) as count, SUM(transactions.amount) as total_amount')
                ->groupBy('recipients.disco')
                ->get()
                ->keyBy('disco');
            
            // Today's statistics
            $todayTransactions = Transaction::whereDate('created_at', today())->count();
            $todayAmount = Transaction::whereDate('created_at', today())->where('status', 'success')->sum('amount');
            
            // This month's statistics
            $monthTransactions = Transaction::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            $monthAmount = Transaction::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('status', 'success')
                ->sum('amount');
            
            // Average transaction amount
            $avgTransactionAmount = $successfulTransactions > 0 ? $totalAmount / $successfulTransactions : 0;
            
            return [
                'totalBatches' => BatchUpload::count(),
                'completedBatches' => $completedBatches,
                'processingBatches' => $processingBatches,
                'failedBatches' => $failedBatches,
                'totalRecipients' => Recipient::count(),
                'uniqueRecipients' => $uniqueRecipients,
                'recipientsWithTokens' => $recipientsWithTokens,
                'totalTransactions' => $totalTransactions,
                'totalAmount' => $totalAmount,
                'totalUnits' => $totalUnits,
                'successfulTransactions' => $successfulTransactions,
                'processingCount' => Transaction::where('status', 'processing')->count(),
                'failedCount' => Transaction::where('status', 'failed')->count(),
                'pendingCount' => Transaction::where('status', 'pending')->count(),
                'discoStats' => $discoStats,
                'todayTransactions' => $todayTransactions,
                'todayAmount' => $todayAmount,
                'monthTransactions' => $monthTransactions,
                'monthAmount' => $monthAmount,
                'avgTransactionAmount' => $avgTransactionAmount,
            ];
        });
        
        // Calculate success rate
        $successRate = $stats['totalTransactions'] > 0 ? 
            round(($stats['successfulTransactions'] / $stats['totalTransactions']) * 100, 1) : 0;
        
        // Cache recent data for 1 minute
        $recentData = Cache::remember('dashboard_recent_' . auth()->id(), 60, function () {
            return [
                'recentBatches' => BatchUpload::with('user')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(),
                'recentActivity' => ActivityLog::with('causer')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get()
            ];
        });

        // Cache chart data for 5 minutes
        $chartData = Cache::remember('dashboard_chart_' . auth()->id(), 300, function () {
            // Get transaction data for the last 7 days
            $last7Days = collect(range(6, 0))->map(function ($days) {
                return now()->subDays($days)->format('Y-m-d');
            });

            $dailyTransactions = Transaction::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total_amount')
                ->where('created_at', '>=', now()->subDays(6))
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            $dailyAmounts = Transaction::selectRaw('DATE(created_at) as date, SUM(amount) as total_amount')
                ->where('created_at', '>=', now()->subDays(6))
                ->groupBy('date')
                ->pluck('total_amount', 'date')
                ->toArray();

            // Get status distribution
            $statusDistribution = Transaction::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Get disco distribution
            $discoDistribution = Transaction::join('recipients', 'transactions.recipient_id', '=', 'recipients.id')
                ->selectRaw('recipients.disco, COUNT(*) as count')
                ->groupBy('recipients.disco')
                ->pluck('count', 'disco')
                ->toArray();

            return [
                'dailyTransactions' => $last7Days->map(function ($date) use ($dailyTransactions) {
                    return $dailyTransactions[$date] ?? 0;
                })->values()->toArray(),
                'dailyAmounts' => $last7Days->map(function ($date) use ($dailyAmounts) {
                    return $dailyAmounts[$date] ?? 0;
                })->values()->toArray(),
                'labels' => $last7Days->map(function ($date) {
                    return now()->parse($date)->format('M d');
                })->values()->toArray(),
                'statusDistribution' => $statusDistribution,
                'discoDistribution' => $discoDistribution
            ];
        });

        return view('dashboard', array_merge($stats, [
            'successRate' => $successRate,
            'recentBatches' => $recentData['recentBatches'],
            'recentActivity' => $recentData['recentActivity'],
            'chartData' => $chartData
        ]));
    }

    /**
     * Register or update device fingerprint for the authenticated user.
     * This is called when user first accesses the dashboard after login.
     */
    private function registerDeviceFingerprint(Request $request, $user): void
    {
        try {
            // Check if device_fingerprints table exists
            try {
                DB::select('SELECT 1 FROM device_fingerprints LIMIT 1');
            } catch (\Exception $e) {
                // Table doesn't exist yet - skip registration
                \Log::info('Device fingerprints table not found, skipping device registration', [
                    'error' => $e->getMessage(),
                ]);
                return;
            }

            // Try to get fingerprint from header first (for AJAX), then from sessionStorage (sent via form)
            $deviceFingerprint = $request->header('X-Device-Fingerprint') 
                ?? $request->input('_device_fingerprint');

            // Sanitize fingerprint if present
            if ($deviceFingerprint) {
                $deviceFingerprint = trim($deviceFingerprint);
                // Remove null bytes and control characters
                $deviceFingerprint = preg_replace('/[\x00-\x1F\x7F]/', '', $deviceFingerprint);
            }
            
            if (!$deviceFingerprint) {
                \Log::warning('Device fingerprint not provided during dashboard access', [
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
            $existingDevice = DeviceFingerprint::where('user_id', $user->id)
                ->where('fingerprint_hash', $fingerprintHash)
                ->first();

            if ($existingDevice) {
                // Update existing device (same device accessing dashboard again)
                $existingDevice->update([
                    'last_used_at' => now(),
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'is_active' => true,
                ]);
            } else {
                // This is a new device - register it
                DeviceFingerprint::create([
                    'user_id' => $user->id,
                    'fingerprint_hash' => $fingerprintHash,
                    'device_name' => $deviceName,
                    'user_agent' => $userAgent,
                    'ip_address' => $ipAddress,
                    'last_used_at' => now(),
                    'is_active' => true,
                ]);

                \Log::info('Device fingerprint registered for user on dashboard access', [
                    'user_id' => $user->id,
                    'device_name' => $deviceName,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to register device fingerprint', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw exception - allow dashboard access even if device registration fails
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
}
