<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - WinIt Prize Distribution</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --winit-navy: #010133;
            --winit-navy-light: #020247;
            --winit-navy-dark: #01011b;
            --winit-primary: #010133;
            --winit-accent: #17F7B6;
            --winit-accent-dark: #13d9a0;
            --winit-secondary: #23A3D6;
            --winit-success: #10b981;
            --winit-warning: #f59e0b;
            --winit-danger: #ef4444;
            --winit-dark: #010133;
            --winit-gray: #6b7280;
            --winit-light: #f8fafc;
            --winit-border: #e5e7eb;
            --winit-white: #ffffff;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #010133 0%, #01011b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.03) 0%, transparent 50%);
            pointer-events: none;
        }

        .forgot-container {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 40px rgba(1, 1, 51, 0.4);
            overflow: hidden;
            max-width: 380px;
            width: 100%;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .forgot-header {
            background: linear-gradient(135deg, #010133 0%, #01011b 100%);
            color: white;
            padding: 2rem 1.5rem 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .forgot-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .forgot-header .logo {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            position: relative;
            z-index: 1;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
            padding: 12px;
        }

        .forgot-header .logo img {
            max-width: 100%;
            width: 100%;
            height: auto;
            object-fit: contain;
            filter: contrast(1.3) brightness(1.1);
        }

        .forgot-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 1.65rem;
            position: relative;
            z-index: 1;
        }

        .forgot-header p {
            margin: 0.4rem 0 0 0;
            opacity: 0.9;
            font-weight: 400;
            font-size: 0.9rem;
            position: relative;
            z-index: 1;
        }

        .forgot-body {
            padding: 2rem 2rem;
        }

        .info-box {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-left: 4px solid #0284c7;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-box p {
            margin: 0;
            color: #075985;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        .form-label {
            font-weight: 600;
            color: var(--winit-dark);
            margin-bottom: 0.6rem;
            font-size: 0.95rem;
        }

        .form-control {
            border: 2px solid var(--winit-border);
            border-radius: 12px;
            padding: 0.9rem 1.25rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: var(--winit-light);
            font-weight: 500;
            height: auto;
        }

        .form-control:focus {
            border-color: var(--winit-accent);
            box-shadow: 0 0 0 4px rgba(23, 247, 182, 0.15);
            background: white;
            outline: none;
            transform: translateY(-2px);
        }

        .input-group {
            position: relative;
        }

        .input-group .form-control {
            padding-left: 2.5rem;
        }

        .input-group .input-group-text {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--winit-gray);
            z-index: 3;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #010133 0%, #01011b 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(1, 1, 51, 0.3);
            border: none;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 0.95rem;
            padding: 0.85rem 1.5rem;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--winit-accent) 0%, var(--winit-accent-dark) 100%);
            color: #010133;
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(23, 247, 182, 0.5);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            transform: none;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-secondary {
            background: white;
            color: var(--winit-dark);
            border: 2px solid var(--winit-border);
            font-weight: 600;
        }

        .btn-secondary:hover {
            background: var(--winit-light);
            border-color: var(--winit-accent);
            color: var(--winit-dark);
            transform: translateY(-2px);
        }

        .btn {
            border-radius: 10px;
            padding: 0.85rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 0.85rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--winit-success);
            border-left: 4px solid var(--winit-success);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--winit-danger);
            border-left: 4px solid var(--winit-danger);
        }

        .text-muted {
            color: var(--winit-gray) !important;
        }

        .text-primary {
            color: #010133 !important;
        }

        .text-primary:hover {
            color: var(--winit-accent) !important;
        }

        /* Responsive Design - Mobile First Approach */
        /* Extra Small Devices (< 360px) */
        @media (max-width: 359px) {
            body {
                padding: 8px;
            }
            
            .forgot-container {
                border-radius: 1.25rem;
                max-width: 100%;
            }
            
            .forgot-header {
                padding: 1.5rem 1rem 1rem;
            }
            
            .forgot-header h1 {
                font-size: 1.25rem;
            }
            
            .forgot-header p {
                font-size: 0.8rem;
            }
            
            .forgot-header .logo {
                width: 60px;
                height: 60px;
                padding: 6px;
                margin-bottom: 0.75rem;
            }
            
            .forgot-body {
                padding: 1.25rem 1rem;
            }
            
            .info-box {
                padding: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .info-box p {
                font-size: 0.8rem;
            }
            
            .form-label {
                font-size: 0.9rem;
                margin-bottom: 0.4rem;
            }
            
            .form-control {
                padding: 0.75rem 0.875rem;
                font-size: 0.9rem;
            }
            
            .input-group .input-group-text {
                left: 0.5rem;
                font-size: 0.85rem;
            }
            
            .input-group .form-control {
                padding-left: 2.25rem;
            }
            
            .btn {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
            
            .alert {
                padding: 0.75rem 0.875rem;
                font-size: 0.85rem;
            }
        }

        /* Small Devices (360px - 576px) */
        @media (min-width: 360px) and (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .forgot-container {
                margin: 0.5rem;
                border-radius: 1.5rem;
                max-width: 100%;
            }
            
            .forgot-header {
                padding: 2rem 1.25rem 1.5rem;
            }
            
            .forgot-header h1 {
                font-size: 1.5rem;
            }
            
            .forgot-header p {
                font-size: 0.9rem;
            }
            
            .forgot-header .logo {
                width: 70px;
                height: 70px;
                padding: 8px;
                margin-bottom: 1rem;
            }
            
            .forgot-body {
                padding: 1.5rem 1.25rem;
            }
            
            .info-box {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .info-box p {
                font-size: 0.85rem;
            }
            
            .form-label {
                font-size: 0.95rem;
                margin-bottom: 0.5rem;
            }
            
            .form-control {
                padding: 0.875rem 1rem;
                font-size: 0.95rem;
            }
            
            .input-group .input-group-text {
                left: 0.75rem;
                font-size: 0.9rem;
            }
            
            .input-group .form-control {
                padding-left: 2.5rem;
            }
            
            .btn {
                padding: 0.875rem 1rem;
                font-size: 0.95rem;
            }
            
            .alert {
                padding: 0.875rem 1rem;
                font-size: 0.9rem;
            }
        }

        /* Tablets (577px - 768px) */
        @media (min-width: 577px) and (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .forgot-container {
                max-width: 95%;
                border-radius: 2rem;
            }
            
            .forgot-header {
                padding: 2.5rem 1.75rem 1.75rem;
            }
            
            .forgot-header h1 {
                font-size: 1.75rem;
            }
            
            .forgot-header p {
                font-size: 0.95rem;
            }
            
            .forgot-header .logo {
                width: 80px;
                height: 80px;
            }
            
            .forgot-body {
                padding: 2.5rem 2rem;
            }
            
            .info-box {
                padding: 1.25rem;
            }
            
            .info-box p {
                font-size: 0.9rem;
            }
            
            .form-label {
                font-size: 1rem;
            }
            
            .form-control {
                padding: 1rem 1.25rem;
                font-size: 1rem;
            }
            
            .btn {
                padding: 0.9rem 1.25rem;
                font-size: 0.95rem;
            }
        }

        /* Small Laptops/Desktops (769px - 992px) */
        @media (min-width: 769px) and (max-width: 992px) {
            body {
                padding: 20px;
            }
            
            .forgot-container {
                max-width: 520px;
                border-radius: 2rem;
            }
            
            .forgot-header {
                padding: 2.75rem 2rem 2rem;
            }
            
            .forgot-header h1 {
                font-size: 1.9rem;
            }
            
            .forgot-body {
                padding: 2.75rem 2.25rem;
            }
            
            .info-box {
                padding: 1.5rem;
                margin-bottom: 1.75rem;
            }
            
            .form-label {
                font-size: 1.05rem;
            }
            
            .form-control {
                padding: 1.1rem 1.4rem;
                font-size: 1.05rem;
            }
            
            .btn {
                padding: 0.95rem 1.5rem;
                font-size: 1rem;
            }
        }

        /* 13-inch Laptop Screens (1024px - 1440px) - Optimized */
        @media (min-width: 1024px) and (max-width: 1440px) {
            body {
                padding: 30px 20px;
            }
            
            .forgot-container {
                max-width: 480px;
                border-radius: 2rem;
            }
            
            .forgot-header {
                padding: 2.5rem 2rem 1.75rem;
            }
            
            .forgot-header h1 {
                font-size: 1.85rem;
            }
            
            .forgot-header p {
                font-size: 0.95rem;
            }
            
            .forgot-header .logo {
                width: 85px;
                height: 85px;
                padding: 12px;
                margin-bottom: 1.25rem;
            }
            
            .forgot-body {
                padding: 2.5rem 2rem;
            }
            
            .info-box {
                padding: 1.25rem;
                margin-bottom: 1.5rem;
            }
            
            .info-box p {
                font-size: 0.9rem;
                line-height: 1.5;
            }
            
            .form-label {
                font-size: 1rem;
                margin-bottom: 0.65rem;
            }
            
            .form-control {
                padding: 1rem 1.25rem;
                font-size: 1rem;
            }
            
            .input-group .input-group-text {
                left: 0.875rem;
                font-size: 0.95rem;
            }
            
            .input-group .form-control {
                padding-left: 2.75rem;
            }
            
            .btn {
                padding: 0.9rem 1.5rem;
                font-size: 1rem;
            }
            
            .alert {
                padding: 0.9rem 1.1rem;
                font-size: 0.95rem;
            }
        }

        /* Specific optimization for 1280 x 800 resolution */
        @media (min-width: 1270px) and (max-width: 1290px) and (min-height: 750px) and (max-height: 850px) {
            body {
                padding: 25px 15px;
            }
            
            .forgot-container {
                max-width: 460px;
                border-radius: 1.75rem;
            }
            
            .forgot-header {
                padding: 2.25rem 1.75rem 1.5rem;
            }
            
            .forgot-header h1 {
                font-size: 1.75rem;
            }
            
            .forgot-header p {
                font-size: 0.9rem;
            }
            
            .forgot-header .logo {
                width: 80px;
                height: 80px;
                padding: 10px;
                margin-bottom: 1rem;
            }
            
            .forgot-body {
                padding: 2rem 1.75rem;
            }
            
            .info-box {
                padding: 1rem;
                margin-bottom: 1.25rem;
            }
            
            .info-box p {
                font-size: 0.85rem;
                line-height: 1.45;
            }
            
            .form-label {
                font-size: 0.95rem;
                margin-bottom: 0.6rem;
            }
            
            .form-control {
                padding: 0.9rem 1.15rem;
                font-size: 0.95rem;
            }
            
            .input-group .input-group-text {
                left: 0.8rem;
                font-size: 0.9rem;
            }
            
            .input-group .form-control {
                padding-left: 2.5rem;
            }
            
            .btn {
                padding: 0.85rem 1.4rem;
                font-size: 0.95rem;
            }
            
            .alert {
                padding: 0.85rem 1rem;
                font-size: 0.9rem;
                margin-bottom: 1.25rem;
            }
        }

        /* Medium Laptops (993px - 1200px) */
        @media (min-width: 993px) and (max-width: 1200px) {
            body {
                padding: 30px;
            }
            
            .forgot-container {
                max-width: 500px;
                border-radius: 2.5rem;
            }
            
            .forgot-header {
                padding: 3rem 2.25rem 2.25rem;
            }
            
            .forgot-header h1 {
                font-size: 2rem;
            }
            
            .forgot-body {
                padding: 3rem 2.5rem;
            }
            
            .form-label {
                font-size: 1.1rem;
            }
            
            .form-control {
                padding: 1.25rem 1.5rem;
                font-size: 1.1rem;
            }
            
            .btn {
                padding: 1rem 1.5rem;
                font-size: 1.05rem;
            }
        }

        /* Large Laptops (1201px - 1400px) */
        @media (min-width: 1201px) and (max-width: 1400px) {
            body {
                padding: 40px;
            }
            
            .forgot-container {
                max-width: 520px;
                border-radius: 2.5rem;
            }
            
            .forgot-header {
                padding: 3.5rem 2.5rem 2.5rem;
            }
            
            .forgot-header h1 {
                font-size: 2.25rem;
            }
            
            .forgot-body {
                padding: 3.5rem 2.75rem;
            }
        }

        /* Extra Large Laptops (1401px - 1600px) */
        @media (min-width: 1401px) and (max-width: 1600px) {
            body {
                padding: 50px;
            }
            
            .forgot-container {
                max-width: 550px;
                border-radius: 3rem;
            }
            
            .forgot-header {
                padding: 4rem 2.75rem 2.75rem;
            }
            
            .forgot-header h1 {
                font-size: 2.4rem;
            }
            
            .forgot-body {
                padding: 4rem 3rem;
            }
        }

        /* Ultra Large Laptops (> 1600px) */
        @media (min-width: 1601px) {
            body {
                padding: 60px;
            }
            
            .forgot-container {
                max-width: 600px;
                border-radius: 3rem;
            }
            
            .forgot-header {
                padding: 4.5rem 3rem 3rem;
            }
            
            .forgot-header h1 {
                font-size: 2.5rem;
            }
            
            .forgot-body {
                padding: 4.5rem 3.5rem;
            }
            
            .form-label {
                font-size: 1.2rem;
            }
            
            .form-control {
                padding: 1.4rem 1.75rem;
                font-size: 1.2rem;
            }
            
            .btn {
                padding: 1.1rem 1.75rem;
                font-size: 1.15rem;
            }
        }

        /* Fix for layout shifts */
        .forgot-container {
            min-height: 350px;
        }

        /* Prevent horizontal scroll */
        body {
            overflow-x: hidden;
        }

        .forgot-container {
            overflow-x: hidden;
        }

        /* Additional mobile improvements */
        @media (max-width: 576px) {
            .forgot-header .logo img {
                max-width: 80%;
            }
            
            .input-group .form-control {
                font-size: 16px; /* Prevents zoom on iOS */
            }
            
            .btn {
                min-height: 48px; /* Better touch target */
            }
            
            .info-box {
                font-size: 0.85rem;
                line-height: 1.5;
            }
        }

        /* Landscape mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            body {
                padding: 10px;
            }
            
            .forgot-container {
                max-width: 100%;
            }
            
            .forgot-header {
                padding: 1.5rem 1.25rem 1rem;
            }
            
            .forgot-body {
                padding: 1.5rem 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <div class="logo">
                <img src="{{ asset('images/winit-logo-C73aMBts (2).svg') }}" alt="WinIt Logo" style="max-width: 100%; width: 100%; height: auto; display: block; margin: 0 auto;">
            </div>
            <h1 style="color: white;">Reset Password</h1>
            <p style="color: rgba(255, 255, 255, 0.9);">WinIt Prize Distribution</p>
        </div>

        <div class="forgot-body">
            <div class="info-box">
                <i class="fas fa-info-circle me-2"></i>
                <p>Forgot your password? No problem. Just enter your email address and we'll send you a password reset link.</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('status') }}
                </div>
            @endif

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

            <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
                @csrf

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input id="email" 
                               class="form-control" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus
                               placeholder="Enter your email"
                               style="font-size: 16px;">
                    </div>
                </div>

                <div class="d-grid gap-2 mb-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Send Reset Link
                    </button>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('login') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Clear any stale session data on page load
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
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Session Expired:</strong> Your session has expired. Please refresh the page and try again.
                `;
                const forgotBody = document.querySelector('.forgot-body');
                if (forgotBody) {
                    forgotBody.insertBefore(alertDiv, forgotBody.firstChild);
                }
                setTimeout(function() {
                    window.location.href = window.location.pathname;
                }, 3000);
            }
            
            // Ensure CSRF token is fresh when form is submitted
            const form = document.getElementById('forgotForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Get fresh CSRF token from meta tag
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
