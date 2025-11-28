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
        $superAdminUser = User::updateOrCreate(
            ['email' => 'superadmin@buypower.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('SuperAdmin@2025'),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );

        // Assign super-admin role to user
        if (!$superAdminUser->roles->contains('id', $superAdminRole->id)) {
            $superAdminUser->roles()->attach($superAdminRole->id);
        }

        $this->command->info('✅ Superadmin user created/updated');
        $this->command->info('   Email: superadmin@buypower.com');
        $this->command->info('   Password: SuperAdmin@2025');
    }
}
