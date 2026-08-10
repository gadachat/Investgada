<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; color: #1e293b; padding: 40px 20px; }
        .invoice { max-width: 700px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #6366f1, #7c3aed, #a855f7); padding: 32px; text-align: center; }
        .header h1 { color: white; font-size: 24px; font-weight: 700; }
        .header p { color: rgba(255,255,255,0.8); font-size: 13px; margin-top: 4px; }
        .body { padding: 32px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 12px; }
        .label { color: #64748b; font-size: 13px; }
        .value { color: #1e293b; font-size: 14px; font-weight: 600; }
        .amount-box { background: #f8fafc; border-radius: 12px; padding: 20px; text-align: center; margin: 20px 0; border: 1px solid #e2e8f0; }
        .amount-box .label { font-size: 12px; margin-bottom: 4px; }
        .amount-box .amount { font-size: 36px; font-weight: 800; color: #6366f1; }
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; text-transform: capitalize; }
        .status-confirmed, .status-completed, .status-active { background: #d1fae5; color: #059669; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-rejected, .status-failed { background: #fee2e2; color: #dc2626; }
        .divider { border: none; border-top: 1px dashed #cbd5e1; margin: 24px 0; }
        .footer { text-align: center; padding: 24px; color: #94a3b8; font-size: 12px; border-top: 1px solid #e2e8f0; }
        .print-btn { display: block; width: 200px; margin: 20px auto 0; padding: 12px; background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 14px; }
        @media print { .print-btn { display: none; } body { padding: 0; background: white; } }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <h1>{{ $title }}</h1>
            <p>{{ config('app.name', 'Platform') }} · {{ now()->format('F d, Y') }}</p>
        </div>
        <div class="body">
            <div class="row">
                <span class="label">Reference</span>
                <span class="value" style="font-family: monospace;">{{ $reference }}</span>
            </div>
            <div class="row">
                <span class="label">Date</span>
                <span class="value">{{ $date }}</span>
            </div>
            <div class="row">
                <span class="label">Account Holder</span>
                <span class="value">{{ $user->name }}</span>
            </div>
            <div class="row">
                <span class="label">Email</span>
                <span class="value">{{ $user->email }}</span>
            </div>
            <div class="row">
                <span class="label">Payment Method</span>
                <span class="value">{{ ucfirst($method) }}</span>
            </div>
            <div class="row">
                <span class="label">Status</span>
                <span class="status-badge status-{{ $status }}">{{ $status }}</span>
            </div>

            <hr class="divider">

            <div class="amount-box">
                <div class="label">Amount</div>
                <div class="amount">${{ number_format($amount, 2) }}</div>
            </div>

            <hr class="divider">

            <p style="font-size: 12px; color: #94a3b8; line-height: 1.6; text-align: center;">
                This is a system-generated receipt. For questions about this transaction, please contact support.<br>
                Generated on {{ now()->format('M d, Y \a\t H:i') }} ({{ config('app.timezone', 'UTC') }}).
            </p>
        </div>
        <div class="footer">
            © {{ date('Y') }} {{ config('app.name', 'Platform') }} · All rights reserved<br>
            This document is computer-generated and does not require a signature.
        </div>
    </div>
    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
</body>
</html>
