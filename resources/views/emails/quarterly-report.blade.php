<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quarterly Token Summary Report - WinIt Prize Distribution</title>
    <style>
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .summary-box {
            background: #f8f9fa;
            border-left: 4px solid rgb(18, 18, 104);
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .summary-item {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
        }
        .summary-label {
            font-weight: 600;
            color: #666;
        }
        .summary-value {
            font-weight: 700;
            color: rgb(18, 18, 104);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: rgb(18, 18, 104);
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background: #10b981;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ url('images/winit-logo-C73aMBts (2).svg') }}" alt="WinIt Logo" width="200" style="display:block; margin:0 auto 20px auto; max-width: 100%; height: auto;">
            <h1>📊 Quarterly Token Summary Report</h1>
            <p>Q{{ $summary['quarter'] }} {{ $summary['year'] }}</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $recipient->name ?? $recipient->customer_name ?? 'Valued Recipient' }},</p>
            
            <p>We are pleased to provide you with your quarterly summary of electricity token distributions from WinIt Prize Distribution.</p>
            
            <div class="summary-box">
                <h2 style="margin-top: 0; color: rgb(18, 18, 104);">Summary</h2>
                <div class="summary-item">
                    <span class="summary-label">Total Tokens Received:</span>
                    <span class="summary-value">{{ $summary['total_tokens'] }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Amount:</span>
                    <span class="summary-value">₦{{ number_format($summary['total_amount'], 2) }}</span>
                </div>
                @if($summary['total_units'] > 0)
                <div class="summary-item">
                    <span class="summary-label">Total Units (KWh):</span>
                    <span class="summary-value">{{ number_format($summary['total_units'], 2) }} KWh</span>
                </div>
                @endif
            </div>
            
            <div class="summary-box">
                <h2 style="margin-top: 0; color: rgb(18, 18, 104);">Meter Information</h2>
                <div class="summary-item">
                    <span class="summary-label">Meter Number:</span>
                    <span class="summary-value">{{ $summary['meter_details']['meter_number'] }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Disco:</span>
                    <span class="summary-value">{{ $summary['meter_details']['disco'] }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Meter Type:</span>
                    <span class="summary-value">{{ ucfirst($summary['meter_details']['meter_type']) }}</span>
                </div>
                @if($summary['meter_details']['address'])
                <div class="summary-item">
                    <span class="summary-label">Address:</span>
                    <span class="summary-value">{{ $summary['meter_details']['address'] }}</span>
                </div>
                @endif
            </div>
            
            <h2 style="color: rgb(18, 18, 104);">Transaction Details</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Token</th>
                        <th>Amount</th>
                        <th>Units</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['transactions'] as $transaction)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}</td>
                        <td><code>{{ $transaction['token'] }}</code></td>
                        <td>₦{{ number_format($transaction['amount'], 2) }}</td>
                        <td>{{ $transaction['units'] ? number_format($transaction['units'], 2) . ' KWh' : 'N/A' }}</td>
                        <td><small>{{ $transaction['reference'] }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <p style="margin-top: 30px;">
                If you have any questions about this report, please contact our support team.
            </p>
            
            <p>
                Thank you for being part of WinIt Prize Distribution!
            </p>
        </div>
        
        <div class="footer">
            <p><strong>WinIt Prize Distribution</strong></p>
            <p>This is an automated report. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
