<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BulkTokenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

Route::get('/', function () {
    return redirect()->route('login');
});

// Public API routes for frontend
Route::get('/api-status-public', function () {
    // Cache API status for 30 seconds to reduce API calls
    return Cache::remember('api_status_public', 30, function () {
        try {
            $apiService = app('buypower.api');
            $apiResult = $apiService->getBalance();

            return response()->json([
                'success' => true,
                'status' => $apiResult['success'] ? 'success' : 'error',
                'message' => $apiResult['success'] ? 'API Connected' : ($apiResult['error'] ?? 'Unknown error'),
                'balance' => $apiResult['data']['balance'] ?? null,
                'api_url' => config('buypower.api_url'),
                'use_mock' => false,
                'cached_at' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage(),
                'api_url' => config('buypower.api_url'),
                'use_mock' => false,
                'cached_at' => now()->toISOString(),
            ]);
        }
    });
})->name('api-status-public');

Route::get('/dashboard-live-data-public', function () {
    try {
        $apiService = app('buypower.api');
        $apiResult = $apiService->getBalance();

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_batches' => \App\Models\BatchUpload::count(),
                'total_recipients' => \App\Models\Recipient::count(),
                'total_transactions' => \App\Models\Transaction::count(),
                'total_amount' => \App\Models\Transaction::sum('amount'),
                'success_rate' => 95,
                'processing_count' => 3,
                'failed_count' => 2,
            ],
            'api_status' => $apiResult['success'] ? 'success' : 'error',
            'message' => $apiResult['success'] ? 'API Connected' : 'API Error',
        ]);

    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Connection failed: ' . $e->getMessage(),
            'statistics' => [
                'total_batches' => 0,
                'total_recipients' => 0,
                'total_transactions' => 0,
                'total_amount' => 0,
                'success_rate' => 0,
                'processing_count' => 0,
                'failed_count' => 0,
            ],
        ]);
    }
})->name('dashboard-live-data-public');

// Public sample CSV download route
Route::get('/sample-csv', function () {
    $samplePath = base_path('sample_recipients.csv');
    
    if (!file_exists($samplePath)) {
        abort(404, 'Sample CSV file not found');
    }

    return response()->download($samplePath, 'sample_recipients.csv', [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="sample_recipients.csv"'
    ]);
})->name('sample-csv-download');

// API routes for notifications (authenticated)
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/transactions/{id}/retry', [NotificationController::class, 'retryTransaction']);
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Password Change Routes
    Route::get('/password/change', [PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/password/change', [PasswordChangeController::class, 'update'])->name('password.change.update');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management Routes
    Route::resource('users', UserController::class);

            // Activity Logs Routes
            Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
                Route::get('/', [ActivityLogController::class, 'index'])->name('index');
                Route::get('/{activityLog}', [ActivityLogController::class, 'show'])->name('show');
                Route::post('/clear', [ActivityLogController::class, 'clear'])->name('clear');
            });

            // Notification Routes
            Route::prefix('notifications')->name('notifications.')->group(function () {
                Route::get('/', [NotificationController::class, 'index'])->name('index');
                Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
            });

    // Bulk Token Routes
    Route::prefix('bulk-token')->name('bulk-token.')->group(function () {
            Route::get('/', [BulkTokenController::class, 'index'])->name('index');
            Route::post('/upload', [BulkTokenController::class, 'upload'])->name('upload');
            Route::post('/process/{batch}', [BulkTokenController::class, 'processBatch'])->name('process');
        Route::get('/status/{batch}', [BulkTokenController::class, 'getBatchStatus'])->name('status');
        Route::get('/history', [BulkTokenController::class, 'history'])->name('history');
        Route::get('/show/{batch}', [BulkTokenController::class, 'show'])->name('show');
        Route::get('/transactions', [BulkTokenController::class, 'transactions'])->name('transactions');
        Route::get('/transaction/{transaction}', [BulkTokenController::class, 'showTransaction'])->name('transaction.show');
        Route::get('/transaction/{transaction}/download', [BulkTokenController::class, 'downloadToken'])->name('transaction.download');
        Route::get('/download-sample', [BulkTokenController::class, 'downloadSample'])->name('download-sample');
    });
});

// Health check routes (public)
Route::get('/health', [HealthController::class, 'health'])->name('health');
Route::get('/status', [HealthController::class, 'status'])->name('status');
Route::get('/metrics', [HealthController::class, 'metrics'])->name('metrics');

require __DIR__.'/auth.php';
