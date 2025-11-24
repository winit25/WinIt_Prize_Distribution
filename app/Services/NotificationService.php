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
     * @param string|null $customSmsTemplate
     * @param string|null $customEmailTemplate
     * @return array
     */
    public function sendTransactionNotifications(Transaction $transaction, string $messageContent, bool $sendSms = true, bool $sendEmail = true, ?string $customSmsTemplate = null, ?string $customEmailTemplate = null): array
    {
        $results = [
            'sms' => ['success' => false, 'error' => 'SMS not sent (disabled)'],
            'email' => ['success' => false, 'error' => 'Email not sent (disabled)'],
        ];

        if ($sendSms) {
            $results['sms'] = $this->sendSmsNotification($transaction, $messageContent, $customSmsTemplate);
        }

        if ($sendEmail && $transaction->recipient->email) {
            $results['email'] = $this->sendEmailNotification($transaction, $messageContent, $customEmailTemplate);
        } elseif ($sendEmail && !$transaction->recipient->email) {
            $results['email'] = ['success' => false, 'error' => 'Email not sent (no recipient email provided)'];
        }

        return $results;
    }

    /**
     * Send batch completion notification to user
     *
     * @param \App\Models\User $user
     * @param string $subject
     * @param string $message
     * @param string $type ('success' or 'failed')
     * @return array
     */
    public function sendBatchNotification(\App\Models\User $user, string $subject, string $message, string $type = 'success', ?int $batchId = null): array
    {
        try {
            // Send email notification
            if ($user->email) {
                // Use sendCustomEmail with a simple text-based email template
                // Format the message as HTML for better display
                $htmlMessage = nl2br(e($message));
                
                // Create a simple email template view data
                $emailData = [
                    'user' => $user,
                    'subject' => $subject,
                    'message' => $htmlMessage,
                    'type' => $type,
                    'plainMessage' => $message, // Plain text version
                    'batch_id' => $batchId, // Batch ID for link
                ];
                
                // Use sendCustomEmail with a simple template
                return $this->productionEmailService->sendCustomEmail(
                    $user->email,
                    $subject,
                    'emails.batch-notification', // We'll create this template
                    $emailData
                );
            }

            return ['success' => false, 'error' => 'User email not found'];
        } catch (Exception $e) {
            Log::error("Failed to send batch notification", [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send SMS notification for a transaction.
     *
     * @param Transaction $transaction
     * @param string $messageContent
     * @param string|null $customSmsTemplate
     * @return array
     */
    protected function sendSmsNotification(Transaction $transaction, string $messageContent, ?string $customSmsTemplate = null): array
    {
        try {
            // Check if transaction has a valid token
            if (!$transaction->token) {
                return [
                    'success' => false,
                    'error' => 'SMS not sent (no token available)'
                ];
            }

            // Prepare variables for template replacement
            $now = now();
            $variables = [
                'name' => $transaction->recipient->name ?? '',
                'customer_name' => $transaction->recipient->customer_name ?? $transaction->recipient->name ?? '',
                'token' => $transaction->token,
                'amount' => number_format($transaction->amount, 2),
                'units' => $transaction->units ? number_format((float)$transaction->units, 2) : 'N/A',
                'disco' => $transaction->recipient->disco ?? '',
                'meter_number' => $transaction->recipient->meter_number ?? '',
                'address' => $transaction->recipient->address ?? '',
                'date' => $now->format('d/m/Y'),
                'time' => $now->format('h:i A'),
                'year' => $now->format('Y'),
                'month' => $now->format('F'), // Full month name (e.g., "January")
                'month_number' => $now->format('m'), // Month number with leading zero (e.g., "01")
                'month_short' => $now->format('M'), // Short month name (e.g., "Jan")
                'month_numeric' => $now->format('n'), // Month number without leading zero (e.g., "1")
            ];

            // Use Termii SMS service to send personalized token message
            $smsResult = $this->termiiSmsService->sendTokenSms(
                $transaction->recipient->phone_number,
                $transaction->token,
                $transaction->amount,
                $transaction->recipient->disco,
                $transaction->recipient->meter_number,
                $transaction->units,
                $customSmsTemplate,
                $variables
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
     * @param string|null $customEmailTemplate
     * @return array
     */
    protected function sendEmailNotification(Transaction $transaction, string $messageContent, ?string $customEmailTemplate = null): array
    {
        try {
            // Prepare variables for template replacement
            $now = now();
            $variables = [
                'name' => $transaction->recipient->name ?? '',
                'customer_name' => $transaction->recipient->customer_name ?? $transaction->recipient->name ?? '',
                'token' => $transaction->token ?? '',
                'amount' => number_format($transaction->amount, 2),
                'units' => $transaction->units ? number_format((float)$transaction->units, 2) : 'N/A',
                'disco' => $transaction->recipient->disco ?? '',
                'meter_number' => $transaction->recipient->meter_number ?? '',
                'address' => $transaction->recipient->address ?? '',
                'date' => $now->format('d/m/Y'),
                'time' => $now->format('h:i A'),
                'year' => $now->format('Y'),
                'month' => $now->format('F'), // Full month name (e.g., "January")
                'month_number' => $now->format('m'), // Month number with leading zero (e.g., "01")
                'month_short' => $now->format('M'), // Short month name (e.g., "Jan")
                'month_numeric' => $now->format('n'), // Month number without leading zero (e.g., "1")
            ];

            // Replace template variables if custom template is provided
            if ($customEmailTemplate) {
                $emailContent = $this->replaceTemplateVariables($customEmailTemplate, $variables);
            } else {
                $emailContent = $messageContent;
            }

            // Use ProductionEmailService to configure SMTP and send email
            $emailResult = $this->productionEmailService->sendCustomEmail(
                $transaction->recipient->email,
                'Electricity Token - WinIt Prize Distribution',
                'emails.anti-spam-token-notification', // Using the anti-spam template
                [
                    'transaction' => $transaction,
                    'recipient' => $transaction->recipient,
                    'messageContent' => $emailContent,
                    'customTemplate' => $customEmailTemplate,
                    'variables' => $variables,
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
     * Replace template variables in a message
     */
    protected function replaceTemplateVariables(string $template, array $variables): string
    {
        $message = $template;
        
        // Replace all variables
        foreach ($variables as $key => $value) {
            $message = str_replace('{' . $key . '}', $value ?? '', $message);
        }
        
        return $message;
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
