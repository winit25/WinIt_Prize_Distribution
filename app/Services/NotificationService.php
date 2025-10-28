<?php

namespace App\Services;

use App\Models\Transaction;
use App\Mail\TokenNotification;
use Illuminate\Support\Facades\Log;
use Exception;

class NotificationService
{
    protected ProductionEmailService $productionEmailService;
    protected TermiiSmsService $termiiSmsService;

    public function __construct(ProductionEmailService $productionEmailService, TermiiSmsService $termiiSmsService)
    {
        $this->productionEmailService = $productionEmailService;
        $this->termiiSmsService = $termiiSmsService;
    }

    /**
     * Send notifications for a transaction.
     *
     * @param Transaction $transaction
     * @param string $messageContent
     * @param bool $sendSms
     * @param bool $sendEmail
     * @return array
     */
    public function sendTransactionNotifications(Transaction $transaction, string $messageContent, bool $sendSms = true, bool $sendEmail = true): array
    {
        $results = [
            'sms' => ['success' => false, 'error' => 'SMS not sent (disabled)'],
            'email' => ['success' => false, 'error' => 'Email not sent (disabled)'],
        ];

        if ($sendSms) {
            $results['sms'] = $this->sendSmsNotification($transaction, $messageContent);
        }

        if ($sendEmail && $transaction->recipient->email) {
            $results['email'] = $this->sendEmailNotification($transaction, $messageContent);
        } elseif ($sendEmail && !$transaction->recipient->email) {
            $results['email'] = ['success' => false, 'error' => 'Email not sent (no recipient email provided)'];
        }

        return $results;
    }

    /**
     * Send SMS notification for a transaction.
     *
     * @param Transaction $transaction
     * @param string $messageContent
     * @return array
     */
    protected function sendSmsNotification(Transaction $transaction, string $messageContent): array
    {
        try {
            // Check if transaction has a valid token
            if (!$transaction->token) {
                return [
                    'success' => false,
                    'error' => 'SMS not sent (no token available)'
                ];
            }

            // Use Termii SMS service to send personalized token message
            $smsResult = $this->termiiSmsService->sendTokenSms(
                $transaction->recipient->phone_number,
                $transaction->token,
                $transaction->amount,
                $transaction->recipient->disco,
                $transaction->recipient->meter_number,
                $transaction->units
            );

            if ($smsResult['success']) {
                Log::info("SMS notification sent successfully to {$transaction->recipient->phone_number}", [
                    'recipient_id' => $transaction->recipient->id,
                    'phone_number' => $transaction->recipient->phone_number,
                    'message_id' => $smsResult['message_id'] ?? null,
                ]);

                return [
                    'success' => true, 
                    'message' => 'SMS notification sent successfully', 
                    'method' => 'termii_sms',
                    'message_id' => $smsResult['message_id'] ?? null
                ];
            } else {
                Log::error("Failed to send SMS notification to {$transaction->recipient->phone_number}", [
                    'recipient_id' => $transaction->recipient->id,
                    'phone_number' => $transaction->recipient->phone_number,
                    'error' => $smsResult['error'] ?? 'Unknown error',
                ]);

                return [
                    'success' => false, 
                    'error' => $smsResult['error'] ?? 'SMS sending failed', 
                    'method' => 'termii_sms'
                ];
            }

        } catch (Exception $e) {
            Log::error("Failed to send SMS notification for {$transaction->recipient->phone_number}: " . $e->getMessage(), [
                'recipient_id' => $transaction->recipient->id,
                'phone_number' => $transaction->recipient->phone_number,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage(), 'method' => 'termii_sms'];
        }
    }

    /**
     * Send email notification for a transaction.
     *
     * @param Transaction $transaction
     * @param string $messageContent
     * @return array
     */
    protected function sendEmailNotification(Transaction $transaction, string $messageContent): array
    {
        try {
            // Use ProductionEmailService to configure SMTP and send email
            $emailResult = $this->productionEmailService->sendCustomEmail(
                $transaction->recipient->email,
                'Electricity Token - WinIt Prize Distribution',
                'emails.anti-spam-token-notification', // Using the anti-spam template
                [
                    'transaction' => $transaction,
                    'recipient' => $transaction->recipient,
                    'messageContent' => $messageContent,
                    'token' => $transaction->token,
                    'units' => $transaction->units,
                    'amount' => $transaction->amount,
                    'meter_number' => $transaction->recipient->meter_number,
                    'disco' => $transaction->recipient->disco,
                ]
            );

            return [
                'success' => $emailResult['success'] ?? false,
                'error' => $emailResult['error'] ?? null,
                'method' => $emailResult['method'] ?? 'unknown',
                'smtp_used' => $emailResult['smtp_used'] ?? null,
                'delivery_time_ms' => $emailResult['delivery_time_ms'] ?? null,
            ];

        } catch (Exception $e) {
            Log::error("Failed to send email notification for {$transaction->recipient->email}: " . $e->getMessage(), [
                'recipient_id' => $transaction->recipient->id,
                'email' => $transaction->recipient->email,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage(), 'method' => 'email_service'];
        }
    }

    /**
     * Build the email message content.
     *
     * @param Transaction $transaction
     * @return string
     */
    protected function buildEmailMessage(Transaction $transaction): string
    {
        // This can be customized based on batch-specific templates or default content
        return "Dear {$transaction->recipient->name},\n\n" .
               "Your electricity token for meter number {$transaction->recipient->meter_number} ({$transaction->recipient->disco}) is:\n\n" .
               "Token: {$transaction->token}\n" .
               "Units: {$transaction->units} KWh\n" .
               "Amount: ₦" . number_format($transaction->amount, 2) . "\n\n" .
               "Thank you for using our service.";
    }
}
