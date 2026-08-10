@extends('layouts.admin')

@section('page-title', 'Auto-Trade Sessions')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 8px; font-size: 22px;">
        <i class="fas fa-users" style="color: var(--purple-3);"></i> Trading Sessions
    </h2>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">All user auto-trade sessions across the platform.</p>

    <div style="background: var(--bg-card); border-radius: 16px; padding: 20px; border: 1px solid var(--border);">
        <div style="overflow-x: auto;">
            <table class="table" style="color: var(--text); font-size: 12px; margin: 0;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border); color: var(--text-muted); font-size: 10px; text-transform: uppercase;">
                        <th>Reference</th>
                        <th>User</th>
                        <th>Capital</th>
                        <th>Balance</th>
                        <th>Profit</th>
                        <th>Trades</th>
                        <th>Win Rate</th>
                        <th>Status</th>
                        <th>Started</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    <tr style="border-bottom: 1px solid rgba(51,65,85,0.3);">
                        <td style="font-family: monospace; font-size: 10px;">{{ $session->reference }}</td>
                        <td>{{ $session->user->name ?? '—' }}<br><small style="color: var(--text-muted);">{{ $session->user->email ?? '' }}</small></td>
                        <td>${{ number_format($session->allocated_capital, 2) }}</td>
                        <td>${{ number_format($session->current_balance, 2) }}</td>
                        <td style="color: {{ $session->netProfit() >= 0 ? 'var(--green)' : 'var(--red)' }}; font-weight: 600;">
                            {{ $session->netProfit() >= 0 ? '+' : '' }}${{ number_format(abs($session->netProfit()), 2) }}
                        </td>
                        <td>{{ (int)$session->total_trades }}</td>
                        <td>{{ $session->winRate() }}%</td>
                        <td>
                            @if($session->status === 'active')
                            <span class="badge" style="background: rgba(16,185,129,0.15); color: var(--green); font-size: 9px;">ACTIVE</span>
                            @elseif($session->status === 'stopped')
                            <span class="badge" style="background: rgba(245,158,11,0.15); color: var(--amber); font-size: 9px;">STOPPED</span>
                            @else
                            <span class="badge" style="background: rgba(100,116,139,0.15); color: var(--text-muted); font-size: 9px;">{{ strtoupper($session->status) }}</span>
                            @endif
                        </td>
                        <td style="color: var(--text-muted);">{{ $session->created_at->format('M d, H:i') }}</td>
                        <td>
                            @if($session->status === 'active')
                            <form method="POST" action="{{ route('admin.autotrade.force-stop', $session) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm" style="font-size: 10px; padding: 4px 12px; border-radius: 6px;" onclick="return confirm('Force stop this session?')">
                                    <i class="fas fa-stop"></i> Force Stop
                                </button>
                            </form>
                            @else
                            <span style="color: var(--text-dim); font-size: 11px;">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" style="text-align: center; padding: 40px; color: var(--text-muted);">No sessions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $sessions->links() }}</div>
    </div>

    <a href="{{ route('admin.autotrade.index') }}" style="margin-top: 16px; display: inline-block; color: var(--purple-1); text-decoration: none; font-size: 13px;"><i class="fas fa-arrow-left"></i> Back to Settings</a>
</div>
@endsection