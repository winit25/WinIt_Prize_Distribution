@extends('layouts.sidebar')

@section('title', 'User Management - WinIt Prize Distribution')

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

    .user-management-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 2px 8px rgba(18, 18, 104, 0.1);
        border: 1px solid var(--winit-border);
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .user-management-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(18, 18, 104, 0.15);
        border-color: var(--winit-accent);
    }

    .user-header {
        background: linear-gradient(135deg, var(--winit-primary) 0%, #1e1e6b 100%);
        color: white;
        border-radius: 1rem 1rem 0 0;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .user-header h2 {
        margin: 0;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 1.5rem;
    }

    .user-filters {
        background: white;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(18, 18, 104, 0.1);
        border: 1px solid var(--winit-border);
    }

    .filter-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 0.5rem 1rem;
        border: 2px solid var(--winit-border);
        border-radius: 0.75rem;
        background: white;
        color: var(--winit-text-light);
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: var(--winit-accent);
        border-color: var(--winit-accent);
        color: var(--winit-primary);
        transform: translateY(-1px);
    }

    .user-item {
        background: white;
        border: 1px solid var(--winit-border);
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        position: relative;
    }

    .user-item:hover {
        border-color: var(--winit-accent);
        background: linear-gradient(135deg, rgba(23, 247, 182, 0.05) 0%, rgba(23, 247, 182, 0.02) 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(23, 247, 182, 0.1);
    }

    .user-item.inactive {
        opacity: 0.6;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(239, 68, 68, 0.02) 100%);
    }

    .user-avatar {
        width: 60px;
        height: 60px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 600;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .user-avatar.super-admin {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .user-avatar.admin {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .user-avatar.user {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .user-avatar.audit {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
    }

    .user-content {
        flex: 1;
    }

    .user-name {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--winit-text);
        margin: 0 0 0.5rem 0;
    }

    .user-email {
        color: var(--winit-text-light);
        font-size: 0.95rem;
        margin: 0 0 1rem 0;
    }

    .user-roles {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .role-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        font-family: 'Montserrat', sans-serif;
    }

    .role-badge.super-admin {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .role-badge.admin {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .role-badge.user {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .role-badge.audit {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
    }

    .user-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.875rem;
        color: var(--winit-text-light);
    }

    .user-time {
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
    }

    .user-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        padding: 0.375rem 0.75rem;
        border: 1px solid var(--winit-border);
        border-radius: 0.5rem;
        background: white;
        color: var(--winit-text-light);
        font-size: 0.875rem;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .action-btn:hover {
        background: var(--winit-accent);
        border-color: var(--winit-accent);
        color: var(--winit-primary);
    }

    .action-btn.edit {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border-color: #3b82f6;
    }

    .action-btn.edit:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border-color: #2563eb;
        color: white;
    }

    .action-btn.delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border-color: #ef4444;
    }

    .action-btn.delete:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        border-color: #dc2626;
        color: white;
    }

    .action-btn.reset-password {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-color: #f59e0b;
    }

    .action-btn.reset-password:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        border-color: #d97706;
        color: white;
    }

    .action-btn.reset-device {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border-color: #10b981;
    }

    .action-btn.reset-device:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        border-color: #059669;
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: var(--winit-text-light);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(18, 18, 104, 0.1);
        border: 1px solid var(--winit-border);
        text-align: center;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(18, 18, 104, 0.15);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        font-family: 'Montserrat', sans-serif;
        margin-bottom: 0.5rem;
    }

    .stat-number.total { color: var(--winit-primary); }
    .stat-number.active { color: #10b981; }
    .stat-number.inactive { color: #ef4444; }
    .stat-number.admins { color: #f59e0b; }

    .stat-label {
        color: var(--winit-text-light);
        font-size: 0.875rem;
        font-weight: 500;
        font-family: 'Montserrat', sans-serif;
    }

    .user-status-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        font-family: 'Montserrat', sans-serif;
    }

    .user-status-badge.active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .user-status-badge.inactive {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .user-status-badge.pending {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
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

    .loading-spinner {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2rem;
    }

    .spinner-border {
        width: 2rem;
        height: 2rem;
        border: 0.25rem solid var(--winit-border);
        border-right-color: var(--winit-accent);
        border-radius: 50%;
        animation: spinner-border 0.75s linear infinite;
    }

    @keyframes spinner-border {
        to { transform: rotate(360deg); }
    }

    @media (max-width: 768px) {
        .user-item {
            padding: 1rem;
        }
        
        .user-header {
            padding: 1rem;
        }
        
        .user-header h2 {
            font-size: 1.25rem;
        }
        
        .filter-buttons {
            justify-content: center;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .user-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .user-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="user-management-card">
        <div class="user-header">
            <h2>
                <i class="fas fa-users me-2"></i>User Management
            </h2>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="fas fa-plus me-1"></i>Create User
                </button>
            </div>
        </div>
        
        <div class="card-body p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number total">{{ $users->total() }}</div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number active">{{ $users->where('email_verified_at', '!=', null)->count() }}</div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number inactive">{{ $users->where('email_verified_at', null)->count() }}</div>
                    <div class="stat-label">Pending Verification</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number admins">{{ $users->filter(function($user) { return $user->hasRole('super-admin') || $user->hasRole('Super Admin'); })->count() }}</div>
                    <div class="stat-label">Administrators</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="user-filters">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0" style="font-family: 'Montserrat', sans-serif; font-weight: 600;">Filter Users</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshUsers()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="exportUsers()">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="filter-buttons">
                    <a href="#" class="filter-btn active" data-filter="all">
                        <i class="fas fa-list"></i>All ({{ $users->total() }})
                    </a>
                    <a href="#" class="filter-btn" data-filter="super-admin">
                        <i class="fas fa-shield-alt"></i>Super Admin ({{ $users->filter(function($user) { return $user->hasRole('super-admin') || $user->hasRole('Super Admin'); })->count() }})
                    </a>
                    <a href="#" class="filter-btn" data-filter="admin">
                        <i class="fas fa-user-shield"></i>Admin ({{ $users->filter(function($user) { return $user->hasRole('admin'); })->count() }})
                    </a>
                    <a href="#" class="filter-btn" data-filter="user">
                        <i class="fas fa-user"></i>User ({{ $users->filter(function($user) { return $user->hasRole('user'); })->count() }})
                    </a>
                    <a href="#" class="filter-btn" data-filter="audit">
                        <i class="fas fa-eye"></i>Audit ({{ $users->filter(function($user) { return $user->hasRole('audit'); })->count() }})
                    </a>
                </div>
            </div>

            <!-- Users List -->
            <div id="usersList">
                @if($users->count() > 0)
                    @foreach($users as $user)
                    <div class="user-item {{ $user->email_verified_at ? 'active' : 'inactive' }}" 
                         data-role="{{ $user->roles->first()->name ?? 'user' }}" 
                         data-id="{{ $user->id }}">
                        
                        <div class="user-status-badge {{ $user->email_verified_at ? 'active' : 'inactive' }}">
                            {{ $user->email_verified_at ? 'Active' : 'Pending' }}
                        </div>
                        
                        <div class="d-flex align-items-start">
                            <div class="user-avatar {{ $user->roles->first()->name ?? 'user' }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            
                            <div class="user-content">
                                <h6 class="user-name">{{ $user->name }}</h6>
                                <p class="user-email">{{ $user->email }}</p>
                                
                                <div class="user-roles">
                                    @foreach($user->roles as $role)
                                        <span class="role-badge {{ $role->name }}">{{ $role->display_name ?? $role->name }}</span>
                                    @endforeach
                                    @if($user->roles->isEmpty())
                                        <span class="role-badge user">No Role</span>
                                    @endif
                                </div>

                                {{-- Capabilities summary badges --}}
                                <div class="mb-2" style="display: flex; flex-wrap: wrap; gap: .5rem;">
                                    @php
                                        $canUploadCsv = $user->hasPermission('upload-csv') || $user->hasRole('super-admin') || $user->hasRole('Super Admin');
                                        $canViewTx = $user->hasPermission('view-transactions') || $user->hasRole('super-admin') || $user->hasRole('Super Admin');
                                        $canManageUsers = $user->hasPermission('manage-users') || $user->hasRole('super-admin') || $user->hasRole('Super Admin');
                                        $isAudit = $user->hasRole('audit') && !$user->hasRole('super-admin') && !$user->hasRole('Super Admin');
                                    @endphp
                                    @if($canUploadCsv)
                                        <span class="badge bg-primary"><i class="fas fa-file-upload me-1"></i> Upload CSV</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="fas fa-ban me-1"></i> No Upload</span>
                                    @endif
                                    @if($canViewTx)
                                        <span class="badge bg-info text-dark"><i class="fas fa-list me-1"></i> View Transactions</span>
                                    @endif
                                    @if($canManageUsers)
                                        <span class="badge bg-warning text-dark"><i class="fas fa-user-cog me-1"></i> Manage Users</span>
                                    @endif
                                    @if($isAudit)
                                        <span class="badge bg-success"><i class="fas fa-eye me-1"></i> Audit Read-only</span>
                                    @endif
                                </div>

                                {{-- Effective permissions (collapsible) --}}
                                @php
                                    $effectivePermissions = $user->roles->load('permissions')->flatMap->permissions->pluck('name')->unique()->values();
                                @endphp
                                <div class="mb-3">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#perms_{{ $user->id }}" aria-expanded="false" aria-controls="perms_{{ $user->id }}">
                                        <i class="fas fa-shield-alt me-1"></i>Permissions ({{ $effectivePermissions->count() }})
                                    </button>
                                    <div class="collapse mt-2" id="perms_{{ $user->id }}">
                                        @if($effectivePermissions->count() > 0)
                                            <div style="display: flex; flex-wrap: wrap; gap: .4rem;">
                                                @foreach($effectivePermissions as $perm)
                                                    <span class="badge bg-light text-dark" style="border:1px solid var(--winit-border);">{{ $perm }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">No permissions assigned.</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="user-meta">
                                    <span class="user-time">
                                        <i class="fas fa-calendar me-1"></i>
                                        Created {{ $user->created_at->diffForHumans() }}
                                    </span>
                                    
                                    <div class="user-actions">
                                        <a href="{{ route('users.edit', $user) }}" class="action-btn edit">
                                            <i class="fas fa-edit"></i>Edit
                                        </a>
                                        
                                        @if($user->id !== auth()->id())
                                            <button class="action-btn reset-password" onclick="resetPassword('{{ $user->id }}', '{{ $user->name }}')">
                                                <i class="fas fa-key"></i>Reset Password
                                            </button>
                                            
                                            @if(auth()->user()->canManageUsers() || auth()->user()->hasRole('super-admin'))
                                                <form action="{{ route('device-fingerprints.reset-user', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('This will reset all devices for {{ $user->name }}. They can register a new device on next login. Continue?')">
                                                    @csrf
                                                    <button type="submit" class="action-btn reset-device">
                                                        <i class="fas fa-mobile-alt"></i>Reset Device
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('Super Admin'))
                                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirmDelete('{{ $user->name }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn delete">
                                                        <i class="fas fa-trash"></i>Delete
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <span class="action-btn" style="opacity: 0.5; cursor: not-allowed;">
                                                <i class="fas fa-user"></i>Current User
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Users Found</h3>
                        <p>Create your first user to get started with user management.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                            <i class="fas fa-plus me-2"></i>Create First User
                        </button>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('users.store') }}" method="POST" id="createUserForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Roles</label>
                        <div class="row">
                            @foreach(\App\Models\Role::all() as $role)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}">
                                        <label class="form-check-label" for="role_{{ $role->id }}">
                                            {{ $role->display_name ?? $role->name }}
                                            <small class="text-muted d-block">{{ $role->description }}</small>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        A temporary password will be generated and sent to the user's email address. They will be required to change it on first login.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset User Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="resetPasswordForm">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to reset the password for <strong id="resetUserName"></strong>?</p>
                    <p class="text-muted">A new temporary password will be generated and sent to their email address.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Utility functions
const utils = {
    showAlert: function(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Insert at the top of the content
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    },

    makeRequest: async function(url, options = {}) {
        try {
            const { headers: optionHeaders = {}, credentials, body, ...restOptions } = options;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const method = (restOptions.method || options.method || 'GET').toUpperCase();

            const defaultHeaders = {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                ...optionHeaders
            };

            let requestBody = body;

            if (method !== 'GET' && method !== 'HEAD') {
                if (!requestBody) {
                    // Default to JSON payload with CSRF token when body is not provided
                    defaultHeaders['Content-Type'] = 'application/json';
                    requestBody = JSON.stringify({ _token: csrfToken });
                } else if (requestBody instanceof FormData) {
                    // Let the browser set the Content-Type header for FormData
                    delete defaultHeaders['Content-Type'];
                    requestBody.append('_token', csrfToken);
                } else if (typeof requestBody === 'object' && !(requestBody instanceof Blob)) {
                    defaultHeaders['Content-Type'] = 'application/json';
                    requestBody = JSON.stringify({ _token: csrfToken, ...requestBody });
                }
            }

            const response = await fetch(url, {
                credentials: credentials ?? 'same-origin',
                headers: defaultHeaders,
                body: requestBody,
                method,
                ...restOptions
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

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const userItems = document.querySelectorAll('.user-item');

    filterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter users
            const filter = this.getAttribute('data-filter');
            
            userItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-role') === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});

// Reset password functionality
function resetPassword(userId, userName) {
    document.getElementById('resetUserName').textContent = userName;
    document.getElementById('resetPasswordForm').setAttribute('data-user-id', userId);
    
    const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
}

// Handle reset password form submission
document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const userId = this.getAttribute('data-user-id');
    const submitButton = this.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    
    // Disable button to prevent multiple submissions
    if (submitButton.disabled) {
        return;
    }
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
    
    try {
        const result = await utils.makeRequest(`/users/${userId}/reset-password`, {
            method: 'POST'
        });
        
        // Close modal first
        const modal = bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal'));
        modal.hide();
        
        // Show success notification immediately after modal closes
        setTimeout(() => {
            utils.showAlert(result.message || 'Password reset successfully! New password has been sent to the user\'s email.', 'success');
        }, 300);
        
    } catch (error) {
        utils.showAlert(error.message || 'Failed to reset password. Please try again.', 'error');
    } finally {
        // Re-enable button after a short delay
        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }, 1000);
    }
});

// Confirm delete functionality
function confirmDelete(userName) {
    return confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`);
}

// Refresh users
function refreshUsers() {
    location.reload();
}

// Export users
function exportUsers() {
    utils.showAlert('Export functionality coming soon!', 'info');
}

// Form validation
document.getElementById('createUserForm').addEventListener('submit', function(e) {
    const roles = document.querySelectorAll('input[name="roles[]"]:checked');
    if (roles.length === 0) {
        e.preventDefault();
        utils.showAlert('Please select at least one role for the user.', 'error');
    }
});

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
@endpush