@extends('layouts.sidebar')

@section('title', 'Notifications - WinIt')

@section('styles')
<style>
    :root {
        --winit-primary: rgb(18, 18, 104);
        --winit-primary-dark: rgb(12, 12, 80);
        --winit-success: #10b981;
        --winit-warning: #f59e0b;
        --winit-danger: #ef4444;
        --winit-accent: #3b82f6;
    }

    .notification-container {
        background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-primary-dark) 100%);
        border-radius: 1rem;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
    }

    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .notification-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
    }

    .notification-filters {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .filter-btn {
        padding: 0.5rem 1rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: transparent;
        color: white;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
    }

    .notification-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-left: 4px solid var(--winit-primary);
        transition: all 0.3s ease;
    }

    .notification-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }

    .notification-card.success {
        border-left-color: var(--winit-success);
    }

    .notification-card.warning {
        border-left-color: var(--winit-warning);
    }

    .notification-card.error {
        border-left-color: var(--winit-danger);
    }

    .notification-card.info {
        border-left-color: var(--winit-accent);
    }

    .notification-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .notification-type {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .notification-type.success {
        background: #d1fae5;
        color: #065f46;
    }

    .notification-type.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .notification-type.error {
        background: #fecaca;
        color: #991b1b;
    }

    .notification-type.info {
        background: #dbeafe;
        color: #1e40af;
    }

    .notification-time {
        color: #6b7280;
        font-size: 0.875rem;
    }

    .notification-content {
        margin-bottom: 1rem;
    }

    .notification-title-text {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .notification-message {
        color: #4b5563;
        line-height: 1.6;
    }

    .notification-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .action-btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .action-btn.primary {
        background: var(--winit-primary);
        color: white;
    }

    .action-btn.secondary {
        background: #f3f4f6;
        color: #374151;
    }

    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .notification-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--winit-primary);
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #6b7280;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #d1d5db;
    }

    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid var(--winit-primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .notification-badge {
        position: relative;
    }

    .notification-badge::after {
        content: '';
        position: absolute;
        top: -5px;
        right: -5px;
        width: 12px;
        height: 12px;
        background: var(--winit-danger);
        border-radius: 50%;
        border: 2px solid white;
    }

    .notification-badge.has-count::after {
        content: attr(data-count);
        width: auto;
        height: auto;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
        background: var(--winit-danger);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Notification Header -->
    <div class="notification-container">
        <div class="notification-header">
            <h1 class="notification-title">
                <i class="fas fa-bell me-2"></i>
                BuyPower Notifications
            </h1>
            <div class="notification-badge" id="notificationBadge" data-count="0">
                <i class="fas fa-bell fa-lg"></i>
            </div>
        </div>

        <!-- Notification Stats -->
        <div class="notification-stats">
            <div class="stat-card">
                <div class="stat-number" id="totalNotifications">0</div>
                <div class="stat-label">Total Notifications</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="unreadNotifications">0</div>
                <div class="stat-label">Unread</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="successNotifications">0</div>
                <div class="stat-label">Successful</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="failedNotifications">0</div>
                <div class="stat-label">Failed</div>
            </div>
        </div>

        <!-- Notification Filters -->
        <div class="notification-filters">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="success">Success</button>
            <button class="filter-btn" data-filter="warning">Warning</button>
            <button class="filter-btn" data-filter="error">Error</button>
            <button class="filter-btn" data-filter="info">Info</button>
        </div>
    </div>

    <!-- Notifications List -->
    <div id="notificationsList">
        <div class="text-center py-5">
            <div class="loading-spinner"></div>
            <p class="mt-2">Loading notifications...</p>
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="empty-state d-none">
        <i class="fas fa-bell-slash"></i>
        <h3>No notifications found</h3>
        <p>You don't have any notifications yet. Notifications will appear here when transactions are processed.</p>
    </div>
</div>

<!-- Notification Detail Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalTitle">
                    <i class="fas fa-bell me-2"></i>
                    Notification Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="notificationModalBody">
                <!-- Notification details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="markAsReadBtn">Mark as Read</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
class NotificationManager {
    constructor() {
        this.notifications = [];
        this.currentFilter = 'all';
        this.currentNotificationId = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadNotifications();
        this.startPolling();
    }

    setupEventListeners() {
        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.setFilter(e.target.dataset.filter);
            });
        });

        // Mark as read button
        document.getElementById('markAsReadBtn').addEventListener('click', () => {
            this.markAsRead(this.currentNotificationId);
        });

        // Real-time updates
        this.setupRealTimeUpdates();
    }

    setFilter(filter) {
        this.currentFilter = filter;
        
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-filter="${filter}"]`).classList.add('active');

        this.renderNotifications();
    }

    async loadNotifications() {
        try {
            const response = await fetch('/api/notifications', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.notifications = data.notifications || [];
                this.updateStats();
                this.renderNotifications();
            } else {
                console.error('Failed to load notifications');
                this.showError('Failed to load notifications');
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            this.showError('Error loading notifications');
        }
    }

    updateStats() {
        const total = this.notifications.length;
        const unread = this.notifications.filter(n => !n.read_at).length;
        const success = this.notifications.filter(n => n.type === 'success').length;
        const failed = this.notifications.filter(n => n.type === 'error').length;

        document.getElementById('totalNotifications').textContent = total;
        document.getElementById('unreadNotifications').textContent = unread;
        document.getElementById('successNotifications').textContent = success;
        document.getElementById('failedNotifications').textContent = failed;

        // Update badge
        const badge = document.getElementById('notificationBadge');
        if (unread > 0) {
            badge.classList.add('has-count');
            badge.setAttribute('data-count', unread);
        } else {
            badge.classList.remove('has-count');
        }
    }

    renderNotifications() {
        const container = document.getElementById('notificationsList');
        const emptyState = document.getElementById('emptyState');

        let filteredNotifications = this.notifications;
        if (this.currentFilter !== 'all') {
            filteredNotifications = this.notifications.filter(n => n.type === this.currentFilter);
        }

        if (filteredNotifications.length === 0) {
            container.innerHTML = '';
            emptyState.classList.remove('d-none');
            return;
        }

        emptyState.classList.add('d-none');

        const notificationsHtml = filteredNotifications.map(notification => {
            return this.renderNotificationCard(notification);
        }).join('');

        container.innerHTML = notificationsHtml;

        // Add click listeners to notification cards
        container.querySelectorAll('.notification-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (!e.target.closest('.action-btn')) {
                    this.showNotificationDetails(card.dataset.notificationId);
                }
            });
        });
    }

    renderNotificationCard(notification) {
        const timeAgo = this.getTimeAgo(notification.created_at);
        const isUnread = !notification.read_at;

        return `
            <div class="notification-card ${notification.type} ${isUnread ? 'unread' : ''}" data-notification-id="${notification.id}">
                <div class="notification-meta">
                    <div class="notification-type ${notification.type}">
                        <i class="fas ${this.getNotificationIcon(notification.type)}"></i>
                        ${notification.type.toUpperCase()}
                    </div>
                    <div class="notification-time">${timeAgo}</div>
                </div>
                <div class="notification-content">
                    <div class="notification-title-text">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                </div>
                <div class="notification-actions">
                    ${this.renderNotificationActions(notification)}
                </div>
            </div>
        `;
    }

    renderNotificationActions(notification) {
        let actions = '';

        if (!notification.read_at) {
            actions += `<button class="action-btn secondary" onclick="notificationManager.markAsRead(${notification.id})">
                <i class="fas fa-check me-1"></i>Mark as Read
            </button>`;
        }

        if (notification.type === 'success' && notification.transaction_id) {
            actions += `<button class="action-btn primary" onclick="notificationManager.viewTransaction(${notification.transaction_id})">
                <i class="fas fa-eye me-1"></i>View Transaction
            </button>`;
        }

        if (notification.type === 'error' && notification.transaction_id) {
            actions += `<button class="action-btn secondary" onclick="notificationManager.retryTransaction(${notification.transaction_id})">
                <i class="fas fa-redo me-1"></i>Retry
            </button>`;
        }

        return actions;
    }

    getNotificationIcon(type) {
        const icons = {
            'success': 'fa-check-circle',
            'warning': 'fa-exclamation-triangle',
            'error': 'fa-times-circle',
            'info': 'fa-info-circle'
        };
        return icons[type] || 'fa-bell';
    }

    getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        return `${Math.floor(diffInSeconds / 86400)}d ago`;
    }

    async showNotificationDetails(notificationId) {
        const notification = this.notifications.find(n => n.id == notificationId);
        if (!notification) return;

        this.currentNotificationId = notificationId;

        const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
        document.getElementById('notificationModalTitle').innerHTML = `
            <i class="fas ${this.getNotificationIcon(notification.type)} me-2"></i>
            ${notification.title}
        `;

        document.getElementById('notificationModalBody').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Notification Details</h6>
                    <p><strong>Type:</strong> ${notification.type.toUpperCase()}</p>
                    <p><strong>Created:</strong> ${new Date(notification.created_at).toLocaleString()}</p>
                    <p><strong>Status:</strong> ${notification.read_at ? 'Read' : 'Unread'}</p>
                </div>
                <div class="col-md-6">
                    <h6>Message</h6>
                    <p>${notification.message}</p>
                </div>
            </div>
            ${notification.transaction_id ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Transaction Details</h6>
                        <p><strong>Transaction ID:</strong> ${notification.transaction_id}</p>
                        <p><strong>Reference:</strong> ${notification.transaction_reference || 'N/A'}</p>
                        <p><strong>Amount:</strong> ₦${notification.amount || 'N/A'}</p>
                        <p><strong>Phone:</strong> ${notification.phone_number || 'N/A'}</p>
                    </div>
                </div>
            ` : ''}
        `;

        modal.show();
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/api/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const notification = this.notifications.find(n => n.id == notificationId);
                if (notification) {
                    notification.read_at = new Date().toISOString();
                }
                this.updateStats();
                this.renderNotifications();
                
                // Close modal if open
                const modal = bootstrap.Modal.getInstance(document.getElementById('notificationModal'));
                if (modal) modal.hide();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async viewTransaction(transactionId) {
        window.location.href = `/bulk-token/transaction/${transactionId}`;
    }

    async retryTransaction(transactionId) {
        try {
            const response = await fetch(`/api/transactions/${transactionId}/retry`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                this.showSuccess('Transaction retry initiated');
                this.loadNotifications();
            } else {
                this.showError('Failed to retry transaction');
            }
        } catch (error) {
            console.error('Error retrying transaction:', error);
            this.showError('Error retrying transaction');
        }
    }

    startPolling() {
        // Poll for new notifications every 30 seconds
        setInterval(() => {
            this.loadNotifications();
        }, 30000);
    }

    setupRealTimeUpdates() {
        // Listen for real-time notifications via WebSocket or Server-Sent Events
        if (window.Echo) {
            window.Echo.channel('notifications')
                .listen('NotificationCreated', (e) => {
                    this.loadNotifications();
                });
        }
    }

    showSuccess(message) {
        this.showAlert('success', message);
    }

    showError(message) {
        this.showAlert('error', message);
    }

    showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.insertBefore(alertDiv, document.body.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

// Initialize notification manager
const notificationManager = new NotificationManager();
</script>
@endsection
