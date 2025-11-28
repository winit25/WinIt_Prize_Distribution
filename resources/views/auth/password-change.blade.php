<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - WinIt</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --winit-navy: rgb(18, 18, 104);
            --winit-navy-light: rgb(30, 30, 120);
            --winit-navy-dark: rgb(12, 12, 80);
            --winit-gray: #f8fafc;
            --winit-text: #374151;
            --winit-border: rgba(18, 18, 104, 0.1);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 1rem;
        }

        .password-change-container {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 40px rgba(18, 18, 104, 0.3);
            border: 1px solid var(--winit-border);
            backdrop-filter: blur(10px);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }

        .password-change-header {
            background: linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-light) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .password-change-header .logo {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 25px rgba(18, 18, 104, 0.2);
            padding: 15px;
        }

        .password-change-header .logo img {
            max-width: 100%;
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
            border-radius: 15px;
            padding: 1.25rem 1.5rem;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: var(--winit-gray);
            height: auto;
        }

        .form-control:focus {
            border-color: var(--winit-navy);
            box-shadow: 0 0 0 4px rgba(18, 18, 104, 0.1);
            background: white;
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--winit-navy) 0%, var(--winit-navy-light) 100%);
            border: none;
            border-radius: 15px;
            padding: 1rem 1.5rem;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(18, 18, 104, 0.4);
            background: linear-gradient(135deg, var(--winit-navy-dark) 0%, var(--winit-navy) 100%);
        }

        .alert {
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

        @media (max-width: 480px) {
            .password-change-container {
                max-width: 95%;
            }
            .password-change-header {
                padding: 2.5rem 1.5rem 1.5rem;
            }
            .password-change-header .logo {
                width: 80px;
                height: 80px;
                padding: 10px;
            }
            
            .password-change-header .logo img {
                width: 100%;
                height: auto;
            }
            .password-change-body {
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
    <div class="password-change-container">
        <div class="password-change-header">
            <div class="logo">
                <img src="{{ asset('images/winit-logo.svg') }}" alt="WinIt Logo" style="width: 200px; height: auto; display: block; margin: 0 auto;">
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
                           placeholder="Enter your current password">
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
                           placeholder="Enter your new password">
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input id="password_confirmation" 
                           type="password" 
                           class="form-control" 
                           name="password_confirmation" 
                           required
                           placeholder="Confirm your new password">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Password
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
