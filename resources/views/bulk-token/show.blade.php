@extends('layouts.app')

@section('title', 'Batch Details - ' . $batch->batch_name)

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Batch Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>{{ $batch->batch_name }}</h2>
                <p class="text-muted mb-0">{{ $batch->filename }} • {{ $batch->created_at->format('M d, Y h:i A') }}</p>
            </div>
            <div>
                <a href="{{ route('bulk-token.history') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to History
                </a>
                @if($batch->status == 'uploaded')
                    <button type="button" class="btn btn-success" onclick="processBatch({{ $batch->id }})">
                        <i class="fas fa-play"></i> Start Processing
                    </button>
                @endif
            </div>
        </div>

        <!-- Batch Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ number_format($batch->total_recipients) }}</h3>
                        <small>Total Recipients</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ number_format($batch->successful_transactions) }}</h3>
                        <small>Successful</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ number_format($batch->failed_transactions) }}</h3>
                        <small>Failed</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">₦{{ number_format($batch->total_amount, 2) }}</h3>
                        <small>Total Amount</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Overview -->
        @if($batch->status !== 'uploaded')
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-2">Processing Progress</h6>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar bg-{{ $batch->status == 'completed' ? 'success' : 'info' }}" 
                                 style="width: {{ $batch->completion_percentage }}%"
                                 id="mainProgress">
                                {{ $batch->completion_percentage }}%
                            </div>
                        </div>
                        <small class="text-muted mt-1">
                            {{ $batch->processed_recipients }}/{{ $batch->total_recipients }} processed
                        </small>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="status-badge status-{{ $batch->status }} fs-6">
                            {{ ucfirst($batch->status) }}
                        </span>
                        @if($batch->processed_recipients > 0)
                            <div class="mt-2">
                                <small class="text-muted">Success Rate:</small>
                                <strong class="text-{{ $batch->success_rate > 80 ? 'success' : ($batch->success_rate > 50 ? 'warning' : 'danger') }}">
                                    {{ $batch->success_rate }}%
                                </strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Recipients List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recipients</h5>
                <div>
                    @if($batch->status == 'processing')
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshStatus()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($batch->recipients && $batch->recipients->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Phone Number</th>
                                    <th>Disco</th>
                                    <th>Meter Number</th>
                                    <th>Meter Type</th>
                                    <th>Amount</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th>Token</th>
                                    <th>Units</th>
                                    <th>Transaction Ref</th>
                                    <th>Processed At</th>
                                    <th>Error</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batch->recipients as $recipient)
                                    <tr>
                                        <td>
                                            <strong>{{ $recipient->name }}</strong>
                                        </td>
                                        <td>
                                            <code>{{ $recipient->phone_number }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $recipient->disco }}</span>
                                        </td>
                                        <td>
                                            <code>{{ $recipient->meter_number }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $recipient->meter_type == 'prepaid' ? 'success' : 'warning' }}">
                                                {{ ucfirst($recipient->meter_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">₦{{ number_format($recipient->amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $recipient->address }}</small>
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ $recipient->status }}">
                                                @if($recipient->status == 'pending')
                                                    <i class="fas fa-clock"></i>
                                                @elseif($recipient->status == 'processing')
                                                    <i class="fas fa-cog fa-spin"></i>
                                                @elseif($recipient->status == 'success')
                                                    <i class="fas fa-check"></i>
                                                @else
                                                    <i class="fas fa-times"></i>
                                                @endif
                                                {{ ucfirst($recipient->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($recipient->transaction && $recipient->transaction->token)
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="showToken('{{ $recipient->transaction->token }}')"
                                                        title="Click to view token">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($recipient->transaction && $recipient->transaction->units)
                                                <strong>{{ $recipient->transaction->units }} KWh</strong>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($recipient->transaction_reference)
                                                <small><code>{{ $recipient->transaction_reference }}</code></small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($recipient->processed_at)
                                                <small>{{ $recipient->processed_at->format('M d, H:i') }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($recipient->error_message)
                                                <small class="text-danger" title="{{ $recipient->error_message }}">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    {{ Str::limit($recipient->error_message, 30) }}
                                                </small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-2x text-muted mb-3"></i>
                        <p class="text-muted">No recipients found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Auto-refresh for processing batches -->
@if($batch->status == 'processing')
<script>
    // Auto-refresh every 5 seconds for processing batches
    setInterval(function() {
        refreshStatus();
    }, 5000);
</script>
@endif
@endsection

@section('scripts')
<script>
    function refreshStatus() {
        fetch(`/bulk-token/status/{{ $batch->id }}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update progress bar
                    const progressBar = document.getElementById('mainProgress');
                    if (progressBar) {
                        progressBar.style.width = data.data.completion_percentage + '%';
                        progressBar.textContent = data.data.completion_percentage + '%';
                        
                        if (data.data.status === 'completed') {
                            progressBar.className = 'progress-bar bg-success';
                        }
                    }
                    
                    // Reload page if status changed to completed
                    if (data.data.status === 'completed' && '{{ $batch->status }}' === 'processing') {
                        location.reload();
                    }
                }
            })
            .catch(error => {
                console.error('Error refreshing status:', error);
            });
    }
    
    function processBatch(batchId) {
        if (!confirm('Are you sure you want to start processing this batch?')) {
            return;
        }
        
        fetch(`/bulk-token/process/${batchId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                utils.showAlert('success', 'Batch processing started! Page will refresh in 3 seconds...');
                setTimeout(() => location.reload(), 3000);
            } else {
                utils.showAlert('danger', 'Failed to start processing: ' + data.message);
            }
        })
        .catch(error => {
            utils.showAlert('danger', 'Failed to start processing: ' + error.message);
        });
    }
</script>
@endsection