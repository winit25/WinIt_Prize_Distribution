@extends('layouts.sidebar')

@section('title', 'Activity Logs - WinIt')

@push('styles')
<style>
    .log-card {
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
        border: 1px solid rgba(18, 18, 104, 0.1);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .log-card:hover {
        box-shadow: 0 10px 25px rgba(18, 18, 104, 0.15);
        transform: translateY(-2px);
    }

    .filter-card {
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
        border: 1px solid rgba(18, 18, 104, 0.1);
        margin-bottom: 2rem;
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

    .btn-outline-secondary {
        border: 2px solid #6b7280;
        color: #6b7280;
        border-radius: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-outline-secondary:hover {
        background: #6b7280;
        color: white;
        transform: translateY(-1px);
    }

    .log-item {
        border-left: 4px solid rgb(18, 18, 104);
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .log-item:hover {
        background: rgba(18, 18, 104, 0.05);
        transform: translateX(4px);
    }

    .log-item.created {
        border-left-color: #10b981;
    }

    .log-item.updated {
        border-left-color: #f59e0b;
    }

    .log-item.deleted {
        border-left-color: #ef4444;
    }

    .log-item.login {
        border-left-color: #3b82f6;
    }

    .log-item.logout {
        border-left-color: #8b5cf6;
    }

    .event-badge {
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1rem;
    }

    .table {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
        table-layout: fixed;
        width: 100%;
    }
    
    .table th,
    .table td {
        width: auto;
        word-wrap: break-word;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-clipboard-list me-3"></i>Activity Logs</h1>
                <p>Monitor system activities and user actions</p>
            </div>
            <div>
                @if($canViewAll)
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                    <i class="fas fa-trash me-2"></i>Clear Old Logs
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('activity-logs.index') }}">
                <div class="row g-3">
                    @if($canViewAll)
                    <div class="col-md-3">
                        <label for="user_id" class="form-label"><i class="fas fa-user me-1"></i>User</label>
                        <select name="user_id" id="user_id" class="form-control">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-user me-1"></i>User</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                        <small class="text-muted">You can only view your own activities</small>
                    </div>
                    @endif
                    <div class="col-md-3">
                        <label for="event" class="form-label"><i class="fas fa-tag me-1"></i>Event Type</label>
                        <select name="event" id="event" class="form-control">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $event)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label"><i class="fas fa-calendar me-1"></i>From Date</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label"><i class="fas fa-calendar me-1"></i>To Date</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2 w-100">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
                @if(request()->hasAny(['user_id', 'event', 'date_from', 'date_to', 'search']))
                <div class="row mt-2">
                    <div class="col-12">
                        <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Activity Logs -->
    <div class="log-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user me-2"></i>User</th>
                            <th><i class="fas fa-tag me-2"></i>Event</th>
                            <th><i class="fas fa-info-circle me-2"></i>Description</th>
                            <th><i class="fas fa-network-wired me-2"></i>User IP Address</th>
                            <th><i class="fas fa-mobile-alt me-2"></i>Device</th>
                            <th><i class="fas fa-clock me-2"></i>Time</th>
                            <th><i class="fas fa-eye me-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($logs) && $logs->count() > 0)
                        @foreach($logs as $log)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            {{ $log->causer ? strtoupper(substr($log->causer->name, 0, 1)) : 'S' }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $log->causer ? $log->causer->name : 'System' }}</h6>
                                            <small class="text-muted">{{ $log->causer ? $log->causer->email : 'system@winit.com' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = 'bg-secondary';
                                        if (in_array($log->event, ['user_created', 'batch_created', 'transaction_created'])) {
                                            $badgeClass = 'bg-success';
                                        } elseif (in_array($log->event, ['user_login', 'token_generated'])) {
                                            $badgeClass = 'bg-info';
                                        } elseif (in_array($log->event, ['password_changed', 'batch_status_changed', 'transaction_status_changed'])) {
                                            $badgeClass = 'bg-warning';
                                        } elseif (in_array($log->event, ['error', 'user_deleted', 'user_logout'])) {
                                            $badgeClass = 'bg-danger';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $log->event ?? 'unknown')) }}</span>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $log->description }}</span>
                                    @if($log->properties)
                                        <br><small class="text-muted">{{ Str::limit(json_encode($log->properties), 100) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($log->ip_address)
                                        <span class="badge bg-info">{{ $log->ip_address }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $deviceInfo = $log->device_info;
                                        $deviceType = $deviceInfo['device_type'] ?? 'Unknown';
                                        $browser = $deviceInfo['browser'] ?? 'Unknown';
                                        $os = $deviceInfo['os'] ?? 'Unknown';
                                        
                                        // Set badge color based on device type
                                        $deviceBadgeClass = 'bg-secondary';
                                        if ($deviceType === 'Mobile') {
                                            $deviceBadgeClass = 'bg-primary';
                                        } elseif ($deviceType === 'Tablet') {
                                            $deviceBadgeClass = 'bg-info';
                                        } elseif ($deviceType === 'Desktop') {
                                            $deviceBadgeClass = 'bg-success';
                                        }
                                    @endphp
                                    <span class="badge {{ $deviceBadgeClass }} mb-1">{{ $deviceType }}</span>
                                    <br><small class="text-muted">{{ $browser }}</small>
                                    <br><small class="text-muted">{{ $os }}</small>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $log->created_at->format('M d, Y H:i:s') }}</span>
                                    <br><small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('activity-logs.show', $log) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                        <h5>No activity logs found</h5>
                                        <p>Activity logs will appear here as users interact with the system.</p>
                                        @if(config('app.debug'))
                                            <small class="text-muted mt-2 d-block">
                                                Debug: Total logs in DB: {{ \App\Models\ActivityLog::count() }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($logs->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $logs->links() }}
        </div>
    @endif
</div>

<!-- Clear Logs Modal -->
<div class="modal fade" id="clearLogsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clear Old Activity Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('activity-logs.clear') }}">
                @csrf
                <div class="modal-body">
                    <p>This will permanently delete activity logs older than the specified number of days.</p>
                    <div class="mb-3">
                        <label for="days" class="form-label">Delete logs older than (days):</label>
                        <select name="days" id="days" class="form-control">
                            <option value="7">7 days</option>
                            <option value="30" selected>30 days</option>
                            <option value="90">90 days</option>
                            <option value="180">180 days</option>
                            <option value="365">1 year</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Clear Logs</button>
                </div>
            </form>
        </div>
    </div>
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
