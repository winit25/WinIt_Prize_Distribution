<?php

namespace App\Services;

use App\Models\Transaction;
use App\Mail\TokenNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationService
{
    protected $productionEmailService;

    public function __construct(ProductionEmailService $productionEmailService)
    {
        $this->productionEmailService = $productionEmailService;
    }

    public function sendTokenNotification(Transaction $transaction, string $messageContent = ''): array
    {
        try {
            if (!$transaction->recipient->email) {
                return [
                    'success' => false,
                    'error' => 'No email address provided for recipient',
                    'method' => 'skipped'
                ];
            }

            $result = $this->productionEmailService->sendCustomEmail(
                $transaction->recipient->email,
                'Electricity Token - WinIt Prize Distribution',
                'emails.anti-spam-token-notification',
                [
                    'transaction' => $transaction,
                    'recipient' => $transaction->recipient,
                    'messageContent' => $messageContent
                ]
            );

            Log::info('Token notification sent', [
                'transaction_id' => $transaction->id,
                'recipient_email' => $transaction->recipient->email,
                'success' => $result['success']
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to send token notification', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'method' => 'failed'
            ];
        }
    }

    public function sendBatchCompletionNotification($batch, array $recipients): array
    {
        try {
            $results = [];
            
            foreach ($recipients as $recipient) {
                if ($recipient->email) {
                    $result = $this->productionEmailService->sendCustomEmail(
                        $recipient->email,
                        'Batch Processing Complete - WinIt Prize Distribution',
                        'emails.batch-completed',
                        [
                            'batch' => $batch,
                            'recipient' => $recipient
                        ]
                    );
                    
                    $results[] = [
                        'recipient_id' => $recipient->id,
                        'email' => $recipient->email,
                        'success' => $result['success'],
                        'error' => $result['error'] ?? null
                    ];
                }
            }

            return [
                'success' => true,
                'results' => $results,
                'method' => 'batch_email'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send batch completion notifications', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'method' => 'failed'
            ];
        }
    }

    public function sendBatchFailureNotification($batch, string $errorMessage): array
    {
        try {
            // Send notification to batch creator or admin
            $adminEmail = config('mail.admin_email', 'admin@winit.ng');
            
            $result = $this->productionEmailService->sendCustomEmail(
                $adminEmail,
                'Batch Processing Failed - WinIt Prize Distribution',
                'emails.batch-failed',
                [
                    'batch' => $batch,
                    'error_message' => $errorMessage
                ]
            );

            Log::info('Batch failure notification sent', [
                'batch_id' => $batch->id,
                'admin_email' => $adminEmail,
                'success' => $result['success']
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to send batch failure notification', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'method' => 'failed'
            ];
        }
    }
}
