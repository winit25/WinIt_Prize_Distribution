<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create simplified, clear permissions organized by category
        $permissions = [
            // User Management (Write operations)
            [
                'name' => 'manage-users',
                'slug' => 'manage-users',
                'display_name' => 'Manage Users',
                'description' => 'Create, edit, and delete user accounts',
                'category' => 'user_management',
                'is_active' => true
            ],
            [
                'name' => 'view-users',
                'slug' => 'view-users',
                'display_name' => 'View Users',
                'description' => 'View user list and details',
                'category' => 'user_management',
                'is_active' => true
            ],
            
            // Batch Management (Critical write operations)
            [
                'name' => 'upload-csv',
                'slug' => 'upload-csv',
                'display_name' => 'Upload CSV',
                'description' => 'Upload CSV files for bulk token generation (WRITE)',
                'category' => 'batch_management',
                'is_active' => true
            ],
            [
                'name' => 'process-batches',
                'slug' => 'process-batches',
                'display_name' => 'Process Batches',
                'description' => 'Trigger batch processing (WRITE)',
                'category' => 'batch_management',
                'is_active' => true
            ],
            [
                'name' => 'view-batches',
                'slug' => 'view-batches',
                'display_name' => 'View Batches',
                'description' => 'View batch upload history and status',
                'category' => 'batch_management',
                'is_active' => true
            ],
            
            // Transaction Management
            [
                'name' => 'view-transactions',
                'slug' => 'view-transactions',
                'display_name' => 'View Transactions',
                'description' => 'View transaction history and details',
                'category' => 'transaction_management',
                'is_active' => true
            ],
            [
                'name' => 'download-tokens',
                'slug' => 'download-tokens',
                'display_name' => 'Download Tokens',
                'description' => 'Download generated token reports',
                'category' => 'transaction_management',
                'is_active' => true
            ],
            
            // System Administration
            [
                'name' => 'view-activity-logs',
                'slug' => 'view-activity-logs',
                'display_name' => 'View Activity Logs',
                'description' => 'View system activity and audit logs',
                'category' => 'system_administration',
                'is_active' => true
            ],
            [
                'name' => 'clear-activity-logs',
                'slug' => 'clear-activity-logs',
                'display_name' => 'Clear Activity Logs',
                'description' => 'Delete activity logs (WRITE)',
                'category' => 'system_administration',
                'is_active' => true
            ],
            [
                'name' => 'view-notifications',
                'slug' => 'view-notifications',
                'display_name' => 'View Notifications',
                'description' => 'View system notifications',
                'category' => 'system_administration',
                'is_active' => true
            ],
            [
                'name' => 'manage-notifications',
                'slug' => 'manage-notifications',
                'display_name' => 'Manage Notifications',
                'description' => 'Create and manage notifications (WRITE)',
                'category' => 'system_administration',
                'is_active' => true
            ],
            [
                'name' => 'view-dashboard',
                'slug' => 'view-dashboard',
                'display_name' => 'View Dashboard',
                'description' => 'Access the main dashboard',
                'category' => 'system_administration',
                'is_active' => true
            ],
            [
                'name' => 'manage-profile',
                'slug' => 'manage-profile',
                'display_name' => 'Manage Profile',
                'description' => 'Edit own profile and settings',
                'category' => 'user_management',
                'is_active' => true
            ],
            [
                'name' => 'retry-transactions',
                'slug' => 'retry-transactions',
                'display_name' => 'Retry Transactions',
                'description' => 'Retry failed transactions',
                'category' => 'transaction_management',
                'is_active' => true
            ],
            [
                'name' => 'manage-roles',
                'slug' => 'manage-roles',
                'display_name' => 'Manage Roles',
                'description' => 'Create, edit, and delete roles',
                'category' => 'user_management',
                'is_active' => true
            ],
            [
                'name' => 'manage-permissions',
                'slug' => 'manage-permissions',
                'display_name' => 'Manage Permissions',
                'description' => 'Create, edit, and delete permissions',
                'category' => 'system_administration',
                'is_active' => true
            ],
            [
                'name' => 'view-reports',
                'slug' => 'view-reports',
                'display_name' => 'View Reports',
                'description' => 'View system reports and analytics',
                'category' => 'system_administration',
                'is_active' => true
            ],
            [
                'name' => 'export-data',
                'slug' => 'export-data',
                'display_name' => 'Export Data',
                'description' => 'Export data to CSV/Excel',
                'category' => 'transaction_management',
                'is_active' => true
            ],
            [
                'name' => 'view-airtime',
                'slug' => 'view-airtime',
                'display_name' => 'View Airtime',
                'description' => 'View airtime transactions and history',
                'category' => 'transaction_management',
                'is_active' => true
            ],
            [
                'name' => 'view-dstv',
                'slug' => 'view-dstv',
                'display_name' => 'View DSTV',
                'description' => 'View DSTV subscriptions and transactions',
                'category' => 'transaction_management',
                'is_active' => true
            ],
            [
                'name' => 'cancel-transactions',
                'slug' => 'cancel-transactions',
                'display_name' => 'Cancel Transactions',
                'description' => 'Cancel pending or processing transactions',
                'category' => 'transaction_management',
                'is_active' => true
            ],
            [
                'name' => 'refund-transactions',
                'slug' => 'refund-transactions',
                'display_name' => 'Refund Transactions',
                'description' => 'Process refunds for failed transactions',
                'category' => 'transaction_management',
                'is_active' => true
            ],
            [
                'name' => 'view-device-fingerprints',
                'slug' => 'view-device-fingerprints',
                'display_name' => 'View Device Fingerprints',
                'description' => 'View device fingerprint information',
                'category' => 'user_management',
                'is_active' => true
            ],
            [
                'name' => 'manage-device-fingerprints',
                'slug' => 'manage-device-fingerprints',
                'display_name' => 'Manage Device Fingerprints',
                'description' => 'Manage and deactivate device fingerprints',
                'category' => 'user_management',
                'is_active' => true
            ],
            [
                'name' => 'view-balance',
                'slug' => 'view-balance',
                'display_name' => 'View Balance',
                'description' => 'View API balance and account balance',
                'category' => 'system_administration',
                'is_active' => true
            ],
            [
                'name' => 'manage-settings',
                'slug' => 'manage-settings',
                'display_name' => 'Manage Settings',
                'description' => 'Manage system settings and configuration',
                'category' => 'system_administration',
                'is_active' => true
            ],
            [
                'name' => 'view-settings',
                'slug' => 'view-settings',
                'display_name' => 'View Settings',
                'description' => 'View system settings',
                'category' => 'system_administration',
                'is_active' => true
            ],
            [
                'name' => 'manage-api-keys',
                'slug' => 'manage-api-keys',
                'display_name' => 'Manage API Keys',
                'description' => 'Manage API keys and credentials',
                'category' => 'system_administration',
                'is_active' => true
            ],
            [
                'name' => 'view-api-keys',
                'slug' => 'view-api-keys',
                'display_name' => 'View API Keys',
                'description' => 'View API keys (masked)',
                'category' => 'system_administration',
                'is_active' => true
            ],
            [
                'name' => 'search-data',
                'slug' => 'search-data',
                'display_name' => 'Search Data',
                'description' => 'Search across transactions, batches, and users',
                'category' => 'system_administration',
                'is_active' => true
            ],
            [
                'name' => 'view-health',
                'slug' => 'view-health',
                'display_name' => 'View Health Status',
                'description' => 'View system health and metrics',
                'category' => 'system_administration',
                'is_active' => true
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        // Create roles
        $roles = [
            [
                'name' => 'super-admin',
                'slug' => 'super-admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'is_active' => true,
            ],
            [
                'name' => 'admin',
                'slug' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Administrative access with most permissions except user management',
                'is_active' => true,
            ],
            [
                'name' => 'user',
                'slug' => 'user',
                'display_name' => 'Standard User',
                'description' => 'Standard user with basic permissions for token operations',
                'is_active' => true,
            ],
            [
                'name' => 'audit',
                'slug' => 'audit',
                'display_name' => 'Auditor',
                'description' => 'Read-only access for auditing and monitoring',
                'is_active' => true,
            ],
            [
                'name' => 'manager',
                'slug' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Management role with operational oversight and reporting access',
                'is_active' => true,
            ],
            [
                'name' => 'support',
                'slug' => 'support',
                'display_name' => 'Support Staff',
                'description' => 'Customer support role with limited operational access',
                'is_active' => true,
            ],
            [
                'name' => 'finance',
                'slug' => 'finance',
                'display_name' => 'Finance',
                'description' => 'Finance role with access to transactions, reports, and financial data',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );

            // Assign permissions based on role
            $this->assignPermissionsToRole($role);
        }
    }

    /**
     * Assign permissions to a role based on the role type
     */
    private function assignPermissionsToRole(Role $role): void
    {
        $permissions = Permission::all();
        
        switch ($role->name) {
            case 'super-admin':
                // Super admin gets all permissions
                $role->permissions()->sync($permissions->pluck('id'));
                break;
                
            case 'admin':
                // Admin gets most permissions except user management
                $adminPermissions = $permissions->whereNotIn('name', [
                    'manage-users',
                    'view-users'
                ]);
                $role->permissions()->sync($adminPermissions->pluck('id'));
                break;
                
            case 'user':
                // Standard user gets basic operational permissions
                $userPermissions = $permissions->whereIn('name', [
                    'upload-csv',
                    'process-batches',
                    'view-batches',
                    'view-transactions',
                    'view-airtime',
                    'view-dstv',
                    'download-tokens',
                    'retry-transactions',
                    'view-dashboard',
                    'manage-profile',
                    'view-notifications',
                    'manage-notifications',
                    'export-data',
                    'search-data'
                ]);
                $role->permissions()->sync($userPermissions->pluck('id'));
                break;
                
            case 'audit':
                // Audit role gets read-only permissions
                $auditPermissions = $permissions->whereIn('name', [
                    'view-users',
                    'view-batches',
                    'view-transactions',
                    'view-airtime',
                    'view-dstv',
                    'view-activity-logs',
                    'view-dashboard',
                    'manage-profile',
                    'view-notifications',
                    'view-reports',
                    'export-data',
                    'view-device-fingerprints',
                    'view-balance',
                    'view-settings',
                    'view-api-keys',
                    'search-data',
                    'view-health'
                ]);
                $role->permissions()->sync($auditPermissions->pluck('id'));
                break;
                
            case 'manager':
                // Manager gets operational oversight and reporting permissions
                $managerPermissions = $permissions->whereIn('name', [
                    'view-users',
                    'view-batches',
                    'view-transactions',
                    'view-airtime',
                    'view-dstv',
                    'download-tokens',
                    'retry-transactions',
                    'cancel-transactions',
                    'view-dashboard',
                    'manage-profile',
                    'view-notifications',
                    'manage-notifications',
                    'view-reports',
                    'export-data',
                    'view-activity-logs',
                    'view-balance',
                    'view-settings',
                    'view-api-keys',
                    'search-data',
                    'view-health'
                ]);
                $role->permissions()->sync($managerPermissions->pluck('id'));
                break;
                
            case 'support':
                // Support staff gets limited operational access
                $supportPermissions = $permissions->whereIn('name', [
                    'view-users',
                    'view-batches',
                    'view-transactions',
                    'view-airtime',
                    'view-dstv',
                    'view-dashboard',
                    'manage-profile',
                    'view-notifications',
                    'manage-notifications',
                    'retry-transactions',
                    'search-data'
                ]);
                $role->permissions()->sync($supportPermissions->pluck('id'));
                break;
                
            case 'finance':
                // Finance role gets financial and reporting permissions
                $financePermissions = $permissions->whereIn('name', [
                    'view-users',
                    'view-batches',
                    'view-transactions',
                    'view-airtime',
                    'view-dstv',
                    'download-tokens',
                    'view-dashboard',
                    'manage-profile',
                    'view-notifications',
                    'view-reports',
                    'export-data',
                    'view-balance',
                    'refund-transactions',
                    'view-activity-logs',
                    'search-data'
                ]);
                $role->permissions()->sync($financePermissions->pluck('id'));
                break;
        }
    }
}
