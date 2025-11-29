<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure super-admin role exists
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin'],
            [
                'slug' => 'super-admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'is_active' => true,
            ]
        );

        // Assign all permissions to super-admin role
        $allPermissions = Permission::all();
        if ($allPermissions->isNotEmpty()) {
            $superAdminRole->permissions()->sync($allPermissions->pluck('id'));
            $this->command->info("✅ Assigned {$allPermissions->count()} permissions to super-admin role");
        }

        // Create or update superadmin user
        $superAdminEmail = 'superadmin@winit.com';
        $superAdminPassword = 'SuperAdmin@2025!Secure';
        
        $superAdminUser = User::updateOrCreate(
            ['email' => $superAdminEmail],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make($superAdminPassword),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );

        // Ensure super-admin role is assigned (sync to ensure it's the only role)
        $superAdminUser->roles()->sync([$superAdminRole->id]);

        // Double-check: Ensure all permissions are assigned to the role
        $allPermissions = Permission::all();
        if ($allPermissions->isNotEmpty()) {
            $superAdminRole->permissions()->sync($allPermissions->pluck('id'));
            $this->command->info("✅ Assigned {$allPermissions->count()} permissions to super-admin role");
        }

        $this->command->info('✅ Superadmin user created/updated');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('🔐 SUPERADMIN CREDENTIALS:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info("   Email:    {$superAdminEmail}");
        $this->command->info("   Password: {$superAdminPassword}");
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info("   Permissions: {$allPermissions->count()} (ALL PERMISSIONS)");
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
