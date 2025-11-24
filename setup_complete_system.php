<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🚀 COMPLETE SYSTEM SETUP\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Step 1: Create tables if they don't exist
echo "Step 1: Ensuring tables exist...\n";

try {
    DB::statement('CREATE TABLE IF NOT EXISTS permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) UNIQUE NOT NULL,
        slug VARCHAR(255) NOT NULL,
        display_name VARCHAR(255),
        description TEXT,
        category VARCHAR(255),
        module VARCHAR(255),
        is_active BOOLEAN DEFAULT 1,
        created_at DATETIME,
        updated_at DATETIME
    )');
    
    DB::statement('CREATE TABLE IF NOT EXISTS roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) UNIQUE NOT NULL,
        slug VARCHAR(255) NOT NULL,
        display_name VARCHAR(255),
        description TEXT,
        is_active BOOLEAN DEFAULT 1,
        created_at DATETIME,
        updated_at DATETIME
    )');
    
    DB::statement('CREATE TABLE IF NOT EXISTS role_permission (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        role_id INTEGER NOT NULL,
        permission_id INTEGER NOT NULL,
        created_at DATETIME,
        updated_at DATETIME
    )');
    
    DB::statement('CREATE TABLE IF NOT EXISTS user_role (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        role_id INTEGER NOT NULL,
        created_at DATETIME,
        updated_at DATETIME
    )');
    
    echo "✅ Tables ready\n\n";
} catch (\Exception $e) {
    echo "⚠️  Tables may already exist: " . $e->getMessage() . "\n\n";
}

// Step 2: Clear old data
echo "Step 2: Clearing old permissions and roles...\n";
DB::table('role_permission')->delete();
DB::table('user_role')->where('role_id', '>', 0)->delete();
DB::table('permissions')->delete();
DB::table('roles')->delete();
echo "✅ Cleared\n\n";

// Step 3: Create comprehensive permissions
echo "Step 3: Creating comprehensive permissions...\n";

$permissions = [
    // User Management
    ['name' => 'view-users', 'slug' => 'view-users', 'display_name' => 'View Users', 'description' => 'View user list and details', 'category' => 'user_management'],
    ['name' => 'manage-users', 'slug' => 'manage-users', 'display_name' => 'Manage Users', 'description' => 'Create, edit, and delete user accounts', 'category' => 'user_management'],
    
    // CSV Upload & Batch Operations
    ['name' => 'upload-csv', 'slug' => 'upload-csv', 'display_name' => 'Upload CSV', 'description' => 'Upload CSV files for bulk token generation', 'category' => 'batch_management'],
    ['name' => 'process-batches', 'slug' => 'process-batches', 'display_name' => 'Process Batches', 'description' => 'Trigger batch processing', 'category' => 'batch_management'],
    ['name' => 'view-batches', 'slug' => 'view-batches', 'display_name' => 'View Batches', 'description' => 'View batch history', 'category' => 'batch_management'],
    
    // Transactions
    ['name' => 'view-transactions', 'slug' => 'view-transactions', 'display_name' => 'View Transactions', 'description' => 'View all transaction history', 'category' => 'transaction_management'],
    ['name' => 'download-tokens', 'slug' => 'download-tokens', 'display_name' => 'Download Tokens', 'description' => 'Download token reports', 'category' => 'transaction_management'],
    ['name' => 'retry-transactions', 'slug' => 'retry-transactions', 'display_name' => 'Retry Transactions', 'description' => 'Retry failed transactions', 'category' => 'transaction_management'],
    
    // System Administration
    ['name' => 'view-dashboard', 'slug' => 'view-dashboard', 'display_name' => 'View Dashboard', 'description' => 'Access dashboard', 'category' => 'system_administration'],
    ['name' => 'view-activity-logs', 'slug' => 'view-activity-logs', 'display_name' => 'View Activity Logs', 'description' => 'View audit logs', 'category' => 'system_administration'],
    ['name' => 'clear-activity-logs', 'slug' => 'clear-activity-logs', 'display_name' => 'Clear Activity Logs', 'description' => 'Delete activity logs', 'category' => 'system_administration'],
    ['name' => 'view-notifications', 'slug' => 'view-notifications', 'display_name' => 'View Notifications', 'description' => 'View notifications', 'category' => 'system_administration'],
    ['name' => 'manage-notifications', 'slug' => 'manage-notifications', 'display_name' => 'Manage Notifications', 'description' => 'Create/edit notifications', 'category' => 'system_administration'],
    ['name' => 'manage-profile', 'slug' => 'manage-profile', 'display_name' => 'Manage Profile', 'description' => 'Edit own profile', 'category' => 'user_management'],
];

foreach ($permissions as $permData) {
    $permData['is_active'] = 1;
    $permData['created_at'] = now();
    $permData['updated_at'] = now();
    DB::table('permissions')->insert($permData);
    echo "  ✅ {$permData['display_name']}\n";
}

echo "\n";

// Step 4: Create Super Admin Role
echo "Step 4: Creating Super Admin role...\n";

DB::table('roles')->insert([
    'name' => 'super-admin',
    'slug' => 'super-admin',
    'display_name' => 'Super Administrator',
    'description' => 'Full system access with all permissions',
    'is_active' => 1,
    'created_at' => now(),
    'updated_at' => now()
]);

$superAdminRole = DB::table('roles')->where('name', 'super-admin')->first();
echo "✅ Super Admin role created (ID: {$superAdminRole->id})\n\n";

// Step 5: Assign ALL permissions to Super Admin
echo "Step 5: Assigning all permissions to Super Admin...\n";

$allPermissions = DB::table('permissions')->get();
foreach ($allPermissions as $perm) {
    DB::table('role_permission')->insert([
        'role_id' => $superAdminRole->id,
        'permission_id' => $perm->id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

echo "✅ {$allPermissions->count()} permissions assigned to Super Admin\n\n";

// Step 6: Create/Update Super Admin User
echo "Step 6: Creating Super Admin user...\n";

$superAdminUser = User::updateOrCreate(
    ['email' => 'superadmin@buypower.com'],
    [
        'name' => 'Super Administrator',
        'password' => Hash::make('SuperAdmin@2025'),
        'email_verified_at' => now(),
        'must_change_password' => false
    ]
);

// Assign super-admin role to user
DB::table('user_role')->updateOrInsert(
    ['user_id' => $superAdminUser->id, 'role_id' => $superAdminRole->id],
    ['created_at' => now(), 'updated_at' => now()]
);

echo "✅ Super Admin user created/updated\n\n";

// Step 7: Create User and Audit roles
echo "Step 7: Creating User and Audit roles...\n";

// User Role
DB::table('roles')->insertOrIgnore([
    'name' => 'user',
    'slug' => 'user',
    'display_name' => 'Standard User',
    'description' => 'Standard user with operational permissions',
    'is_active' => 1,
    'created_at' => now(),
    'updated_at' => now()
]);

// Audit Role
DB::table('roles')->insertOrIgnore([
    'name' => 'audit',
    'slug' => 'audit',
    'display_name' => 'Auditor',
    'description' => 'Read-only access for auditing',
    'is_active' => 1,
    'created_at' => now(),
    'updated_at' => now()
]);

echo "✅ User and Audit roles created\n\n";

// Final Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ SETUP COMPLETE!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "📊 Summary:\n";
echo "  • Permissions: {$allPermissions->count()}\n";
echo "  • Roles: 3 (Super Admin, User, Audit)\n";
echo "  • Super Admin has ALL permissions\n\n";

echo "🔐 LOGIN CREDENTIALS:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Email:    superadmin@buypower.com\n";
echo "Password: SuperAdmin@2025\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "✅ You can now:\n";
echo "  1. Log in at /login\n";
echo "  2. Access all features including CSV Upload\n";
echo "  3. Manage roles and permissions at /permissions\n\n";

echo "🎉 System ready!\n\n";
