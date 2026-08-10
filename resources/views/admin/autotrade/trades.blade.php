@extends('layouts.admin')

@section('page-title', 'All Trades')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 8px; font-size: 22px;">
        <i class="fas fa-list" style="color: var(--purple-3);"></i> All Auto-Trades
    </h2>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Platform-wide trade log.</p>

    <div style="background: var(--bg-card); border-radius: 16px; padding: 20px; border: 1px solid var(--border);">
        <div style="overflow-x: auto;">
            <table class="table" style="color: var(--text); font-size: 12px; margin: 0;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border); color: var(--text-muted); font-size: 10px; text-transform: uppercase;">
                        <th>Reference</th>
                        <th>User</th>
                        <th>Pair</th>
                        <th>Dir</th>
                        <th>Amount</th>
                        <th>Profit</th>
                        <th>Result</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trades as $trade)
                    <tr style="border-bottom: 1px solid rgba(51,65,85,0.3);">
                        <td style="font-family: monospace; font-size: 10px;">{{ $trade->reference }}</td>
                        <td>{{ $trade->user->name ?? '—' }}</td>
                        <td><strong>{{ $trade->pair }}</strong></td>
                        <td>
                            @if($trade->direction === 'buy')
                            <span style="color: var(--green);">BUY</span>
                            @else
                            <span style="color: var(--red);">SELL</span>
                            @endif
                        </td>
                        <td>${{ number_format($trade->amount, 2) }}</td>
                        <td style="color: {{ $trade->profit >= 0 ? 'var(--green)' : 'var(--red)' }}; font-weight: 600;">
                            {{ $trade->profit >= 0 ? '+' : '' }}${{ number_format($trade->profit, 2) }}
                        </td>
                        <td>
                            @if($trade->is_win)
                            <span class="badge" style="background: rgba(16,185,129,0.15); color: var(--green); font-size: 9px;">WIN</span>
                            @else
                            <span class="badge" style="background: rgba(239,68,68,0.15); color: var(--red); font-size: 9px;">LOSS</span>
                            @endif
                        </td>
                        <td><span style="font-size: 10px; color: var(--text-muted);">{{ strtoupper($trade->status) }}</span></td>
                        <td style="color: var(--text-muted);">{{ $trade->created_at->format('M d, H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">No trades found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $trades->links() }}</div>
    </div>

    <a href="{{ route('admin.autotrade.index') }}" style="margin-top: 16px; display: inline-block; color: var(--purple-1); text-decoration: none; font-size: 13px;"><i class="fas fa-arrow-left"></i> Back to Settings</a>
</div>
@endsection