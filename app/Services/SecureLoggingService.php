<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class SecureLoggingService
{
    /**
     * Mask sensitive data in logs
     */
    public static function maskSensitiveData(array $data): array
    {
        $sensitiveFields = [
            'phone_number', 'phone', 'meter_number', 'meter', 
            'api_key', 'token', 'password', 'email'
        ];
        
        $masked = $data;
        
        foreach ($sensitiveFields as $field) {
            if (isset($masked[$field])) {
                $masked[$field] = self::maskValue($masked[$field]);
            }
        }
        
        // Mask nested arrays
        foreach ($masked as $key => $value) {
            if (is_array($value)) {
                $masked[$key] = self::maskSensitiveData($value);
            }
        }
        
        return $masked;
    }
    
    /**
     * Mask a single value
     */
    private static function maskValue($value): string
    {
        if (empty($value)) {
            return '[EMPTY]';
        }
        
        $stringValue = (string) $value;
        $length = strlen($stringValue);
        
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        
        // Show first 2 and last 2 characters
        return substr($stringValue, 0, 2) . str_repeat('*', $length - 4) . substr($stringValue, -2);
    }
    
    /**
     * Log API request securely
     */
    public static function logApiRequest(string $endpoint, array $payload): void
    {
        Log::info('API Request', [
            'endpoint' => $endpoint,
            'payload' => self::maskSensitiveData($payload),
            'timestamp' => now()->toISOString()
        ]);
    }
    
    /**
     * Log API response securely
     */
    public static function logApiResponse(string $endpoint, int $statusCode, array $response): void
    {
        Log::info('API Response', [
            'endpoint' => $endpoint,
            'status_code' => $statusCode,
            'response' => self::maskSensitiveData($response),
            'timestamp' => now()->toISOString()
        ]);
    }
    
    /**
     * Log API error securely
     */
    public static function logApiError(string $endpoint, Exception $exception, array $context = []): void
    {
        Log::error('API Error', [
            'endpoint' => $endpoint,
            'error' => $exception->getMessage(),
            'context' => self::maskSensitiveData($context),
            'timestamp' => now()->toISOString()
        ]);
    }
}
