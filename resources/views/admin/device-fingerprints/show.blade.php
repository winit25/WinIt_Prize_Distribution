@extends('layouts.sidebar')

@section('title', 'Device Fingerprint Details - WinIt')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
        color: white;
        padding: 2rem;
        border-radius: 1.5rem;
        margin-bottom: 2rem;
    }

    .detail-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
        padding: 2rem;
        margin-bottom: 1.5rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 1rem 0;
        border-bottom: 1px solid rgba(18, 18, 104, 0.1);
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: rgb(18, 18, 104);
    }

    .info-value {
        color: #374151;
        text-align: right;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-mobile-alt me-3"></i>Device Fingerprint Details</h1>
                <p>Detailed information about this device binding</p>
            </div>
            <div>
                <a href="{{ route('device-fingerprints.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Device Details -->
    <div class="detail-card">
        <h4 class="mb-4"><i class="fas fa-info-circle me-2"></i>Device Information</h4>
        
        <div class="info-item">
            <span class="info-label">User</span>
            <span class="info-value">
                {{ $deviceFingerprint->user->name ?? 'Unknown' }}<br>
                <small class="text-muted">{{ $deviceFingerprint->user->email ?? 'N/A' }}</small>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">Device Name</span>
            <span class="info-value">{{ $deviceFingerprint->device_name ?? 'Unknown Device' }}</span>
        </div>

        <div class="info-item">
            <span class="info-label">Status</span>
            <span class="info-value">
                @if($deviceFingerprint->is_active)
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-secondary">Inactive</span>
                @endif
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">IP Address</span>
            <span class="info-value">
                <span class="badge bg-info">{{ $deviceFingerprint->ip_address ?? 'N/A' }}</span>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">User Agent</span>
            <span class="info-value">
                <small class="text-muted">{{ Str::limit($deviceFingerprint->user_agent ?? 'N/A', 100) }}</small>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">Fingerprint Hash</span>
            <span class="info-value">
                <code class="text-muted">{{ Str::limit($deviceFingerprint->fingerprint_hash, 50) }}...</code>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">Last Used</span>
            <span class="info-value">
                {{ $deviceFingerprint->last_used_at ? $deviceFingerprint->last_used_at->format('M d, Y H:i:s') : 'Never' }}<br>
                <small class="text-muted">{{ $deviceFingerprint->last_used_at ? $deviceFingerprint->last_used_at->diffForHumans() : '' }}</small>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label">Registered</span>
            <span class="info-value">
                {{ $deviceFingerprint->created_at->format('M d, Y H:i:s') }}<br>
                <small class="text-muted">{{ $deviceFingerprint->created_at->diffForHumans() }}</small>
            </span>
        </div>
    </div>

    <!-- Actions -->
    <div class="detail-card">
        <h4 class="mb-4"><i class="fas fa-cog me-2"></i>Actions</h4>
        
        <div class="d-flex gap-2">
            @if($deviceFingerprint->is_active)
                <form method="POST" action="{{ route('device-fingerprints.deactivate', $deviceFingerprint) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to deactivate this device?')">
                        <i class="fas fa-ban me-2"></i>Deactivate Device
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('device-fingerprints.reset-user', $deviceFingerprint->user) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-info" onclick="return confirm('This will reset all devices for this user. They can register a new device on next login. Continue?')">
                    <i class="fas fa-redo me-2"></i>Reset User Devices
                </button>
            </form>

            <form method="POST" action="{{ route('device-fingerprints.destroy', $deviceFingerprint) }}" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this device fingerprint? This action cannot be undone.')">
                    <i class="fas fa-trash me-2"></i>Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

