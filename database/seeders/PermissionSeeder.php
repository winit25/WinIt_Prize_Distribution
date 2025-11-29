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
                    'download-tokens',
                    'retry-transactions',
                    'view-dashboard',
                    'manage-profile',
                    'view-notifications',
                    'manage-notifications',
                    'export-data'
                ]);
                $role->permissions()->sync($userPermissions->pluck('id'));
                break;
                
            case 'audit':
                // Audit role gets read-only permissions
                $auditPermissions = $permissions->whereIn('name', [
                    'view-users',
                    'view-batches',
                    'view-transactions',
                    'view-activity-logs',
                    'view-dashboard',
                    'manage-profile',
                    'view-notifications',
                    'view-reports',
                    'export-data'
                ]);
                $role->permissions()->sync($auditPermissions->pluck('id'));
                break;
        }
    }
}
