@extends('layouts.dashboard')

@section('title', 'Trade History')

@section('content')
<div class="page-content">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2 class="mb-1" style="font-weight:700;color:var(--text)">
                <i class="fas fa-chart-line me-2" style="color:var(--primary)"></i> Trading History
            </h2>
            <p style="color:var(--text-muted);font-size:14px">All your closed positions</p>
        </div>
        <a href="{{ route('dashboard.trade.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Trade
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                <p style="font-size:12px;color:var(--text-muted);margin:0">Total Trades</p>
                <h4 style="font-weight:700;color:var(--text)">{{ $stats['total_trades'] }}</h4>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="rounded-3 p-3" style="background:rgba(16,185,129,0.05);border:1px solid rgba(16,185,129,0.15)">
                <p style="font-size:12px;color:#10b981;margin:0">Wins</p>
                <h4 style="font-weight:700;color:#10b981">{{ $stats['wins'] }}</h4>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="rounded-3 p-3" style="background:rgba(239,68,68,0.05);border:1px solid rgba(239,68,68,0.15)">
                <p style="font-size:12px;color:#ef4444;margin:0">Losses</p>
                <h4 style="font-weight:700;color:#ef4444">{{ $stats['losses'] }}</h4>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="rounded-3 p-3" style="background:rgba(99,102,241,0.05);border:1px solid rgba(99,102,241,0.15)">
                <p style="font-size:12px;color:var(--primary);margin:0">Total P&L</p>
                <h4 style="font-weight:700;color:{{ $stats['total_pnl'] >= 0 ? '#10b981' : '#ef4444' }}">
                    {{ $stats['total_pnl'] >= 0 ? '+' : '' }}${{ number_format(abs($stats['total_pnl']), 2) }}
                </h4>
            </div>
        </div>
    </div>

    {{-- History Table --}}
    <div class="card-custom">
        @if($positions->count() > 0)
        <div style="overflow-x:auto">
            <table class="table table-hover mb-0" style="color:var(--text)">
                <thead>
                    <tr style="color:var(--text-muted);font-size:12px;text-transform:uppercase">
                        <th>Reference</th>
                        <th>Symbol</th>
                        <th>Dir</th>
                        <th>Entry</th>
                        <th>Close</th>
                        <th>Lev</th>
                        <th>Margin</th>
                        <th>P&L</th>
                        <th>Status</th>
                        <th>Closed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($positions as $pos)
                    <tr>
                        <td style="font-size:12px;font-weight:600">{{ $pos->reference }}</td>
                        <td style="font-size:13px;font-weight:600">{{ $pos->symbol }}</td>
                        <td style="font-size:12px">
                            @if($pos->direction === 'buy')<span style="color:#10b981">Long</span>@else<span style="color:#ef4444">Short</span>@endif
                        </td>
                        <td style="font-size:13px">{{ number_format((float)$pos->entry_price, (float)$pos->entry_price < 1 ? 4 : 2) }}</td>
                        <td style="font-size:13px">{{ number_format((float)$pos->close_price, (float)$pos->close_price < 1 ? 4 : 2) }}</td>
                        <td style="font-size:13px">{{ $pos->leverage }}x</td>
                        <td style="font-size:13px">${{ number_format((float)$pos->amount, 2) }}</td>
                        <td style="font-size:13px;font-weight:600">
                            @php $cp = (float)$pos->close_pnl; @endphp
                            <span style="color:{{ $cp >= 0 ? '#10b981' : '#ef4444' }}">{{ $cp >= 0 ? '+' : '' }}${{ number_format(abs($cp), 2) }}</span>
                        </td>
                        <td>
                            @php
                                $sc = [
                                    'closed'     => ['bg' => 'rgba(99,102,241,0.15)', 'color' => '#6366f1'],
                                    'tp_hit'     => ['bg' => 'rgba(16,185,129,0.15)', 'color' => '#10b981'],
                                    'sl_hit'     => ['bg' => 'rgba(239,68,68,0.15)', 'color' => '#ef4444'],
                                    'liquidated' => ['bg' => 'rgba(239,68,68,0.2)', 'color' => '#ef4444'],
                                ][$pos->status] ?? ['bg' => 'rgba(99,102,241,0.15)', 'color' => '#6366f1'];
                            @endphp
                            <span class="badge" style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};font-size:11px">
                                {{ strtoupper(str_replace('_', ' ', $pos->status)) }}
                            </span>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted)">{{ $pos->closed_at?->format('M d, H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-folder-open" style="font-size:48px;color:var(--text-muted);opacity:0.3"></i>
            <p style="color:var(--text-muted);margin-top:12px">No closed trades yet</p>
        </div>
        @endif
    </div>

    {{ $positions->links() }}
</div>
@endsection
