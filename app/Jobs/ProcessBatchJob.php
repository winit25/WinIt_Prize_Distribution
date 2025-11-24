<?php

namespace App\Jobs;

use App\Models\BatchUpload;
use App\Models\Recipient;
use App\Models\Transaction;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class ProcessBatchJob implements ShouldQueue
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
    public $timeout = 300; // 5 minutes

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
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Starting synchronous batch processing", [
                'batch_id' => $this->batch->id,
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
                Log::warning("No pending recipients found for batch", [
                    'batch_id' => $this->batch->id
                ]);
                return;
            }
            
            // Get batch size from config (default: 5 recipients per batch)
            // This determines how many recipients are processed together before moving to next batch
            $batchSize = config('buypower.batch_size', 5);
            
            Log::info("Processing recipients synchronously in batches of {$batchSize}", [
                'batch_id' => $this->batch->id,
                'total_recipients' => $recipients->count(),
                'batch_size' => $batchSize,
                'total_batches' => ceil($recipients->count() / $batchSize)
            ]);

            // Process recipients in batches (chunks) SYNCHRONOUSLY
            // Each batch completes fully before the next batch starts
            $recipientCollection = $recipients->all();
            $totalBatches = ceil(count($recipientCollection) / $batchSize);
            
            for ($batchIndex = 0; $batchIndex < $totalBatches; $batchIndex++) {
                $batchNumber = $batchIndex + 1;
                
                // Get the chunk for this batch
                $batchChunk = array_slice($recipientCollection, $batchIndex * $batchSize, $batchSize);
                
                Log::info("Processing batch {$batchNumber}/{$totalBatches} (synchronously)", [
                    'batch_id' => $this->batch->id,
                    'batch_number' => $batchNumber,
                    'chunk_size' => count($batchChunk),
                    'processed_so_far' => $processedCount,
                    'success_so_far' => $successCount,
                    'failed_so_far' => $failedCount
                ]);
                
                // Process each recipient in this batch sequentially (one after another)
                // This ensures synchronous processing - each recipient completes before next starts
                foreach ($batchChunk as $recipient) {
                    try {
                        // Process this recipient (synchronous - waits for completion)
                        $this->processRecipient($recipient);
                        $processedCount++;

                        // Refresh recipient to get latest status
                        $recipient->refresh();
                        if ($recipient->status === 'success') {
                            $successCount++;
                        } else {
                            $failedCount++;
                        }

                        // Add delay between individual requests (rate limiting)
                        if ($this->delayMs > 0) {
                            usleep($this->delayMs * 1000);
                        }

                    } catch (Exception $e) {
                        Log::error("Error processing recipient {$recipient->id} in batch {$batchNumber}", [
                            'recipient_id' => $recipient->id,
                            'batch_number' => $batchNumber,
                            'error' => $e->getMessage()
                        ]);

                        $recipient->update([
                            'status' => 'failed',
                            'error_message' => $e->getMessage(),
                            'processed_at' => now()
                        ]);

                        $failedCount++;
                        $processedCount++;
                    }
                }
                
                // Batch complete - log progress
                // Next batch will only start after this batch is fully complete
                Log::info("Completed batch {$batchNumber}/{$totalBatches} - All recipients in this batch processed", [
                    'batch_id' => $this->batch->id,
                    'batch_number' => $batchNumber,
                    'processed_in_batch' => count($batchChunk),
                    'total_processed' => $processedCount,
                    'total_successful' => $successCount,
                    'total_failed' => $failedCount
                ]);
            }

            // Determine final batch status
            $finalStatus = 'processing';
            
            if ($processedCount >= $this->batch->total_recipients) {
                // All recipients have been processed
                if ($successCount > 0) {
                    // At least one successful - mark as completed
                    $finalStatus = 'completed';
                } else if ($failedCount > 0) {
                    // All failed - mark as failed
                    $finalStatus = 'failed';
                }
            } else {
                // Not all recipients processed - batch is incomplete
                // Mark as failed if processing didn't complete
                if ($processedCount > 0) {
                    // Some processing happened but didn't complete
                    // Check failure rate - if >90% failed or no successes, mark as failed
                    $failureRate = ($failedCount / $processedCount) * 100;
                    if ($failureRate >= 90 || $successCount == 0) {
                        $finalStatus = 'failed';
                    } else {
                        // Still processing - keep as processing status for retry
                        $finalStatus = 'processing';
                    }
                } else {
                    // No recipients processed at all - likely an error
                    $finalStatus = 'failed';
                }
            }

            // Update batch statistics
            $oldStatus = $this->batch->status;
            $this->batch->update([
                'processed_recipients' => $processedCount,
                'successful_transactions' => $successCount,
                'failed_transactions' => $failedCount,
                'status' => $finalStatus
            ]);

            // Log batch status change activity
            if ($oldStatus !== $finalStatus) {
                try {
                    app(\App\Services\ActivityLoggingService::class)->logBatchStatusChanged(
                        $this->batch->fresh(),
                        $oldStatus,
                        $finalStatus
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to log batch status change activity', ['error' => $e->getMessage()]);
                }
            }

            Log::info("Batch processing completed", [
                'batch_id' => $this->batch->id,
                'processed' => $processedCount,
                'successful' => $successCount,
                'failed' => $failedCount,
                'final_status' => $finalStatus,
                'total_recipients' => $this->batch->total_recipients
            ]);

            // Send batch completion notification to user
            try {
                $this->sendBatchCompletionNotification($finalStatus, $successCount, $failedCount, $processedCount);
            } catch (Exception $notificationError) {
                Log::error("Failed to send batch completion notification", [
                    'batch_id' => $this->batch->id,
                    'error' => $notificationError->getMessage()
                ]);
                // Don't fail the entire batch processing if notification fails
            }

        } catch (Exception $e) {
            Log::error("Batch processing failed", [
                'batch_id' => $this->batch->id,
                'error' => $e->getMessage()
            ]);

            $this->batch->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            // Send failure notification
            try {
                $this->sendBatchCompletionNotification('failed', 0, $this->batch->total_recipients, 0, $e->getMessage());
            } catch (Exception $notificationError) {
                Log::error("Failed to send batch failure notification", [
                    'batch_id' => $this->batch->id,
                    'error' => $notificationError->getMessage()
                ]);
            }

            throw $e;
        }
    }

    /**
     * Send batch completion notification to user
     */
    protected function sendBatchCompletionNotification(string $status, int $successCount, int $failedCount, int $processedCount, ?string $errorMessage = null): void
    {
        try {
            $user = $this->batch->user;
            if (!$user || !$user->email) {
                Log::warning("Cannot send batch completion notification - user or email not found", [
                    'batch_id' => $this->batch->id,
                    'user_id' => $this->batch->user_id
                ]);
                return;
            }

            $notificationService = app(NotificationService::class);
            
            if ($status === 'completed') {
                // Success notification
                $subject = "✅ Batch Processing Completed Successfully";
                $message = "Your batch '{$this->batch->batch_name}' has been processed successfully!\n\n";
                $message .= "Summary:\n";
                $message .= "- Total Recipients: {$this->batch->total_recipients}\n";
                $message .= "- Successful: {$successCount}\n";
                $message .= "- Failed: {$failedCount}\n";
                $message .= "- Success Rate: " . round(($successCount / $processedCount) * 100, 1) . "%\n";
                $message .= "- Total Amount: ₦" . number_format($this->batch->total_amount, 2) . "\n\n";
                // Use appropriate route based on batch type
                if ($this->batch->batch_type === 'airtime') {
                    $viewRoute = route('bulk-airtime.show', $this->batch->id);
                } elseif ($this->batch->batch_type === 'dstv') {
                    $viewRoute = route('bulk-dstv.show', $this->batch->id);
                } else {
                    $viewRoute = route('bulk-token.show', $this->batch->id);
                }
                $message .= "View details: " . $viewRoute;
                
                // Pass batch_id to notification service
                $notificationService->sendBatchNotification($user, $subject, $message, 'success', $this->batch->id);
                
            } elseif ($status === 'failed') {
                // Failure notification
                $subject = "❌ Batch Processing Failed";
                $message = "Your batch '{$this->batch->batch_name}' processing has failed.\n\n";
                $message .= "Summary:\n";
                $message .= "- Total Recipients: {$this->batch->total_recipients}\n";
                $message .= "- Processed: {$processedCount}\n";
                $message .= "- Successful: {$successCount}\n";
                $message .= "- Failed: {$failedCount}\n";
                if ($errorMessage) {
                    $message .= "- Error: {$errorMessage}\n";
                }
                // Use appropriate route based on batch type
                if ($this->batch->batch_type === 'airtime') {
                    $viewRoute = route('bulk-airtime.show', $this->batch->id);
                } elseif ($this->batch->batch_type === 'dstv') {
                    $viewRoute = route('bulk-dstv.show', $this->batch->id);
                } else {
                    $viewRoute = route('bulk-token.show', $this->batch->id);
                }
                $message .= "\nView details: " . $viewRoute;
                
                // Pass batch_id to notification service
                $notificationService->sendBatchNotification($user, $subject, $message, 'failed', $this->batch->id);
            }

            Log::info("Batch completion notification sent", [
                'batch_id' => $this->batch->id,
                'status' => $status,
                'user_email' => $user->email
            ]);

        } catch (Exception $e) {
            Log::error("Error sending batch completion notification", [
                'batch_id' => $this->batch->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process a single recipient
     */
    protected function processRecipient(Recipient $recipient): void
    {
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            $attempt++;

            try {
                // Update recipient status to processing
                $recipient->update(['status' => 'processing']);

                // Generate unique reference
                $reference = 'BP_' . $this->batch->id . '_' . $recipient->id . '_' . time();

                // Get BuyPower API service
                $buyPowerService = app('buypower.api');

                // Check batch type and call appropriate method
                if ($this->batch->batch_type === 'airtime') {
                    // Process airtime top-up
                    $result = $buyPowerService->topUpAirtime(
                        $recipient->phone_number,
                        (float) $recipient->amount,
                        $recipient->customer_name ?? $recipient->name,
                        $reference
                    );
                } elseif ($this->batch->batch_type === 'dstv') {
                    // Process DSTV subscription
                    $result = $buyPowerService->vendDstv(
                        $recipient->phone_number,
                        $recipient->meter_number, // DSTV smartcard number
                        (float) $recipient->amount,
                        $recipient->customer_name ?? $recipient->name,
                        $recipient->email,
                        $reference,
                        $recipient->disco ?? 'DSTV', // Should be "DSTV"
                        null // No tariff class for DSTV
                    );
                } else {
                    // Process electricity token (default)
                    $result = $buyPowerService->sendToken(
                        $recipient->phone_number,
                        (float) $recipient->amount,
                        $recipient->disco,
                        $recipient->meter_number,
                        $recipient->meter_type,
                        $recipient->customer_name,
                        $recipient->address,
                        $reference
                    );
                }

                // Create transaction record
                $transaction = Transaction::create([
                    'recipient_id' => $recipient->id,
                    'batch_upload_id' => $this->batch->id,
                    'buypower_reference' => $reference,
                    'phone_number' => $recipient->phone_number,
                    'amount' => $recipient->amount,
                    'status' => $result['success'] ? 'success' : 'failed',
                    'token' => $result['token'] ?? null,
                    'units' => $result['units'] ?? null,
                    'order_id' => $result['order_id'] ?? null,
                    'api_response' => $result,
                    'error_message' => $result['success'] ? null : $result['error'],
                    'processed_at' => now()
                ]);

                if ($result['success']) {
                    // Update recipient status
                    $recipient->update([
                        'status' => 'success',
                        'transaction_reference' => $reference,
                        'processed_at' => now()
                    ]);

                    // Send notifications with batch settings
                    $notificationService = app(NotificationService::class);
                    $batch = $this->batch->fresh(); // Reload batch to get latest settings
                    
                    $enableSms = $batch->enable_sms ?? true;
                    $enableEmail = $batch->enable_email ?? true;
                    
                    Log::info("Sending notifications for recipient", [
                        'recipient_id' => $recipient->id,
                        'phone' => $recipient->phone_number,
                        'email' => $recipient->email,
                        'enable_sms' => $enableSms,
                        'enable_email' => $enableEmail
                    ]);
                    
                    // Customize notification message based on batch type
                    if ($this->batch->batch_type === 'airtime') {
                        $notificationMessage = "Successfully sent ₦{$recipient->amount} airtime top-up to {$recipient->phone_number}";
                    } elseif ($this->batch->batch_type === 'dstv') {
                        $notificationMessage = "Successfully sent ₦{$recipient->amount} DSTV subscription to {$recipient->phone_number}";
                    } else {
                        $notificationMessage = "Successfully sent ₦{$recipient->amount} electricity token to {$recipient->phone_number}";
                    }
                    
                    $notificationResults = $notificationService->sendTransactionNotifications(
                        $transaction,
                        $notificationMessage,
                        $enableSms, // Use batch SMS setting
                        $enableEmail, // Use batch Email setting
                        $batch->sms_template ?? null, // Use batch SMS template
                        $batch->email_template ?? null // Use batch Email template
                    );
                    
                    Log::info("Notification results for recipient", [
                        'recipient_id' => $recipient->id,
                        'sms_result' => $notificationResults['sms'] ?? null,
                        'email_result' => $notificationResults['email'] ?? null
                    ]);

                    Log::info("Successfully processed recipient", [
                        'recipient_id' => $recipient->id,
                        'phone' => $recipient->phone_number,
                        'email' => $recipient->email,
                        'amount' => $recipient->amount,
                        'token' => $result['token']
                    ]);

                    return; // Success, exit retry loop

                } else {
                    // Check if it's a 401 error (API key issue) - don't retry, fail immediately
                    if (isset($result['status_code']) && $result['status_code'] === 401) {
                        Log::error("BuyPower API authentication failed - stopping batch processing", [
                            'recipient_id' => $recipient->id,
                            'error' => $result['error'],
                            'requires_config' => $result['requires_config'] ?? false
                        ]);
                        
                        $recipient->update([
                            'status' => 'failed',
                            'error_message' => $result['error'],
                            'processed_at' => now()
                        ]);

                        // Stop entire batch processing for 401 errors
                        throw new Exception("BuyPower API authentication failed. Please configure your API key: " . $result['error']);
                    }
                    
                    // Check if it's a validation error (400) - don't retry, fail immediately
                    if (isset($result['status_code']) && $result['status_code'] === 400) {
                        Log::error("BuyPower API validation error - skipping recipient", [
                            'recipient_id' => $recipient->id,
                            'error' => $result['error'],
                            'validation_errors' => $result['validation_errors'] ?? []
                        ]);
                        
                        $recipient->update([
                            'status' => 'failed',
                            'error_message' => $result['error'],
                            'processed_at' => now()
                        ]);

                        // Don't retry validation errors, move to next recipient
                        return;
                    }
                    
                    // Check if it's a retryable 500 error
                    $isRetryable = isset($result['is_retryable']) && $result['is_retryable'] === true;
                    $is500Error = isset($result['status_code']) && $result['status_code'] === 500;
                    
                    // Log failure
                    Log::warning("Attempt {$attempt} failed for {$recipient->phone_number}", [
                        'recipient_id' => $recipient->id,
                        'error' => $result['error'],
                        'status_code' => $result['status_code'] ?? null,
                        'attempt' => $attempt,
                        'is_retryable' => $isRetryable,
                        'is_500_error' => $is500Error
                    ]);

                    // Retry logic: Only retry if it's a retryable error and we haven't exceeded max retries
                    if ($isRetryable && $attempt < $this->maxRetries) {
                        // For 500 errors, wait longer before retry (2 seconds)
                        $delay = $is500Error ? 2000000 : 500000; // 2 seconds for 500, 500ms for others
                        Log::info("Retrying after delay for {$recipient->phone_number}", [
                            'recipient_id' => $recipient->id,
                            'attempt' => $attempt,
                            'delay_ms' => $delay / 1000
                        ]);
                        usleep($delay);
                        continue;
                    } else {
                        // Final failure - don't retry non-retryable errors or if max retries exceeded
                        $recipient->update([
                            'status' => 'failed',
                            'error_message' => $result['error'],
                            'processed_at' => now()
                        ]);

                        // For non-retryable errors or final attempt, log and continue to next recipient
                        if (!$isRetryable || $attempt >= $this->maxRetries) {
                            Log::error("Final failure for {$recipient->phone_number} after {$attempt} attempts", [
                                'recipient_id' => $recipient->id,
                                'error' => $result['error'],
                                'status_code' => $result['status_code'] ?? null
                            ]);
                            return; // Continue to next recipient instead of throwing exception
                        }
                    }
                }

            } catch (Exception $e) {
                Log::error("Error in processRecipient attempt {$attempt}", [
                    'recipient_id' => $recipient->id,
                    'error' => $e->getMessage(),
                    'attempt' => $attempt
                ]);

                if ($attempt >= $this->maxRetries) {
                    $recipient->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'processed_at' => now()
                    ]);

                    throw $e;
                }

                // Wait before retry
                usleep(500000); // 500ms delay
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("Batch processing job failed", [
            'batch_id' => $this->batch->id,
            'error' => $exception->getMessage()
        ]);

        $this->batch->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage()
        ]);
    }
}
