<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test super admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@buypower.app',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        
        // Assign super-admin role
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdmin->roles()->attach($superAdminRole->id);
        }

        // Create a test regular user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@buypower.app',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        
        // Assign user role
        $userRole = Role::where('name', 'user')->first();
        if ($userRole) {
            $user->roles()->attach($userRole->id);
        }

        $this->command->info('Test users created successfully!');
        $this->command->info('Super Admin: admin@buypower.app / password123');
        $this->command->info('User: user@buypower.app / password123');
    }
}
