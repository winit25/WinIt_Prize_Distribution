<?php

namespace App\Console\Commands;

use App\Models\BatchUpload;
use App\Models\Recipient;
use App\Models\Transaction;
use App\Services\BuyPowerApiService;
use App\Services\TermiiSmsService;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestTokenVendingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buypower:test-vending {--phone=08012345678} {--amount=1000} {--disco=EKO} {--meter=12345678901}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the complete token vending flow with BuyPower API and SMS notifications';

    protected BuyPowerApiService $buyPowerService;
    protected TermiiSmsService $termiiSmsService;
    protected NotificationService $notificationService;

    public function __construct(
        BuyPowerApiService $buyPowerService, 
        TermiiSmsService $termiiSmsService,
        NotificationService $notificationService
    ) {
        parent::__construct();
        $this->buyPowerService = $buyPowerService;
        $this->termiiSmsService = $termiiSmsService;
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->option('phone');
        $amount = (float) $this->option('amount');
        $disco = $this->option('disco');
        $meter = $this->option('meter');

        $this->info("Testing Token Vending Flow");
        $this->info("Phone: {$phone}");
        $this->info("Amount: ₦{$amount}");
        $this->info("Disco: {$disco}");
        $this->info("Meter: {$meter}");
        $this->newLine();

        // Step 1: Test BuyPower API Connection
        $this->info("Step 1: Testing BuyPower API Connection...");
        $balanceResult = $this->buyPowerService->getBalance();
        
        if ($balanceResult['success']) {
            $this->info("✓ BuyPower API connection successful");
            $this->line("Balance: " . json_encode($balanceResult['data']));
        } else {
            $this->warn("⚠ BuyPower API connection failed: " . $balanceResult['error']);
            $this->line("This might be expected in development mode");
        }
        $this->newLine();

        // Step 2: Test Token Vending
        $this->info("Step 2: Testing Token Vending...");
        $tokenResult = $this->buyPowerService->sendToken(
            $phone,
            $amount,
            $disco,
            $meter,
            'prepaid',
            'Test Customer',
            'Test Address'
        );

        if ($tokenResult['success']) {
            $this->info("✓ Token vending successful!");
            $this->line("Token: " . ($tokenResult['token'] ?? 'N/A'));
            $this->line("Units: " . ($tokenResult['units'] ?? 'N/A'));
            $this->line("Order ID: " . ($tokenResult['order_id'] ?? 'N/A'));
            
            $token = $tokenResult['token'] ?? 'TEST123456789';
            $units = $tokenResult['units'] ?? '10.5';
        } else {
            $this->warn("⚠ Token vending failed: " . $tokenResult['error']);
            $this->line("Using test token for SMS testing...");
            $token = 'TEST123456789';
            $units = '10.5';
        }
        $this->newLine();

        // Step 3: Test SMS Service
        $this->info("Step 3: Testing SMS Service...");
        $smsResult = $this->termiiSmsService->sendTokenSms(
            $phone,
            $token,
            $amount,
            $disco,
            $meter,
            $units
        );

        if ($smsResult['success']) {
            $this->info("✓ SMS sent successfully!");
            $this->line("Message ID: " . ($smsResult['message_id'] ?? 'N/A'));
        } else {
            $this->warn("⚠ SMS sending failed: " . $smsResult['error']);
            $this->line("This might be due to missing Termii API key");
        }
        $this->newLine();

        // Step 4: Test Complete Notification Flow
        $this->info("Step 4: Testing Complete Notification Flow...");
        
        try {
            // Create test transaction record
            $transaction = Transaction::create([
                'recipient_id' => 1, // Assuming recipient exists
                'batch_upload_id' => 1, // Assuming batch exists
                'buypower_reference' => 'TEST_' . time(),
                'order_id' => $tokenResult['order_id'] ?? 'TEST_ORDER',
                'phone_number' => $phone,
                'amount' => $amount,
                'status' => $tokenResult['success'] ? 'success' : 'failed',
                'api_response' => $tokenResult['data'] ?? [],
                'token' => $token,
                'units' => $units,
                'error_message' => $tokenResult['success'] ? null : $tokenResult['error'],
                'processed_at' => now()
            ]);

            // Test notification service
            $notificationResult = $this->notificationService->sendTransactionNotifications(
                $transaction,
                "Test notification for token: {$token}",
                true, // Send SMS
                true  // Send Email
            );

            $this->info("✓ Notification service test completed");
            $this->line("SMS Result: " . ($notificationResult['sms']['success'] ? 'Success' : 'Failed'));
            $this->line("Email Result: " . ($notificationResult['email']['success'] ? 'Success' : 'Failed'));

        } catch (\Exception $e) {
            $this->error("✗ Notification test failed: " . $e->getMessage());
        }

        $this->newLine();
        $this->info("Token Vending Test Complete!");
        
        return Command::SUCCESS;
    }
}
