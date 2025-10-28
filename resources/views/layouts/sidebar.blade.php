<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WinIt - Prize Distribution System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* WinIt Brand Colors - Updated with deep blue color */
            --winit-primary: #121268;
            --winit-primary-light: #1a1a7a;
            --winit-primary-dark: #0e0e4a;
            --winit-secondary: #2a2a8a;
            --winit-accent: #06b6d4;
            --winit-success: #059669;
            --winit-warning: #d97706;
            --winit-danger: #dc2626;
            --winit-dark: #121268;
            --winit-gray: #6b7280;
            --winit-light: #f9fafb;
            --winit-border: #d1d5db;
            --winit-white: #ffffff;
            --winit-navy: #121268;
            --winit-navy-light: #1a1a7a;
            --winit-blue-50: #f0f0ff;
            --winit-blue-100: #e0e0ff;
            --winit-blue-200: #c0c0ff;
            --winit-blue-500: #4a4a9a;
            --winit-blue-600: #3a3a8a;
            --winit-blue-700: #2a2a7a;
            --winit-blue-800: #1a1a6a;
            --winit-blue-900: #0e0e4a;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--winit-light) 0%, #f1f5f9 100%);
            margin: 0;
            padding: 0;
            color: var(--winit-dark);
        }

        .sidebar {
            background: linear-gradient(180deg, var(--winit-primary) 0%, var(--winit-primary-dark) 100%);
            min-height: 100vh;
            box-shadow: 4px 0 20px rgba(18, 18, 104, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            z-index: 1000;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .sidebar-brand {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-brand .logo {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.8rem;
            color: white;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-brand h4 {
            color: white;
            margin: 0;
            font-weight: 600;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin: 0.25rem 0;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0.75rem;
            margin: 0 0.75rem;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(4px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 600;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        .nav-section-header {
            color: rgba(255, 255, 255, 0.6);
            padding: 0.75rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .nav-section-header i {
            font-size: 0.875rem;
        }

        .nav-section-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 1rem 1.5rem;
        }

        .logout-btn:hover {
            background: rgba(220, 38, 38, 0.1) !important;
            color: #fca5a5 !important;
        }

        .nav-link .badge {
            font-size: 0.65rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
        }

        .nav-link span {
            flex: 1;
        }

        .system-info {
            font-family: 'Montserrat', sans-serif;
        }

        .system-info .row {
            border-bottom: 1px solid #f0f0f0;
            padding: 0.5rem 0;
        }

        .system-info .row:last-child {
            border-bottom: none;
        }

        .modal-content {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-primary-dark) 100%);
            color: white;
            border-radius: 1rem 1rem 0 0;
            border: none;
        }

        .modal-title {
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
        }

        .btn-close {
            filter: invert(1);
        }

        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            padding: 2rem;
        }

        .content-header {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.1);
            border: 1px solid var(--winit-border);
            backdrop-filter: blur(10px);
        }

        .content-header h1 {
            color: var(--winit-dark);
            margin: 0;
            font-weight: 700;
            font-size: 2rem;
            font-family: 'Montserrat', sans-serif;
        }

        .content-header p {
            color: var(--winit-gray);
            margin: 0.5rem 0 0 0;
            font-family: 'Montserrat', sans-serif;
        }

        .card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.1);
            border: 1px solid var(--winit-border);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            backdrop-filter: blur(10px);
        }

        .card:hover {
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.15);
            transform: translateY(-2px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-primary-dark) 100%);
            color: white;
            border-radius: 1.5rem 1.5rem 0 0 !important;
            padding: 1.5rem 2rem;
            border: none;
            font-weight: 600;
        }

        .card-body {
            padding: 2rem;
        }

        .btn {
            border-radius: 0.75rem;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(18, 18, 104, 0.3);
            border: none;
            font-weight: 600;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(18, 18, 104, 0.4);
            color: white;
            background: linear-gradient(135deg, var(--winit-primary-dark) 0%, var(--winit-primary) 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--winit-success) 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            border: none;
            font-weight: 600;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .api-status-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--winit-border);
        }

        .api-status-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .api-status-header i {
            color: var(--secondary-blue);
        }

        .mock-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: auto;
        }

        .production-badge {
            background: linear-gradient(135deg, var(--accent-green) 0%, #059669 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: auto;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            animation: pulse 2s infinite;
        }

        .status-dot.success {
            background-color: var(--accent-green);
        }

        .status-dot.error {
            background-color: #ef4444;
        }

        .status-dot.warning {
            background-color: #f59e0b;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .form-control {
            border: 2px solid var(--border-light);
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--bg-light);
        }

        .form-control:focus {
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: white;
        }

        .alert {
            border-radius: 1rem;
            border: none;
            padding: 1.25rem;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid var(--accent-green);
        }

        .alert-danger {
            background: #fecaca;
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid var(--secondary-blue);
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
        <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="logo">
                <i class="fas fa-trophy"></i>
                </div>
            <h4>WinIt</h4>
            </div>

        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <!-- Main Dashboard -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Bulk Token Operations Section -->
                <li class="nav-item mt-3">
                    <div class="nav-section-header">
                        <i class="fas fa-bolt"></i>
                        <span>Token Operations</span>
                    </div>
                </li>

                @if(auth()->user()->canUploadCsv())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('bulk-token.index') ? 'active' : '' }}" href="{{ route('bulk-token.index') }}">
                                <i class="fas fa-upload"></i>
                        <span>Upload CSV</span>
                    </a>
                </li>
                @endif

                @if(auth()->user()->canViewTransactions())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('bulk-token.history') ? 'active' : '' }}" href="{{ route('bulk-token.history') }}">
                                <i class="fas fa-history"></i>
                        <span>Batch History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('bulk-token.transactions') ? 'active' : '' }}" href="{{ route('bulk-token.transactions') }}">
                        <i class="fas fa-list-alt"></i>
                        <span>All Transactions</span>
                    </a>
                </li>
                @endif

                <!-- Administration Section -->
                <li class="nav-item mt-3">
                    <div class="nav-section-header">
                        <i class="fas fa-cogs"></i>
                        <span>Administration</span>
                            </div>
                </li>

                @if(auth()->user()->canManageUsers())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>
                </li>
                            @endif

        @if(auth()->user()->hasPermission('view-activity-logs'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}" href="{{ route('activity-logs.index') }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Activity Logs</span>
            </a>
        </li>
        @endif

        @if(auth()->user()->hasPermission('view-notifications'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                <span class="badge bg-danger ms-auto" id="notificationCount" style="display: none;">0</span>
            </a>
        </li>
        @endif

                <!-- System Information -->
                @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('Super Admin'))
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="showSystemInfo()">
                        <i class="fas fa-info-circle"></i>
                        <span>System Info</span>
                    </a>
                </li>
                @endif

                <!-- Profile Section -->
                @auth
                <li class="nav-item mt-3">
                    <div class="nav-section-divider"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user-circle"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                
                @if(auth()->user()->must_change_password)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('password.change') ? 'active' : '' }}" href="{{ route('password.change') }}">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                        <span class="badge bg-warning ms-auto">Required</span>
                    </a>
                </li>
                @endif

                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="nav-link logout-btn" style="background: none; border: none; width: 100%; text-align: left;">
                        <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                    </button>
                    </form>
                </li>
                @endauth
            </ul>
        </nav>
                </div>
                
    <!-- Main Content -->
    <div class="main-content">
                @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // API Status Check
        async function checkApiStatus() {
            try {
                const response = await fetch('/api-status-public', {
                    signal: controller.signal
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                // Update status indicators if they exist
                const statusDot = document.getElementById('statusDot');
                const statusText = document.getElementById('statusText');
                const statusDetails = document.getElementById('statusDetails');
                const statusMessage = document.getElementById('statusMessage');
                
                if (statusDot && statusText) {
                    if (data.success) {
                        statusDot.className = 'status-dot success';
                        statusText.textContent = 'API Connected';
                } else {
                        statusDot.className = 'status-dot error';
                        statusText.textContent = 'API Error';
                    }
                }
                
                if (statusDetails && statusMessage) {
                    statusDetails.style.display = 'block';
                    statusMessage.textContent = data.message || 'No additional information';
                }
                
            } catch (error) {
                console.error('API status check failed:', error);
                
                const statusDot = document.getElementById('statusDot');
                const statusText = document.getElementById('statusText');
                
                if (statusDot && statusText) {
                    statusDot.className = 'status-dot error';
                    statusText.textContent = 'Connection Failed';
                }
            }
        }

        // Optimized API status checking
        let controller = new AbortController();
        let apiStatusInterval;
        
        function startApiStatusCheck() {
            if (apiStatusInterval) clearInterval(apiStatusInterval);
            // Reduced polling frequency from 30s to 60s
            apiStatusInterval = setInterval(() => {
                controller = new AbortController();
                checkApiStatus();
            }, 60000);
        }
        
        function stopApiStatusCheck() {
            if (apiStatusInterval) {
                clearInterval(apiStatusInterval);
                apiStatusInterval = null;
            }
        }
        
        // Only poll when page is visible to save resources
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopApiStatusCheck();
            } else {
                startApiStatusCheck();
                controller = new AbortController();
                checkApiStatus(); // Check immediately when page becomes visible
            }
        });
        
        // Initial check and start polling
        checkApiStatus();
        startApiStatusCheck();

        // System Info Modal
        function showSystemInfo() {
            const systemInfo = {
                'Application': 'WinIt BuyPower System',
                'Version': '1.0.0',
                'Environment': '{{ app()->environment() }}',
                'PHP Version': '{{ PHP_VERSION }}',
                'Laravel Version': '{{ app()->version() }}',
                'Database': '{{ config("database.default") }}',
                'API Base URL': '{{ config("buypower.api_url") }}',
                'Cache Driver': '{{ config("cache.default") }}',
                'Queue Driver': '{{ config("queue.default") }}',
                'Session Driver': '{{ config("session.driver") }}',
                'Mail Driver': '{{ config("mail.default") }}',
                'Timezone': '{{ config("app.timezone") }}',
                'Locale': '{{ config("app.locale") }}',
                'Debug Mode': '{{ config("app.debug") ? "Enabled" : "Disabled" }}',
                'Maintenance Mode': '{{ app()->isDownForMaintenance() ? "Enabled" : "Disabled" }}',
                'User Count': '{{ \App\Models\User::count() }}',
                'Total Batches': '{{ \App\Models\BatchUpload::count() }}',
                'Total Transactions': '{{ \App\Models\Transaction::count() }}',
                'Server Time': new Date().toLocaleString()
            };

            let infoHtml = '<div class="system-info">';
            for (const [key, value] of Object.entries(systemInfo)) {
                infoHtml += `
                    <div class="row mb-2">
                        <div class="col-4"><strong>${key}:</strong></div>
                        <div class="col-8">${value}</div>
                    </div>
                `;
            }
            infoHtml += '</div>';

            // Create and show modal
            const modalHtml = `
                <div class="modal fade" id="systemInfoModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    System Information
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                ${infoHtml}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if any
            const existingModal = document.getElementById('systemInfoModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('systemInfoModal'));
            modal.show();
        }

        // Notification polling
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

        // Poll for notifications every 30 seconds
        setInterval(updateNotificationBadge, 30000);
        
        // Initial load
        updateNotificationBadge();
    </script>
    
    <script>
        // Set CSRF token for AJAX requests
        window.csrfToken = '{{ csrf_token() }}';
    </script>
    
    @stack('scripts')
</body>
</html>
