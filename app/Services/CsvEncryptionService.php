<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class CsvEncryptionService
{
    /**
     * Encrypt CSV file content
     */
    public function encryptCsv(string $content, string $password): string
    {
        try {
            // Use Laravel's encryption or OpenSSL
            $encrypted = openssl_encrypt(
                $content,
                'AES-256-CBC',
                hash('sha256', $password),
                0,
                substr(hash('sha256', config('app.key')), 0, 16)
            );

            if ($encrypted === false) {
                throw new Exception('Encryption failed');
            }

            return base64_encode($encrypted);
        } catch (Exception $e) {
            Log::error('CSV encryption failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Decrypt CSV file content
     */
    public function decryptCsv(string $encryptedContent, string $password): string
    {
        try {
            $encrypted = base64_decode($encryptedContent);
            
            if ($encrypted === false) {
                throw new Exception('Invalid encrypted content');
            }

            $decrypted = openssl_decrypt(
                $encrypted,
                'AES-256-CBC',
                hash('sha256', $password),
                0,
                substr(hash('sha256', config('app.key')), 0, 16)
            );

            if ($decrypted === false) {
                throw new Exception('Decryption failed - invalid password or corrupted data');
            }

            return $decrypted;
        } catch (Exception $e) {
            Log::error('CSV decryption failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Check if file is encrypted (has .encrypted extension or contains encrypted header)
     */
    public function isEncrypted(string $filename): bool
    {
        return str_ends_with(strtolower($filename), '.encrypted') ||
               str_ends_with(strtolower($filename), '.enc');
    }

    /**
     * Validate encrypted file and password
     */
    public function validateEncryptedFile(string $filePath, string $password): array
    {
        try {
            if (!Storage::exists($filePath)) {
                return [
                    'valid' => false,
                    'error' => 'File not found'
                ];
            }

            $encryptedContent = Storage::get($filePath);
            
            // Try to decrypt
            $decrypted = $this->decryptCsv($encryptedContent, $password);
            
            // Validate it's actual CSV content
            if (empty(trim($decrypted))) {
                return [
                    'valid' => false,
                    'error' => 'Decrypted content is empty'
                ];
            }

            // Check if it looks like CSV (has commas or semicolons)
            if (strpos($decrypted, ',') === false && strpos($decrypted, ';') === false) {
                return [
                    'valid' => false,
                    'error' => 'Decrypted content does not appear to be CSV format'
                ];
            }

            return [
                'valid' => true,
                'content' => $decrypted
            ];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
