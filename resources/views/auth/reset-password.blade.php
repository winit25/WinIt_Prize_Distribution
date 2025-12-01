<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - WinIt Prize Distribution</title>
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

        .reset-container {
            background: white;
            border-radius: 2rem;
            box-shadow: 0 25px 50px rgba(1, 1, 51, 0.5);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .reset-header {
            background: linear-gradient(135deg, #010133 0%, #01011b 100%);
            color: white;
            padding: 3rem 2rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .reset-header::before {
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

        .reset-header .logo {
            width: 100px;
            height: 100px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;            
            position: relative;
            z-index: 1;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 15px;
        }

        .reset-header .logo img {
            max-width: 100%;
            height: auto;
            object-fit: contain;
            filter: contrast(1.3) brightness(1.1);
        }

        .reset-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2rem;
            position: relative;
            z-index: 1;
        }

        .reset-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }

        .reset-body {
            padding: 3rem 2.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--winit-dark);
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
        }

        .form-control {
            border: 2px solid var(--winit-border);
            border-radius: 15px;
            padding: 1.25rem 1.5rem;
            font-size: 1.1rem;
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
            padding-left: 3rem;
        }

        .input-group .input-group-text {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--winit-gray);
            z-index: 3;
        }

        .btn-primary {
            background: linear-gradient(135deg, #010133 0%, #01011b 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(1, 1, 51, 0.3);
            border: none;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 1.1rem;
            padding: 1rem 1.5rem;
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

        .btn {
            border-radius: 12px;
            padding: 0.875rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
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

        /* Responsive Design */
        @media (max-width: 359px) {
            .reset-container { border-radius: 1.25rem; }
            .reset-header { padding: 1.5rem 1rem 1rem; }
            .reset-header h1 { font-size: 1.25rem; }
            .reset-header .logo { width: 60px; height: 60px; }
            .reset-body { padding: 1.25rem 1rem; }
            .form-label { font-size: 0.9rem; }
            .form-control { padding: 0.75rem 0.875rem; font-size: 0.9rem; }
            .btn { padding: 0.75rem 1rem; font-size: 0.9rem; }
        }

        @media (min-width: 360px) and (max-width: 576px) {
            .reset-container { max-width: 100%; }
            .reset-header { padding: 2rem 1.25rem 1.5rem; }
            .reset-header h1 { font-size: 1.5rem; }
            .reset-header .logo { width: 70px; height: 70px; }
            .reset-body { padding: 1.5rem 1.25rem; }
            .form-label { font-size: 0.95rem; }
            .form-control { padding: 0.875rem 1rem; font-size: 0.95rem; }
            .btn { padding: 0.875rem 1rem; font-size: 0.95rem; }
        }

        @media (min-width: 577px) and (max-width: 768px) {
            body { padding: 15px; }
            .reset-container { max-width: 95%; border-radius: 2rem; }
            .reset-header { padding: 2.5rem 1.75rem 1.75rem; }
            .reset-header h1 { font-size: 1.75rem; }
            .reset-header .logo { width: 80px; height: 80px; }
            .reset-body { padding: 2.5rem 2rem; }
            .form-label { font-size: 1rem; }
            .form-control { padding: 1rem 1.25rem; font-size: 1rem; }
            .btn { padding: 0.9rem 1.25rem; font-size: 0.95rem; }
        }

        @media (min-width: 769px) and (max-width: 992px) {
            body { padding: 20px; }
            .reset-container { max-width: 520px; border-radius: 2rem; }
            .reset-header { padding: 2.75rem 2rem 2rem; }
            .reset-header h1 { font-size: 1.9rem; }
            .reset-body { padding: 2.75rem 2.25rem; }
            .form-label { font-size: 1.05rem; }
            .form-control { padding: 1.1rem 1.4rem; font-size: 1.05rem; }
            .btn { padding: 0.95rem 1.5rem; font-size: 1rem; }
        }

        @media (min-width: 993px) and (max-width: 1200px) {
            body { padding: 30px; }
            .reset-container { max-width: 500px; border-radius: 2.5rem; }
            .reset-header { padding: 3rem 2.25rem 2.25rem; }
            .reset-header h1 { font-size: 2rem; }
            .reset-body { padding: 3rem 2.5rem; }
            .form-control { padding: 1.25rem 1.5rem; font-size: 1.1rem; }
            .btn { padding: 1rem 1.5rem; font-size: 1.05rem; }
        }

        @media (min-width: 1201px) and (max-width: 1400px) {
            body { padding: 40px; }
            .reset-container { max-width: 520px; border-radius: 2.5rem; }
            .reset-header { padding: 3.5rem 2.5rem 2.5rem; }
            .reset-header h1 { font-size: 2.25rem; }
            .reset-body { padding: 3.5rem 2.75rem; }
        }

        @media (min-width: 1401px) and (max-width: 1600px) {
            body { padding: 50px; }
            .reset-container { max-width: 550px; border-radius: 3rem; }
            .reset-header { padding: 4rem 2.75rem 2.75rem; }
            .reset-header h1 { font-size: 2.4rem; }
            .reset-body { padding: 4rem 3rem; }
        }

        @media (min-width: 1601px) {
            body { padding: 60px; }
            .reset-container { max-width: 600px; border-radius: 3rem; }
            .reset-header { padding: 4.5rem 3rem 3rem; }
            .reset-header h1 { font-size: 2.5rem; }
            .reset-body { padding: 4.5rem 3.5rem; }
            .form-label { font-size: 1.2rem; }
            .form-control { padding: 1.4rem 1.75rem; font-size: 1.2rem; }
            .btn { padding: 1.1rem 1.75rem; font-size: 1.15rem; }
        }

        /* Prevent layout shifts */
        .reset-container { min-height: 400px; overflow-x: hidden; }
        body { overflow-x: hidden; }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <div class="logo">
                <img src="{{ asset('images/winit-logo-C73aMBts (2).svg') }}" alt="WinIt Logo" style="width: 200px; height: auto; display: block; margin: 0 auto;">
            </div>
            <h1 style="color: white;">Reset Password</h1>
            <p style="color: rgba(255, 255, 255, 0.9);">Create your new secure password</p>
        </div>

        <div class="reset-body">
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

            <form method="POST" action="{{ route('password.store') }}" id="reset-password-form">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') ?? $request->input('token') }}">

                <!-- Email Address -->
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input id="email" 
                               class="form-control" 
                               type="email" 
                               name="email" 
                               value="{{ old('email', $request->email) }}" 
                               required 
                               autofocus
                               autocomplete="username"
                               placeholder="Enter your email">
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input id="password" 
                               class="form-control"
                               type="password"
                               name="password"
                               required 
                               autocomplete="new-password"
                               placeholder="Enter your new password">
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <input id="password_confirmation" 
                               class="form-control"
                               type="password"
                               name="password_confirmation" 
                               required 
                               autocomplete="new-password"
                               placeholder="Confirm your password">
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i>
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            sessionStorage.clear();
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                const csrfInputs = document.querySelectorAll('input[name="_token"]');
                csrfInputs.forEach(function(input) {
                    input.value = csrfMeta.getAttribute('content');
                });
            }
            
            const form = document.getElementById('reset-password-form');
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

    <script>
        // Ensure CSRF token is fresh when form is submitted
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
                setTimeout(function() {
                    window.location.href = window.location.pathname + window.location.search;
                }, 3000);
            }
            
            const form = document.getElementById('reset-password-form');
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
                    // Allow form to submit normally
                });
            }
        });
    </script>
</x-guest-layout>
