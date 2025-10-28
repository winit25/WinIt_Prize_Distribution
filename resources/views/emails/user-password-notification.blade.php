<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your WinIt Account Credentials</title>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
            min-height: 100vh;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(18, 18, 104, 0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            font-weight: 700;
            color: rgb(18, 18, 104);
        }
        
        .header h1 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 700;
        }
        
        .header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        
        .content {
            padding: 2rem;
        }
        
        .welcome-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .welcome-section h2 {
            color: rgb(18, 18, 104);
            margin-bottom: 1rem;
        }
        
        .credentials-box {
            background: #f8fafc;
            border: 2px solid rgba(18, 18, 104, 0.1);
            border-radius: 1rem;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(18, 18, 104, 0.1);
        }
        
        .credential-item:last-child {
            border-bottom: none;
        }
        
        .credential-label {
            font-weight: 600;
            color: rgb(18, 18, 104);
        }
        
        .credential-value {
            font-family: 'Courier New', monospace;
            background: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(18, 18, 104, 0.2);
            font-weight: 600;
            color: #374151;
        }
        
        .warning-box {
            background: rgba(245, 158, 11, 0.1);
            border: 2px solid #f59e0b;
            border-radius: 1rem;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .warning-box h3 {
            color: #f59e0b;
            margin: 0 0 1rem 0;
            display: flex;
            align-items: center;
        }
        
        .warning-box h3::before {
            content: "⚠️";
            margin-right: 0.5rem;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
            color: white;
            text-decoration: none;
            padding: 1rem 2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            margin: 1rem 0;
            transition: all 0.3s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(18, 18, 104, 0.3);
        }
        
        .footer {
            background: #f8fafc;
            padding: 1.5rem 2rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .footer p {
            margin: 0.25rem 0;
        }
        
        .security-note {
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid #10b981;
            border-radius: 1rem;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .security-note h3 {
            color: #10b981;
            margin: 0 0 1rem 0;
            display: flex;
            align-items: center;
        }
        
        .security-note h3::before {
            content: "🔒";
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">W</div>
            <h1>WinIt</h1>
            <p>BuyPower Token Distribution System</p>
        </div>
        
        <div class="content">
            <div class="welcome-section">
                <h2>Welcome to WinIt, {{ $user->name }}!</h2>
                <p>Your account has been created successfully. Please find your login credentials below.</p>
            </div>
            
            <div class="credentials-box">
                <h3 style="color: rgb(18, 18, 104); margin: 0 0 1rem 0;">Your Login Credentials</h3>
                
                <div class="credential-item">
                    <span class="credential-label">Email Address:</span>
                    <span class="credential-value">{{ $user->email }}</span>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">Temporary Password:</span>
                    <span class="credential-value">{{ $password }}</span>
                </div>
            </div>
            
            <div class="warning-box">
                <h3>Important Security Notice</h3>
                <p><strong>You must change your password on first login!</strong> This temporary password is only valid for your initial access. For security reasons, you will be required to set a new password immediately after logging in.</p>
            </div>
            
            <div class="security-note">
                <h3>Security Best Practices</h3>
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <li>Choose a strong password with at least 8 characters</li>
                    <li>Include uppercase, lowercase, numbers, and special characters</li>
                    <li>Do not share your credentials with anyone</li>
                    <li>Log out when finished using the system</li>
                </ul>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ $loginUrl }}" class="cta-button">Login to Your Account</a>
            </div>
            
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(18, 18, 104, 0.1);">
                <h4 style="color: rgb(18, 18, 104); margin-bottom: 1rem;">What's Next?</h4>
                <ol style="color: #374151; line-height: 1.6;">
                    <li>Click the login button above or visit: <a href="{{ $loginUrl }}" style="color: rgb(18, 18, 104);">{{ $loginUrl }}</a></li>
                    <li>Enter your email and temporary password</li>
                    <li>You'll be prompted to create a new password</li>
                    <li>Start using the WinIt system!</li>
                </ol>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>WinIt - BuyPower Token Distribution System</strong></p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>If you did not request this account, please contact your system administrator.</p>
        </div>
    </div>
</body>
</html>
