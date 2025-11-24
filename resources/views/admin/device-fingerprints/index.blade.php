@extends('layouts.sidebar')

@section('title', 'Device Fingerprints - WinIt')

@push('styles')
<style>
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

    .device-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
        border: 1px solid rgba(18, 18, 104, 0.1);
        margin-bottom: 1rem;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    .device-card:hover {
        box-shadow: 0 8px 25px rgba(18, 18, 104, 0.15);
        transform: translateY(-2px);
    }

    .device-card.inactive {
        opacity: 0.6;
        background: #f8fafc;
    }

    .badge-active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .badge-inactive {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .btn-action {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-action:hover {
        transform: translateY(-1px);
    }

    .btn-deactivate {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .btn-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .filter-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-mobile-alt me-3"></i>Device Fingerprints</h1>
        <p>Manage device bindings for user accounts</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('device-fingerprints.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="user_id" class="form-label">Filter by User</label>
                <select name="user_id" id="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="is_active" class="form-label">Status</label>
                <select name="is_active" id="is_active" class="form-select">
                    <option value="">All</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
                <a href="{{ route('device-fingerprints.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Device List -->
    @if($devices->count() > 0)
        @foreach($devices as $device)
            <div class="device-card {{ !$device->is_active ? 'inactive' : '' }}">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <h5 class="mb-1">{{ $device->user->name ?? 'Unknown User' }}</h5>
                        <small class="text-muted">{{ $device->user->email ?? 'N/A' }}</small>
                    </div>
                    <div class="col-md-3">
                        <strong>Device:</strong><br>
                        <span>{{ $device->device_name ?? 'Unknown Device' }}</span>
                    </div>
                    <div class="col-md-2">
                        <strong>IP Address:</strong><br>
                        <span class="badge bg-info">{{ $device->ip_address ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-2">
                        <strong>Status:</strong><br>
                        @if($device->is_active)
                            <span class="badge-active">Active</span>
                        @else
                            <span class="badge-inactive">Inactive</span>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <strong>Last Used:</strong><br>
                        <small>{{ $device->last_used_at ? $device->last_used_at->diffForHumans() : 'Never' }}</small>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <a href="{{ route('device-fingerprints.show', $device) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                            @if($device->is_active)
                                <form method="POST" action="{{ route('device-fingerprints.deactivate', $device) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn-action btn-deactivate" onclick="return confirm('Are you sure you want to deactivate this device?')">
                                        <i class="fas fa-ban me-1"></i>Deactivate
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('device-fingerprints.reset-user', $device->user) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn-action btn-deactivate" onclick="return confirm('This will reset all devices for this user. They can register a new device on next login. Continue?')">
                                    <i class="fas fa-redo me-1"></i>Reset User Devices
                                </button>
                            </form>
                            <form method="POST" action="{{ route('device-fingerprints.destroy', $device) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this device fingerprint?')">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Pagination -->
        <div class="mt-4">
            {{ $devices->links() }}
        </div>
    @else
        <div class="device-card text-center py-5">
            <i class="fas fa-mobile-alt fa-3x text-muted mb-3"></i>
            <h5>No device fingerprints found</h5>
            <p class="text-muted">Device fingerprints will appear here once users log in.</p>
        </div>
    @endif
</div>
@endsection

