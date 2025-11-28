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

        .forgot-header {
            background: linear-gradient(135deg, #010133 0%, #01011b 100%);
            color: white;
            padding: 3rem 2rem 2rem;
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
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 15px;
        }

        .forgot-header .logo img {
            max-width: 100%;
            height: auto;
            object-fit: contain;
            filter: contrast(1.3) brightness(1.1);
        }

        .forgot-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2rem;
            position: relative;
            z-index: 1;
        }

        .forgot-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }

        .forgot-body {
            padding: 3rem 2.5rem;
        }

        .info-box {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-left: 4px solid #0284c7;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 2rem;
        }

        .info-box p {
            margin: 0;
            color: #075985;
            font-size: 0.95rem;
            line-height: 1.6;
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
            border-radius: 12px;
            padding: 0.875rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
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

        @media (max-width: 480px) {
            .forgot-container {
                margin: 1rem;
                border-radius: 1.5rem;
                max-width: 95%;
            }
            
            .forgot-header {
                padding: 2.5rem 1.5rem 1.5rem;
            }
            
            .forgot-header .logo {
                width: 80px;
                height: 80px;
                padding: 10px;
            }
            
            .forgot-body {
                padding: 2rem 1.5rem;
            }
            
            .form-control {
                padding: 1rem 1.25rem;
                font-size: 1rem;
            }
            
            .btn-primary, .btn-secondary {
                padding: 0.875rem 1.25rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <div class="logo">
                <img src="{{ asset('images/winit-logo-C73aMBts (2).svg') }}" alt="WinIt Logo" style="width: 200px; height: auto; display: block; margin: 0 auto;">
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
                               value="{{ old('email') }}" 
                               required 
                               autofocus
                               placeholder="Enter your email">
                    </div>
                </div>

                <div class="d-grid gap-2 mb-3">
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
