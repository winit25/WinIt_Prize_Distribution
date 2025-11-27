<?php

namespace App\Services;

use App\Contracts\BuyPowerApiInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class MockBuyPowerApiService implements BuyPowerApiInterface
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('buypower.api_url', 'https://idev.buypower.ng/v2');
        $this->apiKey = config('buypower.api_key');
        $this->timeout = config('buypower.timeout', 30);
    }

    /**
     * Create electricity order (Mock)
     */
    public function createElectricityOrder(string $phoneNumber, string $disco, float $amount, string $meterNumber, string $meterType = 'prepaid', ?string $customerName = null, ?string $address = null, ?string $reference = null): array
    {
        try {
            $reference = $reference ?? $this->generateReference();
            
            // Simulate API delay
            usleep(100000); // 100ms delay
            
            Log::info('Mock BuyPower Create Order', [
                'phone' => $phoneNumber,
                'disco' => $disco,
                'amount' => $amount,
                'meter' => $meterNumber,
                'reference' => $reference
            ]);

            // Mock successful response (mimics BuyPower's /vend endpoint)
            $token = $this->generateMockToken();
            $units = $this->calculateMockUnits($reference);
            
            return [
                'success' => true,
                'data' => [
                    'status' => true,
                    'message' => 'Successful transaction',
                    'responseCode' => 200,
                    'data' => [
                        'orderId' => $reference,
                        'token' => $token,
                        'units' => $units,
                        'amount' => $amount,
                        'phone' => $phoneNumber,
                        'meter' => $meterNumber,
                        'disco' => $disco
                    ]
                ],
                'reference' => $reference,
                'order_id' => $reference,
                'token' => $token,
                'units' => $units,
                'status_code' => 200
            ];

        } catch (Exception $e) {
            Log::error('Mock BuyPower Create Order Error', [
                'phone' => $phoneNumber,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Mock API error: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 500
            ];
        }
    }

    /**
     * Vend electricity token (Mock)
     */
    public function vendElectricity(string $orderId): array
    {
        try {
            // Simulate API delay
            usleep(150000); // 150ms delay
            
            Log::info('Mock BuyPower Vend', [
                'order_id' => $orderId
            ]);

            // Generate mock token and units
            $token = $this->generateMockToken();
            $units = $this->calculateMockUnits($orderId);

            return [
                'success' => true,
                'data' => [
                    'status' => 'success',
                    'message' => 'Token vended successfully',
                    'data' => [
                        'orderId' => $orderId,
                        'token' => $token,
                        'units' => $units,
                        'status' => 'completed'
                    ]
                ],
                'token' => $token,
                'units' => $units,
                'status_code' => 200
            ];

        } catch (Exception $e) {
            Log::error('Mock BuyPower Vend Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Mock vending failed: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 500
            ];
        }
    }

    /**
     * Send token to a phone number (Complete flow: Create Order + Vend)
     * Note: Mock now mimics BuyPower's /vend endpoint which does both in one call
     */
    public function sendToken(string $phoneNumber, float $amount, string $disco, string $meterNumber, string $meterType = 'prepaid', ?string $customerName = null, ?string $address = null, ?string $reference = null): array
    {
        // Mock BuyPower's /vend endpoint behavior - single call does everything
        return $this->createElectricityOrder($phoneNumber, $disco, $amount, $meterNumber, $meterType, $customerName, $address, $reference);
    }

    /**
     * Get order details/status (Mock)
     */
    public function getOrder(string $orderId): array
    {
        try {
            // Simulate API delay
            usleep(50000); // 50ms delay
            
            Log::info('Mock BuyPower Get Order', [
                'order_id' => $orderId
            ]);

            return [
                'success' => true,
                'data' => [
                    'status' => 'success',
                    'data' => [
                        'orderId' => $orderId,
                        'status' => 'completed',
                        'token' => $this->generateMockToken(),
                        'units' => $this->calculateMockUnits($orderId)
                    ]
                ],
                'status_code' => 200
            ];

        } catch (Exception $e) {
            Log::error('Mock BuyPower Get Order Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Mock get order failed: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 500
            ];
        }
    }

    /**
     * Get transaction history (Mock)
     */
    public function getTransactionHistory(int $page = 1, int $limit = 50): array
    {
        try {
            // Simulate API delay
            usleep(75000); // 75ms delay
            
            Log::info('Mock BuyPower Transaction History', [
                'page' => $page,
                'limit' => $limit
            ]);

            return [
                'success' => true,
                'data' => [
                    'status' => 'success',
                    'data' => [
                        'transactions' => [],
                        'pagination' => [
                            'page' => $page,
                            'limit' => $limit,
                            'total' => 0
                        ]
                    ]
                ],
                'transactions' => [],
                'status_code' => 200
            ];

        } catch (Exception $e) {
            Log::error('Mock BuyPower Transaction History Error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Mock transaction history failed: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 500
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
     * Get account balance (Mock)
     */
    public function getBalance(): array
    {
        try {
            // Simulate API delay
            usleep(50000); // 50ms delay
            
            Log::info('Mock BuyPower Balance Check');

            return [
                'success' => true,
                'data' => [
                    'status' => 'success',
                    'data' => [
                        'balance' => 50000.00,
                        'currency' => 'NGN',
                        'account_status' => 'active'
                    ]
                ],
                'status_code' => 200
            ];

        } catch (Exception $e) {
            Log::error('Mock BuyPower Balance Error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Mock balance check failed: ' . $e->getMessage(),
                'data' => null,
                'status_code' => 500
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
        return 'MOCK_' . time() . '_' . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    /**
     * Generate mock token
     */
    protected function generateMockToken(): string
    {
        return str_pad(mt_rand(1000000000000000, 9999999999999999), 16, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate mock units based on amount
     */
    protected function calculateMockUnits(string $orderId): string
    {
        // Extract amount from order ID or use a default calculation
        $baseUnits = 10.5;
        $randomFactor = mt_rand(80, 120) / 100; // 0.8 to 1.2
        return number_format($baseUnits * $randomFactor, 2);
    }
}
