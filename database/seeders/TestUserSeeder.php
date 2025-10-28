<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@winit.ng',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Create a test regular user
        User::create([
            'name' => 'Test User',
            'email' => 'user@winit.ng',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->command->info('Test users created successfully!');
        $this->command->info('Admin: admin@winit.ng / password123');
        $this->command->info('User: user@winit.ng / password123');
    }
}