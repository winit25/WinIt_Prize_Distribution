@extends('layouts.sidebar')

@section('title', 'Transaction History')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-receipt"></i>
                Transaction History
            </h2>
            <div>
                <a href="{{ route('bulk-token.history') }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-archive"></i>
                    Batch History
                </a>
                <a href="{{ route('bulk-token.index') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    New Upload
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $stats['successful'] ?? 0 }}</h3>
                        <small>Successful Transactions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $stats['failed'] ?? 0 }}</h3>
                        <small>Failed Transactions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">₦{{ number_format($stats['total_amount'] ?? 0, 2) }}</h3>
                        <small>Total Amount</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ number_format($stats['success_rate'] ?? 0, 1) }}%</h3>
                        <small>Success Rate</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Transactions</h5>
                <div>
                    <!-- Filter Options -->
                    <select class="form-select form-select-sm d-inline-block w-auto" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="success">Successful</option>
                        <option value="failed">Failed</option>
                        <option value="pending">Pending</option>
                    </select>
                    <select class="form-select form-select-sm d-inline-block w-auto ms-2" id="discoFilter">
                        <option value="">All Discos</option>
                        <option value="EKEDC">EKEDC</option>
                        <option value="IKEDC">IKEDC</option>
                        <option value="AEDC">AEDC</option>
                        <option value="IBEDC">IBEDC</option>
                        <option value="EEDC">EEDC</option>
                        <option value="BEDC">BEDC</option>
                        <option value="JEDC">JEDC</option>
                        <option value="KAEDCO">KAEDCO</option>
                        <option value="KEDCO">KEDCO</option>
                        <option value="PHED">PHED</option>
                        <option value="YEDC">YEDC</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                @if($transactions && $transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" id="transactionsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Recipient</th>
                                    <th>Phone</th>
                                    <th>Disco</th>
                                    <th>Meter</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Token</th>
                                    <th>Units</th>
                                    <th>Reference</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    <tr class="transaction-row" 
                                        data-status="{{ $transaction->status }}" 
                                        data-disco="{{ $transaction->recipient->disco ?? '' }}">
                                        <td>
                                            <strong>{{ $transaction->recipient->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $transaction->recipient->address }}</small>
                                        </td>
                                        <td>
                                            <code>{{ $transaction->phone_number }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $transaction->recipient->disco ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <code class="small">{{ $transaction->recipient->meter_number ?? 'N/A' }}</code>
                                            <br>
                                            <span class="badge bg-{{ ($transaction->recipient->meter_type ?? 'prepaid') == 'prepaid' ? 'success' : 'warning' }} badge-sm">
                                                {{ ucfirst($transaction->recipient->meter_type ?? 'prepaid') }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">₦{{ number_format($transaction->amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ $transaction->status }}">
                                                @if($transaction->status == 'success')
                                                    <i class="fas fa-check"></i> Success
                                                @elseif($transaction->status == 'failed')
                                                    <i class="fas fa-times"></i> Failed
                                                @elseif($transaction->status == 'pending')
                                                    <i class="fas fa-clock"></i> Pending
                                                @else
                                                    <i class="fas fa-cog fa-spin"></i> Processing
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            @if($transaction->token)
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="showToken('{{ $transaction->token }}')"
                                                        title="Click to view token">
                                                    <i class="fas fa-eye"></i> View Token
                                                </button>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->units)
                                                <strong>{{ $transaction->units }} KWh</strong>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->buypower_reference)
                                                <small><code>{{ $transaction->buypower_reference }}</code></small>
                                                @if($transaction->order_id)
                                                    <br><small class="text-muted">Order: {{ $transaction->order_id }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->processed_at)
                                                <small>{{ $transaction->processed_at->format('M d, Y') }}</small>
                                                <br>
                                                <small class="text-muted">{{ $transaction->processed_at->format('h:i A') }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if($transaction->status == 'success' && $transaction->token)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-success"
                                                            onclick="downloadToken('{{ $transaction->id }}')"
                                                            title="Download token details">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                @endif
                                                
                                                @if($transaction->error_message)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="showError('{{ addslashes($transaction->error_message) }}')"
                                                            title="View error details">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </button>
                                                @endif
                                                
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-info"
                                                        onclick="viewDetails('{{ $transaction->id }}')"
                                                        title="View full details">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
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
                                Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} 
                                of {{ $transactions->total() }} results
                            </small>
                        </div>
                        <div>
                            {{ $transactions->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No transactions found</h5>
                        <p class="text-muted mb-4">No tokens have been processed yet.</p>
                        <a href="{{ route('bulk-token.index') }}" class="btn btn-primary">
                            <i class="fas fa-upload"></i>
                            Start Processing Tokens
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Token Modal -->
<div class="modal fade" id="tokenModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Electricity Token</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <h3 class="text-success mb-3" id="tokenDisplay"></h3>
                    <button class="btn btn-outline-secondary" onclick="copyToken()" id="copyTokenBtn">
                        <i class="fas fa-copy"></i> Copy Token
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Error Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="errorDisplay"></span>
                </div>
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

    .btn-group .btn {
        border-radius: 0.375rem !important;
        margin-right: 0.25rem;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
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
</style>
@endpush

@section('scripts')
<script>
    let currentToken = '';
    
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
    
    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', filterTransactions);
    document.getElementById('discoFilter').addEventListener('change', filterTransactions);
    
    function filterTransactions() {
        const statusFilter = document.getElementById('statusFilter').value;
        const discoFilter = document.getElementById('discoFilter').value;
        const rows = document.querySelectorAll('.transaction-row');
        
        rows.forEach(row => {
            const status = row.dataset.status;
            const disco = row.dataset.disco;
            
            let showRow = true;
            
            if (statusFilter && status !== statusFilter) {
                showRow = false;
            }
            
            if (discoFilter && disco !== discoFilter) {
                showRow = false;
            }
            
            row.style.display = showRow ? '' : 'none';
        });
    }
    
    function showToken(token) {
        currentToken = token;
        document.getElementById('tokenDisplay').textContent = token;
        new bootstrap.Modal(document.getElementById('tokenModal')).show();
    }
    
    function copyToken() {
        navigator.clipboard.writeText(currentToken).then(() => {
            const btn = document.getElementById('copyTokenBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        });
    }
    
    function showError(error) {
        document.getElementById('errorDisplay').textContent = error;
        new bootstrap.Modal(document.getElementById('errorModal')).show();
    }
    
    function downloadToken(transactionId) {
        // Create downloadable token receipt
        fetch(`/bulk-token/transaction/${transactionId}/download`)
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `token-${transactionId}.txt`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                utils.showAlert('danger', 'Failed to download token details');
            });
    }
    
    function viewDetails(transactionId) {
        // Redirect to detailed transaction view
        window.location.href = `/bulk-token/transaction/${transactionId}`;
    }
</script>
@endsection