<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This creates a superadmin user that can login immediately after deployment.
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
        $permissions = Permission::all();
        if ($permissions->isNotEmpty()) {
            $superAdminRole->permissions()->sync($permissions->pluck('id'));
        }

        // Create or update superadmin user
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@buypower.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('SuperAdmin@2025'),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );

        // Assign super-admin role to user
        if (!$superAdmin->roles->contains('id', $superAdminRole->id)) {
            $superAdmin->roles()->attach($superAdminRole->id);
        }

        $this->command->info('✅ Superadmin user created/updated');
        $this->command->info('📧 Email: superadmin@buypower.com');
        $this->command->info('🔑 Password: SuperAdmin@2025');
    }
}

