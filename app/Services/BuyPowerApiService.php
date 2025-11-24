<?php

namespace App\Services;

use App\Contracts\BuyPowerApiInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\SecureLoggingService;
use App\Services\CircuitBreakerService;
use Exception;

class BuyPowerApiService implements BuyPowerApiInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;
    protected CircuitBreakerService $circuitBreaker;

    public function __construct()
    {
        // Use config default which is sandbox URL, or fallback to sandbox if config returns null
        $this->baseUrl = config('buypower.api_url') ?: 'https://idev.buypower.ng/v2';
        $this->apiKey = config('buypower.api_key');
        $this->timeout = config('buypower.timeout', 30);
        $this->circuitBreaker = new CircuitBreakerService('buypower');
        
        // Validate API key is set
        if (empty($this->apiKey)) {
            Log::warning('BuyPower API key is not configured. Please set BUYPOWER_API_KEY in your .env file.');
        }
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
            // Validate API key before making request
            if (empty($this->apiKey) || $this->apiKey === '${BUYPOWER_API_KEY}' || strpos($this->apiKey, '${') === 0) {
                $errorMsg = 'BuyPower API key is not configured. Please set BUYPOWER_API_KEY in your .env file with a valid API key.';
                Log::error('BuyPower API Key Missing', [
                    'api_key_value' => $this->apiKey ? 'SET (but invalid)' : 'NOT SET',
                    'api_key_preview' => $this->apiKey ? substr($this->apiKey, 0, 20) . '...' : 'empty'
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorMsg,
                    'data' => null,
                    'status_code' => 401
                ];
            }
            
            // Validate inputs before making API call
            $validationErrors = [];
            
            // Validate meter number (should be 10-11 digits)
            if (empty($meterNumber) || !preg_match('/^\d{10,11}$/', $meterNumber)) {
                $validationErrors[] = "Invalid meter number format. Must be 10-11 digits.";
            }
            
            // Validate disco code
            $validDiscos = ['ABUJA', 'EKO', 'IKEJA', 'IBADAN', 'ENUGU', 'PH', 'JOS', 'KADUNA', 'KANO', 'BH'];
            $mappedDisco = $this->mapDiscoCode($disco);
            if (!in_array($mappedDisco, $validDiscos)) {
                $validationErrors[] = "Invalid disco code: {$disco}. Valid codes are: " . implode(', ', $validDiscos);
            }
            
            // Validate amount (minimum ₦100, maximum ₦100,000)
            if ($amount < 100 || $amount > 100000) {
                $validationErrors[] = "Amount must be between ₦100 and ₦100,000. Provided: ₦{$amount}";
            }
            
            // Validate phone number format
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            if (empty($formattedPhone) || strlen($formattedPhone) < 10) {
                $validationErrors[] = "Invalid phone number format: {$phoneNumber}";
            }
            
            if (!empty($validationErrors)) {
                Log::error('BuyPower API - Input validation failed', [
                    'errors' => $validationErrors,
                    'meter' => $meterNumber,
                    'disco' => $disco,
                    'amount' => $amount,
                    'phone' => substr($phoneNumber, 0, 4) . '****'
                ]);
                
                return [
                    'success' => false,
                    'error' => implode(' ', $validationErrors),
                    'data' => null,
                    'status_code' => 400,
                    'validation_errors' => $validationErrors
                ];
            }
            
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
                $this->baseUrl . '/vend',
                $payload
            );

            $response = Http::timeout(60) // Increased timeout for vend endpoint (takes 4-5 seconds)
                ->retry(2, 500) // Retry twice with 500ms delay
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Connection' => 'keep-alive',
                ])
                ->post($this->baseUrl . '/vend', $payload);

            // Get response body as string first for debugging
            $responseBody = $response->body();
            $responseData = $response->json();
            
            // If JSON parsing failed, log the raw response
            if (is_null($responseData) && !empty($responseBody)) {
                Log::warning('BuyPower Vend - JSON parse failed', [
                    'status_code' => $response->status(),
                    'raw_response' => $responseBody,
                    'payload' => $payload
                ]);
                $responseData = json_decode($responseBody, true) ?? [];
            }
            
            SecureLoggingService::logApiResponse(
                $this->baseUrl . '/vend',
                $response->status(),
                $responseData
            );

            // Log full response for debugging
            Log::info('BuyPower Vend Response', [
                'status_code' => $response->status(),
                'response' => $responseData,
                'payload' => $payload
            ]);

            // Handle 401 Unauthorized (API key issue)
            if ($response->status() === 401) {
                $errorMsg = 'BuyPower API authentication failed. Please check your API key configuration.';
                Log::error('BuyPower Vend - 401 Unauthorized', [
                    'status_code' => 401,
                    'response' => $responseData,
                    'api_key_preview' => $this->apiKey ? substr($this->apiKey, 0, 10) . '...' : 'empty',
                    'payload' => $payload
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorMsg,
                    'data' => $responseData,
                    'status_code' => 401,
                    'requires_config' => true
                ];
            }
            
            // Handle 202 response (Transaction in progress - requires requery)
            if ($response->status() === 202 && isset($responseData['responseCode']) && $responseData['responseCode'] == 202) {
                $delay = $responseData['delay'][0] ?? 20; // Get delay from response or default to 20 seconds
                Log::info('BuyPower Vend - Transaction in progress, requires requery', [
                    'orderId' => $reference,
                    'delay_seconds' => $delay,
                    'response' => $responseData
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Transaction is still processing. Please requery in ' . $delay . ' seconds.',
                    'data' => $responseData,
                    'status_code' => 202,
                    'retry_after' => $delay
                ];
            }
            
            // Handle 500 errors with better error messages
            if ($response->status() === 500) {
                $errorMessage = $responseData['message'] ?? 
                               $responseData['error'] ?? 
                               'An unexpected error occurred on the BuyPower API server';
                
                // Check for specific error patterns
                if (isset($responseData['responseCode']) && $responseData['responseCode'] == 500) {
                    // Common causes for 500 errors:
                    // 1. Invalid meter number
                    // 2. Invalid disco code
                    // 3. Meter/disco mismatch
                    // 4. Account balance issues
                    // 5. Rate limiting
                    
                    $detailedError = "BuyPower API returned a server error (500). ";
                    
                    // Provide helpful suggestions based on error message
                    if (stripos($errorMessage, 'meter') !== false || stripos($errorMessage, 'invalid') !== false) {
                        $detailedError .= "This may be due to an invalid meter number or meter/disco mismatch. ";
                        $detailedError .= "Please verify the meter number ({$meterNumber}) is correct for the selected disco ({$disco}).";
                    } elseif (stripos($errorMessage, 'balance') !== false || stripos($errorMessage, 'fund') !== false) {
                        $detailedError .= "This may be due to insufficient account balance. Please check your BuyPower account balance.";
                    } elseif (stripos($errorMessage, 'requery') !== false) {
                        $detailedError .= "The transaction may still be processing. Please try again in a few seconds.";
                    } else {
                        $detailedError .= "Possible causes: invalid meter number, meter/disco mismatch, or API server issue. ";
                        $detailedError .= "Please verify the meter number and disco code are correct.";
                    }
                    
                    Log::error('BuyPower Vend - 500 Server Error', [
                        'status_code' => 500,
                        'error_message' => $errorMessage,
                        'response' => $responseData,
                        'payload' => [
                            'meter' => $meterNumber,
                            'disco' => $disco,
                            'amount' => $amount,
                            'phone' => substr($phoneNumber, 0, 4) . '****' // Mask phone
                        ]
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => $detailedError,
                        'data' => $responseData,
                        'status_code' => 500,
                        'is_retryable' => true // Some 500 errors might be transient
                    ];
                }
            }

            // BuyPower API returns status as boolean (true) or string ('success')
            // Also check for response structure variations
            // Successful responses can have:
            // - status: true (boolean) at root level
            // - status: 'success' (string) at root level  
            // - responseCode: 200 or 100 in data.data.responseCode
            // - token present in data.data.token or data.token
            $isSuccess = $response->successful() && (
                (isset($responseData['status']) && ($responseData['status'] === true || $responseData['status'] === 'success' || $responseData['status'] === 'ok')) ||
                (isset($responseData['data']['status']) && ($responseData['data']['status'] === true || $responseData['data']['status'] === 'success')) ||
                (isset($responseData['data']['data']['responseCode']) && in_array($responseData['data']['data']['responseCode'], [100, 200, '100', '200'])) ||
                (isset($responseData['data']['responseCode']) && in_array($responseData['data']['responseCode'], [100, 200, '100', '200'])) ||
                (isset($responseData['data']['data']['token'])) || // Token in data.data.token
                (isset($responseData['data']['token'])) || // Token in data.token
                (isset($responseData['token'])) // Token at root level
            );
            
            if ($isSuccess) {
                // Extract token and units from various response structures
                // BuyPower API can nest data: data.data.token or data.token or token
                $token = $responseData['data']['data']['token'] ?? 
                         $responseData['data']['token'] ?? 
                         $responseData['token'] ?? 
                         null;
                $units = $responseData['data']['data']['units'] ?? 
                         $responseData['data']['units'] ?? 
                         $responseData['units'] ?? 
                         null;
                $orderId = $responseData['data']['data']['orderId'] ?? 
                          $responseData['data']['orderId'] ?? 
                          $responseData['orderId'] ?? 
                          $reference;
                
                return [
                    'success' => true,
                    'data' => $responseData,
                    'reference' => $reference,
                    'order_id' => $orderId,
                    'token' => $token,
                    'units' => $units,
                    'status_code' => $response->status()
                ];
            }

            // Extract error message from various response structures
            $errorMessage = $responseData['message'] ?? 
                           $responseData['error'] ?? 
                           ($responseData['data']['message'] ?? null) ??
                           'Failed to create order';
            
            Log::error('BuyPower Vend Failed', [
                'status_code' => $response->status(),
                'error' => $errorMessage,
                'response' => $responseData
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'data' => $responseData,
                'status_code' => $response->status()
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Network/connection errors
            SecureLoggingService::logApiError(
                $this->baseUrl . '/vend',
                $e,
                ['phone' => $phoneNumber, 'amount' => $amount]
            );

            return [
                'success' => false,
                'error' => 'Unable to connect to BuyPower API. Please check your internet connection and try again.',
                'data' => null,
                'status_code' => 0,
                'is_retryable' => true
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // HTTP request errors (including 500)
            $response = $e->response;
            $responseData = $response ? $response->json() : null;
            
            SecureLoggingService::logApiError(
                $this->baseUrl . '/vend',
                $e,
                ['phone' => $phoneNumber, 'amount' => $amount, 'response' => $responseData]
            );
            
            // If we have response data, use it
            if ($responseData && isset($responseData['message'])) {
                $errorMsg = $responseData['message'];
                
                // Format error message better
                if ($response && $response->status() === 500) {
                    $errorMsg = "BuyPower API server error: " . $errorMsg . ". ";
                    $errorMsg .= "Please verify the meter number and disco code are correct.";
                }
                
                return [
                    'success' => false,
                    'error' => $errorMsg,
                    'data' => $responseData,
                    'status_code' => $response ? $response->status() : 0,
                    'is_retryable' => $response && $response->status() === 500
                ];
            }

            return [
                'success' => false,
                'error' => 'BuyPower API request failed: ' . $e->getMessage(),
                'data' => $responseData,
                'status_code' => $response ? $response->status() : 0,
                'is_retryable' => true
            ];
        } catch (Exception $e) {
            SecureLoggingService::logApiError(
                $this->baseUrl . '/vend',
                $e,
                ['phone' => $phoneNumber, 'amount' => $amount]
            );

            return [
                'success' => false,
                'error' => 'An unexpected error occurred: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 0,
                'is_retryable' => false
            ];
        }
    }

    /**
     * Vend electricity token
     * Note: This method is deprecated as BuyPower's /vend endpoint does both creation and vending
     * Kept for backward compatibility
     */
    public function vendElectricity(string $orderId): array
    {
        // Since BuyPower's /vend endpoint handles everything in one call,
        // this method is only useful if you already have an order ID
        // and need to retrieve its details
        return $this->getOrder($orderId);
    }

    /**
     * Send token to a phone number (Complete flow: Create Order + Vend)
     * Note: BuyPower's /vend endpoint does both creation and vending in one call
     */
    public function sendToken(string $phoneNumber, float $amount, string $disco, string $meterNumber, string $meterType = 'prepaid', ?string $customerName = null, ?string $address = null, ?string $reference = null): array
    {
        // BuyPower's /vend endpoint handles both order creation and vending in a single call
        return $this->createElectricityOrder($phoneNumber, $disco, $amount, $meterNumber, $meterType, $customerName, $address, $reference);
    }

    /**
     * Get order details/status
     */
    public function getOrder(string $orderId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/electricity/order/' . $orderId);

            $responseData = $response->json();

            Log::info('BuyPower Get Order Response', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'data' => $responseData
            ]);

            // BuyPower API returns status as boolean (true) or string ('success')
            $isSuccess = $response->successful() && isset($responseData['status']) && 
                         ($responseData['status'] === true || $responseData['status'] === 'success');
            
            if ($isSuccess) {
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
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/transactions', $queryParams);

            $responseData = $response->json();

            Log::info('BuyPower Transaction History Response', [
                'status' => $response->status(),
                'page' => $page,
                'data' => $responseData
            ]);

            // BuyPower transactions endpoint returns status:'ok' or 'success' or boolean true
            $isSuccess = $response->successful() && (
                (isset($responseData['status']) && in_array($responseData['status'], ['ok', 'success', true], true)) ||
                isset($responseData['data']) // Has data array
            );
            
            if ($isSuccess) {
                return [
                    'success' => true,
                    'data' => $responseData,
                    'transactions' => $responseData['data'] ?? [],
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
     * Note: BuyPower API doesn't have a /balance endpoint.
     * This method checks API connectivity using /transactions endpoint instead.
     */
    public function getBalance(): array
    {
        try {
            // Validate API key before making request
            if (empty($this->apiKey) || $this->apiKey === '${BUYPOWER_API_KEY}' || strpos($this->apiKey, '${') === 0) {
                return [
                    'success' => false,
                    'error' => 'BuyPower API key is not configured. Please set BUYPOWER_API_KEY in your .env file.',
                    'data' => null,
                    'status_code' => 401
                ];
            }
            
            // Since /balance endpoint doesn't exist, use /transactions as a health check
            // This endpoint is available and confirms API connectivity
            $response = Http::timeout(5)
                ->retry(1, 100)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                    'Connection' => 'keep-alive',
                ])
                ->get($this->baseUrl . '/transactions', [
                    'page' => 1,
                    'limit' => 1
                ]);
            
            // Handle 401 Unauthorized
            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'error' => 'BuyPower API authentication failed. Please check your API key configuration.',
                    'data' => $response->json(),
                    'status_code' => 401
                ];
            }

            $responseData = $response->json();

            // If transactions endpoint works, API is connected
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => [
                        'balance' => null, // Balance not available via API
                        'api_connected' => true,
                        'message' => 'API connected successfully. Balance endpoint not available.',
                        'transactions_available' => isset($responseData['data'])
                    ],
                    'status_code' => $response->status()
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Failed to check API status',
                'data' => $responseData,
                'status_code' => $response->status()
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('BuyPower API Connection Error', [
                'error' => $e->getMessage(),
                'type' => get_class($e)
            ]);

            return [
                'success' => false,
                'error' => 'Unable to connect to BuyPower API. Please check your internet connection.',
                'data' => null,
                'status_code' => 0
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $response = $e->response;
            $responseData = $response ? $response->json() : null;
            
            Log::error('BuyPower API Request Error', [
                'error' => $e->getMessage(),
                'status' => $response ? $response->status() : null,
                'response' => $responseData
            ]);

            // Handle 404 - balance endpoint doesn't exist (expected)
            if ($response && $response->status() === 404) {
                return [
                    'success' => false,
                    'error' => 'Balance endpoint not available. API connectivity check failed.',
                    'data' => $responseData,
                    'status_code' => 404
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Failed to check API status: ' . $e->getMessage(),
                'data' => $responseData,
                'status_code' => $response ? $response->status() : 0
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
                    'error' => 'API timeout - please check your connection',
                    'data' => null,
                    'status_code' => 408
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to check API status: ' . $e->getMessage(),
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
        // Map legacy codes to BuyPower API codes
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
        
        // If it's already a valid code, return it
        $validCodes = ['ABUJA', 'EKO', 'IKEJA', 'IBADAN', 'ENUGU', 'PH', 'JOS', 'KADUNA', 'KANO', 'BH'];
        if (in_array($upperDisco, $validCodes)) {
            return $upperDisco;
        }
        
        // Map legacy codes
        return $discoMapping[$upperDisco] ?? $upperDisco;
    }

    /**
     * Top up airtime (VTU)
     */
    public function topUpAirtime(
        string $phoneNumber,
        float $amount,
        ?string $customerName = null,
        ?string $reference = null
    ): array {
        return $this->circuitBreaker->execute(function () use ($phoneNumber, $amount, $customerName, $reference) {
            return $this->performTopUpAirtime($phoneNumber, $amount, $customerName, $reference);
        }, ['phone' => $phoneNumber, 'amount' => $amount]);
    }

    /**
     * Perform the actual airtime top-up API call
     */
    protected function performTopUpAirtime(
        string $phoneNumber,
        float $amount,
        ?string $customerName = null,
        ?string $reference = null
    ): array {
        try {
            // Validate API key
            if (empty($this->apiKey) || $this->apiKey === '${BUYPOWER_API_KEY}' || strpos($this->apiKey, '${') === 0) {
                $errorMsg = 'BuyPower API key is not configured. Please set BUYPOWER_API_KEY in your .env file.';
                Log::error('BuyPower API Key Missing', [
                    'api_key_value' => $this->apiKey ? 'SET (but invalid)' : 'NOT SET',
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorMsg,
                    'data' => null,
                    'status_code' => 401
                ];
            }
            
            // Validate inputs
            $validationErrors = [];
            
            // Validate amount (minimum ₦50, maximum ₦10,000 for airtime)
            if ($amount < 50 || $amount > 10000) {
                $validationErrors[] = "Amount must be between ₦50 and ₦10,000. Provided: ₦{$amount}";
            }
            
            // Validate phone number format
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            if (empty($formattedPhone) || strlen($formattedPhone) < 10) {
                $validationErrors[] = "Invalid phone number format: {$phoneNumber}";
            }
            
            if (!empty($validationErrors)) {
                Log::error('BuyPower Airtime Top-Up - Input validation failed', [
                    'errors' => $validationErrors,
                    'amount' => $amount,
                    'phone' => substr($phoneNumber, 0, 4) . '****'
                ]);
                
                return [
                    'success' => false,
                    'error' => implode(' ', $validationErrors),
                    'data' => null,
                    'status_code' => 400,
                    'validation_errors' => $validationErrors
                ];
            }
            
            $reference = $reference ?? $this->generateReference();
            
            $payload = [
                'orderId' => $reference,
                'amount' => number_format($amount, 2, '.', ''),
                'phone' => $this->formatPhoneNumber($phoneNumber),
                'vertical' => 'VTU',
                'paymentType' => 'B2B',
            ];
            
            // Add optional fields if provided
            if ($customerName) {
                $payload['customerName'] = $customerName;
            }

            SecureLoggingService::logApiRequest(
                $this->baseUrl . '/vend',
                $payload
            );

            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/vend', $payload);

            $responseBody = $response->body();
            $responseData = $response->json();
            
            if (is_null($responseData) && !empty($responseBody)) {
                Log::warning('BuyPower Airtime Vend - JSON parse failed', [
                    'status_code' => $response->status(),
                    'raw_response' => $responseBody,
                    'payload' => $payload
                ]);
                $responseData = json_decode($responseBody, true) ?? [];
            }
            
            SecureLoggingService::logApiResponse(
                $this->baseUrl . '/vend',
                $response->status(),
                $responseData
            );

            Log::info('BuyPower Airtime Vend Response', [
                'status_code' => $response->status(),
                'response' => $responseData,
                'payload' => $payload
            ]);

            // Handle 401 Unauthorized
            if ($response->status() === 401) {
                $errorMsg = 'BuyPower API authentication failed. Please check your API key configuration.';
                Log::error('BuyPower Airtime Vend - 401 Unauthorized', [
                    'status_code' => 401,
                    'response' => $responseData,
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorMsg,
                    'data' => $responseData,
                    'status_code' => 401,
                    'requires_config' => true
                ];
            }
            
            // Handle 202 response (Transaction in progress)
            if ($response->status() === 202 && isset($responseData['responseCode']) && $responseData['responseCode'] == 202) {
                $delay = $responseData['delay'][0] ?? 20;
                Log::info('BuyPower Airtime Vend - Transaction in progress, requires requery', [
                    'orderId' => $reference,
                    'delay_seconds' => $delay,
                    'response' => $responseData
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Transaction is still processing. Please requery in ' . $delay . ' seconds.',
                    'data' => $responseData,
                    'status_code' => 202,
                    'retry_after' => $delay
                ];
            }
            
            // Handle 500 errors
            if ($response->status() === 500) {
                $errorMessage = $responseData['message'] ?? 
                               $responseData['error'] ?? 
                               'An unexpected error occurred on the BuyPower API server';
                
                Log::error('BuyPower Airtime Vend - 500 Server Error', [
                    'status_code' => 500,
                    'response' => $responseData,
                    'payload' => $payload
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'data' => $responseData,
                    'status_code' => 500
                ];
            }
            
            // Check for success response
            $isSuccess = $response->successful() && (
                (isset($responseData['status']) && ($responseData['status'] === true || $responseData['status'] === 'success')) ||
                (isset($responseData['responseCode']) && $responseData['responseCode'] == 200) ||
                (isset($responseData['data']['status']) && $responseData['data']['status'] === 'success')
            );
            
            if ($isSuccess) {
                Log::info('BuyPower Airtime Top-Up Successful', [
                    'orderId' => $reference,
                    'phone' => substr($formattedPhone, 0, 4) . '****',
                    'amount' => $amount,
                    'response' => $responseData
                ]);
                
                return [
                    'success' => true,
                    'data' => $responseData,
                    'order_id' => $reference,
                    'buypower_reference' => $responseData['data']['reference'] ?? $responseData['reference'] ?? $reference,
                    'status_code' => $response->status()
                ];
            }

            // Handle error response
            $errorMessage = $responseData['message'] ?? 
                          $responseData['error'] ?? 
                          'Airtime top-up failed';
            
            Log::error('BuyPower Airtime Top-Up Failed', [
                'orderId' => $reference,
                'phone' => substr($formattedPhone, 0, 4) . '****',
                'amount' => $amount,
                'error' => $errorMessage,
                'response' => $responseData
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'data' => $responseData,
                'status_code' => $response->status()
            ];

        } catch (Exception $e) {
            Log::error('BuyPower Airtime Top-Up Exception', [
                'phone' => substr($phoneNumber, 0, 4) . '****',
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to top up airtime: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 0
            ];
        }
    }

    /**
     * Vend DSTV subscription or Data bundles
     */
    public function vendDstv(
        string $phoneNumber,
        string $smartcardNumber,
        float $amount,
        ?string $customerName = null,
        ?string $email = null,
        ?string $reference = null,
        ?string $disco = null,
        ?string $tariffClass = null
    ): array {
        return $this->circuitBreaker->execute(function () use ($phoneNumber, $smartcardNumber, $amount, $customerName, $email, $reference, $disco, $tariffClass) {
            return $this->performVendDstv($phoneNumber, $smartcardNumber, $amount, $customerName, $email, $reference, $disco, $tariffClass);
        }, ['phone' => $phoneNumber, 'amount' => $amount]);
    }

    /**
     * Perform the actual DSTV/Data bundle vend API call
     */
    protected function performVendDstv(
        string $phoneNumber,
        string $smartcardNumber,
        float $amount,
        ?string $customerName = null,
        ?string $email = null,
        ?string $reference = null,
        ?string $disco = null,
        ?string $tariffClass = null
    ): array {
        try {
            // Validate API key
            if (empty($this->apiKey) || $this->apiKey === '${BUYPOWER_API_KEY}' || strpos($this->apiKey, '${') === 0) {
                $errorMsg = 'BuyPower API key is not configured. Please set BUYPOWER_API_KEY in your .env file.';
                Log::error('BuyPower API Key Missing', [
                    'api_key_value' => $this->apiKey ? 'SET (but invalid)' : 'NOT SET',
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorMsg,
                    'data' => null,
                    'status_code' => 401
                ];
            }
            
            // Validate inputs
            $validationErrors = [];
            
            // Validate smartcard number (10-11 digits) - for DSTV, meter is the smartcard number
            if (empty($smartcardNumber) || !preg_match('/^\d{10,11}$/', $smartcardNumber)) {
                $validationErrors[] = "Invalid smartcard number format. Must be 10-11 digits.";
            }
            
            // Validate amount (minimum ₦100, maximum ₦100,000 for DSTV)
            if ($amount < 100 || $amount > 100000) {
                $validationErrors[] = "Amount must be between ₦100 and ₦100,000. Provided: ₦{$amount}";
            }
            
            // Validate phone number format
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            if (empty($formattedPhone) || strlen($formattedPhone) < 10) {
                $validationErrors[] = "Invalid phone number format: {$phoneNumber}";
            }
            
            // Validate disco - should be DSTV for DSTV subscriptions
            $finalDisco = $disco ? strtoupper($disco) : 'DSTV';
            if ($finalDisco !== 'DSTV') {
                $validationErrors[] = "Invalid disco. For DSTV subscriptions, disco must be 'DSTV'.";
            }
            
            if (!empty($validationErrors)) {
                Log::error('BuyPower DSTV Vend - Input validation failed', [
                    'errors' => $validationErrors,
                    'smartcard' => substr($smartcardNumber, 0, 4) . '****',
                    'amount' => $amount,
                    'phone' => substr($phoneNumber, 0, 4) . '****',
                    'disco' => $disco
                ]);
                
                return [
                    'success' => false,
                    'error' => implode(' ', $validationErrors),
                    'data' => null,
                    'status_code' => 400,
                    'validation_errors' => $validationErrors
                ];
            }
            
            $reference = $reference ?? $this->generateReference();
            
            // Build payload for DSTV subscription
            $payload = [
                'orderId' => $reference,
                'meter' => $smartcardNumber, // DSTV smartcard number
                'disco' => 'DSTV',
                'phone' => $this->formatPhoneNumber($phoneNumber),
                'paymentType' => 'B2B',
                'vendType' => 'PREPAID',
                'vertical' => 'TV',
                'amount' => number_format($amount, 2, '.', ''),
            ];
            
            // Add optional fields if provided
            if ($email) {
                $payload['email'] = $email;
            }
            if ($customerName) {
                $payload['name'] = $customerName;
            }

            SecureLoggingService::logApiRequest(
                $this->baseUrl . '/vend',
                $payload
            );

            $response = Http::timeout(60)
                ->retry(2, 500)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Connection' => 'keep-alive',
                ])
                ->post($this->baseUrl . '/vend', $payload);

            $responseBody = $response->body();
            $responseData = $response->json();
            
            if (is_null($responseData) && !empty($responseBody)) {
                Log::warning('BuyPower DSTV Vend - JSON parse failed', [
                    'status_code' => $response->status(),
                    'raw_response' => $responseBody,
                    'payload' => $payload
                ]);
                $responseData = json_decode($responseBody, true) ?? [];
            }
            
            SecureLoggingService::logApiResponse(
                $this->baseUrl . '/vend',
                $response->status(),
                $responseData
            );

            Log::info('BuyPower DSTV Vend Response', [
                'status_code' => $response->status(),
                'response' => $responseData,
                'payload' => $payload
            ]);

            // Handle 401 Unauthorized
            if ($response->status() === 401) {
                $errorMsg = 'BuyPower API authentication failed. Please check your API key configuration.';
                Log::error('BuyPower DSTV Vend - 401 Unauthorized', [
                    'status_code' => 401,
                    'response' => $responseData,
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorMsg,
                    'data' => $responseData,
                    'status_code' => 401,
                    'requires_config' => true
                ];
            }
            
            // Handle 202 response (Transaction in progress)
            if ($response->status() === 202 && isset($responseData['responseCode']) && $responseData['responseCode'] == 202) {
                $delay = $responseData['delay'][0] ?? 20;
                Log::info('BuyPower DSTV Vend - Transaction in progress, requires requery', [
                    'orderId' => $reference,
                    'delay_seconds' => $delay,
                    'response' => $responseData
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Transaction is still processing. Please requery in ' . $delay . ' seconds.',
                    'data' => $responseData,
                    'status_code' => 202,
                    'retry_after' => $delay
                ];
            }
            
            // Handle 500 errors
            if ($response->status() === 500) {
                $errorMessage = $responseData['message'] ?? 
                               $responseData['error'] ?? 
                               'An unexpected error occurred on the BuyPower API server';
                
                Log::error('BuyPower DSTV Vend - 500 Server Error', [
                    'status_code' => 500,
                    'response' => $responseData,
                    'payload' => $payload
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'data' => $responseData,
                    'status_code' => 500,
                    'is_retryable' => true
                ];
            }
            
            // Check for success response
            $isSuccess = $response->successful() && (
                (isset($responseData['status']) && ($responseData['status'] === true || $responseData['status'] === 'success' || $responseData['status'] === 'ok')) ||
                (isset($responseData['data']['status']) && ($responseData['data']['status'] === true || $responseData['data']['status'] === 'success')) ||
                (isset($responseData['data']['data']['responseCode']) && in_array($responseData['data']['data']['responseCode'], [100, 200, '100', '200'])) ||
                (isset($responseData['data']['responseCode']) && in_array($responseData['data']['responseCode'], [100, 200, '100', '200'])) ||
                (isset($responseData['responseCode']) && in_array($responseData['responseCode'], [100, 200, '100', '200']))
            );
            
            if ($isSuccess) {
                // Extract token/reference from response
                $token = $responseData['data']['data']['token'] ?? 
                         $responseData['data']['token'] ?? 
                         $responseData['token'] ?? 
                         null;
                $orderId = $responseData['data']['data']['orderId'] ?? 
                          $responseData['data']['orderId'] ?? 
                          $responseData['orderId'] ?? 
                          $reference;
                
                Log::info('BuyPower DSTV Vend Successful', [
                    'orderId' => $reference,
                    'smartcard' => substr($smartcardNumber, 0, 4) . '****',
                    'amount' => $amount,
                    'response' => $responseData
                ]);
                
                return [
                    'success' => true,
                    'data' => $responseData,
                    'order_id' => $orderId,
                    'buypower_reference' => $responseData['data']['reference'] ?? $responseData['reference'] ?? $reference,
                    'token' => $token,
                    'status_code' => $response->status()
                ];
            }

            // Handle error response
            $errorMessage = $responseData['message'] ?? 
                          $responseData['error'] ?? 
                          'DSTV subscription failed';
            
            Log::error('BuyPower DSTV Vend Failed', [
                'orderId' => $reference,
                'smartcard' => substr($smartcardNumber, 0, 4) . '****',
                'amount' => $amount,
                'error' => $errorMessage,
                'response' => $responseData
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'data' => $responseData,
                'status_code' => $response->status()
            ];

        } catch (Exception $e) {
            Log::error('BuyPower DSTV Vend Exception', [
                'smartcard' => substr($smartcardNumber, 0, 4) . '****',
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to vend DSTV subscription: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 0
            ];
        }
    }

    /**
     * Generate unique reference
     */
    protected function generateReference(): string
    {
        return 'BP_' . time() . '_' . strtoupper(substr(md5(uniqid()), 0, 6));
    }
}