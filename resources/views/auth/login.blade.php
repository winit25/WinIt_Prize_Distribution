<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WinIt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --winit-navy: rgb(18, 18, 104);
            --winit-navy-light: rgb(30, 30, 120);
            --winit-navy-dark: rgb(12, 12, 80);
            --winit-primary: rgb(18, 18, 104);
            --winit-secondary: #8b5cf6;
            --winit-accent: #06b6d4;
            --winit-success: #10b981;
            --winit-warning: #f59e0b;
            --winit-danger: #ef4444;
            --winit-dark: #1f2937;
            --winit-gray: #6b7280;
            --winit-light: #f8fafc;
            --winit-border: #e5e7eb;
            --winit-white: #ffffff;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-dark) 100%);
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

        .login-container {
            background: white;
            border-radius: 2rem;
            box-shadow: 0 25px 50px rgba(18, 18, 104, 0.3);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            background: linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-light) 100%);
            color: white;
            padding: 3rem 2rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
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

        .login-header .logo {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            color: white;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .winit-logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1.8rem;
            color: var(--winit-navy);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .login-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2rem;
            position: relative;
            z-index: 1;
        }

        .login-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }

        .login-body {
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
            border-color: var(--winit-navy);
            box-shadow: 0 0 0 4px rgba(18, 18, 104, 0.1);
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
            background: linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-light) 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(18, 18, 104, 0.3);
            border: none;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 1.1rem;
            padding: 1rem 1.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(18, 18, 104, 0.4);
            color: white;
            background: linear-gradient(135deg, var(--winit-navy-dark) 0%, var(--winit-navy) 100%);
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

        .form-check-input:checked {
            background-color: var(--winit-primary);
            border-color: var(--winit-primary);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
        }

        .text-muted {
            color: var(--winit-gray) !important;
        }

        .text-primary {
            color: var(--winit-primary) !important;
        }

        .text-primary:hover {
            color: var(--winit-secondary) !important;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                border-radius: 1.5rem;
                max-width: 95%;
            }
            
            .login-header {
                padding: 2.5rem 1.5rem 1.5rem;
            }
            
            .login-header .logo {
                width: 80px;
                height: 80px;
                font-size: 2.5rem;
            }
            
            .winit-logo {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }
            
            .login-body {
                padding: 2rem 1.5rem;
            }
            
            .form-control {
                padding: 1rem 1.25rem;
                font-size: 1rem;
            }
            
            .btn-primary {
                padding: 0.875rem 1.25rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <div class="winit-logo">
                    W
                </div>
            </div>
            <h1>WinIt</h1>
            <p>BuyPower Token Distribution</p>
        </div>

        <div class="login-body">
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

            <form method="POST" action="{{ route('login') }}">
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
                               autocomplete="username"
                               placeholder="Enter your email">
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input id="password" 
                               class="form-control"
                               type="password"
                               name="password"
                               required 
                               autocomplete="current-password"
                               placeholder="Enter your password">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="mb-4">
                    <div class="form-check">
                        <input id="remember_me" 
                               type="checkbox" 
                               class="form-check-input" 
                               name="remember">
                        <label for="remember_me" class="form-check-label text-muted">
                            Remember me
                        </label>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In to WinIt
                    </button>
                </div>

                @if (Route::has('password.request'))
                    <div class="text-center mt-3">
                        <a class="text-primary text-decoration-none" href="{{ route('password.request') }}">
                            Forgot your password?
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</body>
</html>
