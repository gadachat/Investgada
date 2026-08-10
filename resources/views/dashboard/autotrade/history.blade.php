@extends('layouts.dashboard')

@section('title', 'Trade History')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-weight: 700; margin: 0; font-size: 22px;"><i class="fas fa-history" style="color: var(--purple-3);"></i> Trade History</h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 4px 0 0;">All your auto-trade records.</p>
        </div>
        <a href="{{ route('dashboard.autotrade.index') }}" style="font-size: 13px; color: var(--purple-1); text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Auto Trade</a>
    </div>

    <!-- Filters -->
    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 4px;">Pair</label>
                <select name="pair" class="form-select form-select-sm" style="background: var(--bg-card); border: 1px solid var(--border); color: var(--text); font-size: 12px;">
                    <option value="all">All Pairs</option>
                    @foreach($pairs as $pair)
                    <option value="{{ $pair }}" {{ request('pair') === $pair ? 'selected' : '' }}>{{ $pair }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 4px;">Status</label>
                <select name="status" class="form-select form-select-sm" style="background: var(--bg-card); border: 1px solid var(--border); color: var(--text); font-size: 12px;">
                    <option value="all">All</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                </select>
            </div>
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 4px;">Result</label>
                <select name="result" class="form-select form-select-sm" style="background: var(--bg-card); border: 1px solid var(--border); color: var(--text); font-size: 12px;">
                    <option value="all">All</option>
                    <option value="win" {{ request('result') === 'win' ? 'selected' : '' }}>Wins Only</option>
                    <option value="loss" {{ request('result') === 'loss' ? 'selected' : '' }}>Losses Only</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm" style="background: linear-gradient(135deg, var(--purple-1), var(--purple-2)); color: white; border-radius: 8px; padding: 6px 20px; border: none; font-size: 12px;">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>
    </form>

    <!-- Trades Table -->
    <div style="background: var(--bg-card); border-radius: 16px; padding: 20px; border: 1px solid var(--border);">
        <div style="overflow-x: auto;">
            <table class="table" style="color: var(--text); font-size: 12px; margin: 0;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border); color: var(--text-muted); font-size: 10px; text-transform: uppercase;">
                        <th>Reference</th>
                        <th>Pair</th>
                        <th>Dir</th>
                        <th>Amount</th>
                        <th>Entry</th>
                        <th>Exit</th>
                        <th>Profit</th>
                        <th>%</th>
                        <th>Result</th>
                        <th>Duration</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trades as $trade)
                    <tr style="border-bottom: 1px solid rgba(51,65,85,0.3);">
                        <td style="font-family: monospace; font-size: 10px;">{{ $trade->reference }}</td>
                        <td><strong>{{ $trade->pair }}</strong></td>
                        <td>
                            @if($trade->direction === 'buy')
                            <span style="color: var(--green);">BUY</span>
                            @else
                            <span style="color: var(--red);">SELL</span>
                            @endif
                        </td>
                        <td>${{ number_format($trade->amount, 2) }}</td>
                        <td>${{ number_format($trade->entry_price, $trade->entry_price < 1 ? 4 : 2) }}</td>
                        <td>@if($trade->exit_price) ${{ number_format($trade->exit_price, $trade->exit_price < 1 ? 4 : 2) }} @else — @endif</td>
                        <td style="color: {{ $trade->profit >= 0 ? 'var(--green)' : 'var(--red)' }}; font-weight: 600;">
                            {{ $trade->profit >= 0 ? '+' : '' }}${{ number_format($trade->profit, 2) }}
                        </td>
                        <td style="color: {{ $trade->profit >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ $trade->profit_pct >= 0 ? '+' : '' }}{{ $trade->profit_pct }}%</td>
                        <td>
                            @if($trade->is_win)
                            <span class="badge" style="background: rgba(16,185,129,0.15); color: var(--green); font-size: 9px;">WIN</span>
                            @else
                            <span class="badge" style="background: rgba(239,68,68,0.15); color: var(--red); font-size: 9px;">LOSS</span>
                            @endif
                        </td>
                        <td style="color: var(--text-muted);">{{ $trade->duration_seconds }}s</td>
                        <td style="color: var(--text-muted);">{{ $trade->created_at->format('M d, H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="11" style="text-align: center; padding: 40px; color: var(--text-muted);">No trades found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $trades->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection