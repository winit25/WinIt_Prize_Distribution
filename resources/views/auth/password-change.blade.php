<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Change Password - WinIt</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Cross-browser compatibility fixes */
        * {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        :root {
            --winit-navy: rgb(18, 18, 104);
            --winit-navy-light: rgb(30, 30, 120);
            --winit-navy-dark: rgb(12, 12, 80);
            --winit-gray: #f8fafc;
            --winit-text: #374151;
            --winit-border: rgba(18, 18, 104, 0.1);
        }

        body {
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            /* Cross-browser gradient support */
            background: rgb(18, 18, 104); /* Fallback */
            background: -webkit-linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-dark) 100%);
            background: -moz-linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-dark) 100%);
            background: -o-linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-dark) 100%);
            background: linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-dark) 100%);
            min-height: 100vh;
            /* Cross-browser flexbox support */
            display: -webkit-box;
            display: -webkit-flex;
            display: -moz-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -moz-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-pack: center;
            -webkit-justify-content: center;
            -moz-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            margin: 0;
            padding: 1rem;
            /* Font rendering fixes for Safari/Edge */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        .password-change-container {
            background: white;
            /* Cross-browser border-radius */
            -webkit-border-radius: 1.5rem;
            -moz-border-radius: 1.5rem;
            border-radius: 1.5rem;
            /* Cross-browser box-shadow */
            -webkit-box-shadow: 0 20px 40px rgba(18, 18, 104, 0.3);
            -moz-box-shadow: 0 20px 40px rgba(18, 18, 104, 0.3);
            box-shadow: 0 20px 40px rgba(18, 18, 104, 0.3);
            border: 1px solid var(--winit-border);
            /* Cross-browser backdrop-filter with fallback */
            background-color: rgba(255, 255, 255, 0.95); /* Fallback for browsers without backdrop-filter */
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }

        .password-change-header {
            /* Cross-browser gradient support */
            background: rgb(18, 18, 104); /* Fallback */
            background: -webkit-linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-light) 100%);
            background: -moz-linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-light) 100%);
            background: -o-linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-light) 100%);
            background: linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-light) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .password-change-header .logo {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0);
            -webkit-border-radius: 25px;
            -moz-border-radius: 25px;
            border-radius: 25px;
            /* Cross-browser flexbox */
            display: -webkit-box;
            display: -webkit-flex;
            display: -moz-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -moz-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-pack: center;
            -webkit-justify-content: center;
            -moz-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            margin: 0 auto 1rem;
            -webkit-box-shadow: 0 8px 25px rgba(18, 18, 104, 0.2);
            -moz-box-shadow: 0 8px 25px rgba(18, 18, 104, 0.2);
            box-shadow: 0 8px 25px rgba(18, 18, 104, 0.2);
            padding: 15px;
        }

        .password-change-header .logo img {
            max-width: 100%;
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        .password-change-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 1.75rem;
        }

        .password-change-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }

        .password-change-body {
            padding: 3rem 2.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--winit-navy);
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
        }

        .form-control {
            border: 2px solid var(--winit-border);
            -webkit-border-radius: 15px;
            -moz-border-radius: 15px;
            border-radius: 15px;
            padding: 1.25rem 1.5rem;
            font-size: 1.1rem;
            /* Cross-browser transition */
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            -o-transition: all 0.3s ease;
            transition: all 0.3s ease;
            background: var(--winit-gray);
            height: auto;
            /* Safari input fixes */
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .form-control:focus {
            border-color: var(--winit-navy);
            -webkit-box-shadow: 0 0 0 4px rgba(18, 18, 104, 0.1);
            -moz-box-shadow: 0 0 0 4px rgba(18, 18, 104, 0.1);
            box-shadow: 0 0 0 4px rgba(18, 18, 104, 0.1);
            background: white;
            outline: none;
        }

        .btn-primary {
            /* Cross-browser gradient */
            background: rgb(18, 18, 104); /* Fallback */
            background: -webkit-linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-light) 100%);
            background: -moz-linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-light) 100%);
            background: -o-linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-light) 100%);
            background: linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-light) 100%);
            border: none;
            -webkit-border-radius: 15px;
            -moz-border-radius: 15px;
            border-radius: 15px;
            padding: 1rem 1.5rem;
            font-weight: 700;
            font-size: 1.1rem;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            -o-transition: all 0.3s ease;
            transition: all 0.3s ease;
            width: 100%;
            /* Safari button fixes */
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .btn-primary:hover {
            -webkit-transform: translateY(-3px);
            -moz-transform: translateY(-3px);
            -ms-transform: translateY(-3px);
            transform: translateY(-3px);
            -webkit-box-shadow: 0 12px 30px rgba(18, 18, 104, 0.4);
            -moz-box-shadow: 0 12px 30px rgba(18, 18, 104, 0.4);
            box-shadow: 0 12px 30px rgba(18, 18, 104, 0.4);
            background: -webkit-linear-gradient(135deg, var(--winit-navy-dark) 0%, var(--winit-navy) 100%);
            background: -moz-linear-gradient(135deg, var(--winit-navy-dark) 0%, var(--winit-navy) 100%);
            background: -o-linear-gradient(135deg, var(--winit-navy-dark) 0%, var(--winit-navy) 100%);
            background: linear-gradient(135deg, var(--winit-navy-dark) 0%, var(--winit-navy) 100%);
        }

        .alert {
            -webkit-border-radius: 15px;
            -moz-border-radius: 15px;
            border-radius: 15px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border-left: 4px solid #f59e0b;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-left: 4px solid #ef4444;
        }

        .password-requirements {
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid #10b981;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .password-requirements h5 {
            color: #10b981;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .password-requirements h5::before {
            content: "🔒";
            margin-right: 0.5rem;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 1.5rem;
            color: var(--winit-text);
        }

        .password-requirements li {
            margin-bottom: 0.5rem;
        }

        /* Responsive Design - Mobile First Approach */
        /* Extra Small Devices (< 360px) */
        @media (max-width: 359px) {
            body {
                padding: 8px;
            }
            
            .password-change-container {
                border-radius: 1.25rem;
                max-width: 100%;
            }
            
            .password-change-header {
                padding: 1.5rem 1rem 1rem;
            }
            
            .password-change-header h1 {
                font-size: 1.25rem;
            }
            
            .password-change-header p {
                font-size: 0.8rem;
            }
            
            .password-change-header .logo {
                width: 60px;
                height: 60px;
                padding: 6px;
                margin-bottom: 0.75rem;
            }
            
            .password-change-body {
                padding: 1.25rem 1rem;
            }
            
            .form-label {
                font-size: 0.9rem;
                margin-bottom: 0.4rem;
            }
            
            .form-control {
                padding: 0.75rem 0.875rem;
                font-size: 0.9rem;
            }
            
            .btn-primary {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
            
            .alert {
                padding: 0.75rem 0.875rem;
                font-size: 0.85rem;
            }
            
            .password-requirements {
                padding: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .password-requirements h5 {
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
            }
            
            .password-requirements ul {
                font-size: 0.8rem;
                padding-left: 1rem;
            }
        }

        /* Small Devices (360px - 576px) */
        @media (min-width: 360px) and (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .password-change-container {
                margin: 0.5rem;
                border-radius: 1.25rem;
                max-width: 100%;
            }
            
            .password-change-header {
                padding: 2rem 1.25rem 1.5rem;
            }
            
            .password-change-header h1 {
                font-size: 1.5rem;
            }
            
            .password-change-header p {
                font-size: 0.9rem;
            }
            
            .password-change-header .logo {
                width: 70px;
                height: 70px;
                padding: 8px;
                margin-bottom: 1rem;
            }
            
            .password-change-body {
                padding: 1.5rem 1.25rem;
            }
            
            .form-label {
                font-size: 0.95rem;
                margin-bottom: 0.5rem;
            }
            
            .form-control {
                padding: 0.875rem 1rem;
                font-size: 0.95rem;
            }
            
            .btn-primary {
                padding: 0.875rem 1rem;
                font-size: 0.95rem;
            }
            
            .alert {
                padding: 0.875rem 1rem;
                font-size: 0.9rem;
            }
            
            .password-requirements {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .password-requirements h5 {
                font-size: 1rem;
            }
            
            .password-requirements ul {
                font-size: 0.85rem;
                padding-left: 1.25rem;
            }
        }

        /* Tablets (577px - 768px) */
        @media (min-width: 577px) and (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .password-change-container {
                max-width: 95%;
                border-radius: 1.75rem;
            }
            
            .password-change-header {
                padding: 2.5rem 1.75rem 1.75rem;
            }
            
            .password-change-header h1 {
                font-size: 1.75rem;
            }
            
            .password-change-header p {
                font-size: 0.95rem;
            }
            
            .password-change-header .logo {
                width: 80px;
                height: 80px;
            }
            
            .password-change-body {
                padding: 2.5rem 2rem;
            }
            
            .form-label {
                font-size: 1rem;
            }
            
            .form-control {
                padding: 1rem 1.25rem;
                font-size: 1rem;
            }
            
            .btn-primary {
                padding: 0.9rem 1.25rem;
                font-size: 0.95rem;
            }
            
            .password-requirements {
                padding: 1.25rem;
                margin-bottom: 1.75rem;
            }
            
            .password-requirements h5 {
                font-size: 1.1rem;
            }
            
            .password-requirements ul {
                font-size: 0.9rem;
            }
        }

        /* Small Laptops/Desktops (769px - 992px) */
        @media (min-width: 769px) and (max-width: 992px) {
            body {
                padding: 20px;
            }
            
            .password-change-container {
                max-width: 520px;
                border-radius: 2rem;
            }
            
            .password-change-header {
                padding: 2.75rem 2rem 2rem;
            }
            
            .password-change-header h1 {
                font-size: 1.9rem;
            }
            
            .password-change-body {
                padding: 2.75rem 2.25rem;
            }
            
            .form-label {
                font-size: 1.05rem;
            }
            
            .form-control {
                padding: 1.1rem 1.4rem;
                font-size: 1.05rem;
            }
            
            .btn-primary {
                padding: 0.95rem 1.5rem;
                font-size: 1rem;
            }
            
            .password-requirements {
                padding: 1.5rem;
            }
        }

        /* 13-inch Laptop Screens (1024px - 1440px) - Optimized */
        @media (min-width: 1024px) and (max-width: 1440px) {
            body {
                padding: 30px 20px;
            }
            
            .password-change-container {
                max-width: 480px;
                border-radius: 2rem;
            }
            
            .password-change-header {
                padding: 2.5rem 2rem 1.75rem;
            }
            
            .password-change-header h1 {
                font-size: 1.85rem;
            }
            
            .password-change-header p {
                font-size: 0.95rem;
            }
            
            .password-change-header .logo {
                width: 85px;
                height: 85px;
                padding: 12px;
                margin-bottom: 1.25rem;
            }
            
            .password-change-body {
                padding: 2.5rem 2rem;
            }
            
            .form-label {
                font-size: 1rem;
                margin-bottom: 0.65rem;
            }
            
            .form-control {
                padding: 1rem 1.25rem;
                font-size: 1rem;
            }
            
            .btn-primary {
                padding: 0.9rem 1.5rem;
                font-size: 1rem;
            }
            
            .alert {
                padding: 0.9rem 1.1rem;
                font-size: 0.95rem;
            }
            
            .password-requirements {
                padding: 1.25rem;
                margin-bottom: 1.5rem;
            }
            
            .password-requirements h5 {
                font-size: 1.05rem;
                margin-bottom: 0.875rem;
            }
            
            .password-requirements ul {
                font-size: 0.9rem;
                line-height: 1.6;
            }
            
            .password-requirements li {
                margin-bottom: 0.4rem;
            }
        }

        /* Specific optimization for 1280 x 800 resolution */
        @media (min-width: 1270px) and (max-width: 1290px) and (min-height: 750px) and (max-height: 850px) {
            body {
                padding: 25px 15px;
            }
            
            .password-change-container {
                max-width: 460px;
                border-radius: 1.75rem;
            }
            
            .password-change-header {
                padding: 2.25rem 1.75rem 1.5rem;
            }
            
            .password-change-header h1 {
                font-size: 1.75rem;
            }
            
            .password-change-header p {
                font-size: 0.9rem;
            }
            
            .password-change-header .logo {
                width: 80px;
                height: 80px;
                padding: 10px;
                margin-bottom: 1rem;
            }
            
            .password-change-body {
                padding: 2rem 1.75rem;
            }
            
            .form-label {
                font-size: 0.95rem;
                margin-bottom: 0.6rem;
            }
            
            .form-control {
                padding: 0.9rem 1.15rem;
                font-size: 0.95rem;
            }
            
            .btn-primary {
                padding: 0.85rem 1.4rem;
                font-size: 0.95rem;
            }
            
            .alert {
                padding: 0.85rem 1rem;
                font-size: 0.9rem;
                margin-bottom: 1.25rem;
            }
            
            .password-requirements {
                padding: 1rem;
                margin-bottom: 1.25rem;
            }
            
            .password-requirements h5 {
                font-size: 1rem;
                margin-bottom: 0.75rem;
            }
            
            .password-requirements ul {
                font-size: 0.85rem;
                line-height: 1.5;
                padding-left: 1.25rem;
            }
            
            .password-requirements li {
                margin-bottom: 0.35rem;
            }
        }

        /* Medium Laptops (993px - 1200px) */
        @media (min-width: 993px) and (max-width: 1200px) {
            body {
                padding: 30px;
            }
            
            .password-change-container {
                max-width: 500px;
                border-radius: 2.5rem;
            }
            
            .password-change-header {
                padding: 3rem 2.25rem 2.25rem;
            }
            
            .password-change-header h1 {
                font-size: 2rem;
            }
            
            .password-change-body {
                padding: 3rem 2.5rem;
            }
            
            .form-label {
                font-size: 1.1rem;
            }
            
            .form-control {
                padding: 1.25rem 1.5rem;
                font-size: 1.1rem;
            }
            
            .btn-primary {
                padding: 1rem 1.5rem;
                font-size: 1.05rem;
            }
        }

        /* Large Laptops (1201px - 1400px) */
        @media (min-width: 1201px) and (max-width: 1400px) {
            body {
                padding: 40px;
            }
            
            .password-change-container {
                max-width: 520px;
                border-radius: 2.5rem;
            }
            
            .password-change-header {
                padding: 3.5rem 2.5rem 2.5rem;
            }
            
            .password-change-header h1 {
                font-size: 2.25rem;
            }
            
            .password-change-body {
                padding: 3.5rem 2.75rem;
            }
        }

        /* Extra Large Laptops (1401px - 1600px) */
        @media (min-width: 1401px) and (max-width: 1600px) {
            body {
                padding: 50px;
            }
            
            .password-change-container {
                max-width: 550px;
                border-radius: 3rem;
            }
            
            .password-change-header {
                padding: 4rem 2.75rem 2.75rem;
            }
            
            .password-change-header h1 {
                font-size: 2.4rem;
            }
            
            .password-change-body {
                padding: 4rem 3rem;
            }
        }

        /* Ultra Large Laptops (> 1600px) */
        @media (min-width: 1601px) {
            body {
                padding: 60px;
            }
            
            .password-change-container {
                max-width: 600px;
                border-radius: 3rem;
            }
            
            .password-change-header {
                padding: 4.5rem 3rem 3rem;
            }
            
            .password-change-header h1 {
                font-size: 2.5rem;
            }
            
            .password-change-body {
                padding: 4.5rem 3.5rem;
            }
            
            .form-label {
                font-size: 1.2rem;
            }
            
            .form-control {
                padding: 1.4rem 1.75rem;
                font-size: 1.2rem;
            }
            
            .btn-primary {
                padding: 1.1rem 1.75rem;
                font-size: 1.15rem;
            }
        }

        /* Fix for layout shifts */
        .password-change-container {
            min-height: 400px;
        }

        /* Prevent horizontal scroll */
        body {
            overflow-x: hidden;
        }

        .password-change-container {
            overflow-x: hidden;
        }

        /* Additional mobile improvements */
        @media (max-width: 576px) {
            .password-change-header .logo img {
                max-width: 80%;
            }
            
            .form-control {
                font-size: 16px; /* Prevents zoom on iOS */
            }
            
            .btn-primary {
                min-height: 48px; /* Better touch target */
            }
            
            .password-requirements {
                font-size: 0.85rem;
            }
            
            .password-requirements ul {
                line-height: 1.6;
            }
        }

        /* Landscape mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            body {
                padding: 10px;
            }
            
            .password-change-container {
                max-width: 100%;
            }
            
            .password-change-header {
                padding: 1.5rem 1.25rem 1rem;
            }
            
            .password-change-body {
                padding: 1.5rem 1.25rem;
            }
            
            .password-requirements {
                padding: 1rem;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="password-change-container">
        <div class="password-change-header">
            <div class="logo">
                <img src="{{ asset('images/winit-logo.svg') }}" alt="WinIt Logo" style="max-width: 100%; width: 100%; height: auto; display: block; margin: 0 auto;">
            </div>
            <h1 style="color: white;">Change Password</h1>
            <p style="color: rgba(255, 255, 255, 0.9);">Set your new secure password</p>
        </div>

        <div class="password-change-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(auth()->user()->must_change_password)
            <div class="alert alert-warning">
                <i class="fas fa-shield-alt me-2"></i>
                <strong>First-Time Password Change Required:</strong> Please set a new password to access the system. You don't need to enter your current password for this first change.
            </div>
            @else
            <div class="alert alert-info" style="background: rgba(23, 247, 182, 0.1); color: rgb(18, 18, 104); border-left: 4px solid rgb(18, 18, 104);">
                <i class="fas fa-key me-2"></i>
                <strong>Update Password:</strong> Please enter your current password to set a new one.
            </div>
            @endif

            <div class="password-requirements">
                <h5>Password Requirements</h5>
                <ul>
                    <li>At least 8 characters long</li>
                    <li>Include uppercase and lowercase letters</li>
                    <li>Include at least one number</li>
                    <li>Include at least one special character</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('password.change.update') }}">
                @csrf

                @if(!auth()->user()->must_change_password)
                <!-- Current Password - Only show if not first-time password change -->
                <div class="mb-4">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input id="current_password" 
                           type="password" 
                           class="form-control @error('current_password') is-invalid @enderror" 
                           name="current_password" 
                           required 
                           autofocus
                           placeholder="Enter your current password"
                           style="font-size: 16px;">
                </div>
                @endif

                <!-- New Password -->
                <div class="mb-4">
                    <label for="password" class="form-label">New Password</label>
                    <input id="password" 
                           type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           name="password" 
                           required
                           @if(auth()->user()->must_change_password) autofocus @endif
                           placeholder="Enter your new password"
                           style="font-size: 16px;">
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input id="password_confirmation" 
                           type="password" 
                           class="form-control" 
                           name="password_confirmation" 
                           required
                           placeholder="Confirm your new password"
                           style="font-size: 16px;">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Password
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Clear any stale session data
            sessionStorage.clear();
            
            // Refresh CSRF token from meta tag
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                const csrfInput = document.querySelector('input[name="_token"]');
                if (csrfInput) {
                    csrfInput.value = csrfMeta.getAttribute('content');
                }
            }
            
            // Handle 419 CSRF errors
            if (window.location.search.includes('419') || window.location.search.includes('csrf')) {
                sessionStorage.clear();
                setTimeout(function() {
                    window.location.href = window.location.pathname;
                }, 3000);
            }
            
            // Ensure CSRF token is fresh on form submit
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        const tokenInput = this.querySelector('input[name="_token"]');
                        if (tokenInput) {
                            tokenInput.value = csrfToken.getAttribute('content');
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
