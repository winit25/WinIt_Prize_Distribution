<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - WinIt Prize Distribution</title>
    
    <!-- Favicon - Same as logo -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/winit-logo-C73aMBts (2).svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/winit-logo-C73aMBts (2).png') }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Cross-browser compatibility fixes */
        * {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

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
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            /* Cross-browser gradient support */
            background: #010133; /* Fallback for older browsers */
            background: -webkit-linear-gradient(135deg, #010133 0%, #01011b 100%);
            background: -moz-linear-gradient(135deg, #010133 0%, #01011b 100%);
            background: -o-linear-gradient(135deg, #010133 0%, #01011b 100%);
            background: linear-gradient(135deg, #010133 0%, #01011b 100%);
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
            padding: 20px;
            position: relative;
            overflow: hidden;
            /* Font rendering fixes for Safari/Edge */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            /* Cross-browser radial gradient support */
            background: 
                -webkit-radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                -webkit-radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
                -webkit-radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.03) 0%, transparent 50%);
            background: 
                -moz-radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                -moz-radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
                -moz-radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.03) 0%, transparent 50%);
            background: 
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.03) 0%, transparent 50%);
            pointer-events: none;
        }

        .login-container {
            background: white;
            /* Cross-browser border-radius */
            -webkit-border-radius: 1.5rem;
            -moz-border-radius: 1.5rem;
            border-radius: 1.5rem;
            /* Cross-browser box-shadow */
            -webkit-box-shadow: 0 20px 40px rgba(1, 1, 51, 0.4);
            -moz-box-shadow: 0 20px 40px rgba(1, 1, 51, 0.4);
            box-shadow: 0 20px 40px rgba(1, 1, 51, 0.4);
            overflow: hidden;
            max-width: 320px;
            width: 100%;
            position: relative;
            z-index: 1;
            /* Cross-browser backdrop-filter with fallback */
            background-color: rgba(255, 255, 255, 0.95); /* Fallback for browsers without backdrop-filter */
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            /* Cross-browser gradient support */
            background: #010133; /* Fallback */
            background: -webkit-linear-gradient(135deg, #010133 0%, #01011b 100%);
            background: -moz-linear-gradient(135deg, #010133 0%, #01011b 100%);
            background: -o-linear-gradient(135deg, #010133 0%, #01011b 100%);
            background: linear-gradient(135deg, #010133 0%, #01011b 100%);
            color: white;
            padding: 1.5rem 1.25rem 1.25rem;
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
            /* Cross-browser radial gradient */
            background: -webkit-radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            background: -moz-radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            /* Cross-browser animation */
            -webkit-animation: float 6s ease-in-out infinite;
            -moz-animation: float 6s ease-in-out infinite;
            -o-animation: float 6s ease-in-out infinite;
            animation: float 6s ease-in-out infinite;
        }

        /* Cross-browser keyframes */
        @-webkit-keyframes float {
            0%, 100% { -webkit-transform: translateY(0px) rotate(0deg); transform: translateY(0px) rotate(0deg); }
            50% { -webkit-transform: translateY(-20px) rotate(180deg); transform: translateY(-20px) rotate(180deg); }
        }
        @-moz-keyframes float {
            0%, 100% { -moz-transform: translateY(0px) rotate(0deg); transform: translateY(0px) rotate(0deg); }
            50% { -moz-transform: translateY(-20px) rotate(180deg); transform: translateY(-20px) rotate(180deg); }
        }
        @keyframes float {
            0%, 100% { -webkit-transform: translateY(0px) rotate(0deg); -moz-transform: translateY(0px) rotate(0deg); transform: translateY(0px) rotate(0deg); }
            50% { -webkit-transform: translateY(-20px) rotate(180deg); -moz-transform: translateY(-20px) rotate(180deg); transform: translateY(-20px) rotate(180deg); }
        }

        .login-header .logo {
            width: 65px;
            height: 65px;
            -webkit-border-radius: 20px;
            -moz-border-radius: 20px;
            border-radius: 20px;
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
            margin: 0 auto 0.75rem;            
            position: relative;
            z-index: 1;
            -webkit-box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
            -moz-box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
            padding: 10px;
        }

        .login-header .logo img {
            max-width: 100%;
            width: 100%;
            height: auto;
            object-fit: contain;
            filter: contrast(1.3) brightness(1.1);
        }

        .login-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 1.4rem;
            position: relative;
            z-index: 1;
        }

        .login-header p {
            margin: 0.3rem 0 0 0;
            opacity: 0.9;
            font-weight: 400;
            font-size: 0.85rem;
            position: relative;
            z-index: 1;
        }

        .login-body {
            padding: 1.5rem 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--winit-dark);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control {
            border: 2px solid var(--winit-border);
            -webkit-border-radius: 12px;
            -moz-border-radius: 12px;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            /* Cross-browser transition */
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            -o-transition: all 0.3s ease;
            transition: all 0.3s ease;
            background: var(--winit-light);
            font-weight: 500;
            height: auto;
            /* Safari input fixes */
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .form-control:focus {
            border-color: var(--winit-accent);
            -webkit-box-shadow: 0 0 0 4px rgba(23, 247, 182, 0.15);
            -moz-box-shadow: 0 0 0 4px rgba(23, 247, 182, 0.15);
            box-shadow: 0 0 0 4px rgba(23, 247, 182, 0.15);
            background: white;
            outline: none;
            -webkit-transform: translateY(-2px);
            -moz-transform: translateY(-2px);
            -ms-transform: translateY(-2px);
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
            -webkit-transform: translateY(-50%);
            -moz-transform: translateY(-50%);
            -ms-transform: translateY(-50%);
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--winit-gray);
            z-index: 3;
            font-size: 0.9rem;
        }

        .btn-primary {
            /* Cross-browser gradient */
            background: #010133; /* Fallback */
            background: -webkit-linear-gradient(135deg, #010133 0%, #01011b 100%);
            background: -moz-linear-gradient(135deg, #010133 0%, #01011b 100%);
            background: -o-linear-gradient(135deg, #010133 0%, #01011b 100%);
            background: linear-gradient(135deg, #010133 0%, #01011b 100%);
            color: white;
            -webkit-box-shadow: 0 5px 15px rgba(1, 1, 51, 0.3);
            -moz-box-shadow: 0 5px 15px rgba(1, 1, 51, 0.3);
            box-shadow: 0 5px 15px rgba(1, 1, 51, 0.3);
            border: none;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
            padding: 0.75rem 1.25rem;
            /* Safari button fixes */
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .btn-primary:hover {
            background: -webkit-linear-gradient(135deg, var(--winit-accent) 0%, var(--winit-accent-dark) 100%);
            background: -moz-linear-gradient(135deg, var(--winit-accent) 0%, var(--winit-accent-dark) 100%);
            background: -o-linear-gradient(135deg, var(--winit-accent) 0%, var(--winit-accent-dark) 100%);
            background: linear-gradient(135deg, var(--winit-accent) 0%, var(--winit-accent-dark) 100%);
            color: #010133;
            -webkit-transform: translateY(-3px);
            -moz-transform: translateY(-3px);
            -ms-transform: translateY(-3px);
            transform: translateY(-3px);
            -webkit-box-shadow: 0 12px 30px rgba(23, 247, 182, 0.5);
            -moz-box-shadow: 0 12px 30px rgba(23, 247, 182, 0.5);
            box-shadow: 0 12px 30px rgba(23, 247, 182, 0.5);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            -webkit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            transform: none;
            -webkit-box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            -moz-box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn {
            -webkit-border-radius: 10px;
            -moz-border-radius: 10px;
            border-radius: 10px;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            -o-transition: all 0.3s ease;
            transition: all 0.3s ease;
            display: -webkit-inline-box;
            display: -webkit-inline-flex;
            display: -moz-inline-box;
            display: -ms-inline-flexbox;
            display: inline-flex;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -moz-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 0.75rem 0.9rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
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

        /* Responsive Design - Mobile First Approach */
        /* Extra Small Devices (< 360px) */
        @media (max-width: 359px) {
            body {
                padding: 8px;
            }
            
            .login-container {
                border-radius: 1.25rem;
                max-width: 100%;
            }
            
            .login-header {
                padding: 1.5rem 1rem 1rem;
            }
            
            .login-header h1 {
                font-size: 1.25rem;
            }
            
            .login-header p {
                font-size: 0.8rem;
            }
            
            .login-header .logo {
                width: 60px;
                height: 60px;
                padding: 6px;
                margin-bottom: 0.75rem;
            }
            
            .login-body {
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
            
            .login-container {
                margin: 0.5rem;
                border-radius: 1.5rem;
                max-width: 100%;
            }
            
            .login-header {
                padding: 2rem 1.25rem 1.5rem;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
            
            .login-header p {
                font-size: 0.9rem;
            }
            
            .login-header .logo {
                width: 70px;
                height: 70px;
                padding: 8px;
                margin-bottom: 1rem;
            }
            
            .login-body {
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
            
            .login-container {
                max-width: 95%;
                border-radius: 2rem;
            }
            
            .login-header {
                padding: 2.5rem 1.75rem 1.75rem;
            }
            
            .login-header h1 {
                font-size: 1.75rem;
            }
            
            .login-header p {
                font-size: 0.95rem;
            }
            
            .login-header .logo {
                width: 80px;
                height: 80px;
            }
            
            .login-body {
                padding: 2.5rem 2rem;
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
            
            .login-container {
                max-width: 520px;
                border-radius: 2rem;
            }
            
            .login-header {
                padding: 2.75rem 2rem 2rem;
            }
            
            .login-header h1 {
                font-size: 1.9rem;
            }
            
            .login-body {
                padding: 2.75rem 2.25rem;
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
            
            .login-container {
                max-width: 400px;
                border-radius: 2rem;
            }
            
            .login-header {
                padding: 1.75rem 1.5rem 1.5rem;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
            
            .login-header p {
                font-size: 0.9rem;
            }
            
            .login-header .logo {
                width: 70px;
                height: 70px;
                padding: 10px;
                margin-bottom: 1rem;
            }
            
            .login-body {
                padding: 2rem 1.75rem;
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
            
            .login-container {
                max-width: 360px;
                border-radius: 1.75rem;
            }
            
            .login-header {
                padding: 1.5rem 1.25rem 1.25rem;
            }
            
            .login-header h1 {
                font-size: 1.35rem;
            }
            
            .login-header p {
                font-size: 0.85rem;
            }
            
            .login-header .logo {
                width: 65px;
                height: 65px;
                padding: 10px;
                margin-bottom: 0.75rem;
            }
            
            .login-body {
                padding: 1.5rem 1.5rem;
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
            
            .login-container {
                max-width: 500px;
                border-radius: 2.5rem;
            }
            
            .login-header {
                padding: 3rem 2.25rem 2.25rem;
            }
            
            .login-header h1 {
                font-size: 2rem;
            }
            
            .login-body {
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
            
            .login-container {
                max-width: 520px;
                border-radius: 2.5rem;
            }
            
            .login-header {
                padding: 3.5rem 2.5rem 2.5rem;
            }
            
            .login-header h1 {
                font-size: 2.25rem;
            }
            
            .login-body {
                padding: 3.5rem 2.75rem;
            }
        }

        /* Extra Large Laptops (1401px - 1600px) */
        @media (min-width: 1401px) and (max-width: 1600px) {
            body {
                padding: 50px;
            }
            
            .login-container {
                max-width: 550px;
                border-radius: 3rem;
            }
            
            .login-header {
                padding: 4rem 2.75rem 2.75rem;
            }
            
            .login-header h1 {
                font-size: 2.4rem;
            }
            
            .login-body {
                padding: 4rem 3rem;
            }
        }

        /* Ultra Large Laptops (> 1600px) */
        @media (min-width: 1601px) {
            body {
                padding: 60px;
            }
            
            .login-container {
                max-width: 600px;
                border-radius: 3rem;
            }
            
            .login-header {
                padding: 4.5rem 3rem 3rem;
            }
            
            .login-header h1 {
                font-size: 2.5rem;
            }
            
            .login-body {
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
        .login-container {
            min-height: 300px;
        }

        /* Prevent horizontal scroll */
        body {
            overflow-x: hidden;
        }

        .login-container {
            overflow-x: hidden;
        }

        /* Additional mobile improvements */
        @media (max-width: 576px) {
            .login-header .logo img {
                max-width: 80%;
            }
            
            .input-group .form-control {
                font-size: 16px; /* Prevents zoom on iOS */
            }
            
            .btn {
                min-height: 48px; /* Better touch target */
            }
        }

        /* Landscape mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            body {
                padding: 10px;
            }
            
            .login-container {
                max-width: 100%;
            }
            
            .login-header {
                padding: 1.5rem 1.25rem 1rem;
            }
            
            .login-body {
                padding: 1.5rem 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <img src="{{ asset('images/winit-logo-C73aMBts (2).svg') }}" alt="WinIt Logo" style="max-width: 100%; width: 100%; height: auto; display: block; margin: 0 auto;">
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
                <div class="mb-2">
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
                               placeholder="Enter your email"
                               style="font-size: 16px;">
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-2">
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
                               placeholder="Enter your password"
                               style="font-size: 16px;">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="mb-2">
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

                <div class="d-grid gap-2 mb-2">
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
            // Clear any stale session data on page load (for new device sessions)
            sessionStorage.removeItem('deviceFingerprint');
            localStorage.removeItem('loginAttempts');
            
            // Refresh CSRF token from meta tag
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                const csrfInput = document.querySelector('input[name="_token"]');
                if (csrfInput) {
                    csrfInput.value = csrfMeta.getAttribute('content');
                }
            }
            
            // Handle 419 CSRF errors with user-friendly message and redirect
            if (window.location.search.includes('419') || window.location.search.includes('csrf')) {
                // Clear stale session data
                sessionStorage.clear();
                localStorage.removeItem('loginAttempts');
                
                // Regenerate CSRF token by refreshing the page
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Session Expired:</strong> Your session has expired. Please refresh the page and try again.
                `;
                const loginBody = document.querySelector('.login-body');
                if (loginBody) {
                    loginBody.insertBefore(alertDiv, loginBody.firstChild);
                }
                
                // Auto-refresh after 3 seconds to get fresh CSRF token
                setTimeout(function() {
                    window.location.href = window.location.pathname;
                }, 3000);
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

            // IMPORTANT: Only generate device fingerprint AFTER user submits login form
            // This ensures fingerprint is only used for device validation, not as a biometric login method
            // Fingerprint generation happens on form submit, not on page load
            const form = document.getElementById('loginForm');
            
            if (!form) {
                console.error('Login form not found');
                return;
            }
            
            // Intercept form submission to generate and add device fingerprint
            form.addEventListener('submit', function(e) {
                // Generate device fingerprint ONLY when form is submitted (user has entered credentials)
                // This ensures fingerprint is not used as a biometric login before authentication
                const deviceFingerprint = generateDeviceFingerprint();
                
                // Create a hidden input to send fingerprint via form
                const fingerprintInput = document.createElement('input');
                fingerprintInput.type = 'hidden';
                fingerprintInput.name = '_device_fingerprint';
                fingerprintInput.value = deviceFingerprint;
                form.appendChild(fingerprintInput);

                // Store in sessionStorage for use after successful login
                sessionStorage.setItem('deviceFingerprint', deviceFingerprint);
                
                // Clear any stale login attempts
                localStorage.removeItem('loginAttempts');
                
                // Ensure CSRF token is fresh
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (csrfMeta) {
                    const tokenInput = form.querySelector('input[name="_token"]');
                    if (tokenInput) {
                        tokenInput.value = csrfMeta.getAttribute('content');
                    }
                }
            });
        });
    </script>
</body>
</html>
