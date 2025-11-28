<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WinIt Prize Distribution - Prize Distribution System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat+Alternates:wght@300;400;500;600;700;800&family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* WinIt Brand Colors - Exact colors from https://win-it-web.vercel.app/dashboard */
            --winit-primary: #4313F7;
            --winit-primary-light: #5a2bfa;
            --winit-primary-dark: #3510c9;
            --winit-secondary: #23A3D6;
            --winit-accent: #17F7B6;
            --winit-accent-alt: #13F7B5;
            --winit-pink: #F71355;
            --winit-lime: #C7F713;
            --winit-success: #10B981;
            --winit-warning: #F59E0B;
            --winit-danger: #EF4444;
            --winit-dark: #010133;
            --winit-dark-alt: #01011B;
            --winit-gray: #4C4D61;
            --winit-gray-light: #9899AD;
            --winit-light: #F7F7F9;
            --winit-border: #D0D5DD;
            --winit-white: #ffffff;
        }

        body {
            font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--winit-light) 0%, #FAFAFB 100%);
            margin: 0;
            padding: 0;
            color: var(--winit-dark);
        }

        .sidebar {
            background: linear-gradient(135deg, #010133 0%, #01011b 100%);
            height: 100vh;
            box-shadow: 4px 0 20px rgba(1, 1, 51, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            z-index: 1000;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-brand {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            flex-shrink: 0;
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
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
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
            background: linear-gradient(135deg, rgba(23, 247, 182, 0.2) 0%, rgba(23, 247, 182, 0.1) 100%);
            color: var(--winit-accent);
            transform: translateX(4px);
            box-shadow: 0 2px 8px rgba(23, 247, 182, 0.2);
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(23, 247, 182, 0.25) 0%, rgba(23, 247, 182, 0.15) 100%);
            color: var(--winit-accent);
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(23, 247, 182, 0.3);
            border-left: 4px solid var(--winit-accent);
            padding-left: calc(1.5rem - 4px);
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

        .logout-btn {
            border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
            margin-top: 1rem !important;
            padding-top: 1rem !important;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.15) !important;
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

        .user-profile-section {
            padding: 1.5rem;
            background: rgba(0, 0, 0, 0.2);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            flex-shrink: 0;
        }

        .user-profile-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
            margin-bottom: 1rem;
        }

        .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 600;
            border: 2px solid rgba(255, 255, 255, 0.3);
            flex-shrink: 0;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 0.75rem;
            opacity: 0.8;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .logout-button {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 0.75rem;
            color: white;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
            font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;
        }

        .logout-button:hover {
            background: rgba(239, 68, 68, 0.3);
            border-color: rgba(239, 68, 68, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .user-quick-actions {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .quick-action-btn {
            flex: 1;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            color: white;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .quick-action-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }

        .user-status-badge {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #10B981;
            border: 2px solid white;
            position: absolute;
            bottom: 2px;
            right: 2px;
        }

        .user-avatar-wrapper {
            position: relative;
        }

        .sidebar-nav {
            padding: 1rem 0;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            min-height: 0;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
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
            background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-accent) 100%);
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
            background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-accent) 100%);
            color: white;
            border-radius: 1.5rem 1.5rem 0 0 !important;
            padding: 1.5rem 2rem;
            border: none;
            font-weight: 600;
            font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;
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
            background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-accent) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(67, 19, 247, 0.3);
            border: none;
            font-weight: 600;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 19, 247, 0.4);
            color: white;
            background: linear-gradient(135deg, var(--winit-accent) 0%, var(--winit-primary) 100%);
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

        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-accent) 100%);
            color: white;
            border: none;
            border-radius: 0.75rem;
            width: 48px;
            height: 48px;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(67, 19, 247, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .mobile-menu-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(67, 19, 247, 0.4);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            backdrop-filter: blur(4px);
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .sidebar-overlay.active {
                display: block;
            }

            .mobile-menu-toggle {
                display: flex;
            }

            .main-content {
                margin-left: 0;
                padding: 5rem 1rem 1rem 1rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="logo" style="background: transparent; border: none;">
                <img src="{{ asset('images/winit-logo-C73aMBts (2).svg') }}" alt="WinIt Logo" style="width: 120px; height: auto; display: block; margin: 0 auto;">
            </div>
            <h4 style="color: white;">WinIt Prize Distribution</h4>
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

                <!-- Airtime Operations Section -->
                <li class="nav-item mt-3">
                    <div class="nav-section-header">
                        <i class="fas fa-mobile-alt"></i>
                        <span>Airtime</span>
                    </div>
                </li>

                @if(auth()->user()->canUploadCsv())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('bulk-airtime.index') ? 'active' : '' }}" href="{{ route('bulk-airtime.index') }}">
                        <i class="fas fa-upload"></i>
                        <span>Upload CSV</span>
                    </a>
                </li>
                @endif

                @if(auth()->user()->canViewTransactions())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('bulk-airtime.history') ? 'active' : '' }}" href="{{ route('bulk-airtime.history') }}">
                        <i class="fas fa-history"></i>
                        <span>Batch History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('bulk-airtime.transactions') ? 'active' : '' }}" href="{{ route('bulk-airtime.transactions') }}">
                        <i class="fas fa-list-alt"></i>
                        <span>All Transactions</span>
                    </a>
                </li>
                @endif

                <!-- DSTV Operations Section -->
                <li class="nav-item mt-3">
                    <div class="nav-section-header">
                        <i class="fas fa-tv"></i>
                        <span>DSTV (TV)</span>
                    </div>
                </li>

                @if(auth()->user()->canUploadCsv())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('bulk-dstv.index') ? 'active' : '' }}" href="{{ route('bulk-dstv.index') }}">
                        <i class="fas fa-upload"></i>
                        <span>Upload CSV</span>
                    </a>
                </li>
                @endif

                @if(auth()->user()->canViewTransactions())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('bulk-dstv.history') ? 'active' : '' }}" href="{{ route('bulk-dstv.history') }}">
                        <i class="fas fa-history"></i>
                        <span>Batch History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('bulk-dstv.transactions') ? 'active' : '' }}" href="{{ route('bulk-dstv.transactions') }}">
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

                @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('Super Admin'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}" href="{{ route('permissions.index') }}">
                        <i class="fas fa-shield-alt"></i>
                        <span>Permissions</span>
                    </a>
                </li>
                @endif

        <!-- Activity Logs - Available to all authenticated users -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}" href="{{ route('activity-logs.index') }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Activity Logs</span>
            </a>
        </li>

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

                <!-- Settings Section -->
                @auth
                <li class="nav-item mt-3">
                    <div class="nav-section-header">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </div>
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
                @endauth
            </ul>
        </nav>
        
        <!-- User Profile Section at Bottom -->
        @auth
        <div class="user-profile-section">
            <div class="user-profile-card">
                <div class="user-avatar-wrapper">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="user-status-badge" title="Online"></span>
                </div>
                <div class="user-info">
                    <p class="user-name">{{ auth()->user()->name }}</p>
                    <p class="user-role">
                        @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('Super Admin'))
                            <i class="fas fa-shield-alt"></i> Super Admin
                        @elseif(auth()->user()->hasRole('admin'))
                            <i class="fas fa-user-shield"></i> Admin
                        @else
                            <i class="fas fa-user"></i> {{ ucfirst(auth()->user()->roles->first()->name ?? 'User') }}
                        @endif
                    </p>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="user-quick-actions">
                <a href="{{ route('profile.edit') }}" class="quick-action-btn" title="Profile Settings">
                    <i class="fas fa-user-cog"></i>
                    <span class="d-none d-lg-inline">Profile</span>
                </a>
                @if(auth()->user()->hasPermission('view-notifications'))
                <a href="{{ route('notifications.index') }}" class="quick-action-btn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="d-none d-lg-inline">Alerts</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('Super Admin'))
                <a href="#" onclick="showSystemInfo(); return false;" class="quick-action-btn" title="System Info">
                    <i class="fas fa-info-circle"></i>
                </a>
                @endif
            </div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-button">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
        @endauth
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
                'Application': 'WinIt Prize Distribution',
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

        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('mobileMenuToggle');
            
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            
            // Toggle icon
            const icon = toggleBtn.querySelector('i');
            if (sidebar.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }

        // Close sidebar when clicking on a nav link (mobile)
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        const sidebar = document.querySelector('.sidebar');
                        const overlay = document.getElementById('sidebarOverlay');
                        const toggleBtn = document.getElementById('mobileMenuToggle');
                        
                        if (sidebar.classList.contains('active')) {
                            sidebar.classList.remove('active');
                            overlay.classList.remove('active');
                            
                            const icon = toggleBtn.querySelector('i');
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    }
                });
            });
        });
    </script>
    
    <script>
        // Set CSRF token for AJAX requests
        window.csrfToken = '{{ csrf_token() }}';
        
        /**
         * Generate device fingerprint (same function as login page)
         * Returns the full fingerprint string - backend will hash it with SHA256
         */
        function generateDeviceFingerprint() {
            const components = [];
            components.push(navigator.userAgent || '');
            components.push(navigator.platform || '');
            components.push(`${screen.width}x${screen.height}`);
            components.push(screen.colorDepth || '');
            components.push(new Date().getTimezoneOffset());
            components.push(navigator.language || navigator.userLanguage || '');
            components.push(navigator.hardwareConcurrency || '');
            if (navigator.deviceMemory) {
                components.push(navigator.deviceMemory);
            }
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                ctx.textBaseline = 'top';
                ctx.font = '14px Arial';
                ctx.fillText('Device fingerprint', 2, 2);
                components.push(canvas.toDataURL().substring(0, 100));
            } catch (e) {}
            try {
                const gl = document.createElement('canvas').getContext('webgl');
                if (gl) {
                    const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                    if (debugInfo) {
                        components.push(gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL));
                        components.push(gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL));
                    }
                }
            } catch (e) {}
            // Return the full fingerprint string (backend will hash it with SHA256)
            return components.join('|');
        }
        
        // Generate and store device fingerprint
        const deviceFingerprint = generateDeviceFingerprint();
        sessionStorage.setItem('deviceFingerprint', deviceFingerprint);
        
        // Intercept all fetch requests to add device fingerprint header
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            const [url, options = {}] = args;
            const headers = options.headers || {};
            
            // Add device fingerprint header
            if (typeof headers === 'object' && !(headers instanceof Headers)) {
                headers['X-Device-Fingerprint'] = deviceFingerprint;
            } else if (headers instanceof Headers) {
                headers.set('X-Device-Fingerprint', deviceFingerprint);
            } else {
                options.headers = {
                    ...headers,
                    'X-Device-Fingerprint': deviceFingerprint
                };
            }
            
            return originalFetch.apply(this, [url, options]);
        };
        
        // Intercept XMLHttpRequest to add device fingerprint header
        const originalOpen = XMLHttpRequest.prototype.open;
        const originalSetRequestHeader = XMLHttpRequest.prototype.setRequestHeader;
        
        XMLHttpRequest.prototype.open = function(method, url, ...rest) {
            this._url = url;
            return originalOpen.apply(this, [method, url, ...rest]);
        };
        
        XMLHttpRequest.prototype.setRequestHeader = function(name, value) {
            if (name.toLowerCase() === 'x-device-fingerprint') {
                return; // Don't override if already set
            }
            return originalSetRequestHeader.apply(this, [name, value]);
        };
        
        // Add device fingerprint to all XMLHttpRequest sends
        const originalSend = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.send = function(...args) {
            this.setRequestHeader('X-Device-Fingerprint', deviceFingerprint);
            return originalSend.apply(this, args);
        };
    </script>
    
    @stack('scripts')
</body>
</html>
