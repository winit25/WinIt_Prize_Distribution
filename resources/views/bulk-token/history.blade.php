@extends('layouts.sidebar')

@section('title', 'Batch History')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-history"></i>
                Batch History
            </h2>
            <a href="{{ route('bulk-token.index') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                New Batch Upload
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @if($batches && $batches->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch Name</th>
                                    <th>Recipients</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Success Rate</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batches as $batch)
                                    <tr>
                                        <td>
                                            <strong>{{ $batch->batch_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $batch->filename }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ number_format($batch->total_recipients) }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">
                                                ₦{{ number_format($batch->total_amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ $batch->status }}">
                                                {{ ucfirst($batch->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($batch->status == 'processing' || $batch->status == 'completed')
                                                <div class="progress" style="width: 100px; height: 20px;">
                                                    <div class="progress-bar bg-{{ $batch->status == 'completed' ? 'success' : 'info' }}" 
                                                         style="width: {{ $batch->completion_percentage }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $batch->completion_percentage }}%</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($batch->processed_recipients > 0)
                                                <span class="fw-bold text-{{ $batch->success_rate > 80 ? 'success' : ($batch->success_rate > 50 ? 'warning' : 'danger') }}">
                                                    {{ $batch->success_rate }}%
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $batch->successful_transactions }}/{{ $batch->processed_recipients }}
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
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('bulk-token.show', $batch->id) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if($batch->status == 'uploaded')
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-success"
                                                            onclick="processBatch({{ $batch->id }})">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                @endif
                                                
                                                @if($batch->status == 'completed')
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-info"
                                                            onclick="downloadReport({{ $batch->id }})">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <small class="text-muted">
                                Showing {{ $batches->firstItem() }} to {{ $batches->lastItem() }} 
                                of {{ $batches->total() }} results
                            </small>
                        </div>
                        <div>
                            {{ $batches->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No batch history found</h5>
                        <p class="text-muted mb-4">You haven't uploaded any CSV files yet.</p>
                        <a href="{{ route('bulk-token.index') }}" class="btn btn-primary">
                            <i class="fas fa-upload"></i>
                            Upload Your First CSV
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Processing Modal -->
<div class="modal fade" id="processingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Processing Batch</h5>
            </div>
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Starting batch processing...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .status-success {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: white;
    }

    .status-failed {
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        color: white;
    }

    .status-pending {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        color: white;
    }

    .status-processing {
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        color: white;
    }

    .status-uploaded {
        background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);
        color: white;
    }

    .status-completed {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: white;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(18, 18, 104, 0.05);
    }

    .card {
        border: 1px solid rgba(18, 18, 104, 0.1);
        box-shadow: 0 2px 8px rgba(18, 18, 104, 0.1);
    }

    .card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid rgba(18, 18, 104, 0.1);
    }

    .btn-group .btn {
        border-radius: 0.375rem !important;
        margin-right: 0.25rem;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@endpush

@section('scripts')
<script>
    // Utility functions
    const utils = {
        showAlert: function(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container-fluid') || document.body;
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    };

    function processBatch(batchId) {
        const modal = new bootstrap.Modal(document.getElementById('processingModal'));
        modal.show();
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value || 
                         window.csrfToken;
        
        fetch(`/bulk-token/process/${batchId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            modal.hide();
            
            if (data.success) {
                utils.showAlert('success', 'Batch processing started! Redirecting to details page...');
                setTimeout(() => {
                    window.location.href = `/bulk-token/show/${batchId}`;
                }, 2000);
            } else {
                utils.showAlert('danger', 'Failed to start processing: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            modal.hide();
            utils.showAlert('danger', 'Failed to start processing: ' + error.message);
        });
    }
    
    function downloadReport(batchId) {
        // This would generate and download a CSV report
        // For now, we'll redirect to the details page
        window.location.href = `/bulk-token/show/${batchId}`;
    }
</script>
@endsection