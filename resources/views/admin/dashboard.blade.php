@extends('layouts.sidebar')

@section('title', 'Admin Dashboard - BuyPower')

@section('content')
<div class="content-header">
    <h1><i class="fas fa-shield-alt me-3"></i>Admin Dashboard</h1>
    <p>System administration and management</p>
</div>

<!-- Admin Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-users fa-2x text-primary mb-3"></i>
                <h3 class="mb-1">{{ $totalUsers ?? 0 }}</h3>
                <p class="text-muted mb-0">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-upload fa-2x text-success mb-3"></i>
                <h3 class="mb-1">{{ $totalBatches ?? 0 }}</h3>
                <p class="text-muted mb-0">Total Batches</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-bolt fa-2x text-warning mb-3"></i>
                <h3 class="mb-1">{{ $totalTransactions ?? 0 }}</h3>
                <p class="text-muted mb-0">Total Transactions</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-money-bill-wave fa-2x text-info mb-3"></i>
                <h3 class="mb-1">₦{{ number_format($totalAmount ?? 0, 2) }}</h3>
                <p class="text-muted mb-0">Total Amount</p>
            </div>
        </div>
    </div>
</div>

<!-- Admin Actions -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>System Management</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-users me-2"></i>Manage Users
                    </a>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-shield me-2"></i>Manage Roles
                    </a>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-key me-2"></i>Manage Permissions
                    </a>
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-history me-2"></i>Activity Logs
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>System Status</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>API Status</span>
                        <span class="badge bg-success">Connected</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Database</span>
                        <span class="badge bg-success">Online</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Email Service</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Queue System</span>
                        <span class="badge bg-success">Running</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent System Activity</h5>
            </div>
            <div class="card-body">
                @if(isset($recentActivity) && $recentActivity->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivity as $activity)
                                <tr>
                                    <td>{{ $activity->user->name ?? 'System' }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</span>
                                    </td>
                                    <td>{{ $activity->description }}</td>
                                    <td>{{ $activity->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No recent activity found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
