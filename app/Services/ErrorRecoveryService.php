<?php

namespace App\Services;

use App\Models\BatchUpload;
use App\Models\Transaction;
use App\Models\Recipient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ErrorRecoveryService
{
    /**
     * Recover failed transactions
     */
    public function recoverFailedTransactions(int $batchId = null): array
    {
        $query = Transaction::where('status', 'failed');
        
        if ($batchId) {
            $query->where('batch_upload_id', $batchId);
        }
        
        $failedTransactions = $query->get();
        $recovered = 0;
        $stillFailed = 0;
        
        foreach ($failedTransactions as $transaction) {
            try {
                $this->retryTransaction($transaction);
                $recovered++;
            } catch (Exception $e) {
                Log::error("Failed to recover transaction {$transaction->id}", [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage()
                ]);
                $stillFailed++;
            }
        }
        
        return [
            'total_failed' => $failedTransactions->count(),
            'recovered' => $recovered,
            'still_failed' => $stillFailed
        ];
    }
    
    /**
     * Retry a single transaction
     */
    public function retryTransaction(Transaction $transaction): void
    {
        DB::beginTransaction();
        
        try {
            $recipient = $transaction->recipient;
            $buyPowerService = app('buypower.api');
            
            // Generate new reference
            $newReference = 'RETRY_' . $transaction->id . '_' . time();
            
            $result = $buyPowerService->sendToken(
                $recipient->phone_number,
                (float) $recipient->amount,
                $recipient->disco,
                $recipient->meter_number,
                $recipient->meter_type,
                $recipient->customer_name,
                $recipient->address,
                $newReference
            );
            
            if ($result['success']) {
                // Update transaction
                $transaction->update([
                    'status' => 'success',
                    'token' => $result['token'],
                    'units' => $result['units'],
                    'order_id' => $result['order_id'],
                    'buypower_reference' => $newReference,
                    'api_response' => $result,
                    'error_message' => null,
                    'processed_at' => now()
                ]);
                
                // Update recipient
                $recipient->update([
                    'status' => 'success',
                    'transaction_reference' => $newReference,
                    'processed_at' => now()
                ]);
                
                Log::info("Transaction recovered successfully", [
                    'transaction_id' => $transaction->id,
                    'new_reference' => $newReference
                ]);
                
            } else {
                throw new Exception($result['error']);
            }
            
            DB::commit();
            
        } catch (Exception $e) {
            DB::rollBack();
            
            $transaction->update([
                'error_message' => 'Retry failed: ' . $e->getMessage(),
                'processed_at' => now()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Rollback failed batch
     */
    public function rollbackBatch(int $batchId): array
    {
        $batch = BatchUpload::findOrFail($batchId);
        
        if ($batch->status === 'completed') {
            throw new Exception('Cannot rollback completed batch');
        }
        
        DB::beginTransaction();
        
        try {
            // Reset all recipients to pending
            $batch->recipients()->update([
                'status' => 'pending',
                'transaction_reference' => null,
                'error_message' => null,
                'processed_at' => null
            ]);
            
            // Delete all transactions
            $batch->transactions()->delete();
            
            // Reset batch status
            $batch->update([
                'status' => 'pending',
                'processed_recipients' => 0,
                'successful_transactions' => 0,
                'failed_transactions' => 0,
                'error_message' => null
            ]);
            
            DB::commit();
            
            Log::info("Batch rolled back successfully", [
                'batch_id' => $batchId
            ]);
            
            return [
                'success' => true,
                'message' => 'Batch rolled back successfully',
                'recipients_reset' => $batch->recipients()->count()
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to rollback batch", [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Clean up orphaned transactions
     */
    public function cleanupOrphanedTransactions(): int
    {
        $orphanedTransactions = Transaction::whereDoesntHave('recipient')->get();
        $deletedCount = 0;
        
        foreach ($orphanedTransactions as $transaction) {
            $transaction->delete();
            $deletedCount++;
        }
        
        Log::info("Cleaned up orphaned transactions", [
            'deleted_count' => $deletedCount
        ]);
        
        return $deletedCount;
    }
    
    /**
     * Fix inconsistent batch statistics
     */
    public function fixBatchStatistics(int $batchId = null): array
    {
        $query = BatchUpload::query();
        
        if ($batchId) {
            $query->where('id', $batchId);
        }
        
        $batches = $query->get();
        $fixed = 0;
        
        foreach ($batches as $batch) {
            $actualStats = $this->calculateActualStats($batch);
            
            if ($this->statsAreInconsistent($batch, $actualStats)) {
                $batch->update([
                    'processed_recipients' => $actualStats['processed'],
                    'successful_transactions' => $actualStats['successful'],
                    'failed_transactions' => $actualStats['failed']
                ]);
                
                $fixed++;
                
                Log::info("Fixed batch statistics", [
                    'batch_id' => $batch->id,
                    'old_stats' => [
                        'processed' => $batch->processed_recipients,
                        'successful' => $batch->successful_transactions,
                        'failed' => $batch->failed_transactions
                    ],
                    'new_stats' => $actualStats
                ]);
            }
        }
        
        return [
            'total_batches' => $batches->count(),
            'fixed' => $fixed
        ];
    }
    
    /**
     * Calculate actual statistics for a batch
     */
    private function calculateActualStats(BatchUpload $batch): array
    {
        $recipients = $batch->recipients;
        
        return [
            'processed' => $recipients->whereIn('status', ['success', 'failed'])->count(),
            'successful' => $recipients->where('status', 'success')->count(),
            'failed' => $recipients->where('status', 'failed')->count()
        ];
    }
    
    /**
     * Check if batch statistics are inconsistent
     */
    private function statsAreInconsistent(BatchUpload $batch, array $actualStats): bool
    {
        return $batch->processed_recipients !== $actualStats['processed'] ||
               $batch->successful_transactions !== $actualStats['successful'] ||
               $batch->failed_transactions !== $actualStats['failed'];
    }
    
    /**
     * Get system health summary
     */
    public function getSystemHealthSummary(): array
    {
        return [
            'failed_transactions' => Transaction::where('status', 'failed')->count(),
            'failed_batches' => BatchUpload::where('status', 'failed')->count(),
            'processing_batches' => BatchUpload::where('status', 'processing')->count(),
            'orphaned_transactions' => Transaction::whereDoesntHave('recipient')->count(),
            'inconsistent_batches' => $this->countInconsistentBatches()
        ];
    }
    
    /**
     * Count batches with inconsistent statistics
     */
    private function countInconsistentBatches(): int
    {
        $batches = BatchUpload::all();
        $inconsistent = 0;
        
        foreach ($batches as $batch) {
            $actualStats = $this->calculateActualStats($batch);
            if ($this->statsAreInconsistent($batch, $actualStats)) {
                $inconsistent++;
            }
        }
        
        return $inconsistent;
    }
}
