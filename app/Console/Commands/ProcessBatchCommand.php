<?php

namespace App\Console\Commands;

use App\Models\BatchUpload;
use App\Models\Recipient;
use App\Models\Transaction;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessBatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buypower:process-batch {batch_id : The ID of the batch to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process a batch of token transactions via BuyPower API';

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchId = $this->argument('batch_id');
        
        $batch = BatchUpload::with('recipients')
            ->where('id', $batchId)
            ->where('status', 'processing')
            ->first();

        if (!$batch) {
            $this->error("Batch {$batchId} not found or not in processing status");
            return Command::FAILURE;
        }

        $this->info("Processing batch: {$batch->batch_name} ({$batch->total_recipients} recipients)");
        
        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;
        
        $batchSize = config('buypower.batch_size', 5);
        $delayMs = config('buypower.delay_between_requests', 2000);
        
        // Get pending recipients
        $recipients = $batch->recipients()->where('status', 'pending')->get();
        
        $progressBar = $this->output->createProgressBar($recipients->count());
        $progressBar->start();

        foreach ($recipients->chunk($batchSize) as $chunk) {
            foreach ($chunk as $recipient) {
                try {
                    // Update recipient status to processing
                    $recipient->update(['status' => 'processing']);
                    
                    $this->processRecipient($recipient, $batch);
                    
                    $processedCount++;
                    if ($recipient->fresh()->status === 'success') {
                        $successCount++;
                    } else {
                        $failedCount++;
                    }
                    
                    $progressBar->advance();
                    
                    // Add delay between requests to avoid rate limiting
                    if ($delayMs > 0) {
                        usleep($delayMs * 1000); // Convert to microseconds
                    }
                    
                } catch (\Exception $e) {
                    $this->error("Error processing recipient {$recipient->id}: {$e->getMessage()}");
                    $recipient->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'processed_at' => now()
                    ]);
                    $failedCount++;
                    $processedCount++;
                    $progressBar->advance();
                }
            }
        }
        
        $progressBar->finish();
        $this->newLine();
        
        // Update batch statistics
        $batch->update([
            'processed_recipients' => $processedCount,
            'successful_transactions' => $successCount,
            'failed_transactions' => $failedCount,
            'status' => $processedCount >= $batch->total_recipients ? 'completed' : 'processing'
        ]);
        
        $this->info("Batch processing completed!");
        $this->info("Processed: {$processedCount}");
        $this->info("Successful: {$successCount}");
        $this->info("Failed: {$failedCount}");
        
        return Command::SUCCESS;
    }
    
    protected function processRecipient(Recipient $recipient, BatchUpload $batch)
    {
        $maxRetries = config('buypower.max_retries', 3);
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            $attempt++;
            
            try {
                // Generate unique reference for this transaction
                $reference = 'BP_' . $batch->id . '_' . $recipient->id . '_' . time();
                
                // Get BuyPower API service from container (supports mock/real switching)
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
                    'batch_upload_id' => $batch->id,
                    'buypower_reference' => $reference,
                    'order_id' => $result['order_id'] ?? null,
                    'phone_number' => $recipient->phone_number,
                    'amount' => $recipient->amount,
                    'status' => $result['success'] ? 'success' : 'failed',
                    'api_response' => $result['data'],
                    'token' => $result['token'] ?? null,
                    'units' => $result['units'] ?? null,
                    'error_message' => $result['success'] ? null : $result['error'],
                    'processed_at' => now()
                ]);
                
                if ($result['success']) {
                    $recipient->update([
                        'status' => 'success',
                        'transaction_reference' => $reference,
                        'processed_at' => now()
                    ]);
                    
                    // Send success notification
                    $this->sendTransactionNotification($transaction, 'success', "Successfully sent ₦{$recipient->amount} electricity token to {$recipient->phone_number}. Token: {$result['token']}");
                    
                    $this->line("✓ Sent ₦{$recipient->amount} to {$recipient->phone_number}");
                    return; // Success, exit retry loop
                } else {
                    if ($attempt >= $maxRetries) {
                        $recipient->update([
                            'status' => 'failed',
                            'error_message' => $result['error'],
                            'processed_at' => now()
                        ]);
                        
                        // Send failure notification
                        $this->sendTransactionNotification($transaction, 'error', "Failed to send ₦{$recipient->amount} electricity token to {$recipient->phone_number}. Error: {$result['error']}");
                        
                        $this->line("✗ Failed to send to {$recipient->phone_number}: {$result['error']}");
                    } else {
                        $this->line("⚠ Attempt {$attempt} failed for {$recipient->phone_number}, retrying...");
                        sleep(2); // Wait 2 seconds before retry
                    }
                }
                
            } catch (\Exception $e) {
                if ($attempt >= $maxRetries) {
                    throw $e; // Re-throw on final attempt
                }
                $this->line("⚠ Exception on attempt {$attempt} for {$recipient->phone_number}: {$e->getMessage()}");
                sleep(2);
            }
        }
    }

    /**
     * Send transaction notification
     */
    private function sendTransactionNotification(Transaction $transaction, string $type, string $message): void
    {
        try {
            $this->notificationService->sendTransactionNotifications(
                $transaction,
                $message,
                true,  // Enable SMS notifications
                true   // Enable email notifications
            );
        } catch (\Exception $e) {
            Log::error('Failed to send transaction notification', [
                'transaction_id' => $transaction->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }
    }
}
