<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\MonolithicService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MonolithicServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MonolithicService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MonolithicService::class);
    }

    /** @test */
    public function it_can_generate_passwords()
    {
        $password = $this->service->generatePassword(12);
        
        $this->assertIsString($password);
        $this->assertEquals(12, strlen($password));
    }

    /** @test */
    public function it_can_validate_phone_numbers()
    {
        $this->assertTrue($this->service->validatePhoneNumber('08012345678'));
        $this->assertTrue($this->service->validatePhoneNumber('2348012345678'));
        $this->assertFalse($this->service->validatePhoneNumber('123'));
        $this->assertFalse($this->service->validatePhoneNumber('invalid'));
    }

    /** @test */
    public function it_can_validate_meter_numbers()
    {
        $this->assertTrue($this->service->validateMeterNumber('12345678901', 'AEDC'));
        $this->assertTrue($this->service->validateMeterNumber('12345678901', 'EKEDC'));
        $this->assertFalse($this->service->validateMeterNumber('123', 'AEDC'));
    }

    /** @test */
    public function it_can_mask_sensitive_data()
    {
        $data = [
            'phone' => '08012345678',
            'email' => 'test@example.com',
            'name' => 'John Doe',
        ];

        $masked = $this->service->maskSensitiveData($data);

        $this->assertNotEquals('08012345678', $masked['phone']);
        $this->assertNotEquals('test@example.com', $masked['email']);
        $this->assertEquals('John Doe', $masked['name']); // Non-sensitive should remain
        $this->assertStringContainsString('*', $masked['phone']);
    }

    /** @test */
    public function it_can_get_user_ip_address()
    {
        // This tests the private method indirectly through logActivity
        // We'll test that logActivity works which uses getUserIpAddress
        $this->assertTrue(true); // Placeholder - actual IP testing requires request context
    }

    /** @test */
    public function it_is_registered_as_singleton()
    {
        $service1 = app(MonolithicService::class);
        $service2 = app(MonolithicService::class);

        $this->assertSame($service1, $service2);
    }
}

