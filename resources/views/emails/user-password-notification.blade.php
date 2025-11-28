<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your WinIt Prize Distribution Account Credentials</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #1f2937;
        }
        @media screen and (max-width: 600px) {
            .wrapper {
                width: 100% !important;
            }
            .content {
                padding: 24px !important;
            }
        }
        a {
            color: #122168;
        }
    </style>
</head>
<body>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fb; padding: 24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" class="wrapper" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 12px 30px rgba(18,33,104,0.08);">
                    <tr>
                        <td style="padding:32px 32px 24px 32px; text-align:center; background: #0a1628;">
                            <img src="{{ url('images/winit-logo.svg') }}" alt="WinIt Logo" width="200" style="display:block; margin:0 auto 16px auto;">
                            <h1 style="margin:0; font-size:22px; font-weight:700; color:#ffffff;">WinIt Prize Distribution</h1>
                            <p style="margin:8px 0 0 0; font-size:14px; color:rgba(255,255,255,0.85);">Secure access credentials</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="content" style="padding:32px 40px;">
                            <h2 style="margin:0 0 12px 0; font-size:20px; color:#122168;">Hello {{ $user->name }},</h2>
                            <p style="margin:0 0 20px 0; line-height:1.6;">Welcome to <strong>WinIt Prize Distribution</strong>. Your account is now active. Use the details below to sign in and finish setting up your profile.</p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f6ff; border:1px solid rgba(18,33,104,0.12); border-radius:12px;">
                                <tr>
                                    <td style="padding:18px 20px; border-bottom:1px solid rgba(18,33,104,0.08);">
                                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; margin-bottom:6px;">Login Email</div>
                                        <div style="font-family:'Courier New', monospace; font-size:15px; font-weight:600; color:#1f2937; word-break:break-all;">{{ $user->email }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280; margin-bottom:6px;">Temporary Password</div>
                                        <div style="font-family:'Courier New', monospace; font-size:15px; font-weight:600; color:#dc2626;">{{ $password }}</div>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin:24px 0; padding:18px; border-radius:10px; background-color:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.35);">
                                <strong style="display:block; color:#b45309; margin-bottom:6px;">Security Reminder</strong>
                                <span style="font-size:14px; line-height:1.6; color:#78350f;">This password is valid only once. You will be prompted to set a new password immediately after logging in.</span>
                            </div>

                            <div style="margin:0 0 18px 0;">
                                <a href="{{ $loginUrl }}" style="display:inline-block; padding:14px 26px; background: linear-gradient(135deg, #122168 0%, #1f2f7a 100%); color:#ffffff; font-weight:600; font-size:15px; border-radius:40px; text-decoration:none; box-shadow:0 8px 16px rgba(18,33,104,0.25);">Sign in to WinIt</a>
                            </div>

                            <p style="margin:0 0 16px 0; font-size:14px; line-height:1.6;">If the button above doesn’t work, copy and paste this link into your browser:</p>
                            <p style="margin:0 0 20px 0; font-size:13px; color:#4b5563; word-break:break-all;"><a href="{{ $loginUrl }}" style="color:#122168;">{{ $loginUrl }}</a></p>

                            <h3 style="margin:0 0 12px 0; font-size:16px; color:#122168;">Next steps</h3>
                            <ol style="margin:0 0 20px 20px; padding:0; font-size:14px; line-height:1.7; color:#1f2937;">
                                <li>Log in with the email and temporary password above.</li>
                                <li>Follow the prompt to create a new, strong password.</li>
                                <li>Complete your profile and review your permissions.</li>
                            </ol>

                            <h3 style="margin:0 0 12px 0; font-size:16px; color:#122168;">Password tips</h3>
                            <ul style="margin:0; padding-left:18px; font-size:14px; line-height:1.7; color:#1f2937;">
                                <li>Use at least 8 characters with numbers and symbols.</li>
                                <li>Avoid reusing passwords from other accounts.</li>
                                <li>Never share your credentials with anyone.</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:22px 32px; background-color:#f5f7fb; text-align:center; font-size:12px; line-height:1.6; color:#6b7280;">
                            <strong style="display:block; color:#122168;">WinIt Prize Distribution</strong>
                            <span>Automated notification – please do not reply.</span><br>
                            <span>If you didn’t expect this email, contact your administrator immediately.</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
