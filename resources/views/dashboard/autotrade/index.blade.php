@extends('layouts.dashboard')

@section('title', 'Auto Trading')

@section('content')
<div class="fade-in">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 style="font-weight: 700; margin: 0; font-size: 22px;"><i class="fas fa-robot" style="color: var(--purple-3);"></i> Auto Trading</h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 4px 0 0;">Automated trading with admin-configured daily profit rates.</p>
        </div>
        <div>
            @if($activeSession)
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#stopModal" style="border-radius: 10px; font-size: 13px; padding: 10px 24px;">
                <i class="fas fa-stop-circle"></i> Stop Trading
            </button>
            @else
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#startModal" style="border-radius: 10px; font-size: 13px; padding: 10px 24px; background: var(--green); border: none;">
                <i class="fas fa-play-circle"></i> Start Auto Trading
            </button>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: var(--green); border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 13px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: var(--red); border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 13px;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div style="background: var(--bg-card); border-radius: 16px; padding: 20px; border: 1px solid var(--border);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p style="font-size: 11px; color: var(--text-muted); margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Total Profit</p>
                        <h3 style="font-weight: 700; margin: 6px 0 0; color: var(--green);">${{ number_format($totalProfit, 2) }}</h3>
                    </div>
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(16,185,129,0.1); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-arrow-trend-up" style="color: var(--green); font-size: 18px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div style="background: var(--bg-card); border-radius: 16px; padding: 20px; border: 1px solid var(--border);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p style="font-size: 11px; color: var(--text-muted); margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Total Trades</p>
                        <h3 style="font-weight: 700; margin: 6px 0 0;">{{ $totalTrades }}</h3>
                    </div>
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(99,102,241,0.1); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-arrows-rotate" style="color: var(--purple-1); font-size: 18px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div style="background: var(--bg-card); border-radius: 16px; padding: 20px; border: 1px solid var(--border);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p style="font-size: 11px; color: var(--text-muted); margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Win Rate</p>
                        <h3 style="font-weight: 700; margin: 6px 0 0;">{{ $winRate }}%</h3>
                    </div>
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(168,85,247,0.1); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-trophy" style="color: var(--purple-3); font-size: 18px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div style="background: var(--bg-card); border-radius: 16px; padding: 20px; border: 1px solid var(--border);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p style="font-size: 11px; color: var(--text-muted); margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Wallet Balance</p>
                        <h3 style="font-weight: 700; margin: 6px 0 0;">${{ number_format($depositWallet?->balance ?? 0, 2) }}</h3>
                    </div>
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(59,130,246,0.1); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-wallet" style="color: var(--blue-1); font-size: 18px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($activeSession)
    <!-- Active Session Panel -->
    <div style="background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(168,85,247,0.08)); border: 1px solid rgba(99,102,241,0.2); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <span class="badge" style="background: rgba(16,185,129,0.15); color: var(--green); font-size: 11px; padding: 6px 12px; border-radius: 20px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--green); margin-right: 6px; animation: pulse 2s infinite;"></span>
                    LIVE
                </span>
                <span style="color: var(--text-muted); font-size: 12px; margin-left: 12px;">Session: {{ $activeSession->reference }}</span>
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">Started: {{ $activeSession->started_at->diffForHumans() }}</div>
        </div>

        <div class="row g-3">
            <div class="col-md-3 col-sm-6">
                <p style="font-size: 11px; color: var(--text-muted); margin: 0;">Allocated Capital</p>
                <h4 style="font-weight: 700; margin: 4px 0 0;">${{ number_format($activeSession->allocated_capital, 2) }}</h4>
            </div>
            <div class="col-md-3 col-sm-6">
                <p style="font-size: 11px; color: var(--text-muted); margin: 0;">Current Balance</p>
                <h4 style="font-weight: 700; margin: 4px 0 0; color: {{ $activeSession->current_balance >= $activeSession->allocated_capital ? 'var(--green)' : 'var(--text-bright)' }};">${{ number_format($activeSession->current_balance, 2) }}</h4>
            </div>
            <div class="col-md-3 col-sm-6">
                <p style="font-size: 11px; color: var(--text-muted); margin: 0;">Net Profit</p>
                <h4 style="font-weight: 700; margin: 4px 0 0; color: {{ $activeSession->netProfit() >= 0 ? 'var(--green)' : 'var(--red)' }};">
                    {{ $activeSession->netProfit() >= 0 ? '+' : '' }}${{ number_format(abs($activeSession->netProfit()), 2) }}
                </h4>
            </div>
            <div class="col-md-3 col-sm-6">
                <p style="font-size: 11px; color: var(--text-muted); margin: 0;">Trades (W/L)</p>
                <h4 style="font-weight: 700; margin: 4px 0 0;">
                    {{ (int)$activeSession->total_trades }}
                    <span style="font-size: 14px; color: var(--text-muted);">({{ (int)$activeSession->winning_trades }}/{{ (int)$activeSession->losing_trades }})</span>
                </h4>
            </div>
        </div>

        <div class="mt-3" style="font-size: 12px; color: var(--text-muted);">
            <i class="fas fa-chart-line"></i> Pairs: {{ implode(', ', $activeSession->selected_pairs ?? []) }}
            @if($activeSession->next_trade_at)
            <span style="margin-left: 16px;"><i class="fas fa-clock"></i> Next trade: {{ $activeSession->next_trade_at->diffForHumans() }}</span>
            @endif
        </div>
    </div>

    <style>
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    </style>
    @endif

    <!-- Recent Trades -->
    <div style="background: var(--bg-card); border-radius: 16px; padding: 24px; border: 1px solid var(--border); margin-bottom: 24px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="font-weight: 700; margin: 0; font-size: 16px;"><i class="fas fa-list" style="color: var(--purple-3);"></i> Recent Trades</h5>
            <a href="{{ route('dashboard.autotrade.history') }}" style="font-size: 12px; color: var(--purple-1); text-decoration: none;">View All →</a>
        </div>

        @if($recentTrades->isEmpty())
        <div style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
            <i class="fas fa-inbox" style="font-size: 32px; opacity: 0.3; margin-bottom: 12px; display: block;"></i>
            <p style="font-size: 14px;">No trades yet. Start auto-trading to see live trades here.</p>
        </div>
        @else
        <div style="overflow-x: auto;">
            <table class="table" style="color: var(--text); font-size: 13px; margin: 0;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border); color: var(--text-muted); font-size: 11px; text-transform: uppercase;">
                        <th>Pair</th>
                        <th>Direction</th>
                        <th>Amount</th>
                        <th>Entry</th>
                        <th>Exit</th>
                        <th>Profit</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody id="live-trades-body">
                    @foreach($recentTrades as $trade)
                    <tr style="border-bottom: 1px solid rgba(51,65,85,0.3);" data-trade-id="{{ $trade->id }}">
                        <td>
                            <span style="font-weight: 600;">{{ $trade->pair }}</span>
                            <span style="font-size: 10px; color: var(--text-dim); margin-left: 4px;">{{ $trade->category }}</span>
                        </td>
                        <td>
                            @if($trade->direction === 'buy')
                            <span style="color: var(--green);"><i class="fas fa-arrow-up"></i> BUY</span>
                            @else
                            <span style="color: var(--red);"><i class="fas fa-arrow-down"></i> SELL</span>
                            @endif
                        </td>
                        <td>${{ number_format($trade->amount, 2) }}</td>
                        <td>${{ number_format($trade->entry_price, $trade->entry_price < 1 ? 4 : 2) }}</td>
                        <td>@if($trade->exit_price) ${{ number_format($trade->exit_price, $trade->exit_price < 1 ? 4 : 2) }} @else — @endif</td>
                        <td>
                            @if($trade->status === 'closed')
                                @if($trade->profit >= 0)
                                <span style="color: var(--green); font-weight: 600;">+${{ number_format($trade->profit, 2) }}</span>
                                @else
                                <span style="color: var(--red); font-weight: 600;">-${{ number_format(abs($trade->profit), 2) }}</span>
                                @endif
                            @else
                            <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($trade->status === 'closed')
                                @if($trade->is_win)
                                <span class="badge" style="background: rgba(16,185,129,0.15); color: var(--green); font-size: 10px;">WIN</span>
                                @else
                                <span class="badge" style="background: rgba(239,68,68,0.15); color: var(--red); font-size: 10px;">LOSS</span>
                                @endif
                            @elseif($trade->status === 'open')
                            <span class="badge" style="background: rgba(245,158,11,0.15); color: var(--amber); font-size: 10px;">OPEN</span>
                            @else
                            <span class="badge" style="background: rgba(100,116,139,0.15); color: var(--text-muted); font-size: 10px;">{{ strtoupper($trade->status) }}</span>
                            @endif
                        </td>
                        <td style="color: var(--text-muted); font-size: 12px;">{{ $trade->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- Admin Config Info -->
    <div style="background: var(--bg-card); border-radius: 16px; padding: 20px; border: 1px solid var(--border); margin-bottom: 24px;">
        <h6 style="font-weight: 700; margin: 0 0 12px; font-size: 14px; color: var(--text-muted);"><i class="fas fa-info-circle" style="color: var(--blue-1);"></i> Trading Parameters (Admin Configured)</h6>
        <div class="row g-2" style="font-size: 12px;">
            <div class="col-md-3 col-sm-6"><span style="color: var(--text-muted);">Daily Profit Rate:</span> <strong>{{ $settings['daily_profit_pct'] }}%</strong></div>
            <div class="col-md-3 col-sm-6"><span style="color: var(--text-muted);">Win Rate:</span> <strong>{{ $settings['win_rate'] }}%</strong></div>
            <div class="col-md-3 col-sm-6"><span style="color: var(--text-muted);">Trades/Day:</span> <strong>{{ $settings['trades_per_day'] }}</strong></div>
            <div class="col-md-3 col-sm-6"><span style="color: var(--text-muted);">Min Capital:</span> <strong>${{ number_format($settings['min_capital'], 2) }}</strong></div>
        </div>
    </div>

    <!-- Past Sessions -->
    @if($allSessions->count() > 0)
    <div style="background: var(--bg-card); border-radius: 16px; padding: 24px; border: 1px solid var(--border);">
        <h5 style="font-weight: 700; margin: 0 0 16px; font-size: 16px;"><i class="fas fa-history" style="color: var(--purple-3);"></i> Trading Sessions</h5>
        <div style="overflow-x: auto;">
            <table class="table" style="color: var(--text); font-size: 13px; margin: 0;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border); color: var(--text-muted); font-size: 11px; text-transform: uppercase;">
                        <th>Reference</th>
                        <th>Capital</th>
                        <th>Balance</th>
                        <th>Profit</th>
                        <th>Trades</th>
                        <th>Win Rate</th>
                        <th>Status</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allSessions as $sess)
                    <tr style="border-bottom: 1px solid rgba(51,65,85,0.3);">
                        <td style="font-family: monospace; font-size: 11px;">{{ $sess->reference }}</td>
                        <td>${{ number_format($sess->allocated_capital, 2) }}</td>
                        <td>${{ number_format($sess->current_balance, 2) }}</td>
                        <td style="color: {{ $sess->netProfit() >= 0 ? 'var(--green)' : 'var(--red)' }}; font-weight: 600;">
                            {{ $sess->netProfit() >= 0 ? '+' : '' }}${{ number_format(abs($sess->netProfit()), 2) }}
                        </td>
                        <td>{{ (int)$sess->total_trades }}</td>
                        <td>{{ $sess->winRate() }}%</td>
                        <td>
                            @if($sess->status === 'active')
                            <span class="badge" style="background: rgba(16,185,129,0.15); color: var(--green); font-size: 10px;">ACTIVE</span>
                            @elseif($sess->status === 'stopped')
                            <span class="badge" style="background: rgba(245,158,11,0.15); color: var(--amber); font-size: 10px;">STOPPED</span>
                            @else
                            <span class="badge" style="background: rgba(100,116,139,0.15); color: var(--text-muted); font-size: 10px;">{{ strtoupper($sess->status) }}</span>
                            @endif
                        </td>
                        <td style="color: var(--text-muted); font-size: 12px;">{{ $sess->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Start Modal -->
<div class="modal fade" id="startModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" style="font-weight: 700;"><i class="fas fa-play-circle" style="color: var(--green);"></i> Start Auto Trading</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('dashboard.autotrade.start') }}">
                @csrf
                <div class="modal-body" style="padding: 24px;">
                    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">
                        Allocate capital from your deposit wallet. The system will auto-trade your selected pairs at <strong>{{ $settings['daily_profit_pct'] }}% daily</strong> with a <strong>{{ $settings['win_rate'] }}% win rate</strong>.
                    </p>

                    <div class="mb-3">
                        <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 8px;">Capital Amount (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text-muted);">$</span>
                            <input type="number" name="capital" min="{{ $settings['min_capital'] }}" max="{{ $settings['max_capital'] }}" step="0.01" value="{{ $settings['min_capital'] }}" class="form-control" required style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
                            <span class="input-group-text" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text-muted); font-size: 12px;">Min: ${{ number_format($settings['min_capital'], 0) }} / Max: ${{ number_format($settings['max_capital'], 0) }}</span>
                        </div>
                        <small style="color: var(--text-dim); font-size: 11px;">Available: ${{ number_format($depositWallet?->balance ?? 0, 2) }}</small>
                    </div>

                    <div class="mb-3">
                        <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 8px;">Select Trading Pairs</label>
                        @php
                        $allPairs = [];
                        foreach ($pairs as $cat => $items) {
                            foreach ($items as $item) {
                                $allPairs[] = $item;
                            }
                        }
                        @endphp

                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border); border-radius: 10px; padding: 12px;">
                            @foreach ($pairs as $category => $items)
                            <p style="font-size: 10px; color: var(--text-dim); text-transform: uppercase; margin: 8px 0 4px; font-weight: 600;">{{ $category }}</p>
                            <div class="row g-2 mb-2">
                                @foreach ($items as $item)
                                <div class="col-md-4 col-sm-6">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; padding: 6px 10px; border-radius: 8px; border: 1px solid var(--border); transition: all 0.2s;" onmouseover="this.style.borderColor='var(--purple-1)'" onmouseout="this.style.borderColor='var(--border)'">
                                        <input type="checkbox" name="selected_pairs[]" value="{{ $item['symbol'] }}" style="accent-color: var(--purple-1);"> {{ $item['symbol'] }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @endforeach
                        </div>
                        <small style="color: var(--text-dim); font-size: 11px;">Select at least 1 pair. The system will randomly trade across your selection.</small>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="font-size: 13px; border-radius: 10px;">Cancel</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, var(--purple-1), var(--purple-2)); color: white; font-size: 13px; border-radius: 10px; padding: 10px 28px; border: none;">
                        <i class="fas fa-play"></i> Start Trading
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stop Modal -->
@if($activeSession)
<div class="modal fade" id="stopModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" style="font-weight: 700;"><i class="fas fa-stop-circle" style="color: var(--red);"></i> Stop Auto Trading</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <p style="color: var(--text); font-size: 14px;">Are you sure you want to stop this session?</p>
                <div style="background: var(--bg-input); border-radius: 10px; padding: 16px; margin-top: 12px;">
                    <div class="d-flex justify-content-between mb-2"><span style="color: var(--text-muted); font-size: 12px;">Current Balance:</span> <strong>${{ number_format($activeSession->current_balance, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span style="color: var(--text-muted); font-size: 12px;">Net Profit:</span> <strong style="color: {{ $activeSession->netProfit() >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ $activeSession->netProfit() >= 0 ? '+' : '' }}${{ number_format(abs($activeSession->netProfit()), 2) }}</strong></div>
                </div>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 12px;">Your balance will be returned to your deposit wallet immediately.</p>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="font-size: 13px; border-radius: 10px;">Cancel</button>
                <form method="POST" action="{{ route('dashboard.autotrade.stop', $activeSession) }}">
                    @csrf
                    <button type="submit" class="btn btn-danger" style="font-size: 13px; border-radius: 10px; padding: 10px 24px;">
                        <i class="fas fa-stop"></i> Stop & Withdraw
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Auto-refresh script -->
@if($activeSession)
<script>
    let liveInterval;
    function refreshLive() {
        fetch('{{ route("dashboard.autotrade.live") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.active_session) {
                clearInterval(liveInterval);
                location.reload();
                return;
            }
            // Could update DOM here for real-time feel
        })
        .catch(() => {});
    }
    document.addEventListener('DOMContentLoaded', () => {
        liveInterval = setInterval(refreshLive, 15000);
    });
</script>
@endif
@endsection