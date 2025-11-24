<?php

namespace Tests\Unit;

use App\Contracts\BuyPowerApiInterface;
use App\Services\MockBuyPowerApiService;
use Tests\TestCase;

class BuyPowerApiServiceTest extends TestCase
{
    protected BuyPowerApiInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BuyPowerApiInterface::class);
    }

    /** @test */
    public function it_can_get_account_balance()
    {
        $result = $this->service->getBalance();

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('status_code', $result);
        $this->assertEquals(200, $result['status_code']);
    }

    /** @test */
    public function it_can_create_electricity_order()
    {
        $result = $this->service->createElectricityOrder(
            phoneNumber: '08012345678',
            disco: 'EKO',
            amount: 1000.00,
            meterNumber: '12345678901',
            meterType: 'prepaid'
        );

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('order_id', $result);
        $this->assertArrayHasKey('reference', $result);
        $this->assertEquals(200, $result['status_code']);
    }

    /** @test */
    public function it_can_vend_electricity_token()
    {
        // First create an order
        $orderResult = $this->service->createElectricityOrder(
            phoneNumber: '08012345678',
            disco: 'EKO',
            amount: 1000.00,
            meterNumber: '12345678901',
            meterType: 'prepaid'
        );

        $this->assertTrue($orderResult['success']);

        // Then vend the token
        $vendResult = $this->service->vendElectricity($orderResult['order_id']);

        $this->assertTrue($vendResult['success']);
        $this->assertArrayHasKey('token', $vendResult);
        $this->assertArrayHasKey('units', $vendResult);
        $this->assertNotEmpty($vendResult['token']);
        $this->assertNotEmpty($vendResult['units']);
    }

    /** @test */
    public function it_can_complete_send_token_flow()
    {
        $result = $this->service->sendToken(
            phoneNumber: '08012345678',
            amount: 1000.00,
            disco: 'EKO',
            meterNumber: '12345678901',
            meterType: 'prepaid'
        );

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('units', $result);
        $this->assertArrayHasKey('order_id', $result);
        $this->assertArrayHasKey('reference', $result);
        $this->assertNotEmpty($result['token']);
        $this->assertNotEmpty($result['units']);
    }

    /** @test */
    public function it_can_get_order_details()
    {
        // First create an order
        $orderResult = $this->service->createElectricityOrder(
            phoneNumber: '08012345678',
            disco: 'EKO',
            amount: 1000.00,
            meterNumber: '12345678901',
            meterType: 'prepaid'
        );

        $this->assertTrue($orderResult['success']);

        // Then get the order details
        $getResult = $this->service->getOrder($orderResult['order_id']);

        $this->assertTrue($getResult['success']);
        $this->assertArrayHasKey('data', $getResult);
        $this->assertEquals(200, $getResult['status_code']);
    }

    /** @test */
    public function it_can_get_transaction_history()
    {
        $result = $this->service->getTransactionHistory(page: 1, limit: 10);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('transactions', $result);
        $this->assertIsArray($result['transactions']);
    }

    /** @test */
    public function it_can_check_transaction_status()
    {
        // First create an order
        $orderResult = $this->service->createElectricityOrder(
            phoneNumber: '08012345678',
            disco: 'EKO',
            amount: 1000.00,
            meterNumber: '12345678901',
            meterType: 'prepaid'
        );

        $this->assertTrue($orderResult['success']);

        // Check the transaction status
        $statusResult = $this->service->checkTransactionStatus($orderResult['reference']);

        $this->assertTrue($statusResult['success']);
        $this->assertArrayHasKey('data', $statusResult);
    }

    /** @test */
    public function it_handles_different_disco_codes()
    {
        $discoCodes = ['EKO', 'IKEJA', 'ABUJA', 'IBADAN', 'ENUGU', 'PH', 'JOS', 'KADUNA', 'KANO'];

        foreach ($discoCodes as $disco) {
            $result = $this->service->createElectricityOrder(
                phoneNumber: '08012345678',
                disco: $disco,
                amount: 1000.00,
                meterNumber: '12345678901',
                meterType: 'prepaid'
            );

            $this->assertTrue(
                $result['success'],
                "Failed to create order for DISCO: {$disco}"
            );
        }
    }

    /** @test */
    public function it_handles_different_meter_types()
    {
        $meterTypes = ['prepaid', 'postpaid'];

        foreach ($meterTypes as $meterType) {
            $result = $this->service->sendToken(
                phoneNumber: '08012345678',
                amount: 1000.00,
                disco: 'EKO',
                meterNumber: '12345678901',
                meterType: $meterType
            );

            $this->assertTrue(
                $result['success'],
                "Failed to send token for meter type: {$meterType}"
            );
        }
    }

    /** @test */
    public function it_generates_unique_references()
    {
        $result1 = $this->service->createElectricityOrder(
            phoneNumber: '08012345678',
            disco: 'EKO',
            amount: 1000.00,
            meterNumber: '12345678901',
            meterType: 'prepaid'
        );

        $result2 = $this->service->createElectricityOrder(
            phoneNumber: '08012345678',
            disco: 'EKO',
            amount: 1000.00,
            meterNumber: '12345678901',
            meterType: 'prepaid'
        );

        $this->assertTrue($result1['success']);
        $this->assertTrue($result2['success']);
        $this->assertNotEquals($result1['reference'], $result2['reference']);
    }
}
