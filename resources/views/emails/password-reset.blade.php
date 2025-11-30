<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 10px;
            background-color: #f5f5f5;
        }
        .email-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .logo-container {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #122168 0%, #1a2d7a 100%);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            padding: 12px;
        }
        .logo-container img {
            max-width: 85%;
            height: auto;
        }
        h1 {
            color: #122168;
            margin: 0 0 6px 0;
            font-size: 22px;
        }
        .subtitle {
            color: #666;
            margin: 0;
            font-size: 13px;
        }
        .content {
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #122168 0%, #1a2d7a 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 16px 0;
            text-align: center;
            border: 2px solid #122168;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .button:hover {
            background: linear-gradient(135deg, #1a2d7a 0%, #223688 100%);
            color: #ffffff !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        .link-text {
            word-break: break-all;
            color: #122168;
            font-size: 11px;
            margin-top: 12px;
            padding: 12px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 11px;
            color: #999;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            margin: 16px 0;
            border-radius: 5px;
        }
        .warning strong {
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo-container" style="background: transparent; padding: 20px;">
                <img src="{{ asset('images/winit-logo-C73aMBts (2).svg') }}" alt="WinIt Logo" width="180" style="display:block; margin:0 auto; max-width: 100%; height: auto;">
            </div>
            <h1>Reset Your Password</h1>
            <p class="subtitle">WinIt Prize Distribution</p>
        </div>

        <div class="content">
            <p style="margin: 0 0 12px 0;">Hello {{ $user->name ?? 'User' }},</p>
            
            <p style="margin: 0 0 16px 0;">You are receiving this email because we received a password reset request for your account.</p>
            
            <div style="text-align: center; margin: 20px 0;">
                <a href="{{ $resetUrl }}" class="button" style="display:inline-block; padding:14px 32px; background-color:#122168; background: linear-gradient(135deg, #122168 0%, #1a2d7a 100%); color:#ffffff !important; text-decoration:none; border-radius:8px; font-weight:600; font-size:16px; margin:16px 0; text-align:center; border:2px solid #122168; box-shadow:0 2px 4px rgba(0,0,0,0.2);"><span style="color:#ffffff !important;">Reset Password</span></a>
            </div>
            
            <p style="margin: 0 0 16px 0; font-size: 13px;">This password reset link will expire in 60 minutes.</p>
            
            <div class="warning">
                <strong>⚠️ Security Notice:</strong> If you did not request a password reset, no further action is required. Your password will remain unchanged.
            </div>
            
            <p style="margin: 0 0 8px 0; font-size: 13px;">If the button above doesn't work, copy and paste the following link into your browser:</p>
            <div class="link-text">{{ $resetUrl }}</div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} WinIt Prize Distribution. All rights reserved.</p>
            <p>This is an automated email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>

