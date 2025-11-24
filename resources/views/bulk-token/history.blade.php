@extends('layouts.sidebar')

@section('title', 'Batch History - WinIt Prize Distribution')

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

    .batch-history-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 2px 8px rgba(18, 18, 104, 0.1);
        border: 1px solid var(--winit-border);
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .batch-history-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(18, 18, 104, 0.15);
        border-color: var(--winit-accent);
    }

    .batch-header {
        background: linear-gradient(135deg, var(--winit-primary) 0%, #1e1e6b 100%);
        color: white;
        border-radius: 1rem 1rem 0 0;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .batch-header h2 {
        margin: 0;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 1.5rem;
    }

    .batch-filters {
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

    .batch-item {
        background: white;
        border: 1px solid var(--winit-border);
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        position: relative;
    }

    .batch-item:hover {
        border-color: var(--winit-accent);
        background: linear-gradient(135deg, rgba(23, 247, 182, 0.05) 0%, rgba(23, 247, 182, 0.02) 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(23, 247, 182, 0.1);
    }

    .batch-status-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        font-family: 'Montserrat', sans-serif;
    }

    .batch-status-badge.uploaded {
        background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);
        color: white;
    }

    .batch-status-badge.processing {
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        color: white;
    }

    .batch-status-badge.completed {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: white;
    }

    .batch-status-badge.failed {
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        color: white;
    }

    .status-clickable {
        position: relative;
    }

    .status-clickable:hover {
        opacity: 0.9;
    }

    .status-clickable:active {
        transform: scale(0.95) !important;
    }

    .status-indicator.animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }

    .search-filter-form {
        background: linear-gradient(135deg, rgba(248, 250, 252, 0.5) 0%, rgba(241, 245, 249, 0.5) 100%);
        padding: 1.25rem;
        border-radius: 0.75rem;
        border: 1px solid rgba(18, 18, 104, 0.1);
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(18, 18, 104, 0.05);
    }

    .search-filter-form .form-label {
        color: var(--winit-text);
        margin-bottom: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .search-filter-form .form-control,
    .search-filter-form .form-select {
        border: 2px solid rgba(18, 18, 104, 0.1);
        border-radius: 0.625rem;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.875rem;
        padding: 0.625rem 0.875rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
        color: var(--winit-text);
    }

    .search-filter-form .form-control:hover,
    .search-filter-form .form-select:hover {
        border-color: rgba(18, 18, 104, 0.2);
        box-shadow: 0 2px 4px rgba(18, 18, 104, 0.08);
    }

    .search-filter-form .form-control:focus,
    .search-filter-form .form-select:focus {
        border-color: var(--winit-accent);
        border-width: 2px;
        box-shadow: 0 0 0 0.25rem rgba(23, 247, 182, 0.15), 0 4px 12px rgba(18, 18, 104, 0.1);
        outline: none;
        background: white;
    }

    .search-filter-form .form-control::placeholder {
        color: var(--winit-text-light);
        opacity: 0.6;
    }

    .search-filter-form .btn {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }

    .search-filter-form .btn-primary {
        background: linear-gradient(135deg, var(--winit-primary) 0%, #1e1e6b 100%);
        border: none;
    }

    .search-filter-form .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(18, 18, 104, 0.3);
    }

    .batch-info {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .batch-details h5 {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        color: var(--winit-text);
        margin: 0 0 0.5rem 0;
    }

    .batch-filename {
        color: var(--winit-text-light);
        font-size: 0.875rem;
        margin: 0 0 1rem 0;
    }

    .batch-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .stat-item {
        text-align: center;
        padding: 0.75rem;
        background: linear-gradient(135deg, rgba(18, 18, 104, 0.05) 0%, rgba(18, 18, 104, 0.02) 100%);
        border-radius: 0.5rem;
        border: 1px solid var(--winit-border);
    }

    .stat-number {
        font-size: 1.25rem;
        font-weight: 700;
        font-family: 'Montserrat', sans-serif;
        margin-bottom: 0.25rem;
    }

    .stat-number.recipients { color: var(--winit-primary); }
    .stat-number.amount { color: #059669; }
    .stat-number.success { color: #10b981; }
    .stat-number.failed { color: #ef4444; }

    .stat-label {
        color: var(--winit-text-light);
        font-size: 0.75rem;
        font-weight: 500;
        font-family: 'Montserrat', sans-serif;
    }

    .batch-progress {
        margin-bottom: 1rem;
    }

    .progress-container {
        background: var(--winit-border);
        border-radius: 0.5rem;
        height: 0.5rem;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .progress-bar {
        height: 100%;
        border-radius: 0.5rem;
        transition: width 0.3s ease;
    }

    .progress-bar.processing {
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
    }

    .progress-bar.completed {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    }

    .progress-text {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.875rem;
        color: var(--winit-text-light);
        font-family: 'Montserrat', sans-serif;
    }

    .batch-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.875rem;
        color: var(--winit-text-light);
        margin-bottom: 1rem;
    }

    .batch-time {
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
    }

    .batch-actions {
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

    .action-btn.view {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border-color: #3b82f6;
    }

    .action-btn.view:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border-color: #2563eb;
        color: white;
    }

    .action-btn.process {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border-color: #10b981;
    }

    .action-btn.process:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        border-color: #059669;
        color: white;
    }

    .action-btn.download {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
        border-color: #8b5cf6;
    }

    .action-btn.download:hover {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        border-color: #7c3aed;
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
    .stat-number.completed { color: #10b981; }
    .stat-number.processing { color: #3b82f6; }
    .stat-number.failed { color: #ef4444; }

    .stat-label {
        color: var(--winit-text-light);
        font-size: 0.875rem;
        font-weight: 500;
        font-family: 'Montserrat', sans-serif;
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

    .table {
        font-family: 'Montserrat', sans-serif;
        background: white;
    }

    .table th {
        background: linear-gradient(135deg, var(--winit-primary) 0%, #1e1e6b 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 1rem;
        white-space: nowrap;
    }

    .table td {
        border-color: var(--winit-border);
        vertical-align: middle;
        padding: 1rem;
    }

    .table tbody tr:hover {
        background: linear-gradient(135deg, rgba(23, 247, 182, 0.05) 0%, rgba(23, 247, 182, 0.02) 100%);
    }

    .table-responsive {
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(18, 18, 104, 0.1);
    }

    .batch-status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        font-family: 'Montserrat', sans-serif;
        display: inline-block;
    }

    @media (max-width: 768px) {
        .batch-header {
            padding: 1rem;
        }
        
        .batch-header h2 {
            font-size: 1.25rem;
        }
        
        .filter-buttons {
            justify-content: center;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table th,
        .table td {
            padding: 0.5rem;
            font-size: 0.875rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="batch-history-card">
        <div class="batch-header">
            <h2>
                <i class="fas fa-history me-2"></i>Batch History
            </h2>
            <div>
                <a href="{{ route('bulk-token.index') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>New Batch Upload
                </a>
            </div>
        </div>
        
        <div class="card-body p-4">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number total">{{ $batches->total() }}</div>
                    <div class="stat-label">Total Batches</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number completed">{{ $batches->where('status', 'completed')->count() }}</div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number processing">{{ $batches->where('status', 'processing')->count() }}</div>
                    <div class="stat-label">Processing</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number failed">{{ $batches->where('status', 'failed')->count() }}</div>
                    <div class="stat-label">Failed</div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="batch-filters">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0" style="font-family: 'Montserrat', sans-serif; font-weight: 600;">
                        <i class="fas fa-search me-2"></i>Search & Filter Batches
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshBatches()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="exportBatches()">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                </div>

                <!-- Search Form -->
                <form id="searchFilterForm" method="GET" action="{{ route('bulk-token.history') }}" class="search-filter-form">
                    <div class="row g-3 mb-3 align-items-end">
                        <!-- Search Query -->
                        <div class="col-md-3">
                            <label for="search" class="form-label small fw-bold">
                                <i class="fas fa-search me-1"></i>Search
                            </label>
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   id="search" 
                                   name="search" 
                                   placeholder="Batch name, filename..."
                                   value="{{ request('search') }}">
                        </div>

                        <!-- Date From -->
                        <div class="col-md-2">
                            <label for="date_from" class="form-label small fw-bold">
                                <i class="fas fa-calendar-alt me-1"></i>Date From
                            </label>
                            <input type="date" 
                                   class="form-control form-control-sm" 
                                   id="date_from" 
                                   name="date_from" 
                                   value="{{ request('date_from') }}">
                        </div>

                        <!-- Date To -->
                        <div class="col-md-2">
                            <label for="date_to" class="form-label small fw-bold">
                                <i class="fas fa-calendar-alt me-1"></i>Date To
                            </label>
                            <input type="date" 
                                   class="form-control form-control-sm" 
                                   id="date_to" 
                                   name="date_to" 
                                   value="{{ request('date_to') }}">
                        </div>

                        <!-- Meter Number -->
                        <div class="col-md-2">
                            <label for="meter_number" class="form-label small fw-bold">
                                <i class="fas fa-tachometer-alt me-1"></i>Meter Number
                            </label>
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   id="meter_number" 
                                   name="meter_number" 
                                   placeholder="Enter meter number..."
                                   value="{{ request('meter_number') }}">
                        </div>

                        <!-- Disco -->
                        <div class="col-md-2">
                            <label for="disco" class="form-label small fw-bold">
                                <i class="fas fa-bolt me-1"></i>Disco
                            </label>
                            <select class="form-select form-select-sm" id="disco" name="disco">
                                <option value="">All Discos</option>
                                @foreach($availableDiscos ?? [] as $discoOption)
                                    <option value="{{ $discoOption }}" {{ request('disco') == $discoOption ? 'selected' : '' }}>
                                        {{ $discoOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-filter me-1"></i>Apply Filters
                            </button>
                            <a href="{{ route('bulk-token.history') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Status Filter Buttons -->
                <div class="filter-buttons">
                    <a href="{{ route('bulk-token.history', array_merge(request()->except('status', 'page'), ['status' => ''])) }}" 
                       class="filter-btn {{ !request('status') ? 'active' : '' }}" 
                       data-filter="all">
                        <i class="fas fa-list"></i>All ({{ $batches->total() }})
                    </a>
                    <a href="{{ route('bulk-token.history', array_merge(request()->except('status', 'page'), ['status' => 'uploaded'])) }}" 
                       class="filter-btn {{ request('status') == 'uploaded' ? 'active' : '' }}" 
                       data-filter="uploaded">
                        <i class="fas fa-upload"></i>Uploaded ({{ $batches->where('status', 'uploaded')->count() }})
                    </a>
                    <a href="{{ route('bulk-token.history', array_merge(request()->except('status', 'page'), ['status' => 'processing'])) }}" 
                       class="filter-btn {{ request('status') == 'processing' ? 'active' : '' }}" 
                       data-filter="processing">
                        <i class="fas fa-cog fa-spin"></i>Processing ({{ $batches->where('status', 'processing')->count() }})
                    </a>
                    <a href="{{ route('bulk-token.history', array_merge(request()->except('status', 'page'), ['status' => 'completed'])) }}" 
                       class="filter-btn {{ request('status') == 'completed' ? 'active' : '' }}" 
                       data-filter="completed">
                        <i class="fas fa-check-circle"></i>Completed ({{ $batches->where('status', 'completed')->count() }})
                    </a>
                    <a href="{{ route('bulk-token.history', array_merge(request()->except('status', 'page'), ['status' => 'failed'])) }}" 
                       class="filter-btn {{ request('status') == 'failed' ? 'active' : '' }}" 
                       data-filter="failed">
                        <i class="fas fa-times-circle"></i>Failed ({{ $batches->where('status', 'failed')->count() }})
                    </a>
                </div>
            </div>

            <!-- Batches List -->
            <div id="batchesList">
                @if($batches->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Batch Name</th>
                                    <th>Recipients</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Success Rate</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batches as $batch)
                                <tr data-status="{{ $batch->status ?? 'uploaded' }}" data-id="{{ $batch->id }}" style="transition: all 0.3s ease;">
                                    <td>
                                        <div class="batch-details">
                                            <h6 class="mb-0" style="font-family: 'Montserrat', sans-serif; font-weight: 600;">{{ $batch->batch_name }}</h6>
                                            <small class="text-muted">{{ $batch->filename }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($batch->total_recipients) }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">₦{{ number_format($batch->total_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusConfig = [
                                                'completed' => ['class' => 'completed', 'icon' => 'fa-check-circle', 'color' => 'linear-gradient(135deg, #059669 0%, #10b981 100%)'],
                                                'processing' => ['class' => 'processing', 'icon' => 'fa-cog fa-spin', 'color' => 'linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)'],
                                                'failed' => ['class' => 'failed', 'icon' => 'fa-times-circle', 'color' => 'linear-gradient(135deg, #dc2626 0%, #ef4444 100%)'],
                                                'uploaded' => ['class' => 'uploaded', 'icon' => 'fa-upload', 'color' => 'linear-gradient(135deg, #6b7280 0%, #9ca3af 100%)'],
                                            ];
                                            $status = $batch->status ?? 'uploaded';
                                            $config = $statusConfig[$status] ?? $statusConfig['uploaded'];
                                        @endphp
                                        <span class="batch-status-badge {{ $config['class'] }} status-clickable" 
                                              data-status="{{ $status }}"
                                              data-batch-id="{{ $batch->id }}"
                                              style="
                                            padding: 0.375rem 0.875rem;
                                            border-radius: 0.5rem;
                                            font-size: 0.75rem;
                                            font-weight: 600;
                                            font-family: 'Montserrat', sans-serif;
                                            display: inline-flex;
                                            align-items: center;
                                            gap: 0.375rem;
                                            background: {{ $config['color'] }};
                                            color: white;
                                            cursor: pointer;
                                            transition: all 0.3s ease;
                                            user-select: none;
                                        "
                                        onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'"
                                        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'"
                                        title="Click to filter by {{ ucfirst($status) }} status">
                                            <i class="fas {{ $config['icon'] }}"></i>
                                            <span class="status-text">{{ ucfirst($status) }}</span>
                                            @if($status == 'processing')
                                                <span class="status-indicator animate-pulse" style="width: 8px; height: 8px; background: white; border-radius: 50%; margin-left: 0.25rem;"></span>
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $processedCount = $batch->recipients()->where('status', '!=', 'pending')->count();
                                            $totalCount = $batch->total_recipients;
                                            $percentage = $totalCount > 0 ? round(($processedCount / $totalCount) * 100, 1) : 0;
                                        @endphp
                                        @if(($batch->status == 'processing' || $batch->status == 'completed') && $totalCount > 0)
                                            <div class="progress-container" style="width: 120px; height: 24px; margin-bottom: 0.25rem;">
                                                <div class="progress-bar {{ $batch->status }}" style="width: {{ $percentage }}%; height: 100%;"></div>
                                            </div>
                                            <small class="text-muted fw-bold">{{ $percentage }}% ({{ $processedCount }}/{{ $totalCount }})</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $successCount = $batch->transactions()->where('status', 'success')->count();
                                            $processedRecipients = $batch->recipients()->where('status', '!=', 'pending')->count();
                                            $successRate = $processedRecipients > 0 ? round(($successCount / $processedRecipients) * 100, 1) : 0;
                                        @endphp
                                        @if($processedRecipients > 0)
                                            <span class="fw-bold text-{{ $successRate >= 80 ? 'success' : ($successRate >= 50 ? 'warning' : 'danger') }}" style="font-size: 1rem;">
                                                {{ $successRate }}%
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                {{ $successCount }}/{{ $processedRecipients }} successful
                                            </small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $batch->created_at->format('M d, Y') }}</span>
                                        <br>
                                        <small class="text-muted">{{ $batch->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <div class="batch-actions">
                                            <a href="{{ route('bulk-token.show', $batch->id) }}" class="action-btn view" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($batch->status == 'uploaded')
                                                <button class="action-btn process" onclick="processBatch({{ $batch->id }})" title="Start Processing">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            @endif
                                            
                                            @if($batch->status == 'completed' || $batch->status == 'failed' || $batch->processed_recipients > 0)
                                                <a href="{{ route('bulk-token.download-report', $batch->id) }}" class="action-btn download" title="Download Batch Report">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Batch History Found</h3>
                        <p>You haven't uploaded any CSV files yet. Start by uploading your first batch!</p>
                        <a href="{{ route('bulk-token.index') }}" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Upload Your First CSV
                        </a>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($batches->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $batches->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Processing Modal -->
<div class="modal fade" id="processingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Processing Batch Synchronously</h5>
            </div>
            <div class="modal-body text-center">
                <div class="loading-spinner">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <p>Starting synchronous batch-by-batch processing...</p>
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle"></i> Recipients will be processed in batches, each completing before the next starts
                </small>
            </div>
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

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const batchRows = document.querySelectorAll('tbody tr[data-status]');

    // Setup filter buttons
    filterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter batches
            const filter = this.getAttribute('data-filter') || this.getAttribute('data-status') || 'all';
            
            filterByStatus(filter);
        });
    });

    // Setup status badge click handlers
    document.querySelectorAll('.status-clickable').forEach(badge => {
        badge.addEventListener('click', function(e) {
            e.stopPropagation();
            const status = this.getAttribute('data-status');
            
            // Update filter buttons
            filterButtons.forEach(btn => {
                btn.classList.remove('active');
                const btnStatus = btn.getAttribute('data-filter') || btn.getAttribute('data-status');
                if (btnStatus === status || (btnStatus === 'all' && !status)) {
                    btn.classList.add('active');
                }
            });
            
            // Filter table
            filterByStatus(status || 'all');
            
            // Scroll to top of table
            const tableContainer = document.querySelector('.table-responsive');
            if (tableContainer) {
                tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });


    // Auto-refresh for processing batches
    checkProcessingBatches();
});

function filterByStatus(status) {
    const rows = document.querySelectorAll('tbody tr[data-status]');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        if (!status || status === 'all' || rowStatus === status) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Show message if no results
    const tbody = document.querySelector('tbody');
    let noResultsMsg = document.getElementById('noResultsMessage');
    
    if (visibleCount === 0) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('tr');
            noResultsMsg.id = 'noResultsMessage';
            noResultsMsg.innerHTML = `
                <td colspan="8" class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No batches found with the selected status filter.</p>
                </td>
            `;
            tbody.appendChild(noResultsMsg);
        }
    } else {
        if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }
}

function checkProcessingBatches() {
    const processingRows = document.querySelectorAll('tr[data-status="processing"]');
    if (processingRows.length > 0) {
        // Refresh page every 30 seconds if there are processing batches
        setTimeout(() => {
            if (window.location.pathname.includes('/bulk-token/history')) {
                console.log('Auto-refreshing batch status...');
                window.location.reload();
            }
        }, 30000); // Refresh every 30 seconds
    }
}

// Process batch functionality
function processBatch(batchId) {
    const modal = new bootstrap.Modal(document.getElementById('processingModal'));
    modal.show();
    
    utils.makeRequest(`/bulk-token/process/${batchId}`, {
        method: 'POST'
    })
    .then(data => {
        modal.hide();
        utils.showAlert('Batch processing started synchronously! Processing recipients in batches...', 'success');
        setTimeout(() => {
            window.location.href = `/bulk-token/show/${batchId}`;
        }, 2000);
    })
    .catch(error => {
        modal.hide();
        utils.showAlert('Failed to start processing: ' + error.message, 'error');
    });
}

// Download report functionality (now handled by direct link)
function downloadReport(batchId) {
    window.location.href = `/bulk-token/download-report/${batchId}`;
}

// Refresh batches
function refreshBatches() {
    location.reload();
}

// Export batches
function exportBatches() {
    utils.showAlert('Export functionality coming soon!', 'info');
}

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