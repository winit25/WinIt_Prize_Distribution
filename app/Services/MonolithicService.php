<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\ActivityLog;
use App\Models\BatchUpload;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * Monolithic Service - Consolidates all service functionality
 * This replaces multiple service classes with a single unified service
 */
class MonolithicService
{
    // ========================================================================
    // EMAIL FUNCTIONALITY
    // ========================================================================
    
    /**
     * Send email with fallback SMTP configurations
     */
    public function sendEmail(string $toEmail, string $subject, string $view, array $data = []): array
    {
        $defaultSmtpConfig = config('mail.mailers.smtp');
        $fallbackSmtpConfigs = config('mail.mailers.fallback_smtp', []);
        $configsToTry = array_merge([$defaultSmtpConfig], $fallbackSmtpConfigs);

        foreach ($configsToTry as $index => $config) {
            try {
                config(['mail.mailers.production' => [
                    'transport' => 'smtp',
                    'host' => $config['host'],
                    'port' => $config['port'],
                    'encryption' => $config['encryption'] ?? null,
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'timeout' => $config['timeout'] ?? null,
                    'auth_mode' => $config['auth_mode'] ?? null,
                ]]);

                $fromAddress = config('mail.mailers.smtp.username') ?: config('mail.from.address');
                $fromName = config('mail.from.name');
                
                Mail::mailer('production')->send($view, $data, function ($message) use ($toEmail, $subject, $fromAddress, $fromName) {
                    $message->to($toEmail)
                            ->from($fromAddress, $fromName)
                            ->replyTo($fromAddress, $fromName)
                            ->subject($subject)
                            ->returnPath($fromAddress);
                });

                Log::info("Email sent successfully", ['to' => $toEmail, 'subject' => $subject]);
                return ['success' => true, 'message' => 'Email sent successfully'];
            } catch (Exception $e) {
                Log::error("Failed to send email via SMTP config {$index}", ['error' => $e->getMessage()]);
            }
        }

        return ['success' => false, 'error' => 'All SMTP configurations failed'];
    }

    /**
     * Send transaction notification email
     */
    public function sendTransactionEmail(Transaction $transaction, string $messageContent = ''): array
    {
        if (!$transaction->recipient->email) {
            return ['success' => false, 'error' => 'No email address provided'];
        }

        return $this->sendEmail(
            $transaction->recipient->email,
            'Electricity Token - WinIt Prize Distribution',
            'emails.anti-spam-token-notification',
            ['transaction' => $transaction, 'recipient' => $transaction->recipient, 'messageContent' => $messageContent]
        );
    }

    // ========================================================================
    // SMS FUNCTIONALITY
    // ========================================================================
    
    /**
     * Send SMS via Termii
     */
    public function sendSms(string $phone, string $message): array
    {
        $apiKey = config('services.termii.api_key');
        $senderId = config('services.termii.sender_id');
        $baseUrl = config('services.termii.base_url', 'https://api.ng.termii.com/api');

        if (!$apiKey || !$senderId) {
            return ['success' => false, 'error' => 'Termii API not configured'];
        }

        try {
            $response = Http::post("{$baseUrl}/sms/send", [
                'api_key' => $apiKey,
                'to' => $phone,
                'from' => $senderId,
                'sms' => $message,
                'type' => 'plain',
                'channel' => 'generic',
            ]);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'error' => $response->body()];
        } catch (Exception $e) {
            Log::error('SMS send failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ========================================================================
    // LOGGING FUNCTIONALITY
    // ========================================================================
    
    /**
     * Log activity with IP tracking
     */
    public function logActivity(string $action, string $description, ?Model $subject = null, array $properties = [], ?Model $causer = null): ActivityLog
    {
        $causer = $causer ?? Auth::user();
        $ipAddress = $this->getUserIpAddress();
        
        return ActivityLog::create([
            'action' => $action,
            'event' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causer ? $causer->id : null,
            'properties' => $properties,
            'ip_address' => $ipAddress,
            'user_agent' => request()->userAgent() ?? 'Unknown',
        ]);
    }

    /**
     * Get user IP address (handles proxies/load balancers)
     */
    private function getUserIpAddress(): string
    {
        $request = request();
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_CLIENT_IP',
        ];
        
        foreach ($ipHeaders as $header) {
            $ip = $request->server($header);
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return request()->ip() ?: '0.0.0.0';
    }

    /**
     * Mask sensitive data in logs
     */
    public function maskSensitiveData(array $data): array
    {
        $sensitiveFields = ['phone_number', 'phone', 'meter_number', 'api_key', 'token', 'password', 'email', 'amount'];
        $masked = $data;
        
        foreach ($masked as $key => $value) {
            if (in_array(strtolower($key), array_map('strtolower', $sensitiveFields))) {
                $masked[$key] = $this->maskValue($value);
            } elseif (is_array($value)) {
                $masked[$key] = $this->maskSensitiveData($value);
            }
        }
        
        return $masked;
    }

    private function maskValue($value): string
    {
        if (empty($value)) return '[EMPTY]';
        $str = (string) $value;
        $len = strlen($str);
        return $len <= 4 ? str_repeat('*', $len) : substr($str, 0, 2) . str_repeat('*', $len - 4) . substr($str, -2);
    }

    // ========================================================================
    // VALIDATION FUNCTIONALITY
    // ========================================================================
    
    /**
     * Validate meter number
     */
    public function validateMeterNumber(string $meterNumber, string $disco): bool
    {
        $patterns = [
            'AEDC' => '/^[0-9]{11}$/',
            'EKEDC' => '/^[0-9]{11}$/',
            'IKEDC' => '/^[0-9]{11}$/',
            'PHED' => '/^[0-9]{11}$/',
            'KEDCO' => '/^[0-9]{11}$/',
        ];
        
        $pattern = $patterns[$disco] ?? '/^[0-9]{11}$/';
        return preg_match($pattern, $meterNumber) === 1;
    }

    /**
     * Validate Nigerian phone number
     */
    public function validatePhoneNumber(string $phone): bool
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return preg_match('/^(0|234)[0-9]{10}$/', $phone) === 1;
    }

    // ========================================================================
    // PASSWORD FUNCTIONALITY
    // ========================================================================
    
    /**
     * Generate secure password
     */
    public function generatePassword(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
    }

    // ========================================================================
    // CSV FUNCTIONALITY
    // ========================================================================
    
    /**
     * Parse CSV file
     */
    public function parseCsv(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = array_combine($headers, $data);
            }
            fclose($handle);
        }
        return $rows;
    }

    // ========================================================================
    // CIRCUIT BREAKER FUNCTIONALITY
    // ========================================================================
    
    /**
     * Execute with circuit breaker protection
     */
    public function executeWithCircuitBreaker(callable $callback, string $serviceName = 'buypower'): mixed
    {
        $state = Cache::get("circuit_breaker_{$serviceName}_state", 'closed');
        $failures = Cache::get("circuit_breaker_{$serviceName}_failures", 0);
        
        if ($state === 'open') {
            $lastFailure = Cache::get("circuit_breaker_{$serviceName}_last_failure_time");
            if (now()->diffInSeconds($lastFailure) < 60) {
                throw new Exception("Circuit breaker is open for {$serviceName}");
            }
            Cache::put("circuit_breaker_{$serviceName}_state", 'half-open', 60);
        }
        
        try {
            $result = $callback();
            Cache::put("circuit_breaker_{$serviceName}_failures", 0, 60);
            Cache::put("circuit_breaker_{$serviceName}_state", 'closed', 60);
            return $result;
        } catch (Exception $e) {
            $failures++;
            Cache::put("circuit_breaker_{$serviceName}_failures", $failures, 60);
            Cache::put("circuit_breaker_{$serviceName}_last_failure_time", now(), 60);
            
            if ($failures >= 5) {
                Cache::put("circuit_breaker_{$serviceName}_state", 'open', 60);
            }
            
            throw $e;
        }
    }
}

