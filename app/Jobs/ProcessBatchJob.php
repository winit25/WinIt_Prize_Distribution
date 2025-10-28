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
            Log::info("Starting async batch processing", [
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

            foreach ($recipients as $recipient) {
                try {
                    $this->processRecipient($recipient);
                    $processedCount++;

                    $recipient->refresh();
                    if ($recipient->status === 'success') {
                        $successCount++;
                    } else {
                        $failedCount++;
                    }

                    // Add delay between requests
                    if ($this->delayMs > 0) {
                        usleep($this->delayMs * 1000);
                    }

                } catch (Exception $e) {
                    Log::error("Error processing recipient {$recipient->id}", [
                        'recipient_id' => $recipient->id,
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

            // Update batch statistics
            $this->batch->update([
                'processed_recipients' => $processedCount,
                'successful_transactions' => $successCount,
                'failed_transactions' => $failedCount,
                'status' => $processedCount >= $this->batch->total_recipients ? 'completed' : 'processing'
            ]);

            Log::info("Batch processing completed", [
                'batch_id' => $this->batch->id,
                'processed' => $processedCount,
                'successful' => $successCount,
                'failed' => $failedCount
            ]);

        } catch (Exception $e) {
            Log::error("Batch processing failed", [
                'batch_id' => $this->batch->id,
                'error' => $e->getMessage()
            ]);

            $this->batch->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
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

                    // Send notifications
                    $notificationService = app(NotificationService::class);
                    $notificationService->sendTransactionNotifications(
                        $transaction,
                        "Successfully sent ₦{$recipient->amount} electricity token to {$recipient->phone_number}",
                        true, // Send SMS
                        true  // Send Email
                    );

                    Log::info("Successfully processed recipient", [
                        'recipient_id' => $recipient->id,
                        'phone' => $recipient->phone_number,
                        'amount' => $recipient->amount,
                        'token' => $result['token']
                    ]);

                    return; // Success, exit retry loop

                } else {
                    // Log failure and retry
                    Log::warning("Attempt {$attempt} failed for {$recipient->phone_number}", [
                        'recipient_id' => $recipient->id,
                        'error' => $result['error'],
                        'attempt' => $attempt
                    ]);

                    if ($attempt < $this->maxRetries) {
                        // Wait before retry
                        usleep(500000); // 500ms delay
                        continue;
                    } else {
                        // Final failure
                        $recipient->update([
                            'status' => 'failed',
                            'error_message' => $result['error'],
                            'processed_at' => now()
                        ]);

                        throw new Exception("Failed to send ₦{$recipient->amount} electricity token to {$recipient->phone_number}. Error: {$result['error']}");
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
