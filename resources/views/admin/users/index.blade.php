@extends('layouts.sidebar')

@section('title', 'User Management - WinIt')

@push('styles')
<style>
    .user-card {
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
        border: 1px solid rgba(18, 18, 104, 0.1);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .user-card:hover {
        box-shadow: 0 10px 25px rgba(18, 18, 104, 0.15);
        transform: translateY(-2px);
    }

    .user-avatar {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.5rem;
    }

    .role-badge {
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        margin: 0.125rem;
        display: inline-block;
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

    .btn-outline-primary {
        border: 2px solid rgb(18, 18, 104);
        color: rgb(18, 18, 104);
        border-radius: 0.75rem;
        padding: 0.5rem 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background: rgb(18, 18, 104);
        color: white;
        transform: translateY(-1px);
    }

    .btn-outline-danger {
        border: 2px solid #ef4444;
        color: #ef4444;
        border-radius: 0.75rem;
        padding: 0.5rem 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-outline-danger:hover {
        background: #ef4444;
        color: white;
        transform: translateY(-1px);
    }

    .table {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
    }

    .table th {
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 1rem;
    }

    .table td {
        padding: 1rem;
        border-color: rgba(18, 18, 104, 0.1);
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background: rgba(18, 18, 104, 0.05);
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-users me-3"></i>User Management</h1>
                <p>Manage system users, roles, and permissions</p>
            </div>
            <div>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create New User
                </a>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="user-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user me-2"></i>User</th>
                            <th><i class="fas fa-envelope me-2"></i>Email</th>
                            <th><i class="fas fa-shield-alt me-2"></i>Roles</th>
                            <th><i class="fas fa-calendar me-2"></i>Created</th>
                            <th><i class="fas fa-cog me-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $user->name }}</h6>
                                            <small class="text-muted">ID: {{ $user->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $user->email }}</span>
                                </td>
                                <td>
                                    @forelse($user->roles as $role)
                                        <span class="role-badge">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-muted">No roles assigned</span>
                                    @endforelse
                                </td>
                                <td>
                                    <span class="text-muted">{{ $user->created_at->format('M d, Y') }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <h5>No users found</h5>
                                        <p>Create your first user to get started.</p>
                                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Create User
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@endsection
