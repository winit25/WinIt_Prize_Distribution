<?php

namespace App\Contracts;

interface BuyPowerApiInterface
{
    /**
     * Create electricity order
     */
    public function createElectricityOrder(
        string $phoneNumber, 
        string $disco, 
        float $amount, 
        string $meterNumber, 
        string $meterType = 'prepaid', 
        ?string $customerName = null, 
        ?string $address = null, 
        ?string $reference = null
    ): array;

    /**
     * Vend electricity token
     */
    public function vendElectricity(string $orderId): array;

    /**
     * Send token to a phone number (Complete flow: Create Order + Vend)
     */
    public function sendToken(
        string $phoneNumber, 
        float $amount, 
        string $disco, 
        string $meterNumber, 
        string $meterType = 'prepaid', 
        ?string $customerName = null, 
        ?string $address = null, 
        ?string $reference = null
    ): array;

    /**
     * Get order details/status
     */
    public function getOrder(string $orderId): array;

    /**
     * Get transaction history
     */
    public function getTransactionHistory(int $page = 1, int $limit = 50): array;

    /**
     * Check transaction status (alias for getOrder)
     */
    public function checkTransactionStatus(string $reference): array;

    /**
     * Get account balance
     */
    public function getBalance(): array;

    /**
     * Top up airtime (VTU)
     */
    public function topUpAirtime(
        string $phoneNumber,
        float $amount,
        ?string $customerName = null,
        ?string $reference = null
    ): array;

    /**
     * Vend DSTV subscription (TV) or Data bundles
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
    ): array;
}
