<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-superadmin 
                            {--email=admin@winit.com : Email address for the superadmin}
                            {--name=Super Administrator : Name of the superadmin}
                            {--password= : Password (will be generated if not provided)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a superadmin user with all permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 Creating Super Admin User...');
        $this->newLine();

        // Get options
        $email = $this->option('email');
        $name = $this->option('name');
        $password = $this->option('password');

        // Generate password if not provided
        if (!$password) {
            $password = $this->generateSecurePassword();
            $this->info("Generated password: {$password}");
            $this->newLine();
        }

        // Ensure super-admin role exists
        $this->info('📋 Ensuring super-admin role exists...');
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin'],
            [
                'slug' => 'super-admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'is_active' => true,
            ]
        );
        $this->info("✅ Super Admin role exists (ID: {$superAdminRole->id})");

        // Assign all permissions to super-admin role
        $this->info('🔑 Assigning all permissions to super-admin role...');
        $allPermissions = Permission::all();
        if ($allPermissions->isEmpty()) {
            $this->warn('⚠️  No permissions found. Run php artisan db:seed --class=PermissionSeeder first.');
        } else {
            $superAdminRole->permissions()->sync($allPermissions->pluck('id'));
            $this->info("✅ {$allPermissions->count()} permissions assigned to super-admin role");
        }

        // Create or update superadmin user
        $this->info('👤 Creating/updating superadmin user...');
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );
        $this->info("✅ User created/updated (ID: {$user->id})");

        // Assign super-admin role to user
        $this->info('🔗 Assigning super-admin role to user...');
        if (!$user->roles->contains('id', $superAdminRole->id)) {
            $user->roles()->attach($superAdminRole->id);
            $this->info('✅ Super-admin role assigned');
        } else {
            $this->info('✅ User already has super-admin role');
        }

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('✅ SUPER ADMIN CREATED SUCCESSFULLY!');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        $this->info('📧 Email:    ' . $email);
        $this->info('🔑 Password: ' . $password);
        $this->newLine();
        $this->info('⚠️  IMPORTANT: Save these credentials securely!');
        $this->newLine();

        return Command::SUCCESS;
    }

    /**
     * Generate a secure random password
     */
    private function generateSecurePassword(): string
    {
        $length = 16;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        // Ensure at least one of each type
        $password .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[random_int(0, 25)]; // uppercase
        $password .= 'abcdefghijklmnopqrstuvwxyz'[random_int(0, 25)]; // lowercase
        $password .= '0123456789'[random_int(0, 9)]; // digit
        $password .= '!@#$%^&*'[random_int(0, 7)]; // special
        
        // Fill the rest randomly
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        // Shuffle the password
        return str_shuffle($password);
    }
}

