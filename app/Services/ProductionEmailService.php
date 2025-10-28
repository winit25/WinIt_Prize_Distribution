<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductionEmailService
{
    protected $defaultSmtpConfig;
    protected $fallbackSmtpConfigs;

    public function __construct()
    {
        $this->defaultSmtpConfig = config('mail.mailers.smtp');
        $this->fallbackSmtpConfigs = config('mail.mailers.fallback_smtp', []);
    }

    /**
     * Send a production email using multiple SMTP configurations with fallback.
     *
     * @param string $toEmail
     * @param string $subject
     * @param string $view
     * @param array $data
     * @return array
     */
    public function sendProductionEmail(string $toEmail, string $subject, string $view, array $data = []): array
    {
        $configsToTry = array_merge([$this->defaultSmtpConfig], $this->fallbackSmtpConfigs);

        foreach ($configsToTry as $index => $config) {
            try {
                // Dynamically set mailer configuration
                config([
                    'mail.mailers.production' => [
                        'transport' => 'smtp',
                        'host' => $config['host'],
                        'port' => $config['port'],
                        'encryption' => $config['encryption'],
                        'username' => $config['username'],
                        'password' => $config['password'],
                        'timeout' => $config['timeout'] ?? null,
                        'auth_mode' => $config['auth_mode'] ?? null,
                    ],
                ]);

                $startTime = microtime(true);

                Mail::mailer('production')->send($view, $data, function ($message) use ($toEmail, $subject) {
                    $message->to($toEmail)
                            ->subject($subject);

                    // Add anti-spam headers
                    $message->getHeaders()
                        ->addTextHeader('X-Mailer', 'WinIt Prize Distribution System v1.0')
                        ->addTextHeader('X-Priority', '3')
                        ->addTextHeader('Importance', 'Normal')
                        ->addTextHeader('X-Message-Type', 'Transaction Notification')
                        ->addTextHeader('X-Auto-Response-Suppress', 'OOF, AutoReply')
                        ->addTextHeader('List-Unsubscribe', '<mailto:unsubscribe@winit.ng?subject=unsubscribe>')
                        ->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click')
                        ->addTextHeader('Organization', 'WinIt Prize Distribution')
                        ->addTextHeader('X-Sender-Domain', config('mail.from.address'))
                        ->addTextHeader('Return-Path', config('mail.from.address'))
                        ->addTextHeader('Reply-To', config('mail.from.address'))
                        ->addTextHeader('Sender', config('mail.from.address'));
                });

                $endTime = microtime(true);
                $deliveryTime = round(($endTime - $startTime) * 1000); // in milliseconds

                Log::info("Email sent successfully via SMTP config {$index}", [
                    'to' => $toEmail,
                    'subject' => $subject,
                    'smtp_used' => $config['host'],
                    'delivery_time_ms' => $deliveryTime,
                ]);

                return [
                    'success' => true,
                    'message' => 'Email sent successfully',
                    'smtp_used' => $config['host'],
                    'method' => 'smtp_config_' . $index,
                    'delivery_time_ms' => $deliveryTime,
                ];

            } catch (Exception $e) {
                Log::error("Failed to send email via SMTP config {$index}: " . $e->getMessage(), [
                    'to' => $toEmail,
                    'subject' => $subject,
                    'smtp_config_index' => $index,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Fallback to manual provision if all SMTP configs fail
        Log::warning("All SMTP configurations failed for email to {$toEmail}. Falling back to manual provision.", [
            'to' => $toEmail,
            'subject' => $subject,
        ]);

        return [
            'success' => false,
            'message' => 'All SMTP configurations failed. Manual provision required.',
            'error' => 'All SMTP configurations failed.',
            'method' => 'manual',
        ];
    }

    /**
     * Send a custom email using a Blade template string and variables.
     *
     * @param string $toEmail
     * @param string $subject
     * @param string $templateViewName
     * @param array $variables
     * @return array
     */
    public function sendCustomEmail(string $toEmail, string $subject, string $templateViewName, array $variables = []): array
    {
        $configsToTry = array_merge([$this->defaultSmtpConfig], $this->fallbackSmtpConfigs);

        // Add default variables
        $defaultVariables = [
            'organization_name' => config('app.name', 'WinIt Prize Distribution'),
            'organization_email' => config('mail.from.address', 'noreply@winit.ng'),
            'login_url' => url('/login'),
        ];
        $data = array_merge($defaultVariables, $variables);

        foreach ($configsToTry as $index => $config) {
            try {
                config([
                    'mail.mailers.production' => [
                        'transport' => 'smtp',
                        'host' => $config['host'],
                        'port' => $config['port'],
                        'encryption' => $config['encryption'],
                        'username' => $config['username'],
                        'password' => $config['password'],
                        'timeout' => $config['timeout'] ?? null,
                        'auth_mode' => $config['auth_mode'] ?? null,
                    ],
                ]);

                $startTime = microtime(true);

                Mail::mailer('production')->send($templateViewName, $data, function ($message) use ($toEmail, $subject) {
                    $message->to($toEmail)
                            ->subject($subject);

                    // Add anti-spam headers
                    $message->getHeaders()
                        ->addTextHeader('X-Mailer', 'WinIt Prize Distribution System v1.0')
                        ->addTextHeader('X-Priority', '3')
                        ->addTextHeader('Importance', 'Normal')
                        ->addTextHeader('X-Message-Type', 'Transaction Notification')
                        ->addTextHeader('X-Auto-Response-Suppress', 'OOF, AutoReply')
                        ->addTextHeader('List-Unsubscribe', '<mailto:unsubscribe@winit.ng?subject=unsubscribe>')
                        ->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click')
                        ->addTextHeader('Organization', 'WinIt Prize Distribution')
                        ->addTextHeader('X-Sender-Domain', config('mail.from.address'))
                        ->addTextHeader('Return-Path', config('mail.from.address'))
                        ->addTextHeader('Reply-To', config('mail.from.address'))
                        ->addTextHeader('Sender', config('mail.from.address'));
                });

                $endTime = microtime(true);
                $deliveryTime = round(($endTime - $startTime) * 1000); // in milliseconds

                Log::info("Custom email sent successfully via SMTP config {$index}", [
                    'to' => $toEmail,
                    'subject' => $subject,
                    'template' => $templateViewName,
                    'smtp_used' => $config['host'],
                    'delivery_time_ms' => $deliveryTime,
                ]);

                return [
                    'success' => true,
                    'message' => 'Email sent successfully',
                    'smtp_used' => $config['host'],
                    'method' => 'smtp_config_' . $index,
                    'delivery_time_ms' => $deliveryTime,
                ];

            } catch (Exception $e) {
                Log::error("Failed to send custom email via SMTP config {$index}: " . $e->getMessage(), [
                    'to' => $toEmail,
                    'subject' => $subject,
                    'template' => $templateViewName,
                    'smtp_config_index' => $index,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::warning("All SMTP configurations failed for custom email to {$toEmail}. Falling back to manual provision.", [
            'to' => $toEmail,
            'subject' => $subject,
            'template' => $templateViewName,
        ]);

        return [
            'success' => false,
            'message' => 'All SMTP configurations failed. Manual provision required.',
            'error' => 'All SMTP configurations failed.',
            'method' => 'manual',
        ];
    }
}
