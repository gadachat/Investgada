<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investment Receipt</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; color: #1e293b; padding: 40px 20px; }
        .invoice { max-width: 700px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #6366f1, #7c3aed, #a855f7); padding: 32px; text-align: center; }
        .header h1 { color: white; font-size: 24px; font-weight: 700; }
        .body { padding: 32px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 12px; }
        .label { color: #64748b; font-size: 13px; }
        .value { color: #1e293b; font-size: 14px; font-weight: 600; }
        .amount-box { background: #f8fafc; border-radius: 12px; padding: 20px; text-align: center; margin: 20px 0; border: 1px solid #e2e8f0; }
        .amount-box .label { font-size: 12px; margin-bottom: 4px; }
        .amount-box .amount { font-size: 32px; font-weight: 800; color: #6366f1; }
        .progress-section { margin: 20px 0; }
        .progress-bar { width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(135deg, #6366f1, #7c3aed); border-radius: 4px; }
        .divider { border: none; border-top: 1px dashed #cbd5e1; margin: 24px 0; }
        .footer { text-align: center; padding: 24px; color: #94a3b8; font-size: 12px; border-top: 1px solid #e2e8f0; }
        .print-btn { display: block; width: 200px; margin: 20px auto 0; padding: 12px; background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 14px; }
        @media print { .print-btn { display: none; } body { padding: 0; background: white; } }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <h1>Investment Receipt</h1>
            <p style="color: rgba(255,255,255,0.8); font-size: 13px; margin-top: 4px;">{{ config('app.name', 'Platform') }} · {{ now()->format('F d, Y') }}</p>
        </div>
        <div class="body">
            <div class="row">
                <span class="label">Reference</span>
                <span class="value" style="font-family: monospace;">{{ $investment->reference }}</span>
            </div>
            <div class="row">
                <span class="label">Date Activated</span>
                <span class="value">{{ $investment->activated_at?->format('M d, Y') ?? $investment->created_at->format('M d, Y') }}</span>
            </div>
            <div class="row">
                <span class="label">Investor</span>
                <span class="value">{{ $user->name }}</span>
            </div>
            <div class="row">
                <span class="label">Package</span>
                <span class="value">{{ $package?->name ?? 'Custom Package' }}</span>
            </div>
            <div class="row">
                <span class="label">Category</span>
                <span class="value">{{ strtoupper($package?->category ?? 'N/A') }}</span>
            </div>
            <div class="row">
                <span class="label">Status</span>
                <span class="value" style="color: {{ $investment->status === 'active' ? '#059669' : '#64748b' }};">{{ ucfirst($investment->status) }}</span>
            </div>

            <hr class="divider">

            <div class="row">
                <span class="label">Invested Amount</span>
                <span class="value">${{ number_format($investment->amount, 2) }}</span>
            </div>
            <div class="row">
                <span class="label">Expected Return</span>
                <span class="value" style="color: #059669;">${{ number_format($investment->expected_return, 2) }}</span>
            </div>
            <div class="row">
                <span class="label">Earned So Far</span>
                <span class="value" style="color: #6366f1;">${{ number_format($investment->earned_so_far, 2) }}</span>
            </div>

            <div class="amount-box">
                <div class="label">Net Profit Expected</div>
                <div class="amount">${{ number_format($investment->expected_return - $investment->amount, 2) }}</div>
            </div>

            @php $progress = $investment->expected_return > 0 ? min(100, ($investment->earned_so_far / $investment->expected_return) * 100) : 0; @endphp
            <div class="progress-section">
                <div class="row" style="margin-bottom: 6px;">
                    <span class="label">Progress</span>
                    <span class="value">{{ number_format($progress, 1) }}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $progress }}%;"></div>
                </div>
            </div>

            <hr class="divider">

            <p style="font-size: 12px; color: #94a3b8; line-height: 1.6; text-align: center;">
                This receipt confirms your investment in {{ $package?->name ?? 'the selected package' }}.<br>
                Generated on {{ now()->format('M d, Y \a\t H:i') }}.
            </p>
        </div>
        <div class="footer">
            © {{ date('Y') }} {{ config('app.name', 'Platform') }} · All rights reserved
        </div>
    </div>
    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
</body>
</html>
