@extends('layouts.sidebar')

@section('title', 'Airtime Batch Details - ' . $batch->batch_name . ' - WinIt Prize Distribution')

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

    .batch-details-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 2px 8px rgba(18, 18, 104, 0.1);
        border: 1px solid var(--winit-border);
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .batch-details-card:hover {
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

    .batch-subtitle {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
        font-size: 0.875rem;
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
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(18, 18, 104, 0.15);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-accent) 100%);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        font-family: 'Montserrat', sans-serif;
        margin-bottom: 0.5rem;
    }

    .stat-number.total { color: var(--winit-primary); }
    .stat-number.successful { color: #10b981; }
    .stat-number.failed { color: #ef4444; }
    .stat-number.amount { color: #8b5cf6; }

    .stat-label {
        color: var(--winit-text-light);
        font-size: 0.875rem;
        font-weight: 500;
        font-family: 'Montserrat', sans-serif;
    }

    .progress-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(18, 18, 104, 0.1);
        border: 1px solid var(--winit-border);
        margin-bottom: 2rem;
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .progress-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        color: var(--winit-text);
        margin: 0;
    }

    .progress-container {
        background: var(--winit-border);
        border-radius: 0.75rem;
        height: 1rem;
        overflow: hidden;
        margin-bottom: 1rem;
        position: relative;
    }

    .progress-bar {
        height: 100%;
        border-radius: 0.75rem;
        transition: width 0.3s ease;
        position: relative;
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

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        font-family: 'Montserrat', sans-serif;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .status-badge.success {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: white;
    }

    .status-badge.failed {
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        color: white;
    }

    .status-badge.pending {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        color: white;
    }

    .status-badge.processing {
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        color: white;
    }

    .status-badge.uploaded {
        background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);
        color: white;
    }

    .status-badge.completed {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: white;
    }

    .recipients-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 2px 8px rgba(18, 18, 104, 0.1);
        border: 1px solid var(--winit-border);
        overflow: hidden;
    }

    .recipients-header {
        background: linear-gradient(135deg, var(--winit-primary) 0%, #1e1e6b 100%);
        color: white;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .recipients-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        margin: 0;
    }

    .table-container {
        padding: 0;
    }

    .table {
        margin: 0;
        font-family: 'Montserrat', sans-serif;
    }

    .table th {
        background: linear-gradient(135deg, var(--winit-primary) 0%, #1e1e6b 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 1rem;
        font-size: 0.875rem;
    }

    .table td {
        border-color: var(--winit-border);
        vertical-align: middle;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }

    .table tbody tr:hover {
        background: linear-gradient(135deg, rgba(23, 247, 182, 0.05) 0%, rgba(23, 247, 182, 0.02) 100%);
    }

    .recipient-name {
        font-weight: 600;
        color: var(--winit-text);
    }

    .recipient-phone {
        font-family: 'Courier New', monospace;
        background: rgba(18, 18, 104, 0.1);
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
    }

    .recipient-meter {
        font-family: 'Courier New', monospace;
        background: rgba(18, 18, 104, 0.1);
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
    }

    .disco-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 500;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .meter-type-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .meter-type-badge.prepaid {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .meter-type-badge.postpaid {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .amount-text {
        font-weight: 600;
        color: #059669;
    }

    .address-text {
        color: var(--winit-text-light);
        font-size: 0.75rem;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .token-btn {
        padding: 0.25rem 0.5rem;
        border: 1px solid var(--winit-border);
        border-radius: 0.25rem;
        background: white;
        color: var(--winit-text-light);
        font-size: 0.75rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .token-btn:hover {
        background: var(--winit-accent);
        border-color: var(--winit-accent);
        color: var(--winit-primary);
    }

    .units-text {
        font-weight: 600;
        color: var(--winit-primary);
    }

    .transaction-ref {
        font-family: 'Courier New', monospace;
        background: rgba(18, 18, 104, 0.1);
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
    }

    .processed-time {
        color: var(--winit-text-light);
        font-size: 0.75rem;
    }

    .error-text {
        color: #ef4444;
        font-size: 0.75rem;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
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

    .btn-outline-secondary {
        border: 2px solid var(--winit-border);
        color: var(--winit-text-light);
        background: transparent;
        border-radius: 0.75rem;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-outline-secondary:hover {
        background: var(--winit-border);
        color: var(--winit-text);
        transform: translateY(-1px);
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        border-radius: 0.75rem;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
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

    @media (max-width: 768px) {
        .batch-header {
            padding: 1rem;
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .batch-header h2 {
            font-size: 1.25rem;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .progress-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .table th,
        .table td {
            padding: 0.5rem;
            font-size: 0.75rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="batch-details-card">
        <div class="batch-header">
            <div>
                <h2>{{ $batch->batch_name }}</h2>
                <p class="batch-subtitle">{{ $batch->filename }} • {{ $batch->created_at->format('M d, Y h:i A') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('bulk-airtime.history') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to History
                </a>
                @if($batch->status == 'uploaded')
                    <button type="button" class="btn btn-success" onclick="processBatch({{ $batch->id }})">
                        <i class="fas fa-play me-1"></i>Start Processing
                    </button>
                @endif
            </div>
        </div>
        
        <div class="card-body p-4">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number total">{{ number_format($batch->total_recipients) }}</div>
                    <div class="stat-label">Total Recipients</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number successful">{{ number_format($batch->successful_transactions) }}</div>
                    <div class="stat-label">Successful</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number failed">{{ number_format($batch->failed_transactions) }}</div>
                    <div class="stat-label">Failed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number amount">₦{{ number_format($batch->total_amount, 2) }}</div>
                    <div class="stat-label">Total Amount</div>
                </div>
            </div>

            <!-- Progress Overview -->
            @if($batch->status !== 'uploaded')
            <div class="progress-card">
                <div class="progress-header">
                    <h5 class="progress-title">
                        <i class="fas fa-cog me-2"></i>Processing Progress (Synchronous Batch-by-Batch)
                    </h5>
                    <p class="small text-muted mb-3">
                        <i class="fas fa-info-circle"></i> Recipients are processed in batches synchronously. Each batch completes before the next starts.
                    </p>
                    <span class="status-badge {{ $batch->status }}">
                        {{ ucfirst($batch->status) }}
                    </span>
                </div>
                
                <div class="progress-container">
                    <div class="progress-bar {{ $batch->status }}" 
                         style="width: {{ $batch->completion_percentage }}%"
                         id="mainProgress">
                    </div>
                </div>
                
                <div class="progress-text">
                    <span>{{ $batch->completion_percentage }}% Complete</span>
                    <span>{{ $batch->processed_recipients }}/{{ $batch->total_recipients }} processed</span>
                    @if($batch->processed_recipients > 0)
                        <span>{{ $batch->success_rate }}% Success Rate</span>
                    @endif
                </div>
            </div>
            @endif

            <!-- Recipients List -->
            <div class="recipients-card">
                <div class="recipients-header">
                    <h5 class="recipients-title">Recipients</h5>
                    @if($batch->status == 'processing')
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="refreshStatus()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    @endif
                </div>
                
                <div class="table-container">
                    @if($batch->recipients && $batch->recipients->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Amount</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                        <th>Time</th>
                                        <th>Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batch->recipients as $recipient)
                                        <tr>
                                            <td>
                                                <div class="recipient-name">{{ $recipient->name }}</div>
                                            </td>
                                            <td>
                                                <div class="recipient-phone">{{ $recipient->phone_number }}</div>
                                            </td>
                                            <td>
                                                <div class="amount-text">₦{{ number_format($recipient->amount, 2) }}</div>
                                            </td>
                                            <td>
                                                <div class="recipient-email">{{ $recipient->email ?? 'N/A' }}</div>
                                            </td>
                                            <td>
                                                <span class="status-badge {{ $recipient->status }}">
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
                                                @if($recipient->transaction_reference)
                                                    <div class="transaction-ref">{{ $recipient->transaction_reference }}</div>
                                                @elseif($recipient->transaction && $recipient->transaction->buypower_reference)
                                                    <div class="transaction-ref">{{ $recipient->transaction->buypower_reference }}</div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($recipient->processed_at)
                                                    <div class="processed-time">{{ $recipient->processed_at->format('M d, H:i') }}</div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($recipient->error_message)
                                                    <div class="error-text" title="{{ $recipient->error_message }}">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        {{ Str::limit($recipient->error_message, 30) }}
                                                    </div>
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
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h3>No Recipients Found</h3>
                            <p>No recipients were found for this batch.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Token Modal -->
<div class="modal fade" id="tokenModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Airtime Top-Up</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <h3 class="text-success mb-3" id="tokenValue"></h3>
                <button class="btn btn-outline-primary" onclick="copyToken()">
                    <i class="fas fa-copy me-1"></i>Copy Token
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Utility functions
const utils = {
    showAlert: function(message, type = 'info', duration = 10000) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' || type === 'danger' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        // Create alert element with flash animation
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show notification-flash`;
        alertDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 400px;
            max-width: 600px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            animation: flashNotification 0.5s ease-in-out;
            font-size: 15px;
            font-weight: 500;
        `;
        alertDiv.innerHTML = `
            <div style="display: flex; align-items: flex-start;">
                <div style="flex: 1; padding-right: 10px;">
                    ${message}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Add flash animation CSS if not already added
        if (!document.getElementById('notification-flash-style')) {
            const style = document.createElement('style');
            style.id = 'notification-flash-style';
            style.textContent = `
                @keyframes flashNotification {
                    0% { transform: translateX(100%); opacity: 0; }
                    50% { transform: translateX(-10px); }
                    100% { transform: translateX(0); opacity: 1; }
                }
                .notification-flash {
                    animation: flashNotification 0.5s ease-in-out;
                }
                .notification-flash.pulse {
                    animation: flashNotification 0.5s ease-in-out, pulseFlash 2s ease-in-out infinite;
                }
                @keyframes pulseFlash {
                    0%, 100% { box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
                    50% { box-shadow: 0 8px 32px rgba(0,0,0,0.5), 0 0 20px rgba(23,247,182,0.4); }
                }
            `;
            document.head.appendChild(style);
        }
        
        // Add pulse effect for success/failure notifications
        if (type === 'success' || type === 'danger' || type === 'error') {
            alertDiv.classList.add('pulse');
        }
        
        // Insert at the top of body
        document.body.appendChild(alertDiv);
        
        // Scroll to notification
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
        // Auto-dismiss after specified duration (default 10 seconds)
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                alertDiv.style.opacity = '0';
                alertDiv.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 500);
            }
        }, duration);
    },

    makeRequest: async function(url, options = {}) {
        try {
            const { headers: optionHeaders = {}, credentials, ...restOptions } = options;
            const response = await fetch(url, {
                credentials: credentials ?? 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    ...optionHeaders
                },
                ...restOptions
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

// Global variables
let currentToken = '';

// Auto-refresh for processing batches
@if($batch->status == 'processing')
setInterval(function() {
    refreshStatus();
}, 5000);
@endif

// Refresh status functionality
function refreshStatus() {
    utils.makeRequest(`/bulk-airtime/status/{{ $batch->id }}`)
        .then(data => {
            if (data.success) {
                // Update progress bar
                const progressBar = document.getElementById('mainProgress');
                if (progressBar) {
                    progressBar.style.width = data.data.completion_percentage + '%';
                    
                    if (data.data.status === 'completed') {
                        progressBar.className = 'progress-bar completed';
                    }
                }
                
                // Show notification and reload if status changed to completed or failed
                if ((data.data.status === 'completed' || data.data.status === 'failed') && '{{ $batch->status }}' === 'processing') {
                    if (data.data.status === 'completed') {
                        const successRate = Math.round((data.data.successful_transactions / data.data.total_recipients) * 100);
                        let message = `✅ <strong>Batch Processing Completed Successfully!</strong><br>`;
                        message += `<strong>${data.data.successful_transactions}/${data.data.total_recipients}</strong> tokens sent successfully (${successRate}% success rate)<br>`;
                        
                        if (data.data.failed_transactions > 0) {
                            message += `<strong>${data.data.failed_transactions}</strong> failed transactions<br>`;
                            message += `<small>💡 Scroll down to see error messages for failed recipients.</small><br>`;
                        }
                        
                        message += `An email notification has been sent to your registered email address.`;
                        utils.showAlert('success', message, 10000);
                    } else {
                        let message = `❌ <strong>Batch Processing Failed!</strong><br>`;
                        message += `<strong>${data.data.processed_recipients || 0}/${data.data.total_recipients}</strong> recipients processed<br>`;
                        message += `<strong>${data.data.successful_transactions || 0}</strong> successful, <strong>${data.data.failed_transactions || 0}</strong> failed<br>`;
                        
                        if (data.data.error_message) {
                            message += `<br><strong>Error:</strong> ${data.data.error_message}`;
                        }
                        
                        message += `<br>💡 Scroll down to see detailed error messages for each recipient.`;
                        message += `<br>An email notification has been sent to your registered email address with error details.`;
                        utils.showAlert('danger', message, 10000);
                    }
                    setTimeout(() => location.reload(), 10000);
                }
            }
        })
        .catch(error => {
            console.error('Error refreshing status:', error);
        });
}

// Process batch functionality
function processBatch(batchId) {
    if (!confirm('Are you sure you want to start processing this batch?')) {
        return;
    }
    
    utils.makeRequest(`/bulk-airtime/process/${batchId}`, {
        method: 'POST'
    })
    .then(data => {
        utils.showAlert('Batch processing started synchronously! Processing recipients in batches...', 'success');
        setTimeout(() => location.reload(), 3000);
    })
    .catch(error => {
        utils.showAlert('Failed to start processing: ' + error.message, 'error');
    });
}

// Show token functionality
function showToken(token) {
    currentToken = token;
    document.getElementById('tokenValue').textContent = token;
    
    const modal = new bootstrap.Modal(document.getElementById('tokenModal'));
    modal.show();
}

// Copy token functionality
function copyToken() {
    navigator.clipboard.writeText(currentToken).then(() => {
        utils.showAlert('Token copied to clipboard!', 'success');
    }).catch(() => {
        utils.showAlert('Failed to copy token to clipboard', 'error');
    });
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