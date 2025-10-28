<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\BatchUpload;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BuyPowerApiService;

class HealthController extends Controller
{
    /**
     * Basic health check endpoint
     */
    public function health(): JsonResponse
    {
        try {
            // Check database connection
            DB::connection()->getPdo();
            
            // Check cache
            Cache::put('health_check', 'ok', 60);
            $cacheStatus = Cache::get('health_check') === 'ok';
            
            return response()->json([
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'checks' => [
                    'database' => 'ok',
                    'cache' => $cacheStatus ? 'ok' : 'error',
                    'api_service' => $this->checkApiService()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Health check failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'unhealthy',
                'timestamp' => now()->toISOString(),
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Detailed system status
     */
    public function status(): JsonResponse
    {
        try {
            $stats = [
                'system' => [
                    'uptime' => $this->getSystemUptime(),
                    'memory_usage' => $this->getMemoryUsage(),
                    'disk_usage' => $this->getDiskUsage()
                ],
                'database' => [
                    'total_users' => User::count(),
                    'total_batches' => BatchUpload::count(),
                    'total_transactions' => Transaction::count(),
                    'pending_batches' => BatchUpload::where('status', 'pending')->count(),
                    'processing_batches' => BatchUpload::where('status', 'processing')->count(),
                    'failed_batches' => BatchUpload::where('status', 'failed')->count()
                ],
                'api' => [
                    'status' => $this->checkApiService(),
                    'last_check' => Cache::get('api_last_check'),
                    'response_time' => Cache::get('api_response_time')
                ],
                'queue' => [
                    'pending_jobs' => $this->getQueueStatus()
                ]
            ];

            return response()->json([
                'status' => 'ok',
                'timestamp' => now()->toISOString(),
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Status check failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'timestamp' => now()->toISOString(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API metrics endpoint
     */
    public function metrics(): JsonResponse
    {
        try {
            $metrics = [
                'transactions' => [
                    'total' => Transaction::count(),
                    'successful' => Transaction::where('status', 'success')->count(),
                    'failed' => Transaction::where('status', 'failed')->count(),
                    'pending' => Transaction::where('status', 'pending')->count(),
                    'success_rate' => $this->calculateSuccessRate()
                ],
                'batches' => [
                    'total' => BatchUpload::count(),
                    'completed' => BatchUpload::where('status', 'completed')->count(),
                    'processing' => BatchUpload::where('status', 'processing')->count(),
                    'failed' => BatchUpload::where('status', 'failed')->count(),
                    'average_processing_time' => $this->getAverageProcessingTime()
                ],
                'api_performance' => [
                    'average_response_time' => Cache::get('api_avg_response_time', 0),
                    'success_rate' => Cache::get('api_success_rate', 0),
                    'last_24h_calls' => Cache::get('api_calls_24h', 0)
                ]
            ];

            return response()->json([
                'status' => 'ok',
                'timestamp' => now()->toISOString(),
                'metrics' => $metrics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check API service status
     */
    private function checkApiService(): string
    {
        try {
            $startTime = microtime(true);
            $apiService = app('buypower.api');
            $result = $apiService->getBalance();
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            Cache::put('api_last_check', now()->toISOString(), 300);
            Cache::put('api_response_time', $responseTime, 300);

            return $result['success'] ? 'ok' : 'error';
        } catch (\Exception $e) {
            Cache::put('api_last_check', now()->toISOString(), 300);
            Cache::put('api_response_time', 0, 300);
            return 'error';
        }
    }

    /**
     * Get system uptime
     */
    private function getSystemUptime(): string
    {
        try {
            $uptime = shell_exec('uptime');
            return trim($uptime) ?: 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage(): array
    {
        try {
            $memory = memory_get_usage(true);
            $peakMemory = memory_get_peak_usage(true);
            
            return [
                'current' => $this->formatBytes($memory),
                'peak' => $this->formatBytes($peakMemory),
                'limit' => ini_get('memory_limit')
            ];
        } catch (\Exception $e) {
            return ['error' => 'Unable to get memory usage'];
        }
    }

    /**
     * Get disk usage
     */
    private function getDiskUsage(): array
    {
        try {
            $totalBytes = disk_total_space('/');
            $freeBytes = disk_free_space('/');
            $usedBytes = $totalBytes - $freeBytes;
            
            return [
                'total' => $this->formatBytes($totalBytes),
                'used' => $this->formatBytes($usedBytes),
                'free' => $this->formatBytes($freeBytes),
                'percentage' => round(($usedBytes / $totalBytes) * 100, 2)
            ];
        } catch (\Exception $e) {
            return ['error' => 'Unable to get disk usage'];
        }
    }

    /**
     * Get queue status
     */
    private function getQueueStatus(): array
    {
        try {
            // This would depend on your queue driver
            return [
                'driver' => config('queue.default'),
                'pending' => 0, // Would need to implement based on queue driver
                'failed' => 0   // Would need to implement based on queue driver
            ];
        } catch (\Exception $e) {
            return ['error' => 'Unable to get queue status'];
        }
    }

    /**
     * Calculate success rate
     */
    private function calculateSuccessRate(): float
    {
        $total = Transaction::count();
        if ($total === 0) return 0;
        
        $successful = Transaction::where('status', 'success')->count();
        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get average processing time
     */
    private function getAverageProcessingTime(): float
    {
        $batches = BatchUpload::where('status', 'completed')
            ->whereNotNull('processed_at')
            ->get();

        if ($batches->isEmpty()) return 0;

        $totalTime = 0;
        foreach ($batches as $batch) {
            $totalTime += $batch->processed_at->diffInSeconds($batch->created_at);
        }

        return round($totalTime / $batches->count(), 2);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
