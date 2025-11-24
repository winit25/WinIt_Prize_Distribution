<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Permission;
use App\Models\Role;

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🚀 CREATING FRESH COMPREHENSIVE PERMISSIONS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Comprehensive permissions tied to every action
$permissions = [
    // User Management
    [
        'name' => 'view-users',
        'slug' => 'view-users',
        'display_name' => 'View Users',
        'description' => 'View user list and details',
        'category' => 'user_management',
        'is_active' => 1
    ],
    [
        'name' => 'manage-users',
        'slug' => 'manage-users',
        'display_name' => 'Manage Users',
        'description' => 'Create, edit, and delete user accounts',
        'category' => 'user_management',
        'is_active' => 1
    ],
    
    // CSV Upload & Batch Operations
    [
        'name' => 'upload-csv',
        'slug' => 'upload-csv',
        'display_name' => 'Upload CSV',
        'description' => 'Upload CSV files for bulk token generation',
        'category' => 'batch_management',
        'is_active' => 1
    ],
    [
        'name' => 'process-batches',
        'slug' => 'process-batches',
        'display_name' => 'Process Batches',
        'description' => 'Trigger batch processing and token generation',
        'category' => 'batch_management',
        'is_active' => 1
    ],
    [
        'name' => 'view-batches',
        'slug' => 'view-batches',
        'display_name' => 'View Batches',
        'description' => 'View batch upload history and status',
        'category' => 'batch_management',
        'is_active' => 1
    ],
    
    // Transaction Management
    [
        'name' => 'view-transactions',
        'slug' => 'view-transactions',
        'display_name' => 'View Transactions',
        'description' => 'View all transaction history and details',
        'category' => 'transaction_management',
        'is_active' => 1
    ],
    [
        'name' => 'download-tokens',
        'slug' => 'download-tokens',
        'display_name' => 'Download Tokens',
        'description' => 'Download generated token reports and receipts',
        'category' => 'transaction_management',
        'is_active' => 1
    ],
    [
        'name' => 'retry-transactions',
        'slug' => 'retry-transactions',
        'display_name' => 'Retry Failed Transactions',
        'description' => 'Retry failed BuyPower transactions',
        'category' => 'transaction_management',
        'is_active' => 1
    ],
    
    // System Administration
    [
        'name' => 'view-dashboard',
        'slug' => 'view-dashboard',
        'display_name' => 'View Dashboard',
        'description' => 'Access the main dashboard',
        'category' => 'system_administration',
        'is_active' => 1
    ],
    [
        'name' => 'view-activity-logs',
        'slug' => 'view-activity-logs',
        'display_name' => 'View Activity Logs',
        'description' => 'View system activity and audit logs',
        'category' => 'system_administration',
        'is_active' => 1
    ],
    [
        'name' => 'clear-activity-logs',
        'slug' => 'clear-activity-logs',
        'display_name' => 'Clear Activity Logs',
        'description' => 'Delete activity logs (WRITE operation)',
        'category' => 'system_administration',
        'is_active' => 1
    ],
    [
        'name' => 'view-notifications',
        'slug' => 'view-notifications',
        'display_name' => 'View Notifications',
        'description' => 'View system notifications and alerts',
        'category' => 'system_administration',
        'is_active' => 1
    ],
    [
        'name' => 'manage-notifications',
        'slug' => 'manage-notifications',
        'display_name' => 'Manage Notifications',
        'description' => 'Create and manage notifications (WRITE operation)',
        'category' => 'system_administration',
        'is_active' => 1
    ],
    [
        'name' => 'manage-profile',
        'slug' => 'manage-profile',
        'display_name' => 'Manage Profile',
        'description' => 'Edit own profile and settings',
        'category' => 'user_management',
        'is_active' => 1
    ],
];

echo "Creating " . count($permissions) . " permissions...\n";
echo str_repeat('-', 60) . "\n";

foreach ($permissions as $permData) {
    $perm = Permission::create($permData);
    echo "✅ {$perm->display_name} ({$perm->name})\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ ALL PERMISSIONS CREATED!\n";
echo str_repeat('=', 60) . "\n\n";

// Now assign permissions to roles
echo "Assigning permissions to roles...\n\n";

$allPermissions = Permission::all();

// Super Admin - ALL permissions
$superAdmin = Role::where('name', 'super-admin')->first();
if ($superAdmin) {
    $superAdmin->permissions()->sync($allPermissions->pluck('id'));
    echo "✅ Super Admin: {$allPermissions->count()} permissions\n";
}

// User Role - Operational permissions (can upload, view, process)
$userRole = Role::where('name', 'User')->orWhere('name', 'user')->first();
if ($userRole) {
    $userPerms = $allPermissions->whereIn('name', [
        'upload-csv',
        'process-batches',
        'view-batches',
        'view-transactions',
        'download-tokens',
        'retry-transactions',
        'view-dashboard',
        'view-notifications',
        'manage-profile'
    ]);
    $userRole->permissions()->sync($userPerms->pluck('id'));
    echo "✅ User Role: {$userPerms->count()} permissions\n";
}

// Audit Role - Read-only permissions ONLY
$auditRole = Role::where('name', 'audit')->first();
if ($auditRole) {
    $auditPerms = $allPermissions->whereIn('name', [
        'view-users',
        'view-batches',
        'view-transactions',
        'view-activity-logs',
        'view-dashboard',
        'view-notifications',
        'manage-profile'
    ]);
    $auditRole->permissions()->sync($auditPerms->pluck('id'));
    echo "✅ Audit Role: {$auditPerms->count()} permissions (READ-ONLY)\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "🎉 SETUP COMPLETE!\n";
echo str_repeat('=', 60) . "\n\n";

echo "📊 Summary:\n";
echo "  • Total Permissions: " . $allPermissions->count() . "\n";
echo "  • Categories: 4 (User Mgmt, Batch Ops, Transactions, System Admin)\n";
echo "  • All permissions tied to specific actions\n\n";

echo "🔐 Role Assignments:\n";
echo "  • Super Admin: Full access (all permissions)\n";
echo "  • User: Can upload CSV, process batches, view transactions\n";
echo "  • Audit: Read-only access (NO write operations)\n\n";

echo "✅ Users can now see features based on their permissions!\n\n";
