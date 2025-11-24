@extends('layouts.sidebar')

@section('title', 'DSTV Transaction History - WinIt Prize Distribution')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="font-family: 'Montserrat', sans-serif; font-weight: 700; color: rgb(18, 18, 104);">
                    <i class="fas fa-receipt me-2"></i>
                    Transaction History
                </h2>
                <p class="text-muted mb-0">View and download all DSTV subscription transactions</p>
            </div>
            <div>
                <a href="{{ route('bulk-dstv.history') }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-archive"></i>
                    Batch History
                </a>
                <a href="{{ route('bulk-dstv.index') }}" class="btn btn-primary">
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

        <!-- Search and Filters -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="font-family: 'Montserrat', sans-serif; font-weight: 600;">
                    <i class="fas fa-search me-2"></i>Search & Filter Transactions
                </h5>
            </div>
            <div class="card-body">
                <form id="searchFilterForm" method="GET" action="{{ route('bulk-dstv.transactions') }}" class="search-filter-form">
                    <div class="row g-3 align-items-end">
                        <!-- Search Query -->
                        <div class="col-md-3">
                            <label for="search" class="form-label small fw-bold">
                                <i class="fas fa-search me-1"></i>Search
                            </label>
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   id="search" 
                                   name="search" 
                                   placeholder="Phone, reference, name..."
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

                        <!-- Recipient Name -->
                        <div class="col-md-3">
                            <label for="recipient_name" class="form-label small fw-bold">
                                <i class="fas fa-user me-1"></i>Recipient Name
                            </label>
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   id="recipient_name" 
                                   name="recipient_name" 
                                   placeholder="Enter recipient name..."
                                   value="{{ request('recipient_name') }}">
                        </div>

                        <!-- Status Filter -->
                        <div class="col-md-2">
                            <label for="status" class="form-label small fw-bold">
                                <i class="fas fa-filter me-1"></i>Status
                            </label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-filter me-1"></i>Apply
                            </button>
                            <a href="{{ route('bulk-dstv.transactions') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Transactions</h5>
                <div>
                    <small class="text-muted">
                        Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} results
                    </small>
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
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Reference</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    <tr class="transaction-row" 
                                        data-status="{{ $transaction->status }}">
                                        <td>
                                            <strong>{{ $transaction->recipient->name ?? 'N/A' }}</strong>
                                            @if($transaction->recipient && $transaction->recipient->email)
                                                <br>
                                                <small class="text-muted">{{ $transaction->recipient->email }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <code>{{ $transaction->phone_number }}</code>
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
                                                @if($transaction->error_message)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="showError('{{ addslashes($transaction->error_message) }}')"
                                                            title="View error details">
                                                        <i class="fas fa-exclamation-triangle me-1"></i> Error
                                                    </button>
                                                @endif
                                                
                                                @if($transaction->batchUpload)
                                                    <a href="{{ route('bulk-dstv.show', $transaction->batchUpload->id) }}" 
                                                       class="btn btn-sm btn-primary"
                                                       title="View batch details">
                                                        <i class="fas fa-eye me-1"></i> View Batch
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center align-items-center mt-4">
                        <div>
                            {{ $transactions->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No transactions found</h5>
                        <p class="text-muted mb-4">No tokens have been processed yet.</p>
                        <a href="{{ route('bulk-dstv.index') }}" class="btn btn-primary">
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
<div class="modal fade" id="tokenModal" tabindex="-1" aria-labelledby="tokenModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 1rem; border: none; box-shadow: 0 10px 40px rgba(18, 18, 104, 0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, rgb(18, 18, 104) 0%, #1e1e6b 100%); color: white; border-radius: 1rem 1rem 0 0; border: none;">
                <h5 class="modal-title" id="tokenModalLabel" style="font-family: 'Montserrat', sans-serif; font-weight: 600;">
                    <i class="fas fa-bolt me-2"></i>Electricity Token
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <i class="fas fa-bolt fa-3x text-warning mb-3"></i>
                    <h3 class="mb-3" id="tokenDisplay" style="font-family: 'Courier New', monospace; font-size: 1.75rem; font-weight: 700; letter-spacing: 0.1em; color: rgb(18, 18, 104); word-break: break-all; min-height: 50px; display: flex; align-items: center; justify-content: center;"></h3>
                </div>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-primary" onclick="copyToken()" id="copyTokenBtn">
                        <i class="fas fa-copy me-2"></i> Copy Token
                    </button>
                    <a href="#" id="downloadTokenBtn" class="btn btn-success" download>
                        <i class="fas fa-download me-2"></i> Download Receipt
                    </a>
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
    .search-filter-form {
        background: linear-gradient(135deg, rgba(248, 250, 252, 0.5) 0%, rgba(241, 245, 249, 0.5) 100%);
        padding: 1rem;
        border-radius: 0.75rem;
        border: 1px solid rgba(18, 18, 104, 0.1);
        box-shadow: 0 2px 8px rgba(18, 18, 104, 0.05);
    }

    .search-filter-form .form-label {
        color: rgb(18, 18, 104);
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
        color: rgb(30, 41, 59);
    }

    .search-filter-form .form-control:hover,
    .search-filter-form .form-select:hover {
        border-color: rgba(18, 18, 104, 0.2);
        box-shadow: 0 2px 4px rgba(18, 18, 104, 0.08);
    }

    .search-filter-form .form-control:focus,
    .search-filter-form .form-select:focus {
        border-color: #17f7b6;
        border-width: 2px;
        box-shadow: 0 0 0 0.25rem rgba(23, 247, 182, 0.15), 0 4px 12px rgba(18, 18, 104, 0.1);
        outline: none;
        background: white;
    }

    .search-filter-form .form-control::placeholder {
        color: #64748b;
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
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, #1e1e6b 100%);
        border: none;
    }

    .search-filter-form .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(18, 18, 104, 0.3);
    }

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
        cursor: pointer;
    }
    
    .recipient-link {
        transition: all 0.3s ease;
    }
    
    .recipient-link:hover {
        color: var(--winit-accent) !important;
        text-decoration: underline !important;
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
    let currentTransactionId = null;
    
    // Utility functions
    const utils = {
        showAlert: function(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.row') || document.body;
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    };
    
    // Form submission handling and View Token functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Setup View Token button event listeners
        document.querySelectorAll('.view-token-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const token = this.getAttribute('data-token');
                const transactionId = this.getAttribute('data-transaction-id');
                
                if (token && transactionId) {
                    showToken(token, transactionId);
                } else {
                    console.error('Missing token or transaction ID');
                    utils.showAlert('danger', 'Unable to display token. Please try again.');
                }
            });
        });

        // Close modal cleanup
        const tokenModal = document.getElementById('tokenModal');
        if (tokenModal) {
            tokenModal.addEventListener('hidden.bs.modal', function() {
                currentToken = '';
                currentTransactionId = null;
            });
        }
    });
    
    function showToken(token, transactionId) {
        if (!token) {
            console.error('No token provided');
            utils.showAlert('warning', 'No token available for this transaction');
            return;
        }
        
        currentToken = token;
        currentTransactionId = transactionId;
        
        // Update token display
        const tokenDisplay = document.getElementById('tokenDisplay');
        if (!tokenDisplay) {
            console.error('Token display element not found');
            utils.showAlert('danger', 'Error displaying token. Please refresh the page.');
            return;
        }
        
        tokenDisplay.textContent = token;
        tokenDisplay.style.color = 'rgb(18, 18, 104)';
        tokenDisplay.style.fontSize = '1.75rem';
        
        // Set download link
        const downloadBtn = document.getElementById('downloadTokenBtn');
        if (downloadBtn && transactionId) {
            downloadBtn.href = `/bulk-dstv/transaction/${transactionId}/download`;
            downloadBtn.style.display = 'inline-block';
        } else if (downloadBtn) {
            downloadBtn.style.display = 'none';
        }
        
        // Show modal using Bootstrap 5
        const modalElement = document.getElementById('tokenModal');
        if (!modalElement) {
            console.error('Token modal element not found');
            utils.showAlert('danger', 'Modal not found. Please refresh the page.');
            return;
        }
        
        // Try Bootstrap 5 first
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            try {
                // Check if modal instance already exists
                let modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalElement, {
                        backdrop: 'static',
                        keyboard: false
                    });
                }
                modalInstance.show();
            } catch (error) {
                console.error('Error showing Bootstrap modal:', error);
                // Fallback to direct display
                showModalDirect(modalElement);
            }
        } else if (typeof $ !== 'undefined' && $.fn.modal) {
            // Fallback to jQuery Bootstrap
            try {
                $(modalElement).modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $(modalElement).modal('show');
            } catch (error) {
                console.error('Error showing jQuery modal:', error);
                showModalDirect(modalElement);
            }
        } else {
            // Direct display fallback
            showModalDirect(modalElement);
        }
    }
    
    function showModalDirect(modalElement) {
        // Fallback method to show modal directly
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        modalElement.setAttribute('aria-hidden', 'false');
        modalElement.setAttribute('aria-modal', 'true');
        
        // Add backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'tokenModalBackdrop';
        document.body.appendChild(backdrop);
        document.body.classList.add('modal-open');
        document.body.style.paddingRight = '17px';
        
        // Remove backdrop when modal is closed
        const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                removeModalBackdrop();
            });
        });
        
        // Remove backdrop on backdrop click
        backdrop.addEventListener('click', function() {
            removeModalBackdrop();
        });
    }
    
    function removeModalBackdrop() {
        const modalElement = document.getElementById('tokenModal');
        const backdrop = document.getElementById('tokenModalBackdrop');
        
        if (modalElement) {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            modalElement.setAttribute('aria-hidden', 'true');
            modalElement.setAttribute('aria-modal', 'false');
        }
        
        if (backdrop) {
            backdrop.remove();
        }
        
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
    }
    
    function copyToken() {
        if (!currentToken) {
            utils.showAlert('warning', 'No token to copy');
            return;
        }
        
        navigator.clipboard.writeText(currentToken).then(() => {
            const btn = document.getElementById('copyTokenBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
            
            utils.showAlert('success', 'Token copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy token:', err);
            utils.showAlert('danger', 'Failed to copy token to clipboard');
        });
    }
    
    function showError(error) {
        document.getElementById('errorDisplay').textContent = error;
        new bootstrap.Modal(document.getElementById('errorModal')).show();
    }
    
    function downloadToken(transactionId) {
        if (!transactionId) {
            utils.showAlert('danger', 'Invalid transaction ID');
            return;
        }
        
        // Redirect to download route
        window.location.href = `/bulk-dstv/transaction/${transactionId}/download`;
    }
    
    // Make entire row clickable to view details (excluding buttons)
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.transaction-row').forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on a button or link
                if (e.target.closest('button') || e.target.closest('a')) {
                    return;
                }
                
                // Find the view details link
                const viewLink = this.querySelector('a[href*="/bulk-dstv/transaction/"]');
                if (viewLink) {
                    window.location.href = viewLink.getAttribute('href');
                }
            });
        });
    });
</script>
@endsection