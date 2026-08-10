@extends('layouts.admin')

@section('title', 'Trading Settings')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="mb-4">
            <a href="{{ route('admin.trading.index') }}" style="color:var(--text-muted);font-size:13px;text-decoration:none">
                <i class="fas fa-arrow-left me-1"></i> Back to Trading
            </a>
            <h2 class="mt-2 mb-1" style="font-weight:700;color:var(--text)">
                <i class="fas fa-cog me-2" style="color:var(--primary)"></i> Trading Settings & Packages
            </h2>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Package Management --}}
        <div class="card-custom mb-4">
            <div class="d-flex justify-content-between align-items-center p-3" style="border-bottom:1px solid var(--border)">
                <h5 class="mb-0" style="font-weight:600;color:var(--text)"><i class="fas fa-box me-1" style="color:var(--primary)"></i> Trading Packages</h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('newPkgForm').style.display='block'">
                    <i class="fas fa-plus me-1"></i> Add Package
                </button>
            </div>

            {{-- New Package Form --}}
            <div id="newPkgForm" style="display:none;border-bottom:1px solid var(--border);padding:16px;background:var(--bg)">
                <form method="POST" action="{{ route('admin.trading.packages.store') }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-3 col-6">
                            <label style="font-size:12px;color:var(--text-muted)">Name</label>
                            <input type="text" name="name" class="form-control" required style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                        </div>
                        <div class="col-md-2 col-6">
                            <label style="font-size:12px;color:var(--text-muted)">Min Amount ($)</label>
                            <input type="number" name="min_amount" class="form-control" step="0.01" required style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                        </div>
                        <div class="col-md-2 col-6">
                            <label style="font-size:12px;color:var(--text-muted)">Max Amount ($)</label>
                            <input type="number" name="max_amount" class="form-control" step="0.01" required style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                        </div>
                        <div class="col-md-1 col-6">
                            <label style="font-size:12px;color:var(--text-muted)">Pairs</label>
                            <input type="number" name="max_pairs" class="form-control" required value="1" min="1" style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                        </div>
                        <div class="col-md-2 col-6">
                            <label style="font-size:12px;color:var(--text-muted)">Daily Profit %</label>
                            <input type="number" name="daily_profit_percent" class="form-control" step="0.01" required value="1.00" style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                        </div>
                        <div class="col-md-2 col-6">
                            <label style="font-size:12px;color:var(--text-muted)">Win Rate %</label>
                            <input type="number" name="win_rate_percent" class="form-control" step="0.01" required value="65.00" style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-6 col-12">
                            <label style="font-size:12px;color:var(--text-muted)">Description</label>
                            <input type="text" name="description" class="form-control" style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                        </div>
                        <div class="col-md-6 col-12 d-flex align-items-end gap-3">
                            <div class="form-check"><input type="checkbox" name="scanner_enabled" value="1" class="form-check-input" id="newScan"><label for="newScan" style="font-size:13px;color:var(--text)">Scanner</label></div>
                            <div class="form-check"><input type="checkbox" name="has_short_selling" value="1" class="form-check-input" id="newShort"><label for="newShort" style="font-size:13px;color:var(--text)">Short Sell</label></div>
                            <button type="submit" class="btn btn-primary btn-sm ms-auto"><i class="fas fa-save me-1"></i> Create</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Packages List --}}
            <div style="overflow-x:auto">
                <table class="table table-hover mb-0" style="color:var(--text)">
                    <thead><tr style="color:var(--text-muted);font-size:12px;text-transform:uppercase">
                        <th>Name</th><th>Range</th><th>Pairs</th><th>Scanner</th><th>Short</th><th>Daily %</th><th>Win Rate</th><th>Status</th><th></th>
                    </tr></thead>
                    <tbody>
                        @foreach($packages as $pkg)
                        <tr>
                            <td style="font-weight:600;font-size:13px">{{ $pkg->name }}</td>
                            <td style="font-size:12px">${{ number_format((float)$pkg->min_amount) }} – ${{ number_format((float)$pkg->max_amount) }}</td>
                            <td style="font-size:13px">{{ $pkg->max_pairs == 99 ? '∞' : $pkg->max_pairs }}</td>
                            <td><i class="fas fa-{{ $pkg->scanner_enabled ? 'check text-success' : 'times text-danger' }}"></i></td>
                            <td><i class="fas fa-{{ $pkg->has_short_selling ? 'check text-success' : 'times text-danger' }}"></i></td>
                            <td style="font-size:13px;color:var(--primary);font-weight:600">{{ number_format((float)$pkg->daily_profit_percent, 2) }}%</td>
                            <td style="font-size:13px">{{ number_format((float)$pkg->win_rate_percent, 1) }}%</td>
                            <td>
                                @if($pkg->is_active)
                                <span class="badge" style="background:rgba(16,185,129,0.15);color:#10b981">Active</span>
                                @else
                                <span class="badge" style="background:rgba(148,163,184,0.15);color:#94a3b8">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.trading.packages.toggle', $pkg) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm" style="background:rgba(99,102,241,0.1);color:var(--primary);font-size:11px">
                                        {{ $pkg->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Subscriptions --}}
        @if(isset($subscriptions) && $subscriptions->count() > 0)
        <div class="card-custom mb-4">
            <div class="p-3" style="border-bottom:1px solid var(--border)">
                <h5 class="mb-0" style="font-weight:600;color:var(--text)"><i class="fas fa-users me-1" style="color:var(--primary)"></i> Recent Subscriptions</h5>
            </div>
            <div style="overflow-x:auto">
                <table class="table table-hover mb-0" style="color:var(--text)">
                    <thead><tr style="color:var(--text-muted);font-size:12px;text-transform:uppercase">
                        <th>Reference</th><th>User</th><th>Package</th><th>Amount</th><th>Pairs</th><th>P&L</th><th>Win Rate</th><th>Status</th>
                    </tr></thead>
                    <tbody>
                        @foreach($subscriptions as $sub)
                        <tr>
                            <td style="font-size:12px;font-weight:600">{{ $sub->reference }}</td>
                            <td style="font-size:13px">{{ $sub->user->name }}</td>
                            <td><span class="badge" style="background:rgba(99,102,241,0.15);color:#a855f7;font-size:11px">{{ $sub->package->name }}</span></td>
                            <td style="font-size:13px">${{ number_format((float)$sub->amount, 2) }}</td>
                            <td style="font-size:12px">{{ count($sub->selected_pairs ?? []) }}</td>
                            <td style="font-size:13px;font-weight:600;color:{{ $sub->netPnl() >= 0 ? '#10b981' : '#ef4444' }}">{{ $sub->netPnl() >= 0 ? '+' : '' }}${{ number_format(abs($sub->netPnl()), 2) }}</td>
                            <td style="font-size:13px">{{ $sub->winRate() }}%</td>
                            <td>
                                @if($sub->status === 'active')
                                <span class="badge" style="background:rgba(16,185,129,0.15);color:#10b981">Active</span>
                                @else
                                <span class="badge" style="background:rgba(148,163,184,0.15);color:#94a3b8">{{ ucfirst($sub->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- General Settings --}}
        <div class="card-custom">
            <h6 class="mb-3 p-3" style="font-weight:600;color:var(--text);border-bottom:1px solid var(--border)"><i class="fas fa-sliders-h me-1" style="color:var(--primary)"></i> General Settings</h6>
            <form method="POST" action="{{ route('admin.trading.settings.update') }}" class="p-3">
                @csrf
                <div class="form-check form-switch mb-3">
                    <input type="checkbox" name="trading_enabled" value="1" class="form-check-input" id="tradingEnabled" {{ ($settings['trading_enabled'] ?? 'true') === 'true' ? 'checked' : '' }}>
                    <label for="tradingEnabled" style="font-weight:600;color:var(--text)">Trading Enabled</label>
                </div>
                <div class="row g-3">
                    <div class="col-md-4 col-12">
                        <label style="font-size:13px;color:var(--text-muted)">Min Trade ($)</label>
                        <input type="number" name="min_trade_amount" class="form-control" step="0.01" value="{{ $settings['min_trade_amount'] ?? 10 }}" style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                    </div>
                    <div class="col-md-4 col-12">
                        <label style="font-size:13px;color:var(--text-muted)">Max Trade ($)</label>
                        <input type="number" name="max_trade_amount" class="form-control" step="0.01" value="{{ $settings['max_trade_amount'] ?? 50000 }}" style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                    </div>
                    <div class="col-md-4 col-12">
                        <label style="font-size:13px;color:var(--text-muted)">Spread (%)</label>
                        <input type="number" name="spread_percent" class="form-control" step="0.01" value="{{ $settings['spread_percent'] ?? 0.05 }}" style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                    </div>
                    <div class="col-md-4 col-12">
                        <label style="font-size:13px;color:var(--text-muted)">Max Leverage</label>
                        <input type="number" name="max_leverage" class="form-control" value="{{ $settings['max_leverage'] ?? 100 }}" style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3" style="font-weight:600"><i class="fas fa-save me-1"></i> Save Settings</button>
            </form>
        </div>
    </div>
</div>
@endsection
