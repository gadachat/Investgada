<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} — {{ $user->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1e293b; padding: 40px; }
        @page { size: A4; margin: 1.5cm; }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 3px solid #6366f1; padding-bottom: 15px; }
        .header-left h1 { font-size: 20px; color: #6366f1; }
        .header-left p { font-size: 12px; color: #64748b; margin-top: 4px; }
        .header-right { text-align: right; }
        .header-right .platform { font-size: 16px; font-weight: 700; color: #1e293b; }
        .header-right .date { font-size: 11px; color: #64748b; margin-top: 4px; }

        .user-info { background: #f8fafc; border-radius: 10px; padding: 15px 20px; margin-bottom: 25px; display: flex; gap: 30px; }
        .user-info .item { font-size: 12px; }
        .user-info .item .label { color: #64748b; font-size: 10px; text-transform: uppercase; }
        .user-info .item .value { color: #1e293b; font-weight: 600; margin-top: 2px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        thead tr { background: #6366f1; color: white; }
        thead th { padding: 10px 12px; text-align: left; font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 8px 12px; color: #334155; }

        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; text-align: center; }

        .print-btn { position: fixed; top: 20px; right: 20px; background: #6366f1; color: white; border: none; padding: 12px 24px; border-radius: 10px; font-size: 14px; cursor: pointer; font-weight: 600; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Save as PDF</button>

    <div class="header">
        <div class="header-left">
            <h1>{{ $title }}</h1>
            <p>Generated on {{ $date }}</p>
        </div>
        <div class="header-right">
            <div class="platform">APTrades</div>
            <div class="date">{{ now()->format('Y-m-d') }}</div>
        </div>
    </div>

    <div class="user-info">
        <div class="item"><div class="label">Account Holder</div><div class="value">{{ $user->name }}</div></div>
        <div class="item"><div class="label">Email</div><div class="value">{{ $user->email }}</div></div>
        <div class="item"><div class="label">Reference Code</div><div class="value">{{ $user->referral_code }}</div></div>
        <div class="item"><div class="label">Records</div><div class="value">{{ count($rows) }}</div></div>
    </div>

    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;"><table>
        <thead>
            <tr>
                @foreach($headers as $header)
                <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                @foreach($row as $cell)
                <td>{{ $cell }}</td>
                @endforeach
            </tr>
            @empty
            <tr><td colspan="{{ count($headers) }}" style="text-align:center; padding:30px; color:#94a3b8;">No records found.</td></tr>
            @endforelse
        </tbody>
    </table></div>

    <div class="footer">
        This is a computer-generated document from APTrades Platform.<br>
        Generated on {{ $date }} for {{ $user->name }} ({{ $user->email }})
    </div>

    <script>
        window.onload = function() { setTimeout(function() { window.print(); }, 500); };
    </script>
</body>
</html>
