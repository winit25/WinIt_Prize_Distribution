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
        // Create permissions
        $permissions = [
            // User Management
            ['name' => 'manage-users', 'slug' => 'manage-users'],
            ['name' => 'view-users', 'slug' => 'view-users'],
            
            // Bulk Token Operations
            ['name' => 'upload-csv', 'slug' => 'upload-csv'],
            ['name' => 'process-batches', 'slug' => 'process-batches'],
            ['name' => 'view-batches', 'slug' => 'view-batches'],
            
            // Transaction Management
            ['name' => 'view-transactions', 'slug' => 'view-transactions'],
            ['name' => 'download-tokens', 'slug' => 'download-tokens'],
            
            // Activity Logs
            ['name' => 'view-activity-logs', 'slug' => 'view-activity-logs'],
            ['name' => 'clear-activity-logs', 'slug' => 'clear-activity-logs'],
            
            // Notifications
            ['name' => 'view-notifications', 'slug' => 'view-notifications'],
            ['name' => 'manage-notifications', 'slug' => 'manage-notifications'],
            
            // Dashboard
            ['name' => 'view-dashboard', 'slug' => 'view-dashboard'],
            
            // Profile Management
            ['name' => 'manage-profile', 'slug' => 'manage-profile'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create roles
        $roles = [
            [
                'name' => 'super-admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'is_active' => true,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Administrative access with most permissions except user management',
                'is_active' => true,
            ],
            [
                'name' => 'user',
                'display_name' => 'Standard User',
                'description' => 'Standard user with basic permissions for token operations',
                'is_active' => true,
            ],
            [
                'name' => 'audit',
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
                    'view-dashboard',
                    'manage-profile',
                    'view-notifications',
                    'manage-notifications'
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
                    'view-notifications'
                ]);
                $role->permissions()->sync($auditPermissions->pluck('id'));
                break;
        }
    }
}
