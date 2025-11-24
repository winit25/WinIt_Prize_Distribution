@extends('layouts.sidebar')

@section('title', 'Recipient & Transaction Details - WinIt Prize Distribution')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Page Header -->
        <div class="page-header mb-4" style="
            background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
            color: white;
            padding: 2rem;
            border-radius: 1.5rem;
            margin-bottom: 2rem;
        ">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2" style="margin: 0; font-weight: 700;">
                        <i class="fas fa-receipt me-2"></i>
                        Recipient & Transaction Details
                    </h2>
                    <p style="margin: 0; opacity: 0.9; font-size: 1rem;">
                        Complete information about the recipient and transaction
                    </p>
                </div>
                <div>
                    <a href="{{ route('bulk-token.transactions') }}" class="btn btn-light me-2" style="
                        background: rgba(255, 255, 255, 0.2);
                        border: 1px solid rgba(255, 255, 255, 0.3);
                        color: white;
                        backdrop-filter: blur(10px);
                    ">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Transactions
                    </a>
                    @if($transaction->token)
                        <button class="btn btn-light" onclick="downloadToken('{{ $transaction->id }}')" style="
                            background: rgba(255, 255, 255, 0.2);
                            border: 1px solid rgba(255, 255, 255, 0.3);
                            color: white;
                            backdrop-filter: blur(10px);
                        ">
                            <i class="fas fa-download me-1"></i>
                            Download Token
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Transaction Status Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card" style="
                    background: white;
                    border-radius: 1.5rem;
                    box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
                    border: 1px solid rgba(18, 18, 104, 0.1);
                ">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-2" style="color: rgb(18, 18, 104); font-weight: 700;">
                                    <i class="fas fa-hashtag me-2"></i>Transaction #{{ $transaction->id }}
                                </h4>
                                <p class="text-muted mb-0" style="font-family: 'Montserrat', sans-serif;">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Processed on {{ $transaction->processed_at ? $transaction->processed_at->format('M d, Y h:i A') : 'Not processed' }}
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="status-badge status-{{ $transaction->status }}" style="font-size: 1.1rem; padding: 0.75rem 1.5rem;">
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="row">
            <!-- Recipient Information -->
            <div class="col-md-6 mb-4">
                <div class="card" style="
                    background: white;
                    border-radius: 1.5rem;
                    box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
                    border: 1px solid rgba(18, 18, 104, 0.1);
                ">
                    <div class="card-header" style="
                        background: linear-gradient(135deg, rgba(18, 18, 104, 0.05) 0%, rgba(18, 18, 104, 0.02) 100%);
                        border-bottom: 1px solid rgba(18, 18, 104, 0.1);
                        border-radius: 1.5rem 1.5rem 0 0;
                        padding: 1.25rem;
                    ">
                        <h5 class="mb-0" style="color: rgb(18, 18, 104); font-weight: 600; font-family: 'Montserrat', sans-serif;">
                            <i class="fas fa-user me-2"></i>Recipient Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $transaction->recipient->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td><code>{{ $transaction->phone_number }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td>{{ $transaction->recipient->address }}</td>
                            </tr>
                            <tr>
                                <td><strong>Customer Name:</strong></td>
                                <td>{{ $transaction->recipient->customer_name ?: 'Same as recipient' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Meter Information -->
            <div class="col-md-6 mb-4">
                <div class="card" style="
                    background: white;
                    border-radius: 1.5rem;
                    box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
                    border: 1px solid rgba(18, 18, 104, 0.1);
                ">
                    <div class="card-header" style="
                        background: linear-gradient(135deg, rgba(18, 18, 104, 0.05) 0%, rgba(18, 18, 104, 0.02) 100%);
                        border-bottom: 1px solid rgba(18, 18, 104, 0.1);
                        border-radius: 1.5rem 1.5rem 0 0;
                        padding: 1.25rem;
                    ">
                        <h5 class="mb-0" style="color: rgb(18, 18, 104); font-weight: 600; font-family: 'Montserrat', sans-serif;">
                            <i class="fas fa-bolt me-2"></i>Meter Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Disco:</strong></td>
                                <td><span class="badge bg-info">{{ $transaction->recipient->disco }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Meter Number:</strong></td>
                                <td><code>{{ $transaction->recipient->meter_number }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>Meter Type:</strong></td>
                                <td>
                                    <span class="badge bg-{{ ($transaction->recipient->meter_type ?? 'prepaid') == 'prepaid' ? 'success' : 'warning' }}">
                                        {{ ucfirst($transaction->recipient->meter_type ?? 'prepaid') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Amount:</strong></td>
                                <td><strong class="text-success">₦{{ number_format($transaction->amount, 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Token Information -->
            @if($transaction->token)
            <div class="col-md-6 mb-4">
                <div class="card" style="
                    background: white;
                    border-radius: 1.5rem;
                    box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
                    border: 1px solid rgba(18, 18, 104, 0.1);
                ">
                    <div class="card-header" style="
                        background: linear-gradient(135deg, rgba(18, 18, 104, 0.05) 0%, rgba(18, 18, 104, 0.02) 100%);
                        border-bottom: 1px solid rgba(18, 18, 104, 0.1);
                        border-radius: 1.5rem 1.5rem 0 0;
                        padding: 1.25rem;
                    ">
                        <h5 class="mb-0" style="color: rgb(18, 18, 104); font-weight: 600; font-family: 'Montserrat', sans-serif;">
                            <i class="fas fa-key me-2"></i>Token Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center">
                            <h3 class="text-success mb-3">{{ $transaction->token }}</h3>
                            <button class="btn btn-outline-primary" onclick="copyToken('{{ $transaction->token }}')">
                                <i class="fas fa-copy"></i> Copy Token
                            </button>
                        </div>
                        @if($transaction->units)
                        <hr>
                        <div class="text-center">
                            <h5 class="text-info">{{ $transaction->units }} KWh</h5>
                            <small class="text-muted">Units Purchased</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Transaction References -->
            <div class="col-md-6 mb-4">
                <div class="card" style="
                    background: white;
                    border-radius: 1.5rem;
                    box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
                    border: 1px solid rgba(18, 18, 104, 0.1);
                ">
                    <div class="card-header" style="
                        background: linear-gradient(135deg, rgba(18, 18, 104, 0.05) 0%, rgba(18, 18, 104, 0.02) 100%);
                        border-bottom: 1px solid rgba(18, 18, 104, 0.1);
                        border-radius: 1.5rem 1.5rem 0 0;
                        padding: 1.25rem;
                    ">
                        <h5 class="mb-0" style="color: rgb(18, 18, 104); font-weight: 600; font-family: 'Montserrat', sans-serif;">
                            <i class="fas fa-hashtag me-2"></i>References
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <table class="table table-borderless">
                            @if($transaction->buypower_reference)
                            <tr>
                                <td><strong>BuyPower Reference:</strong></td>
                                <td><code>{{ $transaction->buypower_reference }}</code></td>
                            </tr>
                            @endif
                            @if($transaction->order_id)
                            <tr>
                                <td><strong>Order ID:</strong></td>
                                <td><code>{{ $transaction->order_id }}</code></td>
                            </tr>
                            @endif
                            @if($transaction->batchUpload)
                            <tr>
                                <td><strong>Batch:</strong></td>
                                <td>
                                    <a href="{{ route('bulk-token.show', $transaction->batchUpload->id) }}" class="text-decoration-none">
                                        {{ $transaction->batchUpload->batch_name }}
                                    </a>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Transaction ID:</strong></td>
                                <td><code>{{ $transaction->id }}</code></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Error Information -->
            @if($transaction->error_message)
            <div class="col-12 mb-4">
                <div class="card" style="
                    background: white;
                    border-radius: 1.5rem;
                    box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.1);
                    border: 2px solid rgba(220, 38, 38, 0.3);
                ">
                    <div class="card-header" style="
                        background: linear-gradient(135deg, rgba(220, 38, 38, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
                        border-bottom: 1px solid rgba(220, 38, 38, 0.2);
                        border-radius: 1.5rem 1.5rem 0 0;
                        padding: 1.25rem;
                    ">
                        <h5 class="mb-0" style="color: #dc2626; font-weight: 600; font-family: 'Montserrat', sans-serif;">
                            <i class="fas fa-exclamation-triangle me-2"></i>Error Details
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-danger">
                            <strong>Error:</strong> {{ $transaction->error_message }}
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Processing Timeline -->
            <div class="col-12">
                <div class="card" style="
                    background: white;
                    border-radius: 1.5rem;
                    box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
                    border: 1px solid rgba(18, 18, 104, 0.1);
                ">
                    <div class="card-header" style="
                        background: linear-gradient(135deg, rgba(18, 18, 104, 0.05) 0%, rgba(18, 18, 104, 0.02) 100%);
                        border-bottom: 1px solid rgba(18, 18, 104, 0.1);
                        border-radius: 1.5rem 1.5rem 0 0;
                        padding: 1.25rem;
                    ">
                        <h5 class="mb-0" style="color: rgb(18, 18, 104); font-weight: 600; font-family: 'Montserrat', sans-serif;">
                            <i class="fas fa-clock me-2"></i>Processing Timeline
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6>Transaction Created</h6>
                                    <small class="text-muted">{{ $transaction->created_at->format('M d, Y h:i A') }}</small>
                                </div>
                            </div>
                            @if($transaction->processed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $transaction->status == 'success' ? 'success' : 'danger' }}"></div>
                                <div class="timeline-content">
                                    <h6>Processing {{ $transaction->status == 'success' ? 'Completed' : 'Failed' }}</h6>
                                    <small class="text-muted">{{ $transaction->processed_at->format('M d, Y h:i A') }}</small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
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

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--winit-border);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .timeline-marker {
        position: absolute;
        left: -2rem;
        top: 0.25rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 2px var(--winit-border);
    }

    .timeline-content h6 {
        margin-bottom: 0.25rem;
        font-weight: 600;
    }
</style>
@endpush

@section('scripts')
<script>
    function copyToken(token) {
        navigator.clipboard.writeText(token).then(() => {
            // Show success feedback
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        }).catch(() => {
            alert('Failed to copy token to clipboard');
        });
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
                alert('Failed to download token details');
            });
    }
</script>
@endsection
