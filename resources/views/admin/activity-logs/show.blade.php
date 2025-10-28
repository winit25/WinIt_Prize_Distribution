@extends('layouts.sidebar')

@section('title', 'Activity Log Details - WinIt')

@push('styles')
<style>
    .detail-card {
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
        border: 1px solid rgba(18, 18, 104, 0.1);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .detail-card:hover {
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

    .info-section {
        background: #f8fafc;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid rgb(18, 18, 104);
    }

    .info-section h5 {
        color: rgb(18, 18, 104);
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
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

    .event-badge {
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
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

    .json-viewer {
        background: #1f2937;
        color: #f9fafb;
        border-radius: 0.75rem;
        padding: 1rem;
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
        overflow-x: auto;
        white-space: pre-wrap;
        word-break: break-all;
    }

    .btn-primary {
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
        border: none;
        border-radius: 0.75rem;
        padding: 0.875rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(18, 18, 104, 0.3);
        background: linear-gradient(135deg, rgb(12, 12, 80) 0%, rgb(18, 18, 104) 100%);
    }

    .btn-secondary {
        background: #6b7280;
        border: none;
        border-radius: 0.75rem;
        padding: 0.875rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: #4b5563;
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-clipboard-list me-3"></i>Activity Log Details</h1>
                <p>Detailed information about this activity log entry</p>
            </div>
            <div>
                <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Logs
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="detail-card">
                <div class="card-body">
                    <div class="info-section">
                        <h5><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                        
                        <div class="info-item">
                            <span class="info-label">Event Type</span>
                            <span class="event-badge">{{ $activityLog->event }}</span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Description</span>
                            <span class="info-value">{{ $activityLog->description }}</span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Subject Type</span>
                            <span class="info-value">{{ $activityLog->subject_type ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Subject ID</span>
                            <span class="info-value">{{ $activityLog->subject_id ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Causer Type</span>
                            <span class="info-value">{{ $activityLog->causer_type ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Causer ID</span>
                            <span class="info-value">{{ $activityLog->causer_id ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties -->
            @if($activityLog->properties)
                <div class="detail-card">
                    <div class="card-body">
                        <div class="info-section">
                            <h5><i class="fas fa-code me-2"></i>Properties</h5>
                            <div class="json-viewer">{{ json_encode($activityLog->properties, JSON_PRETTY_PRINT) }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- User Information -->
            <div class="detail-card">
                <div class="card-body">
                    <div class="info-section">
                        <h5><i class="fas fa-user me-2"></i>User Information</h5>
                        
                        @if($activityLog->causer)
                            <div class="d-flex align-items-center mb-3">
                                <div class="user-avatar me-3">
                                    {{ strtoupper(substr($activityLog->causer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $activityLog->causer->name }}</h6>
                                    <small class="text-muted">{{ $activityLog->causer->email }}</small>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">User ID</span>
                                <span class="info-value">{{ $activityLog->causer->id }}</span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <span class="info-value">{{ $activityLog->causer->email }}</span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Created</span>
                                <span class="info-value">{{ $activityLog->causer->created_at->format('M d, Y') }}</span>
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-user-slash fa-2x mb-2"></i>
                                <p>System Activity</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timestamp Information -->
            <div class="detail-card">
                <div class="card-body">
                    <div class="info-section">
                        <h5><i class="fas fa-clock me-2"></i>Timestamp Information</h5>
                        
                        <div class="info-item">
                            <span class="info-label">Created At</span>
                            <span class="info-value">{{ $activityLog->created_at->format('M d, Y H:i:s') }}</span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Relative Time</span>
                            <span class="info-value">{{ $activityLog->created_at->diffForHumans() }}</span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Updated At</span>
                            <span class="info-value">{{ $activityLog->updated_at->format('M d, Y H:i:s') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="detail-card">
                <div class="card-body">
                    <div class="info-section">
                        <h5><i class="fas fa-cog me-2"></i>Actions</h5>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('activity-logs.index') }}" class="btn btn-primary">
                                <i class="fas fa-list me-2"></i>View All Logs
                            </a>
                            
                            @if($activityLog->causer)
                                <a href="{{ route('users.edit', $activityLog->causer) }}" class="btn btn-secondary">
                                    <i class="fas fa-user-edit me-2"></i>Edit User
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
