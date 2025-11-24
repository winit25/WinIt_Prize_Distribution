<?php

namespace App\Jobs;

use App\Models\BatchUpload;
use App\Models\Recipient;
use App\Models\Transaction;
use App\Services\SecureLoggingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class ProcessBatchUploadAsync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected BatchUpload $batch;
    protected int $maxRetries;
    protected int $delayMs;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(BatchUpload $batch)
    {
        $this->batch = $batch;
        $this->maxRetries = config('buypower.max_retries', 3);
        $this->delayMs = config('buypower.delay_between_requests', 1000);
    }

    /**
     * Execute the job asynchronously
     */
    public function handle(): void
    {
        try {
            SecureLoggingService::logBatchOperation('async_processing_started', $this->batch->id, [
                'total_recipients' => $this->batch->total_recipients
            ]);

            // Update batch status to processing
            $this->batch->update(['status' => 'processing']);

            $processedCount = 0;
            $successCount = 0;
            $failedCount = 0;

            // Get pending recipients
            $recipients = $this->batch->recipients()->where('status', 'pending')->get();
            
            if ($recipients->isEmpty()) {
                Log::warning('No pending recipients for async batch', ['batch_id' => $this->batch->id]);
                return;
            }
            
            $batchSize = config('buypower.batch_size', 5);
            
            Log::info("Processing batch asynchronously in chunks of {$batchSize}", [
                'batch_id' => $this->batch->id,
                'total_recipients' => $recipients->count(),
                'batch_size' => $batchSize
            ]);

            // Process recipients in chunks
            $recipientCollection = $recipients->all();
            $totalChunks = ceil(count($recipientCollection) / $batchSize);
            
            for ($chunkIndex = 0; $chunkIndex < $totalChunks; $chunkIndex++) {
                $chunkNumber = $chunkIndex + 1;
                
                // Get the chunk for this iteration
                $chunk = array_slice($recipientCollection, $chunkIndex * $batchSize, $batchSize);
                
                Log::info("Processing async chunk {$chunkNumber}/{$totalChunks}", [
                    'batch_id' => $this->batch->id,
                    'chunk_size' => count($chunk)
                ]);
                
                // Process each recipient with DB transaction for atomicity
                foreach ($chunk as $recipient) {
                    try {
                        DB::transaction(function () use ($recipient, &$successCount, &$failedCount, &$processedCount) {
                            $this->processRecipientAsync($recipient);
                            $processedCount++;
                            
                            $recipient->refresh();
                            if ($recipient->status === 'success') {
                                $successCount++;
                            } else {
                                $failedCount++;
                            }
                        }, 3); // Retry transaction 3 times

                        // Rate limiting delay
                        if ($this->delayMs > 0) {
                            usleep($this->delayMs * 1000);
                        }

                    } catch (Exception $e) {
                        Log::error("Error processing recipient in async job", [
                            'recipient_id' => $recipient->id,
                            'batch_id' => $this->batch->id,
                            'error' => $e->getMessage()
                        ]);

                        $recipient->update([
                            'status' => 'failed',
                            'error_message' => substr($e->getMessage(), 0, 500),
                            'processed_at' => now()
                        ]);

                        $failedCount++;
                        $processedCount++;
                    }
                }
            }

            // Determine final status
            $finalStatus = $this->determineFinalStatus($processedCount, $successCount, $failedCount);

            // Update batch with final statistics
            $this->batch->update([
                'processed_recipients' => $processedCount,
                'successful_transactions' => $successCount,
                'failed_transactions' => $failedCount,
                'status' => $finalStatus
            ]);

            SecureLoggingService::logBatchOperation('async_processing_completed', $this->batch->id, [
                'processed' => $processedCount,
                'successful' => $successCount,
                'failed' => $failedCount,
                'final_status' => $finalStatus
            ]);

        } catch (Exception $e) {
            Log::error('Async batch processing error', [
                'batch_id' => $this->batch->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->batch->update([
                'status' => 'failed',
                'notes' => 'Async processing failed: ' . substr($e->getMessage(), 0, 255)
            ]);

            // Retry job if attempts remain
            if ($this->attempts() < $this->tries) {
                $this->release(60); // Release back to queue after 60 seconds
            }
        }
    }

    /**
     * Process individual recipient with transaction support
     */
    private function processRecipientAsync(Recipient $recipient): void
    {
        $apiService = app('buypower.api');

        try {
            $recipient->update(['status' => 'processing']);

            $response = $apiService->createElectricityOrder(
                $recipient->phone_number,
                $recipient->disco,
                $recipient->amount,
                $recipient->meter_number,
                $recipient->meter_type ?? 'prepaid',
                $recipient->customer_name,
                $recipient->address
            );

            if ($response['success'] && isset($response['token'])) {
                Transaction::create([
                    'batch_upload_id' => $recipient->batch_upload_id,
                    'recipient_id' => $recipient->id,
                    'buypower_reference' => $response['reference'] ?? '',
                    'order_id' => $response['order_id'] ?? '',
                    'phone_number' => $recipient->phone_number,
                    'amount' => $recipient->amount,
                    'status' => 'success',
                    'token' => $response['token'],
                    'units' => $response['units'] ?? null,
                    'api_response' => $response['data'] ?? [],
                    'processed_at' => now()
                ]);

                $recipient->update([
                    'status' => 'success',
                    'processed_at' => now()
                ]);

                SecureLoggingService::logTransaction('success', $recipient->id, [
                    'batch_id' => $recipient->batch_upload_id
                ]);

            } else {
                $errorMsg = $response['error'] ?? 'Unknown error';
                
                Transaction::create([
                    'batch_upload_id' => $recipient->batch_upload_id,
                    'recipient_id' => $recipient->id,
                    'buypower_reference' => $response['reference'] ?? 'N/A',
                    'phone_number' => $recipient->phone_number,
                    'amount' => $recipient->amount,
                    'status' => 'failed',
                    'error_message' => $errorMsg,
                    'api_response' => $response['data'] ?? [],
                    'processed_at' => now()
                ]);

                $recipient->update([
                    'status' => 'failed',
                    'error_message' => substr($errorMsg, 0, 500),
                    'processed_at' => now()
                ]);

                SecureLoggingService::logTransaction('failed', $recipient->id, [
                    'batch_id' => $recipient->batch_upload_id,
                    'error' => $errorMsg
                ]);
            }

        } catch (Exception $e) {
            Log::error('Transaction processing error', [
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage()
            ]);

            $recipient->update([
                'status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 500),
                'processed_at' => now()
            ]);

            throw $e;
        }
    }

    /**
     * Determine final batch status
     */
    private function determineFinalStatus(int $processed, int $successful, int $failed): string
    {
        if ($processed === 0) {
            return 'failed';
        }

        if ($processed >= $this->batch->total_recipients) {
            return $successful > 0 ? 'completed' : 'failed';
        }

        $successRate = ($successful / $processed) * 100;
        
        return $successRate >= 50 ? 'completed' : 'failed';
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception): void
    {
        Log::error('Async batch job failed permanently', [
            'batch_id' => $this->batch->id,
            'error' => $exception->getMessage()
        ]);

        $this->batch->update([
            'status' => 'failed',
            'notes' => 'Async job failed after all retries'
        ]);
    }
}
