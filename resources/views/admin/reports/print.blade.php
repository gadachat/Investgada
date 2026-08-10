<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} — Admin Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1e293b; padding: 40px; }
        @page { size: A4 landscape; margin: 1cm; }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 3px solid #6366f1; padding-bottom: 12px; }
        .header-left h1 { font-size: 20px; color: #6366f1; }
        .header-left p { font-size: 12px; color: #64748b; margin-top: 4px; }
        .header-right { text-align: right; }
        .header-right .platform { font-size: 16px; font-weight: 700; }
        .header-right .date { font-size: 11px; color: #64748b; margin-top: 4px; }

        .meta { background: #f8fafc; border-radius: 10px; padding: 12px 20px; margin-bottom: 20px; display: flex; gap: 30px; }
        .meta .item .label { color: #64748b; font-size: 10px; text-transform: uppercase; }
        .meta .item .value { color: #1e293b; font-weight: 600; margin-top: 2px; }

        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        thead tr { background: #6366f1; color: white; }
        thead th { padding: 8px 10px; text-align: left; font-weight: 600; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 6px 10px; color: #334155; }

        .footer { margin-top: 25px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; text-align: center; }

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
            <div class="platform">APTrades — Admin</div>
            <div class="date">{{ now()->format('Y-m-d') }}</div>
        </div>
    </div>

    <div class="meta">
        <div class="item"><div class="label">Report Type</div><div class="value">{{ $title }}</div></div>
        <div class="item"><div class="label">Records</div><div class="value">{{ count($rows) }}</div></div>
        <div class="item"><div class="label">Generated</div><div class="value">{{ $date }}</div></div>
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
        APTrades Admin Report — Generated {{ $date }}
    </div>

    <script>
        window.onload = function() { setTimeout(function() { window.print(); }, 500); };
    </script>
</body>
</html>
