@extends('layouts.sidebar')

@section('title', 'Notifications - WinIt Prize Distribution')

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

    .notification-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 2px 8px rgba(18, 18, 104, 0.1);
        border: 1px solid var(--winit-border);
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .notification-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(18, 18, 104, 0.15);
        border-color: var(--winit-accent);
    }

    .notification-header {
        background: linear-gradient(135deg, var(--winit-primary) 0%, #1e1e6b 100%);
        color: white;
        border-radius: 1rem 1rem 0 0;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: between;
        align-items: center;
    }

    .notification-header h2 {
        margin: 0;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 1.5rem;
    }

    .notification-filters {
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

    .notification-item {
        background: white;
        border: 1px solid var(--winit-border);
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        position: relative;
    }

    .notification-item:hover {
        border-color: var(--winit-accent);
        background: linear-gradient(135deg, rgba(23, 247, 182, 0.05) 0%, rgba(23, 247, 182, 0.02) 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(23, 247, 182, 0.1);
    }

    .notification-item.unread {
        border-left: 4px solid var(--winit-accent);
        background: linear-gradient(135deg, rgba(23, 247, 182, 0.08) 0%, rgba(23, 247, 182, 0.03) 100%);
    }

    .notification-icon {
        width: 48px;
        height: 48px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .notification-icon.success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .notification-icon.error {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .notification-icon.warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .notification-icon.info {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .notification-content {
        flex: 1;
    }

    .notification-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--winit-text);
        margin: 0 0 0.5rem 0;
    }

    .notification-message {
        color: var(--winit-text-light);
        font-size: 0.95rem;
        line-height: 1.5;
        margin: 0 0 1rem 0;
    }

    .notification-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.875rem;
        color: var(--winit-text-light);
    }

    .notification-time {
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
    }

    .notification-actions {
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

    .action-btn.retry {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-color: #f59e0b;
    }

    .action-btn.retry:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        border-color: #d97706;
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

    .notification-badge {
        position: absolute;
        top: -0.5rem;
        right: -0.5rem;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
        font-family: 'Montserrat', sans-serif;
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

    .stat-number.success { color: #10b981; }
    .stat-number.error { color: #ef4444; }
    .stat-number.warning { color: #f59e0b; }
    .stat-number.info { color: #3b82f6; }

    .stat-label {
        color: var(--winit-text-light);
        font-size: 0.875rem;
        font-weight: 500;
        font-family: 'Montserrat', sans-serif;
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
        .notification-item {
            padding: 1rem;
        }
        
        .notification-header {
            padding: 1rem;
        }
        
        .notification-header h2 {
            font-size: 1.25rem;
        }
        
        .filter-buttons {
            justify-content: center;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="notification-card">
        <div class="notification-header">
            <h2>
                <i class="fas fa-bell me-2"></i>Notifications
            </h2>
        </div>
        
        <div class="card-body p-4">
            @if(isset($error))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ $error }}
                </div>
            @endif

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number success">{{ $notifications->where('type', 'success')->count() }}</div>
                    <div class="stat-label">Successful</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number error">{{ $notifications->where('type', 'error')->count() }}</div>
                    <div class="stat-label">Failed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number warning">{{ $notifications->where('type', 'warning')->count() }}</div>
                    <div class="stat-label">Processing</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number info">{{ $notifications->where('type', 'info')->count() }}</div>
                    <div class="stat-label">System</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="notification-filters">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0" style="font-family: 'Montserrat', sans-serif; font-weight: 600;">Filter Notifications</h5>
                    <button class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                        <i class="fas fa-check-double me-1"></i>Mark All Read
                    </button>
                </div>
                <div class="filter-buttons">
                    <a href="#" class="filter-btn active" data-filter="all">
                        <i class="fas fa-list"></i>All ({{ $notifications->count() }})
                    </a>
                    <a href="#" class="filter-btn" data-filter="success">
                        <i class="fas fa-check-circle"></i>Success ({{ $notifications->where('type', 'success')->count() }})
                    </a>
                    <a href="#" class="filter-btn" data-filter="error">
                        <i class="fas fa-times-circle"></i>Failed ({{ $notifications->where('type', 'error')->count() }})
                    </a>
                    <a href="#" class="filter-btn" data-filter="warning">
                        <i class="fas fa-clock"></i>Processing ({{ $notifications->where('type', 'warning')->count() }})
                    </a>
                    <a href="#" class="filter-btn" data-filter="info">
                        <i class="fas fa-info-circle"></i>System ({{ $notifications->where('type', 'info')->count() }})
                    </a>
                </div>
            </div>

            <!-- Notifications List -->
            <div id="notificationsList">
                @if($notifications->count() > 0)
                    @foreach($notifications as $notification)
                    <div class="notification-item {{ !$notification['read_at'] ? 'unread' : '' }}" 
                         data-type="{{ $notification['type'] }}" 
                         data-id="{{ $notification['id'] }}">
                        
                        @if(!$notification['read_at'])
                            <div class="notification-badge">!</div>
                        @endif
                        
                        <div class="d-flex align-items-start">
                            <div class="notification-icon {{ $notification['type'] }}">
                                @switch($notification['type'])
                                    @case('success')
                                        <i class="fas fa-check"></i>
                                        @break
                                    @case('error')
                                        <i class="fas fa-times"></i>
                                        @break
                                    @case('warning')
                                        <i class="fas fa-clock"></i>
                                        @break
                                    @default
                                        <i class="fas fa-info"></i>
                                @endswitch
                            </div>
                            
                            <div class="notification-content">
                                <h6 class="notification-title">{{ $notification['title'] }}</h6>
                                <p class="notification-message">{{ $notification['message'] }}</p>
                                
                                <div class="notification-meta">
                                    <span class="notification-time">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                                    </span>
                                    
                                    <div class="notification-actions">
                                        @if(isset($notification['transaction_id']) && $notification['type'] === 'error')
                                            <button class="action-btn retry" onclick="retryTransaction('{{ $notification['transaction_id'] }}')">
                                                <i class="fas fa-redo"></i>Retry
                                            </button>
                                        @endif
                                        
                                        <button class="action-btn" onclick="markAsRead('{{ $notification['id'] }}')">
                                            <i class="fas fa-eye"></i>Mark Read
                                        </button>
                                        
                                        @if(isset($notification['transaction_id']))
                                            <a href="{{ route('bulk-token.transaction.show', $notification['transaction_id']) }}" class="action-btn">
                                                <i class="fas fa-external-link-alt"></i>View
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <h3>No Notifications</h3>
                        <p>You don't have any notifications yet. When you upload CSV files or process transactions, notifications will appear here.</p>
                    </div>
                @endif
            </div>

            <!-- Load More Button -->
            @if($notifications->count() >= 50)
                <div class="text-center mt-4">
                    <button class="btn btn-outline-primary" onclick="loadMoreNotifications()">
                        <i class="fas fa-plus me-1"></i>Load More Notifications
                    </button>
                </div>
            @endif
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
    const notificationItems = document.querySelectorAll('.notification-item');

    filterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter notifications
            const filter = this.getAttribute('data-filter');
            
            notificationItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-type') === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});

// Mark notification as read
function markAsRead(notificationId) {
    utils.makeRequest(`/api/notifications/${notificationId}/mark-read`, {
        method: 'POST'
    }).then(result => {
        utils.showAlert(result.message, 'success');
        
        // Update UI
        const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
        if (notificationItem) {
            notificationItem.classList.remove('unread');
            const badge = notificationItem.querySelector('.notification-badge');
            if (badge) {
                badge.remove();
            }
        }
        
        // Update notification count in sidebar
        updateNotificationBadge();
    }).catch(error => {
        utils.showAlert(error.message, 'error');
    });
}

// Mark all notifications as read
function markAllAsRead() {
    if (confirm('Are you sure you want to mark all notifications as read?')) {
        utils.makeRequest('/api/notifications/mark-all-read', {
            method: 'POST'
        }).then(result => {
            utils.showAlert(result.message, 'success');
            
            // Update UI
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                const badge = item.querySelector('.notification-badge');
                if (badge) {
                    badge.remove();
                }
            });
            
            // Update notification count in sidebar
            updateNotificationBadge();
        }).catch(error => {
            utils.showAlert(error.message, 'error');
        });
    }
}

// Retry failed transaction
function retryTransaction(transactionId) {
    if (confirm('Are you sure you want to retry this transaction?')) {
        utils.makeRequest(`/api/notifications/retry-transaction/${transactionId}`, {
            method: 'POST'
        }).then(result => {
            utils.showAlert(result.message, 'success');
            
            // Update notification status
            const notificationItem = document.querySelector(`[data-id="transaction_${transactionId}"]`);
            if (notificationItem) {
                const icon = notificationItem.querySelector('.notification-icon');
                const title = notificationItem.querySelector('.notification-title');
                
                if (icon && title) {
                    icon.className = 'notification-icon warning';
                    icon.innerHTML = '<i class="fas fa-clock"></i>';
                    title.textContent = 'Token Being Processed';
                }
            }
        }).catch(error => {
            utils.showAlert(error.message, 'error');
        });
    }
}

// Load more notifications
function loadMoreNotifications() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
    button.disabled = true;
    
    // Simulate loading more notifications
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        utils.showAlert('No more notifications to load', 'info');
    }, 1000);
}

// Update notification badge in sidebar
function updateNotificationBadge() {
    fetch('/api/notifications')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.unread > 0) {
                const badge = document.getElementById('notificationCount');
                if (badge) {
                    badge.textContent = data.unread;
                    badge.style.display = 'inline-block';
                }
            } else {
                const badge = document.getElementById('notificationCount');
                if (badge) {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.log('Failed to fetch notifications:', error);
        });
}

// Auto-refresh notifications every 30 seconds
setInterval(() => {
    updateNotificationBadge();
}, 30000);
</script>
@endpush