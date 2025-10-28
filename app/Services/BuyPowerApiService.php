<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\SecureLoggingService;
use App\Services\CircuitBreakerService;
use Exception;

class BuyPowerApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;
    protected CircuitBreakerService $circuitBreaker;

    public function __construct()
    {
        $this->baseUrl = config('buypower.api_url', 'https://api.buypower.ng/v2');
        $this->apiKey = config('buypower.api_key');
        $this->timeout = config('buypower.timeout', 30);
        $this->circuitBreaker = new CircuitBreakerService('buypower');
    }

    /**
     * Create electricity order
     */
    public function createElectricityOrder(string $phoneNumber, string $disco, float $amount, string $meterNumber, string $meterType = 'prepaid', ?string $customerName = null, ?string $address = null, ?string $reference = null): array
    {
        return $this->circuitBreaker->execute(function () use ($phoneNumber, $disco, $amount, $meterNumber, $meterType, $customerName, $address, $reference) {
            return $this->performCreateOrder($phoneNumber, $disco, $amount, $meterNumber, $meterType, $customerName, $address, $reference);
        }, ['phone' => $phoneNumber, 'amount' => $amount]);
    }

    /**
     * Perform the actual create order API call
     */
    protected function performCreateOrder(string $phoneNumber, string $disco, float $amount, string $meterNumber, string $meterType = 'prepaid', ?string $customerName = null, ?string $address = null, ?string $reference = null): array
    {
        try {
            $reference = $reference ?? $this->generateReference();
            
            $payload = [
                'orderId' => $reference,
                'vendType' => strtolower($meterType),
                'amount' => number_format($amount, 2, '.', ''),
                'phone' => $this->formatPhoneNumber($phoneNumber),
                'meter' => $meterNumber,
                'disco' => $this->mapDiscoCode($disco),
                'vertical' => 'ELECTRICITY',
                'paymentType' => 'B2B',
            ];
            
            // Add optional fields if provided
            if ($customerName) {
                $payload['customerName'] = $customerName;
            }
            if ($address) {
                $payload['address'] = $address;
            }

            SecureLoggingService::logApiRequest(
                $this->baseUrl . '/electricity/create-order',
                $payload
            );

            $response = Http::timeout(10) // Increased timeout for create order
                ->retry(2, 200) // Retry twice with 200ms delay
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Connection' => 'keep-alive',
                ])
                ->post($this->baseUrl . '/electricity/create-order', $payload);

            $responseData = $response->json();
            
            SecureLoggingService::logApiResponse(
                $this->baseUrl . '/electricity/create-order',
                $response->status(),
                $responseData
            );

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'success') {
                return [
                    'success' => true,
                    'data' => $responseData,
                    'reference' => $reference,
                    'order_id' => $responseData['data']['orderId'] ?? $reference,
                    'status_code' => $response->status()
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Failed to create order',
                'data' => $responseData,
                'status_code' => $response->status()
            ];

        } catch (Exception $e) {
            SecureLoggingService::logApiError(
                $this->baseUrl . '/electricity/create-order',
                $e,
                ['phone' => $phoneNumber, 'amount' => $amount]
            );

            return [
                'success' => false,
                'error' => 'API connection failed: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 0
            ];
        }
    }

    /**
     * Vend electricity token
     */
    public function vendElectricity(string $orderId): array
    {
        try {
            $payload = [
                'orderId' => $orderId,
            ];

            Log::info('BuyPower Vend Request', [
                'url' => $this->baseUrl . '/electricity/vend',
                'payload' => $payload
            ]);

            $response = Http::timeout(15) // Increased timeout for vending
                ->retry(2, 500) // Retry twice with 500ms delay
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->baseUrl . '/electricity/vend', $payload);

            $responseData = $response->json();
            
            Log::info('BuyPower Vend Response', [
                'status' => $response->status(),
                'data' => $responseData
            ]);

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'success') {
                return [
                    'success' => true,
                    'data' => $responseData,
                    'token' => $responseData['data']['token'] ?? null,
                    'units' => $responseData['data']['units'] ?? null,
                    'status_code' => $response->status()
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Vending failed',
                'data' => $responseData,
                'status_code' => $response->status()
            ];

        } catch (Exception $e) {
            Log::error('BuyPower Vend Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Vending failed: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 0
            ];
        }
    }

    /**
     * Send token to a phone number (Complete flow: Create Order + Vend)
     */
    public function sendToken(string $phoneNumber, float $amount, string $disco, string $meterNumber, string $meterType = 'prepaid', ?string $customerName = null, ?string $address = null, ?string $reference = null): array
    {
        // Step 1: Create Order
        $orderResult = $this->createElectricityOrder($phoneNumber, $disco, $amount, $meterNumber, $meterType, $customerName, $address, $reference);
        
        if (!$orderResult['success']) {
            return $orderResult;
        }
        
        $orderId = $orderResult['order_id'];
        
        // Step 2: Vend Token
        $vendResult = $this->vendElectricity($orderId);
        
        if ($vendResult['success']) {
            return [
                'success' => true,
                'data' => array_merge($orderResult['data'], $vendResult['data']),
                'reference' => $orderResult['reference'],
                'order_id' => $orderId,
                'token' => $vendResult['token'],
                'units' => $vendResult['units'],
                'status_code' => $vendResult['status_code']
            ];
        }
        
        return $vendResult;
    }

    /**
     * Get order details/status
     */
    public function getOrder(string $orderId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/electricity/order/' . $orderId);

            $responseData = $response->json();

            Log::info('BuyPower Get Order Response', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'data' => $responseData
            ]);

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'success') {
                return [
                    'success' => true,
                    'data' => $responseData,
                    'status_code' => $response->status()
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Order not found',
                'data' => $responseData,
                'status_code' => $response->status()
            ];

        } catch (Exception $e) {
            Log::error('BuyPower Get Order Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to get order details: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 0
            ];
        }
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(int $page = 1, int $limit = 50): array
    {
        try {
            $queryParams = [
                'page' => $page,
                'limit' => $limit,
                'type' => 'electricity'
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/transactions', $queryParams);

            $responseData = $response->json();

            Log::info('BuyPower Transaction History Response', [
                'status' => $response->status(),
                'page' => $page,
                'data' => $responseData
            ]);

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'success') {
                return [
                    'success' => true,
                    'data' => $responseData,
                    'transactions' => $responseData['data']['transactions'] ?? [],
                    'status_code' => $response->status()
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Failed to get transaction history',
                'data' => $responseData,
                'status_code' => $response->status()
            ];

        } catch (Exception $e) {
            Log::error('BuyPower Transaction History Error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to get transaction history: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 0
            ];
        }
    }

    /**
     * Check transaction status (alias for getOrder)
     */
    public function checkTransactionStatus(string $reference): array
    {
        return $this->getOrder($reference);
    }

    /**
     * Get account balance
     */
    public function getBalance(): array
    {
        try {
            $response = Http::timeout(3) // Optimized timeout to 3 seconds
                ->retry(2, 100) // Retry twice with 100ms delay
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                    'Connection' => 'keep-alive',
                ])
                ->get($this->baseUrl . '/balance');

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
            Log::error('BuyPower Balance Error', [
                'error' => $e->getMessage(),
                'type' => get_class($e)
            ]);

            // Handle timeout specifically
            if (strpos($e->getMessage(), 'timeout') !== false || strpos($e->getMessage(), 'timed out') !== false) {
                return [
                    'success' => false,
                    'error' => 'API timeout - endpoint may not exist or is slow',
                    'data' => null,
                    'status_code' => 408
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get balance: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 0
            ];
        }
    }

    /**
     * Format phone number to Nigerian format (08000000000)
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Convert to Nigerian format (08000000000)
        if (str_starts_with($phone, '234')) {
            return '0' . substr($phone, 3);
        } elseif (str_starts_with($phone, '0')) {
            return $phone;
        } else {
            return '0' . $phone;
        }
    }

    /**
     * Map DISCO codes to valid BuyPower nomenclature
     */
    protected function mapDiscoCode(string $disco): string
    {
        $discoMapping = [
            'AEDC' => 'ABUJA',
            'EKEDC' => 'EKO', 
            'IKEDC' => 'IKEJA',
            'IBEDC' => 'IBADAN',
            'EEDC' => 'ENUGU',
            'PHED' => 'PH',
            'JEDC' => 'JOS',
            'KAEDCO' => 'KADUNA',
            'KEDCO' => 'KANO',
            'BEDC' => 'BH',
        ];

        $upperDisco = strtoupper($disco);
        return $discoMapping[$upperDisco] ?? $upperDisco;
    }

    /**
     * Generate unique reference
     */
    protected function generateReference(): string
    {
        return 'BP_' . time() . '_' . strtoupper(substr(md5(uniqid()), 0, 6));
    }
}