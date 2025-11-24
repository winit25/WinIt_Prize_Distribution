@extends('layouts.sidebar')

@section('title', 'Roles & Permissions - WinIt Prize Distribution')

@push('styles')
<style>
    :root {
        --winit-primary: rgb(18, 18, 104);
        --winit-accent: #17f7b6;
        --winit-light: #f8fafc;
        --winit-border: #e2e8f0;
        --winit-text: #1e293b;
        --winit-text-light: #64748b;
    }

    .modern-role-card {
        background: white;
        border: 2px solid var(--winit-border);
        border-radius: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .modern-role-card:hover {
        border-color: var(--winit-accent);
        box-shadow: 0 4px 12px rgba(18, 18, 104, 0.15);
    }

    .role-card-header {
        background: linear-gradient(135deg, var(--winit-primary) 0%, #1e1e6b 100%);
        color: white;
        padding: 1.5rem;
        border-bottom: 3px solid var(--winit-accent);
    }

    .role-card-body {
        padding: 1.5rem;
    }

    .permission-toggle-container {
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }

    .permission-category-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--winit-primary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 24px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }

    input:checked + .toggle-slider {
        background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-accent) 100%);
    }

    input:checked + .toggle-slider:before {
        transform: translateX(24px);
    }

    .permission-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--winit-border);
    }

    .permission-row:last-child {
        border-bottom: none;
    }

    .permission-label {
        flex: 1;
        font-size: 0.9rem;
        color: var(--winit-text);
    }

    .permission-label small {
        display: block;
        color: var(--winit-text-light);
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .permission-label {
        cursor: pointer;
    }

    .btn:disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--winit-primary) 0%, #1e1e6b 100%);
        border: none;
        border-radius: 0.75rem;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #1e1e6b 0%, var(--winit-primary) 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(18, 18, 104, 0.3);
    }

    .btn-outline-primary {
        border: 2px solid var(--winit-primary);
        color: var(--winit-primary);
        background: transparent;
        border-radius: 0.75rem;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background: var(--winit-accent);
        border-color: var(--winit-accent);
        color: var(--winit-primary);
        transform: translateY(-1px);
    }

    .form-control {
        border: 2px solid var(--winit-border);
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        font-family: 'Montserrat', sans-serif;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--winit-accent);
        box-shadow: 0 0 0 0.2rem rgba(23, 247, 182, 0.25);
    }

    .badge {
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        border-radius: 0.5rem;
    }

    .modal-content {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 10px 40px rgba(18, 18, 104, 0.2);
    }

    .modal-header {
        background: linear-gradient(135deg, var(--winit-primary) 0%, #1e1e6b 100%);
        color: white;
        border-radius: 1rem 1rem 0 0;
        border: none;
    }

    .modal-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
    }

    .nav-tabs {
        border-bottom: 2px solid var(--winit-border);
    }

    .nav-tabs .nav-link {
        border: none;
        border-radius: 0.75rem 0.75rem 0 0;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        color: var(--winit-text-light);
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link.active {
        background: linear-gradient(135deg, var(--winit-primary) 0%, #1e1e6b 100%);
        color: white;
        border-color: var(--winit-primary);
    }

    .nav-tabs .nav-link:hover {
        border-color: var(--winit-accent);
        color: var(--winit-primary);
    }

    .table {
        font-family: 'Montserrat', sans-serif;
    }

    .table th {
        background: linear-gradient(135deg, var(--winit-primary) 0%, #1e1e6b 100%);
        color: white;
        border: none;
        font-weight: 600;
    }

    .table td {
        border-color: var(--winit-border);
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background: linear-gradient(135deg, rgba(23, 247, 182, 0.05) 0%, rgba(23, 247, 182, 0.02) 100%);
    }

    .role-badge {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .role-badge-super-admin {
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        color: white;
    }

    .role-badge-user {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        color: white;
    }

    .role-badge-audit {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--winit-text-light);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0" style="font-family: 'Montserrat', sans-serif; font-weight: 700; color: var(--winit-primary);">
                        <i class="fas fa-shield-alt me-2"></i>Roles & Permissions
                    </h2>
                    <p class="text-muted mb-0">Simple, click-to-configure role management</p>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                        <i class="fas fa-plus me-1"></i>Create New Role
                    </button>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs mb-4" id="permissionsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles" type="button" role="tab">
                        <i class="fas fa-user-tag me-1"></i>Roles
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                        <i class="fas fa-users me-1"></i>Users
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="permissionsTabsContent">
                <!-- Roles Tab -->
                <div class="tab-pane fade show active" id="roles" role="tabpanel">
                    <div class="row">
                        @forelse($roles as $role)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="modern-role-card">
                                <div class="role-card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h4 class="mb-1" style="font-family: 'Montserrat', sans-serif; font-weight: 600;">
                                                <i class="fas fa-user-shield me-2"></i>{{ $role->display_name }}
                                            </h4>
                                            <p class="mb-0" style="font-size: 0.875rem; opacity: 0.9;">{{ $role->description }}</p>
                                        </div>
                                        {!! $role->status_badge !!}
                                    </div>
                                </div>
                                
                                <div class="role-card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted fw-bold">PERMISSIONS</small>
                                            <span class="badge bg-primary">{{ $role->permissions->count() }}</span>
                                        </div>
                                        @if($role->permissions->count() > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($role->permissions->take(4) as $permission)
                                                <span class="badge" style="background-color: rgba(18, 18, 104, 0.1); color: var(--winit-primary); font-size: 0.75rem;">
                                                    {{ $permission->display_name }}
                                                </span>
                                                @endforeach
                                                @if($role->permissions->count() > 4)
                                                <span class="badge bg-secondary" style="font-size: 0.75rem;">+{{ $role->permissions->count() - 4 }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <p class="text-muted small mb-0">No permissions assigned</p>
                                        @endif
                                    </div>

                                    <div class="d-flex gap-2 mt-3">
                                        <button class="btn btn-outline-primary btn-sm flex-fill" onclick="manageRolePermissions({{ $role->id }})">
                                            <i class="fas fa-sliders-h me-1"></i>Configure
                                        </button>
                                        @if(!in_array($role->name, ['super-admin', 'audit']))
                                        <button class="btn btn-outline-danger btn-sm" onclick="deleteRole({{ $role->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="fas fa-user-tag"></i>
                                <h5>No roles found</h5>
                                <p class="text-muted">Create your first role to get started</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Users Tab -->
                <div class="tab-pane fade" id="users" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0" style="font-family: 'Montserrat', sans-serif; font-weight: 600;">
                                                    {{ $user->name }}
                                                </h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @foreach($user->roles as $role)
                                        <span class="badge bg-primary me-1">{{ $role->display_name }}</span>
                                        @endforeach
                                        @if($user->roles->isEmpty())
                                        <span class="text-muted">No roles assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->email_verified_at)
                                        <span class="badge bg-success">Verified</span>
                                        @else
                                        <span class="badge bg-warning">Unverified</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="manageUserRoles({{ $user->id }})">
                                            <i class="fas fa-user-tag me-1"></i>Manage Roles
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Create New Role</h5>
                    <p class="text-white-50 mb-0 small">Define a role and assign permissions</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createRoleForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Role Name</label>
                            <input type="text" class="form-control" name="display_name" placeholder="e.g., Customer Support" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">System Identifier</label>
                            <input type="text" class="form-control" name="name" placeholder="e.g., customer-support" required>
                            <small class="form-text text-muted">Lowercase, use hyphens</small>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="What can this role do?"></textarea>
                    </div>

                    <hr>

                    <h6 class="mb-3 fw-bold" style="color: var(--winit-primary);"><i class="fas fa-key me-2"></i>Assign Permissions</h6>
                    
                    @php
                        $permissionCategories = [
                            'user_management' => ['icon' => 'users', 'label' => 'User Management'],
                            'batch_management' => ['icon' => 'boxes', 'label' => 'Batch Operations'],
                            'transaction_management' => ['icon' => 'exchange-alt', 'label' => 'Transactions'],
                            'system_administration' => ['icon' => 'cog', 'label' => 'System Admin']
                        ];
                    @endphp

                    @foreach($permissionsByCategory as $category => $categoryPermissions)
                        <div class="permission-toggle-container">
                            <div class="permission-category-title">
                                <i class="fas fa-{{ $permissionCategories[$category]['icon'] ?? 'folder' }}"></i>
                                {{ $permissionCategories[$category]['label'] ?? ucfirst(str_replace('_', ' ', $category)) }}
                            </div>
                            @foreach($categoryPermissions as $permission)
                                <div class="permission-row">
                                    <label class="permission-label" for="create_perm_{{ $permission->id }}">
                                        {{ $permission->display_name }}
                                        @if($permission->description)
                                            <small>{{ $permission->description }}</small>
                                        @endif
                                    </label>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" id="create_perm_{{ $permission->id }}">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i>Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Role Permissions Modal -->
<div class="modal fade" id="manageRolePermissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="manageRoleTitle">Configure Permissions</h5>
                    <p class="text-white-50 mb-0 small" id="manageRoleSubtitle">Toggle permissions on/off</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="manageRolePermissionsForm">
                <div class="modal-body">
                    <input type="hidden" id="roleId" name="role_id">
                    <div id="permissionsList">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Manage User Roles Modal -->
<div class="modal fade" id="manageUserRolesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage User Roles</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="manageUserRolesForm">
                <div class="modal-body">
                    <input type="hidden" id="userId" name="user_id">
                    <div id="userRolesList"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Roles</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Pass PHP data to JavaScript
const rolesData = @json($roles);
const permissionsData = @json($permissions);
const usersData = @json($users);
const permissionsByCategory = @json($permissionsByCategory);

// Utility functions
const utils = {
    showAlert: function(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const icon = type === 'success' ? 'check-circle' : 
                    type === 'error' ? 'exclamation-circle' : 
                    type === 'warning' ? 'exclamation-triangle' : 'info-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 role="alert" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <i class="fas fa-${icon} me-2"></i>
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Insert notification
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto-dismiss after 4 seconds
        const alerts = document.querySelectorAll('.alert');
        const lastAlert = alerts[alerts.length - 1];
        setTimeout(() => {
            if (lastAlert) {
                const bsAlert = new bootstrap.Alert(lastAlert);
                bsAlert.close();
            }
        }, 4000);
    },

    makeRequest: async function(url, options = {}) {
        try {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    ...options.headers
                },
                ...options
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }
            
            return data;
        } catch (error) {
            console.error('Request failed:', error);
            throw error;
        }
    }
};

// Permission category icons and labels
const permissionCategories = {
    'user_management': { icon: 'users', label: 'User Management' },
    'batch_management': { icon: 'boxes', label: 'Batch Operations' },
    'transaction_management': { icon: 'exchange-alt', label: 'Transactions' },
    'system_administration': { icon: 'cog', label: 'System Admin' }
};

// Auto-generate slug from name field
document.querySelector('#createRoleForm input[name="name"]').addEventListener('input', function(e) {
    const nameField = e.target;
    const displayNameField = document.querySelector('#createRoleForm input[name="display_name"]');
    
    // Auto-fill display name if empty
    if (!displayNameField.value) {
        displayNameField.value = nameField.value.split('-').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }
});

// Create Role with Permissions
document.getElementById('createRoleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const roleName = formData.get('name');
    
    const data = {
        name: roleName,
        slug: roleName, // Use same value for slug
        display_name: formData.get('display_name'),
        description: formData.get('description'),
        is_active: true,
        permission_ids: formData.getAll('permission_ids[]')
    };
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    try {
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating...';
        
        // Create the role first
        const roleResult = await utils.makeRequest('/permissions/roles', {
            method: 'POST',
            body: JSON.stringify({
                name: data.name,
                display_name: data.display_name,
                description: data.description,
                is_active: data.is_active
            })
        });
        
        // Then assign permissions if any were selected
        if (data.permission_ids.length > 0) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Assigning permissions...';
            await utils.makeRequest(`/permissions/roles/${roleResult.role.id}/permissions`, {
                method: 'POST',
                body: JSON.stringify({ permission_ids: data.permission_ids })
            });
        }
        
        utils.showAlert(`Role "${data.display_name}" created successfully!`, 'success');
        bootstrap.Modal.getInstance(document.getElementById('createRoleModal')).hide();
        this.reset();
        
        // Reload after showing notification
        setTimeout(() => location.reload(), 1200);
    } catch (error) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        utils.showAlert(error.message || 'Failed to create role', 'error');
    }
});


// Delete Role
function deleteRole(roleId) {
    const role = rolesData.find(r => r.id === roleId);
    const roleName = role ? role.display_name : 'this role';
    
    if (confirm(`⚠️ Are you sure you want to delete "${roleName}"?\n\nThis action cannot be undone.`)) {
        // Show temporary loading notification
        utils.showAlert('Deleting role...', 'info');
        
        utils.makeRequest(`/permissions/roles/${roleId}`, {
            method: 'DELETE'
        }).then(result => {
            utils.showAlert(result.message || `Role "${roleName}" deleted successfully!`, 'success');
            setTimeout(() => location.reload(), 1200);
        }).catch(error => {
            utils.showAlert(error.message || 'Failed to delete role', 'error');
        });
    }
}

// Manage Role Permissions with Toggle Interface
function manageRolePermissions(roleId) {
    const role = rolesData.find(r => r.id === roleId);
    
    if (!role) {
        utils.showAlert('Role not found!', 'error');
        return;
    }
    
    // Update modal title
    document.getElementById('manageRoleTitle').textContent = `Configure: ${role.display_name}`;
    document.getElementById('manageRoleSubtitle').textContent = role.description || 'Toggle permissions on/off';
    
    const modal = new bootstrap.Modal(document.getElementById('manageRolePermissionsModal'));
    document.getElementById('roleId').value = roleId;
    
    const permissionsList = document.getElementById('permissionsList');
    const rolePermissions = role.permissions || [];
    
    // Use grouped permissions from backend
    const groupedPermissions = {};
    Object.keys(permissionsByCategory).forEach(category => {
        groupedPermissions[category] = permissionsByCategory[category];
    });
    
    let html = '';
    
    Object.keys(groupedPermissions).forEach(category => {
        const catConfig = permissionCategories[category] || { icon: 'folder', label: category.replace('_', ' ').toUpperCase() };
        
        html += `
            <div class="permission-toggle-container">
                <div class="permission-category-title">
                    <i class="fas fa-${catConfig.icon}"></i>
                    ${catConfig.label}
                </div>`;
        
        groupedPermissions[category].forEach(permission => {
            const isChecked = rolePermissions.some(rp => rp.id === permission.id);
            const isAuditRole = role.name === 'audit';
            const isWritePermission = ['upload-csv', 'process-batches', 'manage-users', 'clear-activity-logs', 'manage-notifications'].includes(permission.name);
            
            // Disable write permissions for Audit role
            const disabled = isAuditRole && isWritePermission ? 'disabled' : '';
            const disabledClass = disabled ? 'opacity-50' : '';
            
            html += `
                <div class="permission-row ${disabledClass}">
                    <label class="permission-label" for="perm_${permission.id}">
                        ${permission.display_name}
                        ${permission.description ? `<small>${permission.description}</small>` : ''}
                        ${disabled ? '<small class="text-danger"><i class="fas fa-lock me-1"></i>Read-only role restriction</small>' : ''}
                    </label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="permission_ids[]" value="${permission.id}" 
                               id="perm_${permission.id}" ${isChecked ? 'checked' : ''} ${disabled}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>`;
        });
        
        html += '</div>';
    });
    
    permissionsList.innerHTML = html;
    modal.show();
}

// Manage User Roles
function manageUserRoles(userId) {
    const user = usersData.find(u => u.id === userId);
    
    if (!user) {
        utils.showAlert('User not found!', 'error');
        return;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('manageUserRolesModal'));
    document.getElementById('userId').value = userId;
    
    const userRolesList = document.getElementById('userRolesList');
    const userRoles = user.roles || [];
    
    let html = '<div class="mb-3"><h6 class="text-muted">Select roles for <strong>' + user.name + '</strong></h6></div>';
    
    rolesData.forEach(role => {
        const isChecked = userRoles.some(ur => ur.id === role.id);
        html += `
            <div class="form-check mb-3 p-3 border rounded" style="transition: all 0.3s;" 
                 onmouseover="this.style.backgroundColor='#f8fafc'" 
                 onmouseout="this.style.backgroundColor='white'">
                <input class="form-check-input" type="checkbox" name="role_ids[]" 
                       value="${role.id}" id="role_${role.id}" ${isChecked ? 'checked' : ''}>
                <label class="form-check-label w-100 cursor-pointer" for="role_${role.id}">
                    <strong>${role.display_name || role.name}</strong>
                    ${role.description ? `<small class="text-muted d-block mt-1">${role.description}</small>` : ''}
                </label>
            </div>
        `;
    });
    
    userRolesList.innerHTML = html;
    modal.show();
}

// Save Role Permissions
document.getElementById('manageRolePermissionsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const permissionIds = formData.getAll('permission_ids[]');
    const roleId = formData.get('role_id');
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    try {
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        
        const result = await utils.makeRequest(`/permissions/roles/${roleId}/permissions`, {
            method: 'POST',
            body: JSON.stringify({ permission_ids: permissionIds })
        });
        
        utils.showAlert(result.message || 'Permissions updated successfully!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('manageRolePermissionsModal')).hide();
        
        // Reload after short delay to show notification
        setTimeout(() => location.reload(), 1000);
    } catch (error) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        utils.showAlert(error.message || 'Failed to update permissions', 'error');
    }
});

// Save User Roles
document.getElementById('manageUserRolesForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const roleIds = formData.getAll('role_ids[]');
    const userId = formData.get('user_id');
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    try {
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        
        const result = await utils.makeRequest(`/permissions/users/${userId}/roles`, {
            method: 'POST',
            body: JSON.stringify({ role_ids: roleIds })
        });
        
        utils.showAlert(result.message || 'User roles updated successfully!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('manageUserRolesModal')).hide();
        
        // Reload after short delay to show notification
        setTimeout(() => location.reload(), 1000);
    } catch (error) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        utils.showAlert(error.message || 'Failed to update roles', 'error');
    }
});

</script>
@endpush
