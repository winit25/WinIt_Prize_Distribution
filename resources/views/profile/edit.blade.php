@extends('layouts.sidebar')

@section('title', 'Profile - WinIt Prize Distribution')

@push('styles')
<style>
    .profile-card {
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
        border: 1px solid rgba(18, 18, 104, 0.1);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .profile-card:hover {
        box-shadow: 0 10px 25px rgba(18, 18, 104, 0.15);
        transform: translateY(-2px);
    }

    .page-header {
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
        color: white;
        padding: 2rem;
        border-radius: 1.5rem;
        margin-bottom: 2rem;
    }

    .page-header h1 {
        margin: 0;
        font-weight: 700;
        font-size: 2rem;
    }

    .page-header p {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
    }

    .form-control {
        border: 2px solid rgba(18, 18, 104, 0.1);
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .form-control:focus {
        border-color: rgb(18, 18, 104);
        box-shadow: 0 0 0 4px rgba(18, 18, 104, 0.1);
        background: white;
        outline: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
        border: none;
        border-radius: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(18, 18, 104, 0.3);
        background: linear-gradient(135deg, rgb(12, 12, 80) 0%, rgb(18, 18, 104) 100%);
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        border: none;
        border-radius: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);
        background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
    }

    .alert {
        border-radius: 0.75rem;
        border: none;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border-left: 4px solid #10b981;
    }

    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border-left: 4px solid #ef4444;
    }

    .user-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 2rem;
        margin: 0 auto 1rem;
    }

    .activity-item {
        border-left: 4px solid rgb(18, 18, 104);
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        background: rgba(18, 18, 104, 0.05);
        transform: translateX(4px);
    }

    .batch-item {
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 0.5rem;
        border: 1px solid rgba(18, 18, 104, 0.1);
        transition: all 0.3s ease;
    }

    .batch-item:hover {
        background: rgba(18, 18, 104, 0.05);
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-user me-3"></i>Profile Settings</h1>
                <p>Manage your account information and preferences</p>
            </div>
            <div class="user-avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="profile-card mb-4">
        <div class="card-body p-4">
            <h5 class="card-title mb-4">
                <i class="fas fa-user-edit me-2"></i>Profile Information
            </h5>
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    <!-- Password Update -->
    <div class="profile-card mb-4">
        <div class="card-body p-4">
            <h5 class="card-title mb-4">
                <i class="fas fa-lock me-2"></i>Update Password
            </h5>
            @include('profile.partials.update-password-form')
        </div>
    </div>

    <!-- Roles & Permissions -->
    <div class="profile-card mb-4">
        <div class="card-body p-4">
            <h5 class="card-title mb-4">
                <i class="fas fa-shield-alt me-2"></i>Roles & Permissions
            </h5>
            
            <!-- Roles Section -->
            <div class="mb-4">
                <h6 class="mb-3" style="font-family: 'Montserrat', sans-serif; font-weight: 600; color: rgb(18, 18, 104);">
                    <i class="fas fa-user-tag me-2"></i>Your Roles
                </h6>
                @if($user->roles && $user->roles->count() > 0)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($user->roles as $role)
                            <span class="badge" style="
                                background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
                                color: white;
                                padding: 0.5rem 1rem;
                                border-radius: 0.5rem;
                                font-size: 0.875rem;
                                font-weight: 500;
                                font-family: 'Montserrat', sans-serif;
                            ">
                                <i class="fas fa-shield me-1"></i>{{ $role->display_name ?? $role->name }}
                            </span>
                        @endforeach
                    </div>
                    @foreach($user->roles as $role)
                        @if($role->description)
                            <p class="text-muted small mt-2 mb-0">
                                <strong>{{ $role->display_name ?? $role->name }}:</strong> {{ $role->description }}
                            </p>
                        @endif
                    @endforeach
                @else
                    <p class="text-muted mb-0">No roles assigned</p>
                @endif
            </div>
            
            <hr class="my-4">
            
            <!-- Permissions Section -->
            <div>
                <h6 class="mb-3" style="font-family: 'Montserrat', sans-serif; font-weight: 600; color: rgb(18, 18, 104);">
                    <i class="fas fa-key me-2"></i>Your Permissions
                </h6>
                @if($userPermissions && $userPermissions->count() > 0)
                    @foreach($permissionsByCategory as $category => $permissions)
                        <div class="mb-4">
                            <h6 class="text-primary mb-2" style="font-family: 'Montserrat', sans-serif; font-weight: 600; font-size: 0.875rem;">
                                <i class="fas fa-folder me-1"></i>{{ ucfirst(str_replace('_', ' ', $category)) }}
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($permissions as $permission)
                                    <span class="badge" style="
                                        background: linear-gradient(135deg, rgba(23, 247, 182, 0.1) 0%, rgba(23, 247, 182, 0.05) 100%);
                                        color: rgb(18, 18, 104);
                                        border: 1px solid rgba(23, 247, 182, 0.3);
                                        padding: 0.375rem 0.75rem;
                                        border-radius: 0.5rem;
                                        font-size: 0.75rem;
                                        font-weight: 500;
                                        font-family: 'Montserrat', sans-serif;
                                    ">
                                        <i class="fas fa-check-circle me-1"></i>{{ $permission->display_name ?? $permission->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="alert alert-info mt-3 mb-0" style="
                        background: linear-gradient(135deg, rgba(18, 18, 104, 0.05) 0%, rgba(18, 18, 104, 0.02) 100%);
                        border: 1px solid rgba(18, 18, 104, 0.1);
                        border-radius: 0.75rem;
                        padding: 1rem;
                    ">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>
                            <strong>Total Permissions:</strong> {{ $userPermissions->count() }} 
                            across {{ $permissionsByCategory->count() }} categories
                        </small>
                    </div>
                @else
                    <p class="text-muted mb-0">No permissions assigned. Contact your administrator to get access.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    @if($recentActivity->count() > 0)
    <div class="profile-card mb-4">
        <div class="card-body p-4">
            <h5 class="card-title mb-4">
                <i class="fas fa-history me-2"></i>Recent Activity
            </h5>
            @foreach($recentActivity as $activity)
                <div class="activity-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 fw-bold">{{ $activity->description }}</h6>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                        <span class="badge bg-primary">{{ $activity->event }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recent Batches -->
    @if($recentBatches->count() > 0)
    <div class="profile-card mb-4">
        <div class="card-body p-4">
            <h5 class="card-title mb-4">
                <i class="fas fa-upload me-2"></i>Recent Batch Uploads
            </h5>
            @foreach($recentBatches as $batch)
                <div class="batch-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 fw-bold">{{ $batch->batch_name }}</h6>
                            <small class="text-muted">
                                {{ $batch->total_recipients }} recipients • 
                                ₦{{ number_format($batch->total_amount, 2) }} • 
                                {{ $batch->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <span class="badge bg-{{ $batch->status === 'completed' ? 'success' : ($batch->status === 'processing' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($batch->status) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Account Deletion (Superadmins Only) -->
    @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('Super Admin'))
    <div class="profile-card">
        <div class="card-body p-4">
            <h5 class="card-title mb-4 text-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>Delete Account
            </h5>
            @include('profile.partials.delete-user-form')
        </div>
    </div>
    @endif
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
        <i class="fas fa-check-circle me-2"></i>
        @if(session('status') === 'profile-updated')
            Profile information updated successfully!
        @elseif(session('status') === 'password-updated')
            Password updated successfully!
        @else
            {{ session('status') }}
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@endsection
