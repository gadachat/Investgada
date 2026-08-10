@extends('layouts.dashboard')

@section('page-title', 'Trading Signals')

@section('content')
<div class="fade-in">
    <!-- Win Rate Banner -->
    <div style="background: linear-gradient(135deg, #6366f1, #7c3aed); border-radius: 16px; padding: 24px; margin-bottom: 24px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; width: 160px; height: 160px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%;"></div>
        <div style="position: relative; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h3 style="color: white; font-weight: 700; margin: 0 0 4px;"><i class="fas fa-broadcast-tower"></i> Trading Signals</h3>
                <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 14px;">Real-time trade ideas from our analysts</p>
            </div>
            <div style="text-align: right;">
                <div style="color: rgba(255,255,255,0.7); font-size: 12px;">Win Rate</div>
                <div style="color: white; font-size: 28px; font-weight: 800;">{{ $winRate }}%</div>
                <div style="color: rgba(255,255,255,0.7); font-size: 12px;">{{ $totalWins }}W / {{ $totalClosed - $totalWins }}L</div>
            </div>
        </div>
    </div>

    <!-- Active Signals -->
    <h5 style="font-weight: 700; margin-bottom: 16px;"><i class="fas fa-signal" style="color: #10b981;"></i> Active Signals</h5>

    @if($activeSignals->count() > 0)
    <div class="row g-3 mb-4">
        @foreach($activeSignals as $signal)
        <div class="col-md-6">
            <div class="card-custom" style="padding: 20px; border-left: 4px solid {{ $signal->direction === 'buy' ? '#10b981' : '#ef4444' }};">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 18px; font-weight: 700; color: var(--text-bright);">{{ $signal->symbol }}</span>
                            <span class="badge-custom {{ $signal->direction === 'buy' ? 'badge-up' : 'badge-down' }}" style="font-size: 12px;">
                                {{ strtoupper($signal->direction) }}
                            </span>
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                            {{ strtoupper($signal->category) }} · {{ $signal->timeframe }} · {{ $signal->created_at->diffForHumans() }}
                        </div>
                    </div>
                    @if($signal->confidence > 0)
                    <div style="text-align: right;">
                        <div style="font-size: 11px; color: var(--text-muted);">Confidence</div>
                        <div style="font-size: 16px; font-weight: 700; color: var(--purple-3);">{{ $signal->confidence }}%</div>
                    </div>
                    @endif
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div style="background: var(--bg-input); border-radius: 8px; padding: 10px; text-align: center;">
                            <div style="font-size: 10px; color: var(--text-muted);">ENTRY</div>
                            <div style="font-size: 14px; font-weight: 600; color: var(--text-bright);">${{ number_format($signal->entry_price, $signal->entry_price < 1 ? 4 : 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="background: rgba(239,68,68,0.08); border-radius: 8px; padding: 10px; text-align: center;">
                            <div style="font-size: 10px; color: #ef4444;">STOP LOSS</div>
                            <div style="font-size: 14px; font-weight: 600; color: #ef4444;">${{ number_format($signal->stop_loss, $signal->stop_loss < 1 ? 4 : 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="background: rgba(16,185,129,0.08); border-radius: 8px; padding: 10px; text-align: center;">
                            <div style="font-size: 10px; color: #10b981;">TAKE PROFIT</div>
                            <div style="font-size: 14px; font-weight: 600; color: #10b981;">${{ number_format($signal->take_profit, $signal->take_profit < 1 ? 4 : 2) }}</div>
                        </div>
                    </div>
                </div>

                @if($signal->analysis)
                <div style="background: var(--bg-input); border-radius: 8px; padding: 12px; margin-bottom: 8px;">
                    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;"><i class="fas fa-chart-line"></i> Analysis</div>
                    <p style="font-size: 13px; color: var(--text-bright); margin: 0; line-height: 1.5;">{{ $signal->analysis }}</p>
                </div>
                @endif

                <!-- Risk/Reward -->
                @php
                    $risk = abs($signal->entry_price - $signal->stop_loss);
                    $reward = abs($signal->take_profit - $signal->entry_price);
                    $rr = $risk > 0 ? round($reward / $risk, 2) : 0;
                @endphp
                <div style="font-size: 11px; color: var(--text-muted);">
                    Risk/Reward: <span style="color: var(--purple-3); font-weight: 600;">1:{{ $rr }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="card-custom" style="text-align: center; padding: 60px; margin-bottom: 24px;">
        <i class="fas fa-signal" style="font-size: 48px; color: var(--border); margin-bottom: 16px;"></i>
        <p style="font-size: 15px; color: var(--text-muted);">No active signals right now. Check back soon!</p>
    </div>
    @endif

    <!-- Signal History -->
    @if($closedSignals->count() > 0)
    <h5 style="font-weight: 700; margin-bottom: 16px;"><i class="fas fa-history" style="color: var(--text-muted);"></i> Recent Results</h5>
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Symbol</th>
                        <th>Direction</th>
                        <th>Entry</th>
                        <th>Result</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($closedSignals as $signal)
                    <tr>
                        <td style="font-weight: 600;">{{ $signal->symbol }}</td>
                        <td><span class="badge-custom {{ $signal->direction === 'buy' ? 'badge-up' : 'badge-down' }}">{{ strtoupper($signal->direction) }}</span></td>
                        <td>${{ number_format($signal->entry_price, $signal->entry_price < 1 ? 4 : 2) }}</td>
                        <td>
                            @if($signal->result === 'win')
                            <span class="badge-custom badge-up">Win</span>
                            @elseif($signal->result === 'loss')
                            <span class="badge-custom badge-down">Loss</span>
                            @else
                            <span class="badge-custom" style="background: rgba(245,158,11,0.2); color: #f59e0b;">BE</span>
                            @endif
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $signal->closed_at?->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
