<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TermiiSmsService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $senderId;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.termii.api_key', env('TERMII_API_KEY')) ?? 'test_key';
        $this->baseUrl = config('services.termii.base_url', 'https://api.ng.termii.com');
        $this->senderId = config('services.termii.sender_id', env('TERMII_SENDER_ID', 'WinIt'));
        $this->timeout = config('services.termii.timeout', 30);
    }

    /**
     * Send SMS notification for electricity token
     */
    public function sendTokenSms(string $phoneNumber, string $token, float $amount, string $disco, string $meterNumber, string $units = null): array
    {
        try {
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);
            
            $message = $this->buildTokenMessage($token, $amount, $disco, $meterNumber, $units);
            
            $payload = [
                'to' => $phoneNumber,
                'from' => $this->senderId,
                'sms' => $message,
                'type' => 'plain',
                'channel' => 'generic',
                'api_key' => $this->apiKey,
            ];

            Log::info('Termii SMS Request', [
                'phone' => $phoneNumber,
                'message_length' => strlen($message),
                'payload' => $payload
            ]);

            $response = Http::timeout($this->timeout)
                ->retry(2, 1000)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->baseUrl . '/api/sms/send', $payload);

            $responseData = $response->json();

            Log::info('Termii SMS Response', [
                'status' => $response->status(),
                'data' => $responseData
            ]);

            if ($response->successful() && isset($responseData['code']) && $responseData['code'] === 'ok') {
                return [
                    'success' => true,
                    'message_id' => $responseData['messageId'] ?? null,
                    'data' => $responseData,
                    'status_code' => $response->status()
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'SMS sending failed',
                'data' => $responseData,
                'status_code' => $response->status()
            ];

        } catch (Exception $e) {
            Log::error('Termii SMS Error', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'SMS sending failed: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 0
            ];
        }
    }

    /**
     * Build personalized token message
     */
    protected function buildTokenMessage(string $token, float $amount, string $disco, string $meterNumber, string $units = null): string
    {
        $currentDate = now()->format('d/m/Y');
        $currentTime = now()->format('h:i A');
        
        $message = "Your electricity WinIt token is: {$token}\n";
        $message .= "Amount: ₦" . number_format($amount, 2) . "\n";
        $message .= "Disco: {$disco}\n";
        $message .= "Meter: {$meterNumber}\n";
        
        if ($units) {
            $message .= "Units: {$units} KWh\n";
        }
        
        $message .= "Date: {$currentDate}\n";
        $message .= "Time: {$currentTime}\n";
        $message .= "Thank you for using WinIt!";
        
        return $message;
    }

    /**
     * Format phone number to international format
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Convert to international format (234...)
        if (str_starts_with($phone, '0')) {
            return '234' . substr($phone, 1);
        } elseif (str_starts_with($phone, '234')) {
            return $phone;
        } else {
            return '234' . $phone;
        }
    }

    /**
     * Check SMS balance
     */
    public function getBalance(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/api/balance', [
                    'api_key' => $this->apiKey
                ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                    'status_code' => $response->status()
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Failed to get balance',
                'data' => $responseData,
                'status_code' => $response->status()
            ];

        } catch (Exception $e) {
            Log::error('Termii Balance Error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to get balance: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 0
            ];
        }
    }

    /**
     * Send bulk SMS
     */
    public function sendBulkSms(array $phoneNumbers, string $message): array
    {
        try {
            $formattedNumbers = array_map([$this, 'formatPhoneNumber'], $phoneNumbers);
            
            $payload = [
                'to' => $formattedNumbers,
                'from' => $this->senderId,
                'sms' => $message,
                'type' => 'plain',
                'channel' => 'generic',
                'api_key' => $this->apiKey,
            ];

            $response = Http::timeout($this->timeout)
                ->retry(2, 1000)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->baseUrl . '/api/sms/send/bulk', $payload);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['code']) && $responseData['code'] === 'ok') {
                return [
                    'success' => true,
                    'message_id' => $responseData['messageId'] ?? null,
                    'data' => $responseData,
                    'status_code' => $response->status()
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Bulk SMS sending failed',
                'data' => $responseData,
                'status_code' => $response->status()
            ];

        } catch (Exception $e) {
            Log::error('Termii Bulk SMS Error', [
                'phone_count' => count($phoneNumbers),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Bulk SMS sending failed: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 0
            ];
        }
    }
}
