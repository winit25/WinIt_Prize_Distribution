<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - WinIt Prize Distribution</title>
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

        .login-container {
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

        .login-header {
            background: linear-gradient(135deg, #010133 0%, #01011b 100%);
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
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 15px;
        }

        .login-header .logo img {
            max-width: 100%;
            height: auto;
            object-fit: contain;
            filter: contrast(1.3) brightness(1.1);
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

        .form-check-input:checked {
            background-color: var(--winit-accent);
            border-color: var(--winit-accent);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(23, 247, 182, 0.25);
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
                padding: 10px;
            }
            
            .login-header .logo img {
                width: 100%;
                height: auto;
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
                <img src="{{ asset('images/winit-logo-C73aMBts (2).svg') }}" alt="WinIt Logo" style="width: 200px; height: auto; display: block; margin: 0 auto;">
            </div>
            <h1 style="color: white;">WinIt</h1>
            <p style="color: rgba(255, 255, 255, 0.9);">BuyPower Token Distribution</p>
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

            <form method="POST" action="{{ route('login') }}" id="loginForm">
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

    <script>
        /**
         * Generate device fingerprint
         * Combines multiple browser/device characteristics to create a unique fingerprint
         */
        function generateDeviceFingerprint() {
            const components = [];

            // User Agent
            components.push(navigator.userAgent || '');

            // Platform
            components.push(navigator.platform || '');

            // Screen resolution
            components.push(`${screen.width}x${screen.height}`);

            // Color depth
            components.push(screen.colorDepth || '');

            // Timezone offset
            components.push(new Date().getTimezoneOffset());

            // Language
            components.push(navigator.language || navigator.userLanguage || '');

            // Hardware concurrency (CPU cores)
            components.push(navigator.hardwareConcurrency || '');

            // Device memory (if available)
            if (navigator.deviceMemory) {
                components.push(navigator.deviceMemory);
            }

            // Canvas fingerprint (simplified)
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                ctx.textBaseline = 'top';
                ctx.font = '14px Arial';
                ctx.fillText('Device fingerprint', 2, 2);
                components.push(canvas.toDataURL().substring(0, 100));
            } catch (e) {
                // Canvas not available
            }

            // WebGL vendor/renderer (if available)
            try {
                const gl = document.createElement('canvas').getContext('webgl');
                if (gl) {
                    const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                    if (debugInfo) {
                        components.push(gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL));
                        components.push(gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL));
                    }
                }
            } catch (e) {
                // WebGL not available
            }

            // Combine all components
            const fingerprintString = components.join('|');

            // Return the full fingerprint string (backend will hash it with SHA256)
            // This ensures consistency with backend hashing
            return fingerprintString;
        }
        
        /**
         * Generate SHA256 hash (for consistency with backend)
         * Note: This is a simplified version. Backend uses PHP's hash('sha256')
         * For production, consider using Web Crypto API or a library
         */
        async function generateFingerprintHash(fingerprint) {
            // Use Web Crypto API if available (more secure)
            if (window.crypto && window.crypto.subtle) {
                const encoder = new TextEncoder();
                const data = encoder.encode(fingerprint);
                const hashBuffer = await crypto.subtle.digest('SHA-256', data);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            }
            // Fallback: return fingerprint as-is (backend will hash it)
            return fingerprint;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Handle 419 CSRF errors with user-friendly message
            if (window.location.search.includes('419')) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Session Expired:</strong> Please refresh the page and try again.
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
                `;
                const loginBody = document.querySelector('.login-body');
                if (loginBody) {
                    loginBody.insertBefore(alertDiv, loginBody.firstChild);
                }
            }

            // Handle device error message - Use textContent to prevent XSS
            @if($errors->has('device') || session('device_error'))
                const deviceErrorDiv = document.createElement('div');
                deviceErrorDiv.className = 'alert alert-danger';
                const errorMessage = @json($errors->first('device') ?: 'Access denied. This account is tied to another device. Please use your original registered device to login.');
                
                // Create elements safely to prevent XSS
                const icon = document.createElement('i');
                icon.className = 'fas fa-shield-alt me-2';
                
                const strong = document.createElement('strong');
                strong.textContent = 'Device Access Denied: ';
                
                const messageText = document.createTextNode(errorMessage);
                
                const br = document.createElement('br');
                
                const small = document.createElement('small');
                small.textContent = 'If you need to change your registered device, please contact your administrator.';
                
                deviceErrorDiv.appendChild(icon);
                deviceErrorDiv.appendChild(strong);
                deviceErrorDiv.appendChild(messageText);
                deviceErrorDiv.appendChild(br);
                deviceErrorDiv.appendChild(small);
                
                const loginBody = document.querySelector('.login-body');
                if (loginBody) {
                    loginBody.insertBefore(deviceErrorDiv, loginBody.firstChild);
                }
            @endif

            // Generate and store device fingerprint
            const deviceFingerprint = generateDeviceFingerprint();
            
            // Store fingerprint in sessionStorage for use across requests
            sessionStorage.setItem('deviceFingerprint', deviceFingerprint);

            // Clear any stale session data on page load
            const form = document.getElementById('loginForm');
            
            // Intercept form submission to add device fingerprint header
            form.addEventListener('submit', function(e) {
                // Create a hidden input to send fingerprint via form
                // Or use fetch API to send it as header
                const fingerprintInput = document.createElement('input');
                fingerprintInput.type = 'hidden';
                fingerprintInput.name = '_device_fingerprint';
                fingerprintInput.value = deviceFingerprint;
                form.appendChild(fingerprintInput);

                // Also store in sessionStorage for AJAX requests
                sessionStorage.setItem('deviceFingerprint', deviceFingerprint);
                
                localStorage.removeItem('loginAttempts');
            });
        });
    </script>
</body>
</html>
