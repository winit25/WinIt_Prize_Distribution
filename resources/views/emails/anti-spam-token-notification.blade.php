<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electricity Token - WinIt Prize Distribution</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .token-box {
            background-color: #f8f9fa;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .token {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            letter-spacing: 2px;
            margin: 10px 0;
        }
        .details {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px solid #dee2e6;
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
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .success-badge {
            background-color: #d4edda;
            color: #155724;
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
        }
        .instructions {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">⚡ WinIt Prize Distribution</div>
            <h1>Your Electricity Token is Ready!</h1>
        </div>

        <div class="success-badge">
            ✅ Token Generated Successfully
        </div>

        <p>Dear <strong>{{ $recipient->name }}</strong>,</p>

        <p>Congratulations! Your electricity token has been successfully generated and is ready for use.</p>

        @if($transaction->token)
        <div class="token-box">
            <h3>Your Electricity Token</h3>
            <div class="token">{{ $transaction->token }}</div>
            <p><em>Please enter this token into your prepaid meter</em></p>
        </div>
        @endif

        <div class="details">
            <h3>Transaction Details</h3>
            <div class="detail-row">
                <span class="label">Recipient Name:</span>
                <span class="value">{{ $recipient->name }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Phone Number:</span>
                <span class="value">{{ $transaction->phone_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Meter Number:</span>
                <span class="value">{{ $recipient->meter_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Distribution Company:</span>
                <span class="value">{{ $recipient->disco }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Meter Type:</span>
                <span class="value">{{ ucfirst($recipient->meter_type) }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Amount:</span>
                <span class="value">₦{{ number_format($transaction->amount, 2) }}</span>
            </div>
            @if($transaction->units)
            <div class="detail-row">
                <span class="label">Units:</span>
                <span class="value">{{ $transaction->units }} KWh</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="label">Transaction Reference:</span>
                <span class="value">{{ $transaction->buypower_reference }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Date Generated:</span>
                <span class="value">{{ $transaction->processed_at ? $transaction->processed_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        @if($messageContent)
        <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4>Additional Message:</h4>
            <p style="white-space: pre-line;">{{ $messageContent }}</p>
        </div>
        @endif

        <div class="instructions">
            <h4>📋 How to Use Your Token:</h4>
            <ol>
                <li>Go to your prepaid electricity meter</li>
                <li>Press the "Enter" or "OK" button</li>
                <li>Enter the token: <strong>{{ $transaction->token ?? 'Your token' }}</strong></li>
                <li>Press "Enter" or "OK" to confirm</li>
                <li>Your meter will display the units and credit</li>
            </ol>
        </div>

        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

        <div class="footer">
            <p><strong>WinIt Prize Distribution</strong></p>
            <p>Email: support@winit.ng | Phone: +234-XXX-XXXX</p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p><small>This email was sent to {{ $recipient->email }}. If you did not request this token, please contact support immediately.</small></p>
        </div>
    </div>
</body>
</html>
