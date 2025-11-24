<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class SecureLoggingService
{
    /**
     * Sensitive field patterns to mask
     */
    private static array $sensitiveFields = [
        'phone_number', 'phone', 'mobile', 'phoneNumber',
        'meter_number', 'meter', 'meter_id',
        'api_key', 'apikey', 'api_secret',
        'token', 'tokens', 'access_token', 'refresh_token',
        'password', 'passwd', 'secret',
        'email', 'email_address',
        'amount', 'balance',
        'card_number', 'card_token', 'card',
        'bank_account', 'account_number',
        'ssn', 'social_security',
        'nin', 'national_id'
    ];
    
    /**
     * Mask sensitive data in logs
     */
    public static function maskSensitiveData(array $data): array
    {
        $masked = $data;
        
        foreach ($masked as $key => $value) {
            // Check if key matches sensitive field pattern (case-insensitive)
            if (self::isSensitiveField($key)) {
                $masked[$key] = self::maskValue($value);
            } elseif (is_array($value)) {
                // Recursively mask nested arrays
                $masked[$key] = self::maskSensitiveData($value);
            }
        }
        
        return $masked;
    }
    
    /**
     * Check if field name is sensitive
     */
    private static function isSensitiveField(string $field): bool
    {
        $fieldLower = strtolower($field);
        
        foreach (self::$sensitiveFields as $sensitive) {
            if ($fieldLower === strtolower($sensitive) || 
                strpos($fieldLower, strtolower($sensitive)) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Mask a single value (for both scalar and array values)
     */
    private static function maskValue($value): string
    {
        if ($value === null) {
            return '[NULL]';
        }
        
        if (is_array($value)) {
            return '[ARRAY]';
        }
        
        if (is_bool($value)) {
            return $value ? '[TRUE]' : '[FALSE]';
        }
        
        if (empty($value)) {
            return '[EMPTY]';
        }
        
        $stringValue = (string) $value;
        $length = strlen($stringValue);
        
        // Very short strings - mask completely
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

    /**
     * Log batch operation securely
     */
    public static function logBatchOperation(string $operation, int $batchId, array $details = []): void
    {
        Log::info("Batch {$operation}", array_merge([
            'batch_id' => $batchId,
            'timestamp' => now()->toISOString()
        ], self::maskSensitiveData($details)));
    }

    /**
     * Log transaction securely
     */
    public static function logTransaction(string $operation, int $transactionId, array $details = []): void
    {
        Log::info("Transaction {$operation}", array_merge([
            'transaction_id' => $transactionId,
            'timestamp' => now()->toISOString()
        ], self::maskSensitiveData($details)));
    }
}
