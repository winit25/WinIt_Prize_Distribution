<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Recipient;
use App\Models\BatchUpload;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestNotificationCommand extends Command
{
    protected $signature = 'test:notifications';
    protected $description = 'Test the notification system';

    public function handle(NotificationService $notificationService)
    {
        $this->info('Testing notification system...');

        // Create a test transaction
        $batch = BatchUpload::first();
        if (!$batch) {
            $this->error('No batch found. Please create a batch first.');
            return 1;
        }

        $recipient = Recipient::where('batch_upload_id', $batch->id)->first();
        if (!$recipient) {
            $this->error('No recipient found. Please create recipients first.');
            return 1;
        }

        $transaction = Transaction::where('recipient_id', $recipient->id)->first();
        if (!$transaction) {
            $this->error('No transaction found. Please process a batch first.');
            return 1;
        }

        // Test success notification
        $this->info('Sending success notification...');
        $result = $notificationService->sendTransactionNotifications(
            $transaction,
            "Test success notification for transaction {$transaction->id}",
            false, // No SMS
            true   // Send email
        );

        $this->info('Notification result: ' . json_encode($result));

        $this->info('Notification test completed!');
        return 0;
    }
}
