<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email delivery configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Testing email configuration...");
        $this->info("Sending test email to: {$email}");
        
        try {
            // Test basic mail configuration
            $config = config('mail.mailers.smtp');
            $this->info("SMTP Host: " . $config['host']);
            $this->info("SMTP Port: " . $config['port']);
            $this->info("Encryption: " . ($config['encryption'] ?? 'none'));
            $this->info("Username: " . ($config['username'] ? 'set' : 'not set'));
            $this->info("From Address: " . config('mail.from.address'));
            $this->info("From Name: " . config('mail.from.name'));
            
            // Send test email
            Mail::raw('This is a test email from WinIt Prize Distribution system. If you receive this, your email configuration is working correctly.', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email - WinIt Prize Distribution');
            });
            
            $this->info("✅ Test email sent successfully!");
            $this->info("Please check your inbox (and spam folder) for the test email.");
            
            Log::info('Test email sent', [
                'to' => $email,
                'from' => config('mail.from.address'),
                'host' => $config['host']
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to send test email!");
            $this->error("Error: " . $e->getMessage());
            
            Log::error('Test email failed', [
                'to' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->warn("\nTroubleshooting tips:");
            $this->warn("1. Check that MAIL_USERNAME and MAIL_PASSWORD are correct in .env");
            $this->warn("2. For Gmail, ensure you're using an App Password (not regular password)");
            $this->warn("3. Check that port 587 is not blocked by firewall");
            $this->warn("4. Verify MAIL_ENCRYPTION is 'tls' for port 587 (or 'ssl' for port 465)");
            $this->warn("5. Check Gmail account security settings (Less secure app access or App Passwords)");
            
            return Command::FAILURE;
        }
    }
}

