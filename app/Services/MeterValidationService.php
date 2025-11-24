<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class MeterValidationService
{
    protected $buyPowerService;

    public function __construct()
    {
        $this->buyPowerService = app('buypower.api');
    }

    /**
     * Validate meter number format
     */
    public function validateMeterFormat(string $meterNumber, string $disco): array
    {
        // Basic format validation
        if (empty($meterNumber)) {
            return [
                'valid' => false,
                'error' => 'Meter number cannot be empty'
            ];
        }

        // Check length (Nigerian meter numbers are typically 10-11 digits)
        if (strlen($meterNumber) < 10 || strlen($meterNumber) > 11) {
            return [
                'valid' => false,
                'error' => 'Meter number must be 10-11 digits'
            ];
        }

        // Check if it's numeric
        if (!ctype_digit($meterNumber)) {
            return [
                'valid' => false,
                'error' => 'Meter number must contain only digits'
            ];
        }

        // DISCO-specific validation patterns
        $discoPatterns = [
            'EKO' => '/^04\d{9}$/',  // EKEDC typically starts with 04
            'IKEDC' => '/^03\d{9}$/',
            'AEDC' => '/^02\d{9}$/',
            'IBEDC' => '/^02\d{9}$/',
        ];

        if (isset($discoPatterns[$disco])) {
            if (!preg_match($discoPatterns[$disco], $meterNumber)) {
                return [
                    'valid' => false,
                    'error' => "Meter number format does not match {$disco} pattern"
                ];
            }
        }

        return [
            'valid' => true,
            'message' => 'Meter number format is valid'
        ];
    }

    /**
     * Validate meter number by attempting to query BuyPower API
     */
    public function validateMeterWithApi(string $meterNumber, string $disco, float $amount = 100): array
    {
        try {
            // Use a minimal amount for validation
            $result = $this->buyPowerService->createElectricityOrder(
                '08000000000', // Dummy phone for validation
                $disco,
                $amount,
                $meterNumber,
                'prepaid'
            );

            if ($result['success']) {
                return [
                    'valid' => true,
                    'message' => 'Meter number validated successfully',
                    'order_id' => $result['order_id'] ?? null
                ];
            } else {
                return [
                    'valid' => false,
                    'error' => $result['error'] ?? 'Meter validation failed',
                    'api_response' => $result
                ];
            }
        } catch (Exception $e) {
            Log::warning('Meter validation API call failed', [
                'meter' => $meterNumber,
                'disco' => $disco,
                'error' => $e->getMessage()
            ]);

            // Return format validation result if API fails
            return $this->validateMeterFormat($meterNumber, $disco);
        }
    }

    /**
     * Validate multiple meter numbers in batch
     */
    public function validateMeterBatch(array $meters, bool $useApi = false): array
    {
        $results = [
            'valid' => [],
            'invalid' => [],
            'total' => count($meters)
        ];

        foreach ($meters as $meterData) {
            $meterNumber = $meterData['meter_number'] ?? $meterData['meter'] ?? null;
            $disco = $meterData['disco'] ?? null;

            if (!$meterNumber || !$disco) {
                $results['invalid'][] = [
                    'meter' => $meterNumber ?? 'N/A',
                    'disco' => $disco ?? 'N/A',
                    'error' => 'Missing meter number or disco'
                ];
                continue;
            }

            $validation = $useApi 
                ? $this->validateMeterWithApi($meterNumber, $disco)
                : $this->validateMeterFormat($meterNumber, $disco);

            if ($validation['valid']) {
                $results['valid'][] = [
                    'meter' => $meterNumber,
                    'disco' => $disco
                ];
            } else {
                $results['invalid'][] = [
                    'meter' => $meterNumber,
                    'disco' => $disco,
                    'error' => $validation['error'] ?? 'Validation failed'
                ];
            }
        }

        $results['summary'] = [
            'valid_count' => count($results['valid']),
            'invalid_count' => count($results['invalid']),
            'valid_percentage' => $results['total'] > 0 
                ? round((count($results['valid']) / $results['total']) * 100, 2) 
                : 0
        ];

        return $results;
    }
}
