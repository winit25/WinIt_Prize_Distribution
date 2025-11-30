<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Created - WinIt Prize Distribution</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.5;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 10px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.08);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #122168;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .logo-container {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #122168 0%, #1a2d7a 100%);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px auto;
            padding: 12px;
        }
        .logo-container img {
            max-width: 85%;
            height: auto;
        }
        .logo {
            font-size: 20px;
            font-weight: bold;
            color: #122168;
            margin-bottom: 8px;
        }
        .welcome-box {
            background-color: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 14px;
            text-align: center;
            margin: 16px 0;
        }
        .password-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 14px;
            text-align: center;
            margin: 16px 0;
        }
        .password {
            font-size: 18px;
            font-weight: bold;
            color: #856404;
            letter-spacing: 1px;
            margin: 8px 0;
            font-family: 'Courier New', monospace;
        }
        .details {
            background-color: #e9ecef;
            padding: 12px;
            border-radius: 5px;
            margin: 16px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 6px 0;
            padding: 4px 0;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #495057;
        }
        .value {
            color: #212529;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 11px;
        }
        .security-notice {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 5px;
            margin: 16px 0;
        }
        .login-button {
            display: inline-block;
            background: linear-gradient(135deg, #122168 0%, #1a2d7a 100%);
            color: #ffffff !important;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            margin: 16px 0;
            text-align: center;
            border: 2px solid #122168;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .login-button:hover {
            background: linear-gradient(135deg, #1a2d7a 0%, #223688 100%);
            color: #ffffff !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        h1 {
            font-size: 20px;
            margin: 8px 0;
        }
        h3 {
            font-size: 16px;
            margin: 8px 0;
        }
        h4 {
            font-size: 14px;
            margin: 8px 0;
        }
        p {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container" style="background: transparent; padding: 20px;">
                <img src="{{ url('images/winit-logo-C73aMBts (2).svg') }}" alt="WinIt Logo" width="180" style="display:block; margin:0 auto; max-width: 100%; height: auto;">
            </div>
            <div class="logo">WinIt Prize Distribution</div>
            <h1>Welcome to WinIt!</h1>
        </div>

        <div class="welcome-box">
            <h3>🎉 Account Successfully Created!</h3>
            <p>Your account has been set up and is ready to use.</p>
        </div>

        <p>Dear <strong>{{ $user->name }}</strong>,</p>

        <p>Welcome to WinIt Prize Distribution! Your account has been successfully created and you can now access the system to manage electricity token distributions.</p>

        <div class="password-box">
            <h3>🔑 Your Temporary Password</h3>
            <div class="password">{{ $temporaryPassword }}</div>
            <p><em>Please use this password to log in for the first time</em></p>
        </div>

        <div class="details">
            <h3>Account Information</h3>
            <div class="detail-row">
                <span class="label">Full Name:</span>
                <span class="value">{{ $user->name }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Email Address:</span>
                <span class="value">{{ $user->email }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Account Created:</span>
                <span class="value">{{ $user->created_at->format('M d, Y h:i A') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Account Status:</span>
                <span class="value">Active</span>
            </div>
        </div>

        <div class="security-notice">
            <h4>⚠️ Important Security Notice</h4>
            <p><strong>You must change your password immediately after your first login for security reasons.</strong></p>
            <p>This temporary password is only valid for your first login session. Please follow these steps:</p>
            <ol>
                <li>Click the login button below</li>
                <li>Enter your email and the temporary password above</li>
                <li>You will be prompted to change your password</li>
                <li>Choose a strong, unique password</li>
            </ol>
        </div>

        <div style="text-align: center;">
            <a href="{{ url('/login') }}" class="login-button">Login to Your Account</a>
        </div>

        <div style="background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4>📋 What You Can Do:</h4>
            <ul>
                <li>Upload CSV files with recipient data</li>
                <li>Process bulk electricity token distributions</li>
                <li>Monitor transaction status and history</li>
                <li>Manage user accounts and permissions</li>
                <li>View detailed reports and analytics</li>
            </ul>
        </div>

        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

        <div class="footer">
            <p><strong>WinIt Prize Distribution</strong></p>
            <p>Email: support@winit.ng | Phone: +234-XXX-XXXX</p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p><small>This email was sent to {{ $user->email }}. If you did not request this account, please contact support immediately.</small></p>
        </div>
    </div>
</body>
</html>
