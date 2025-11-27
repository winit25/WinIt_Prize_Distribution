<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BulkTokenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UploadCsvController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('landing');
});

// Temporary debug route
Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'success' => true,
            'message' => 'Database connection successful',
            'database' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/login-redirect', function () {
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
                'api_connected' => $apiResult['data']['api_connected'] ?? $apiResult['success'],
                'api_url' => config('buypower.api_url'),
                'use_mock' => false,
                'cached_at' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            // Log error for debugging but don't expose sensitive details
            \Log::error('API Status Check Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Connection failed. Please try again later.',
                'api_url' => config('buypower.api_url'),
                'use_mock' => false,
                'cached_at' => now()->toISOString(),
            ]);
        }
    });
})->name('api-status-public');

Route::get('/dashboard-live-data-public', function () {
    try {
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

// API routes (authenticated)
Route::middleware(['auth'])->prefix('api')->group(function () {
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/transactions/{id}/retry', [NotificationController::class, 'retryTransaction']);

    // CSV Upload (API alias) - explicitly permission-guarded
    Route::post('/uploads/csv', [BulkTokenController::class, 'upload'])
        ->middleware('check.permission:upload-csv')
        ->name('api.uploads.csv');
});

// Password Change Routes (must be outside force.password.change middleware to allow access)
Route::middleware(['auth', 'verified', 'device.fingerprint'])->group(function () {
    Route::get('/password/change', [PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/password/change', [PasswordChangeController::class, 'update'])->name('password.change.update');
});

// Bulk Token Password Verification Route (must be outside force.password.change middleware)
Route::middleware(['auth'])->group(function () {
    Route::post('/bulk-token/verify-password', [BulkTokenController::class, 'verifyPassword'])
        ->name('bulk-token.verify-password');
    Route::post('/bulk-airtime/verify-password', [\App\Http\Controllers\BulkAirtimeController::class, 'verifyPassword'])
        ->name('bulk-airtime.verify-password');
    Route::post('/bulk-dstv/verify-password', [\App\Http\Controllers\BulkDstvController::class, 'verifyPassword'])
        ->name('bulk-dstv.verify-password');
});

Route::middleware(['auth', 'verified', 'device.fingerprint', 'force.password.change'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Search and Filter Routes
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('/', [SearchController::class, 'search'])->name('index');
        Route::get('/options', [SearchController::class, 'getFilterOptions'])->name('options');
    });
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management Routes
    Route::resource('users', UserController::class)->parameters([
        'users' => 'id'
    ]);
    Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])
        ->middleware(['throttle:3,1', 'check.permission:manage-users'])
        ->name('users.reset-password');
    Route::get('/api/users', [UserController::class, 'apiIndex'])->name('users.api.index');
    
    // Device Fingerprint Management Routes (Admin only)
    Route::prefix('device-fingerprints')->name('device-fingerprints.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DeviceFingerprintController::class, 'index'])
            ->middleware(['check.permission:manage-users', 'throttle:60,1'])
            ->name('index');
        Route::get('/{deviceFingerprint}', [\App\Http\Controllers\DeviceFingerprintController::class, 'show'])
            ->middleware(['check.permission:manage-users', 'throttle:60,1'])
            ->name('show');
        Route::post('/{deviceFingerprint}/deactivate', [\App\Http\Controllers\DeviceFingerprintController::class, 'deactivate'])
            ->middleware(['check.permission:manage-users', 'throttle:10,1'])
            ->name('deactivate');
        Route::post('/user/{user}/reset', [\App\Http\Controllers\DeviceFingerprintController::class, 'resetUserDevice'])
            ->middleware(['check.permission:manage-users', 'throttle:10,1'])
            ->name('reset-user');
        Route::delete('/{deviceFingerprint}', [\App\Http\Controllers\DeviceFingerprintController::class, 'destroy'])
            ->middleware(['check.permission:manage-users', 'throttle:10,1'])
            ->name('destroy');
    });

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

    // CSV Upload Routes
    Route::middleware('check.permission:upload-csv')->prefix('upload-csv')->name('upload_csv.')->group(function () {
        Route::get('/', [UploadCsvController::class, 'showUploadForm'])->name('form');
        Route::post('/', [UploadCsvController::class, 'processUpload'])->name('process');
        Route::get('/template', [UploadCsvController::class, 'downloadTemplate'])->name('download_template');
    });

    // Bulk Token Routes
    Route::prefix('bulk-token')->name('bulk-token.')->group(function () {
        Route::get('/download-report/{batchId}', [BulkTokenController::class, 'downloadBatchReport'])->name('download-report');
        Route::post('/status-update', [BulkTokenController::class, 'statusUpdate'])->name('status-update');
            Route::get('/', [BulkTokenController::class, 'index'])
                ->middleware('check.permission:upload-csv')
                ->name('index');
            Route::post('/upload', [BulkTokenController::class, 'upload'])
                ->middleware('check.permission:upload-csv')
                ->name('upload');
            Route::post('/process/{batch}', [BulkTokenController::class, 'processBatch'])->name('process');
        Route::get('/status/{batch}', [BulkTokenController::class, 'getBatchStatus'])->name('status');
        Route::get('/history', [BulkTokenController::class, 'history'])->name('history');
        Route::get('/show/{batch}', [BulkTokenController::class, 'show'])->name('show');
        Route::get('/transactions', [BulkTokenController::class, 'transactions'])->name('transactions');
        Route::get('/transaction/{transaction}', [BulkTokenController::class, 'showTransaction'])->name('transaction.show');
        Route::get('/transaction/{transaction}/download', [BulkTokenController::class, 'downloadToken'])->name('transaction.download');
        Route::get('/download-sample', [BulkTokenController::class, 'downloadSample'])->name('download-sample');
    });

    // Bulk Airtime Routes
    Route::prefix('bulk-airtime')->name('bulk-airtime.')->group(function () {
        Route::post('/verify-password', [\App\Http\Controllers\BulkAirtimeController::class, 'verifyPassword'])
            ->middleware('auth')
            ->name('verify-password');
        Route::get('/', [\App\Http\Controllers\BulkAirtimeController::class, 'index'])
            ->middleware('check.permission:upload-csv')
            ->name('index');
        Route::post('/upload', [\App\Http\Controllers\BulkAirtimeController::class, 'upload'])
            ->middleware('check.permission:upload-csv')
            ->name('upload');
        Route::post('/process/{batch}', [\App\Http\Controllers\BulkAirtimeController::class, 'processBatch'])->name('process');
        Route::get('/status/{batch}', [\App\Http\Controllers\BulkAirtimeController::class, 'getBatchStatus'])->name('status');
        Route::get('/history', [\App\Http\Controllers\BulkAirtimeController::class, 'history'])
            ->middleware('check.permission:view-transactions')
            ->name('history');
        Route::get('/show/{batch}', [\App\Http\Controllers\BulkAirtimeController::class, 'show'])
            ->middleware('check.permission:view-transactions')
            ->name('show');
        Route::get('/transactions', [\App\Http\Controllers\BulkAirtimeController::class, 'transactions'])
            ->middleware('check.permission:view-transactions')
            ->name('transactions');
        Route::get('/download-sample', [\App\Http\Controllers\BulkAirtimeController::class, 'downloadSample'])->name('download-sample');
    });

    // Bulk DSTV Routes
    Route::prefix('bulk-dstv')->name('bulk-dstv.')->group(function () {
        Route::get('/', [\App\Http\Controllers\BulkDstvController::class, 'index'])
            ->middleware('check.permission:upload-csv')
            ->name('index');
        Route::post('/upload', [\App\Http\Controllers\BulkDstvController::class, 'upload'])
            ->middleware('check.permission:upload-csv')
            ->name('upload');
        Route::post('/process/{batch}', [\App\Http\Controllers\BulkDstvController::class, 'processBatch'])->name('process');
        Route::get('/status/{batch}', [\App\Http\Controllers\BulkDstvController::class, 'getBatchStatus'])->name('status');
        Route::get('/download-report/{batchId}', [\App\Http\Controllers\BulkDstvController::class, 'downloadBatchReport'])->name('download-report');
        Route::get('/history', [\App\Http\Controllers\BulkDstvController::class, 'history'])
            ->middleware('check.permission:view-transactions')
            ->name('history');
        Route::get('/show/{batch}', [\App\Http\Controllers\BulkDstvController::class, 'show'])
            ->middleware('check.permission:view-transactions')
            ->name('show');
        Route::get('/transactions', [\App\Http\Controllers\BulkDstvController::class, 'transactions'])
            ->middleware('check.permission:view-transactions')
            ->name('transactions');
        Route::get('/download-sample', [\App\Http\Controllers\BulkDstvController::class, 'downloadSample'])->name('download-sample');
    });
});

// Permissions management routes (Super Admin only)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/permissions', [App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions', [App\Http\Controllers\PermissionController::class, 'store'])->name('permissions.store');
    Route::put('/permissions/{permission}', [App\Http\Controllers\PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [App\Http\Controllers\PermissionController::class, 'destroy'])->name('permissions.destroy');
    
    Route::post('/permissions/roles', [App\Http\Controllers\PermissionController::class, 'storeRole'])->name('permissions.roles.store');
    Route::put('/permissions/roles/{role}', [App\Http\Controllers\PermissionController::class, 'updateRole'])->name('permissions.roles.update');
    Route::delete('/permissions/roles/{role}', [App\Http\Controllers\PermissionController::class, 'destroyRole'])->name('permissions.roles.destroy');
    
    Route::post('/permissions/roles/{role}/permissions', [App\Http\Controllers\PermissionController::class, 'assignPermissionsToRole'])->name('permissions.roles.permissions');
    Route::post('/permissions/users/{id}/roles', [App\Http\Controllers\PermissionController::class, 'assignRolesToUser'])->name('permissions.users.roles');
    
    // Notification routes
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/retry-transaction/{transactionId}', [App\Http\Controllers\NotificationController::class, 'retryTransaction'])->name('notifications.retry-transaction');
});

// API routes for notifications
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'apiIndex'])->name('api.notifications.index');
    Route::post('/notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('api.notifications.mark-read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read');
    Route::post('/notifications/retry-transaction/{transactionId}', [App\Http\Controllers\NotificationController::class, 'retryTransaction'])->name('api.notifications.retry-transaction');
});

// Health check routes (public)
Route::get('/health', [HealthController::class, 'health'])->name('health');
Route::get('/status', [HealthController::class, 'status'])->name('status');
Route::get('/metrics', [HealthController::class, 'metrics'])->name('metrics');

require __DIR__.'/auth.php';
