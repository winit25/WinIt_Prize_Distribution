@extends('layouts.app')

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

@section('scripts')
<script>
    function processBatch(batchId) {
        const modal = new bootstrap.Modal(document.getElementById('processingModal'));
        modal.show();
        
        fetch(`/bulk-token/process/${batchId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
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
                utils.showAlert('danger', 'Failed to start processing: ' + data.message);
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