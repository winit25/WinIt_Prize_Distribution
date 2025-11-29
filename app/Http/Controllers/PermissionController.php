<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole('super-admin') && !auth()->user()->hasRole('Super Admin')) {
                abort(403, 'Unauthorized access. Super Admin role required.');
            }
            return $next($request);
        });
    }

    /**
     * Display permissions management dashboard
     */
    public function index()
    {
        // Get ALL permissions, including inactive ones for admin view
        $permissions = Permission::with('roles')->orderBy('category')->orderBy('name')->get();
        $roles = Role::with('permissions')->orderBy('name')->get();
        $users = User::with('roles')->orderBy('name')->get();

        // Group permissions by category
        $permissionsByCategory = $permissions->groupBy('category');

        // Log for debugging
        Log::info('Permissions loaded', [
            'total_permissions' => $permissions->count(),
            'by_category' => $permissionsByCategory->map->count()->toArray()
        ]);

        return view('permissions.index', compact('permissions', 'roles', 'users', 'permissionsByCategory'));
    }

    /**
     * Store a new permission
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'required|string|in:user_management,batch_management,transaction_management,system_administration',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $permission = Permission::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'category' => $request->category,
                'is_active' => $request->boolean('is_active', true)
            ]);

            Log::info('Permission created', [
                'permission_id' => $permission->id,
                'name' => $permission->name,
                'created_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully!',
                'permission' => $permission->load('roles')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create permission', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a permission
     */
    public function update(Request $request, Permission $permission)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'required|string|in:user_management,batch_management,transaction_management,system_administration',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $permission->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'category' => $request->category,
                'is_active' => $request->boolean('is_active', true)
            ]);

            Log::info('Permission updated', [
                'permission_id' => $permission->id,
                'name' => $permission->name,
                'updated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permission updated successfully!',
                'permission' => $permission->load('roles')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update permission', [
                'error' => $e->getMessage(),
                'permission_id' => $permission->id,
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a permission
     */
    public function destroy(Permission $permission)
    {
        try {
            DB::beginTransaction();

            // Check if permission is assigned to any roles
            if ($permission->roles()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete permission. It is assigned to one or more roles.'
                ], 400);
            }

            Log::info('Permission deleted', [
                'permission_id' => $permission->id,
                'name' => $permission->name,
                'deleted_by' => auth()->id()
            ]);

            $permission->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permission deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete permission', [
                'error' => $e->getMessage(),
                'permission_id' => $permission->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new role
     */
    public function storeRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'slug' => 'nullable|string|max:255|unique:roles,slug',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $request->name,
                'slug' => $request->slug ?? $request->name, // Auto-generate slug from name if not provided
                'display_name' => $request->display_name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true)
            ]);

            Log::info('Role created', [
                'role_id' => $role->id,
                'name' => $role->name,
                'created_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully!',
                'role' => $role->load('permissions')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create role', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a role
     */
    public function updateRole(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'slug' => 'nullable|string|max:255|unique:roles,slug,' . $role->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $role->update([
                'name' => $request->name,
                'slug' => $request->slug ?? $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true)
            ]);

            Log::info('Role updated', [
                'role_id' => $role->id,
                'name' => $role->name,
                'updated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully!',
                'role' => $role->load('permissions')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update role', [
                'error' => $e->getMessage(),
                'role_id' => $role->id,
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a role
     */
    public function destroyRole(Role $role)
    {
        try {
            DB::beginTransaction();

            // Check if role is assigned to any users
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role. It is assigned to one or more users.'
                ], 400);
            }

            Log::info('Role deleted', [
                'role_id' => $role->id,
                'name' => $role->name,
                'deleted_by' => auth()->id()
            ]);

            $role->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete role', [
                'error' => $e->getMessage(),
                'role_id' => $role->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign permissions to role
     */
    public function assignPermissionsToRole(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $role->syncPermissions($request->permission_ids);

            Log::info('Permissions assigned to role', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'permission_count' => count($request->permission_ids),
                'assigned_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permissions assigned to role successfully!',
                'role' => $role->load('permissions')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign permissions to role', [
                'error' => $e->getMessage(),
                'role_id' => $role->id,
                'permission_ids' => $request->permission_ids
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign roles to user
     */
    public function assignRolesToUser(Request $request, $id)
    {
        $user = \App\Models\User::find($id);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user->roles()->sync($request->role_ids);

            Log::info('Roles assigned to user', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'role_count' => count($request->role_ids),
                'assigned_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Roles assigned to user successfully!',
                'user' => $user->load('roles')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign roles to user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'role_ids' => $request->role_ids
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign roles: ' . $e->getMessage()
            ], 500);
        }
    }
}
