<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
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
                            <img src="{{ config('app.url') }}/images/winit-logo-C73aMBts%20(2).svg" alt="WinIt Logo" width="200" style="display:block; margin:0 auto 16px auto; max-width: 100%; height: auto;">
                            <h1 style="margin:0; font-size:22px; font-weight:700; color:#ffffff;">WinIt Prize Distribution</h1>
                            <p style="margin:8px 0 0 0; font-size:14px; color:rgba(255,255,255,0.85);">Batch Processing Notification</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="content" style="padding:32px 40px;">
                            <h2 style="margin:0 0 12px 0; font-size:20px; color:#122168;">Hello {{ $user->name }},</h2>
                            
                            @if($type === 'success')
                                <div style="background-color:#d1fae5; border-left:4px solid #10b981; padding:16px; margin:20px 0; border-radius:8px;">
                                    <p style="margin:0; color:#065f46; font-weight:600;">✅ Batch Processing Completed Successfully!</p>
                                </div>
                            @else
                                <div style="background-color:#fee2e2; border-left:4px solid #ef4444; padding:16px; margin:20px 0; border-radius:8px;">
                                    <p style="margin:0; color:#991b1b; font-weight:600;">❌ Batch Processing Failed</p>
                                </div>
                            @endif

                            <div style="background-color:#f3f6ff; border:1px solid rgba(18,33,104,0.12); border-radius:12px; padding:20px; margin:20px 0;">
                                <div style="font-size:14px; line-height:1.8; color:#1f2937;">
                                    {!! $message !!}
                                </div>
                            </div>

                            @if(isset($batch_id) && $batch_id)
                            <div style="margin:30px 0; text-align:center;">
                                <a href="{{ url('/bulk-token/show/' . $batch_id) }}" style="display:inline-block; background-color:#122168; color:#ffffff !important; text-decoration:none; padding:14px 32px; border-radius:8px; font-weight:600; font-size:16px; border:2px solid #122168; box-shadow:0 2px 4px rgba(0,0,0,0.2); text-align:center; line-height:1.5;"><span style="color:#ffffff !important;">View Batch Details</span></a>
                            </div>
                            @endif

                            <p style="margin:30px 0 0 0; font-size:14px; color:#6b7280; line-height:1.6;">
                                If you have any questions or need assistance, please contact our support team.
                            </p>

                            <hr style="border:none; border-top:1px solid #e5e7eb; margin:30px 0;">
                            
                            <p style="margin:0; font-size:12px; color:#9ca3af; text-align:center;">
                                This is an automated notification from WinIt Prize Distribution System.<br>
                                Please do not reply to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

