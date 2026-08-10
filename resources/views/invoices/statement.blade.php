<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Statement — {{ $month }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; color: #1e293b; padding: 40px 20px; }
        .statement { max-width: 800px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #6366f1, #7c3aed, #a855f7); padding: 32px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { color: white; font-size: 22px; font-weight: 700; }
        .header .period { color: rgba(255,255,255,0.8); font-size: 14px; }
        .body { padding: 32px; }
        .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px; }
        .summary-card { background: #f8fafc; border-radius: 10px; padding: 16px; border: 1px solid #e2e8f0; text-align: center; }
        .summary-card .label { font-size: 11px; color: #64748b; margin-bottom: 4px; }
        .summary-card .value { font-size: 18px; font-weight: 700; color: #1e293b; }
        .summary-card.credit .value { color: #059669; }
        .summary-card.debit .value { color: #dc2626; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f1f5f9; color: #475569; font-size: 11px; text-transform: uppercase; padding: 10px 12px; text-align: left; font-weight: 600; }
        td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        .section-title { font-size: 14px; font-weight: 700; color: #6366f1; margin: 20px 0 8px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-credit { background: #d1fae5; color: #059669; }
        .badge-debit { background: #fee2e2; color: #dc2626; }
        .badge-active { background: #dbeafe; color: #2563eb; }
        .badge-confirmed, .badge-completed { background: #d1fae5; color: #059669; }
        .badge-pending { background: #fef3c7; color: #d97706; }
        .footer { text-align: center; padding: 20px; color: #94a3b8; font-size: 12px; border-top: 1px solid #e2e8f0; }
        .print-btn { display: block; width: 200px; margin: 20px auto 0; padding: 12px; background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 14px; }
        @media print { .print-btn { display: none; } body { padding: 0; background: white; } }
    </style>
</head>
<body>
    <div class="statement">
        <div class="header">
            <div>
                <h1>Account Statement</h1>
                <p style="color: rgba(255,255,255,0.8); font-size: 13px; margin-top: 4px;">{{ $user->name }} · {{ $user->email }}</p>
            </div>
            <div class="period">{{ $startDate->format('M d') }} — {{ $endDate->format('M d, Y') }}</div>
        </div>
        <div class="body">
            <!-- Summary -->
            <div class="summary">
                <div class="summary-card credit">
                    <div class="label">Total Deposits</div>
                    <div class="value">${{ number_format($totalDeposits, 2) }}</div>
                </div>
                <div class="summary-card debit">
                    <div class="label">Total Withdrawals</div>
                    <div class="value">${{ number_format($totalWithdrawals, 2) }}</div>
                </div>
                <div class="summary-card credit">
                    <div class="label">Total Credits</div>
                    <div class="value">${{ number_format($totalCredits, 2) }}</div>
                </div>
                <div class="summary-card debit">
                    <div class="label">Total Debits</div>
                    <div class="value">${{ number_format($totalDebits, 2) }}</div>
                </div>
            </div>

            @if($deposits->count() > 0)
            <div class="section-title">Deposits</div>
            <table>
                <thead><tr><th>Date</th><th>Reference</th><th>Method</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($deposits as $d)
                    <tr>
                        <td>{{ $d->created_at->format('M d, H:i') }}</td>
                        <td style="font-family: monospace;">{{ $d->reference }}</td>
                        <td>{{ ucfirst($d->method) }}</td>
                        <td>${{ number_format($d->amount, 2) }}</td>
                        <td><span class="badge badge-{{ $d->status }}">{{ ucfirst($d->status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            @if($withdrawals->count() > 0)
            <div class="section-title">Withdrawals</div>
            <table>
                <thead><tr><th>Date</th><th>Reference</th><th>Method</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($withdrawals as $w)
                    <tr>
                        <td>{{ $w->created_at->format('M d, H:i') }}</td>
                        <td style="font-family: monospace;">{{ $w->reference }}</td>
                        <td>{{ ucfirst($w->method) }}</td>
                        <td>${{ number_format($w->amount, 2) }}</td>
                        <td><span class="badge badge-{{ $w->status }}">{{ ucfirst($w->status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            @if($investments->count() > 0)
            <div class="section-title">Investments</div>
            <table>
                <thead><tr><th>Date</th><th>Package</th><th>Amount</th><th>Expected Return</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($investments as $inv)
                    <tr>
                        <td>{{ $inv->created_at->format('M d') }}</td>
                        <td>{{ $inv->package?->name ?? 'Custom' }}</td>
                        <td>${{ number_format($inv->amount, 2) }}</td>
                        <td>${{ number_format($inv->expected_return, 2) }}</td>
                        <td><span class="badge badge-{{ $inv->status }}">{{ ucfirst($inv->status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            @if($transactions->count() > 0)
            <div class="section-title">All Transactions</div>
            <table>
                <thead><tr><th>Date</th><th>Type</th><th>Reference</th><th>Direction</th><th>Amount</th></tr></thead>
                <tbody>
                    @foreach($transactions as $tx)
                    <tr>
                        <td>{{ $tx->created_at->format('M d, H:i') }}</td>
                        <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $tx->type) }}</td>
                        <td style="font-family: monospace; font-size: 12px;">{{ $tx->reference }}</td>
                        <td><span class="badge badge-{{ $tx->direction }}">{{ $tx->direction }}</span></td>
                        <td style="color: {{ $tx->direction === 'credit' ? '#059669' : '#dc2626' }}; font-weight: 600;">
                            {{ $tx->direction === 'credit' ? '+' : '-' }}${{ number_format($tx->amount, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            @if($deposits->count() === 0 && $withdrawals->count() === 0 && $transactions->count() === 0 && $investments->count() === 0)
            <div style="text-align: center; padding: 40px; color: #94a3b8;">
                <p>No activity for this period.</p>
            </div>
            @endif
        </div>
        <div class="footer">
            © {{ date('Y') }} {{ config('app.name', 'Platform') }} · This statement covers {{ $startDate->format('M d') }} to {{ $endDate->format('M d, Y') }}<br>
            Generated on {{ now()->format('M d, Y \a\t H:i') }}
        </div>
    </div>
    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
</body>
</html>
