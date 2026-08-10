@extends('layouts.admin')

@section('page-title', 'Auto Trading')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 8px; font-size: 22px;">
        <i class="fas fa-robot" style="color: var(--purple-3);"></i> Auto Trading Settings
    </h2>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Configure daily profit rates, win rates, trading pairs, and risk controls.</p>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div style="background: var(--bg-card); border-radius: 12px; padding: 16px; border: 1px solid var(--border);">
                <p style="font-size: 10px; color: var(--text-muted); margin: 0; text-transform: uppercase;">Active Sessions</p>
                <h4 style="margin: 4px 0 0; font-weight: 700;">{{ $stats['active_sessions'] }}</h4>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div style="background: var(--bg-card); border-radius: 12px; padding: 16px; border: 1px solid var(--border);">
                <p style="font-size: 10px; color: var(--text-muted); margin: 0; text-transform: uppercase;">Total Trades</p>
                <h4 style="margin: 4px 0 0; font-weight: 700;">{{ number_format($stats['total_trades']) }}</h4>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div style="background: var(--bg-card); border-radius: 12px; padding: 16px; border: 1px solid var(--border);">
                <p style="font-size: 10px; color: var(--text-muted); margin: 0; text-transform: uppercase;">Total P&L</p>
                <h4 style="margin: 4px 0 0; font-weight: 700; color: {{ $stats['total_profit'] >= 0 ? 'var(--green)' : 'var(--red)' }}">${{ number_format($stats['total_profit'], 2) }}</h4>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div style="background: var(--bg-card); border-radius: 12px; padding: 16px; border: 1px solid var(--border);">
                <p style="font-size: 10px; color: var(--text-muted); margin: 0; text-transform: uppercase;">Active Capital</p>
                <h4 style="margin: 4px 0 0; font-weight: 700;">${{ number_format($stats['active_capital'], 2) }}</h4>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.autotrade.update') }}">
        @csrf

        <!-- Core Settings -->
        <div style="background: var(--bg-card); border-radius: 16px; padding: 24px; border: 1px solid var(--border); margin-bottom: 20px;">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 20px;">
                <i class="fas fa-sliders" style="color: var(--purple-3);"></i> Profit & Win Rate Configuration
            </h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 6px;">Daily Profit Rate (%)</label>
                    <input type="number" name="autotrade_daily_profit_pct" value="{{ $settings['daily_profit_pct'] }}" step="0.1" min="0.1" max="50" class="form-control" required>
                    <small style="color: var(--text-dim); font-size: 11px;">Percentage of trade amount paid as profit per day.</small>
                </div>
                <div class="col-md-4">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 6px;">Win Rate (%)</label>
                    <input type="number" name="autotrade_win_rate" value="{{ $settings['win_rate'] }}" step="1" min="1" max="100" class="form-control" required>
                    <small style="color: var(--text-dim); font-size: 11px;">Percentage of trades that will be winners.</small>
                </div>
                <div class="col-md-4">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 6px;">Profit Variance (%)</label>
                    <input type="number" name="autotrade_profit_variance" value="{{ $settings['profit_variance'] }}" step="1" min="0" max="100" class="form-control" required>
                    <small style="color: var(--text-dim); font-size: 11px;">Random variation in profit amounts for realism.</small>
                </div>
            </div>
        </div>

        <!-- Capital & Frequency -->
        <div style="background: var(--bg-card); border-radius: 16px; padding: 24px; border: 1px solid var(--border); margin-bottom: 20px;">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 20px;">
                <i class="fas fa-money-bill" style="color: var(--purple-3);"></i> Capital & Trade Frequency
            </h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 6px;">Min Capital ($)</label>
                    <input type="number" name="autotrade_min_capital" value="{{ $settings['min_capital'] }}" step="1" min="1" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 6px;">Max Capital ($)</label>
                    <input type="number" name="autotrade_max_capital" value="{{ $settings['max_capital'] }}" step="100" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 6px;">Trades Per Day</label>
                    <input type="number" name="autotrade_trades_per_day" value="{{ $settings['trades_per_day'] }}" step="1" min="1" max="100" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 6px;">Trade Interval (min)</label>
                    <input type="number" name="autotrade_trade_interval_min" value="{{ $settings['trade_interval_min'] }}" step="1" min="1" max="1440" class="form-control" required>
                </div>
            </div>
        </div>

        <!-- Risk Management -->
        <div style="background: var(--bg-card); border-radius: 16px; padding: 24px; border: 1px solid var(--border); margin-bottom: 20px;">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 20px;">
                <i class="fas fa-shield-halved" style="color: var(--purple-3);"></i> Risk Management
            </h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 6px;">Stop Loss (%)</label>
                    <input type="number" name="autotrade_stop_loss_pct" value="{{ $settings['stop_loss_pct'] }}" step="0.1" min="0.1" max="50" class="form-control" required>
                    <small style="color: var(--text-dim); font-size: 11px;">Max loss per losing trade as % of trade amount.</small>
                </div>
                <div class="col-md-4">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 6px;">Take Profit (%)</label>
                    <input type="number" name="autotrade_take_profit_pct" value="{{ $settings['take_profit_pct'] }}" step="0.1" min="0.1" max="50" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 6px;">Max Concurrent Sessions</label>
                    <input type="number" name="autotrade_max_concurrent" value="{{ $settings['max_concurrent'] }}" step="1" min="1" max="20" class="form-control" required>
                </div>
            </div>
        </div>

        <!-- Trading Pairs -->
        <div style="background: var(--bg-card); border-radius: 16px; padding: 24px; border: 1px solid var(--border); margin-bottom: 20px;">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 20px;">
                <i class="fas fa-coins" style="color: var(--purple-3);"></i> Trading Pairs
            </h5>
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">Comma-separated list of available pairs users can select.</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 6px;">Crypto Pairs</label>
                    <textarea name="pairs_crypto" class="form-control" rows="3" placeholder="BTC/USDT, ETH/USDT, ...">{{ $settings['pairs_crypto'] }}</textarea>
                </div>
                <div class="col-md-4">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 6px;">Forex Pairs</label>
                    <textarea name="pairs_forex" class="form-control" rows="3" placeholder="EUR/USD, GBP/USD, ...">{{ $settings['pairs_forex'] }}</textarea>
                </div>
                <div class="col-md-4">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 6px;">Index Pairs</label>
                    <textarea name="pairs_indices" class="form-control" rows="3" placeholder="SPX, NDX, ...">{{ $settings['pairs_indices'] }}</textarea>
                </div>
            </div>
        </div>

        <!-- Toggles -->
        <div style="background: var(--bg-card); border-radius: 16px; padding: 24px; border: 1px solid var(--border); margin-bottom: 20px;">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 20px;">
                <i class="fas fa-toggle-on" style="color: var(--purple-3);"></i> Module Toggles
            </h5>
            <div class="d-flex gap-4 flex-wrap">
                <div class="form-check form-switch">
                    <input type="hidden" name="autotrade_enabled" value="0">
                    <input type="checkbox" name="autotrade_enabled" value="1" class="form-check-input" id="at-enabled" {{ $settings['enabled'] ? 'checked' : '' }} style="width: 2.5em; height: 1.25em;">
                    <label for="at-enabled" class="form-check-label" style="font-size: 14px; color: var(--text); margin-left: 8px;">Enable Auto Trading</label>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="autotrade_auto_compound" value="0">
                    <input type="checkbox" name="autotrade_auto_compound" value="1" class="form-check-input" id="at-compound" {{ $settings['auto_compound'] ? 'checked' : '' }} style="width: 2.5em; height: 1.25em;">
                    <label for="at-compound" class="form-check-label" style="font-size: 14px; color: var(--text); margin-left: 8px;">Auto-Compound Profits</label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn" style="background: linear-gradient(135deg, #6366f1, #7c3aed, #a855f7); color: white; padding: 14px 40px; font-size: 14px; border-radius: 12px; border: none;">
            <i class="fas fa-save"></i> Save Settings
        </button>

        <a href="{{ route('admin.autotrade.sessions') }}" class="btn btn-outline-secondary ms-2" style="font-size: 14px; border-radius: 12px; padding: 14px 28px; color: var(--text-muted); border-color: var(--border);">
            <i class="fas fa-users"></i> View Sessions
        </a>
        <a href="{{ route('admin.autotrade.trades') }}" class="btn btn-outline-secondary" style="font-size: 14px; border-radius: 12px; padding: 14px 28px; color: var(--text-muted); border-color: var(--border);">
            <i class="fas fa-list"></i> View All Trades
        </a>
    </form>
</div>
@endsection