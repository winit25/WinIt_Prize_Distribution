<?php

use App\Http\Controllers\BulkTokenController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('bulk-token.index');
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
    Route::get('/transaction/{transaction}/download', [BulkTokenController::class, 'downloadToken'])->name('transaction.download');
});

// Debug routes (remove in production)
if (config('app.debug')) {
    require __DIR__.'/debug.php';
    Route::post('/test-upload', [App\Http\Controllers\TestUploadController::class, 'test']);
}
