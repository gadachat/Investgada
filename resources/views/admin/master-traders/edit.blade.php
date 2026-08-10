@extends('layouts.admin')
@section('title', 'Edit Master Trader')

@push('styles')
<style>
    .avatar-preview {
        width: 120px; height: 120px; border-radius: 50%;
        object-fit: cover; border: 3px solid var(--border);
    }
    .avatar-placeholder {
        width: 120px; height: 120px; border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #7c3aed);
        display: flex; align-items: center; justify-content: center;
        font-size: 36px; font-weight: 700; color: #fff;
        border: 3px solid var(--border);
    }
    .toggle-switch { position: relative; width: 44px; height: 24px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background: #ccc; border-radius: 24px; transition: 0.3s;
    }
    .toggle-slider:before {
        content: ""; position: absolute; height: 18px; width: 18px;
        left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: 0.3s;
    }
    input:checked + .toggle-slider { background: #6366f1; }
    input:checked + .toggle-slider:before { transform: translateX(20px); }
    .outcome-section { border: 2px solid #6366f1; border-radius: 10px; padding: 16px; margin-bottom: 16px; background: rgba(99,102,241,0.03); }
</style>
@endpush

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight:700;"><i class="fas fa-user-tie me-2"></i> Edit Master Trader</h4>
        <a href="{{ route('admin.master-traders.index') }}" class="btn btn-sm" style="background:var(--bg-card); border:1px solid var(--border);"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    @if(session('error'))
    <div class="alert alert-danger" style="border-radius:10px; font-size:13px;">{{ session('error') }}</div>
    @endif

    <div class="card-custom" style="max-width:700px;">
        <form method="POST" action="{{ route('admin.master-traders.update', $masterTrader) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Trader info (read-only) --}}
            <div class="mb-3" style="padding:12px; background:var(--bg-card); border-radius:10px; border:1px solid var(--border);">
                <div style="font-size:12px; color:var(--text-muted);">Trader Account</div>
                <div style="font-weight:600; font-size:15px;">{{ $masterTrader->user->name }}</div>
                <div style="font-size:12px; color:var(--text-muted);">{{ $masterTrader->user->email }}</div>
            </div>

            {{-- Avatar upload --}}
            <div class="mb-4">
                <label style="font-size:13px; font-weight:600; margin-bottom:8px;">Profile Picture</label>
                <div class="d-flex align-items-center gap-3 mb-2">
                    @if($masterTrader->avatar)
                        <img src="{{ asset('storage/' . $masterTrader->avatar) }}" class="avatar-preview" id="avatar-preview">
                    @else
                        <div class="avatar-placeholder" id="avatar-placeholder">
                            {{ strtoupper(substr($masterTrader->user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <input type="file" name="avatar" id="avatar-input" accept="image/jpeg,image/png,image/jpg,image/webp" style="display:none;">
                        <button type="button" class="btn btn-sm" style="background:var(--bg-card); border:1px solid var(--border); padding:6px 16px;" onclick="document.getElementById('avatar-input').click()">
                            <i class="fas fa-upload"></i> Upload Photo
                        </button>
                        @if($masterTrader->avatar)
                        <label style="font-size:12px; margin-left:8px; cursor:pointer;">
                            <input type="checkbox" name="remove_avatar" value="1"> Remove current photo
                        </label>
                        @endif
                        <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">JPG, PNG, or WebP. Max 2MB. Recommended 400×400px.</div>
                    </div>
                </div>
            </div>

            {{-- Title --}}
            <div class="mb-3">
                <label style="font-size:13px; font-weight:600; margin-bottom:6px;">Title / Strategy Name</label>
                <input type="text" name="title" class="form-control" value="{{ $masterTrader->title }}" required>
            </div>

            {{-- Strategy Type --}}
            <div class="mb-3">
                <label style="font-size:13px; font-weight:600; margin-bottom:6px;">Strategy Type</label>
                <select name="strategy_type" class="form-control">
                    <option value="">— Select —</option>
                    @foreach(['Scalper','Day Trader','Swing Trader','Position Trader','Crypto Specialist','Forex Expert','Stocks Analyst','Mixed Strategy'] as $type)
                    <option value="{{ $type }}" @selected(old('strategy_type', $masterTrader->strategy_type) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Description --}}
            <div class="mb-3">
                <label style="font-size:13px; font-weight:600; margin-bottom:6px;">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this trader's strategy...">{{ $masterTrader->description }}</textarea>
            </div>

            {{-- Win Rate Section --}}
            <div style="border:1px solid var(--border); border-radius:10px; padding:16px; margin-bottom:16px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div style="font-weight:600; font-size:14px;">Win Rate</div>
                        <div style="font-size:11px; color:var(--text-muted);">Auto-calculated: {{ number_format($masterTrader->win_rate, 1) }}% from {{ $masterTrader->total_trades }} trades</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size:12px; color:var(--text-muted);">Manual Override</span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="use_manual_win_rate" value="1" id="manual-toggle" {{ $masterTrader->use_manual_win_rate ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                <div id="manual-win-rate-field" style="{{ $masterTrader->use_manual_win_rate ? '' : 'display:none;' }}">
                    <label style="font-size:12px; color:var(--text-muted); margin-bottom:4px;">Set Custom Win Rate (%)</label>
                    <input type="number" name="manual_win_rate" class="form-control" min="0" max="100" step="0.1"
                        value="{{ $masterTrader->manual_win_rate ?? '' }}" placeholder="e.g. 87.5">
                    <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">When enabled, this value is shown to users and used to determine trade outcomes.</div>
                </div>
            </div>

            {{-- COPY TRADING OUTCOME CONTROLS (Admin-determined) --}}
            <div class="outcome-section">
                <div style="font-weight:700; font-size:15px; color:#6366f1; margin-bottom:12px;">
                    <i class="fas fa-cogs me-1"></i> Copy Trading Outcome Controls
                </div>
                <div style="font-size:12px; color:var(--text-muted); margin-bottom:14px;">
                    These settings determine the profit/loss that followers receive when copying this trader.
                    The cron job uses these values to generate daily results for all subscribers.
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label style="font-size:12px; font-weight:600; margin-bottom:4px;">Daily Profit %</label>
                        <input type="number" name="daily_profit_pct" class="form-control" min="0" max="100" step="0.01"
                            value="{{ $masterTrader->daily_profit_pct ?? 2.50 }}" required>
                        <div style="font-size:11px; color:var(--text-muted); margin-top:3px;">Profit per winning trade (% of allocation)</div>
                    </div>
                    <div class="col-md-6">
                        <label style="font-size:12px; font-weight:600; margin-bottom:4px;">Loss Rate %</label>
                        <input type="number" name="loss_rate_pct" class="form-control" min="0" max="100" step="0.01"
                            value="{{ $masterTrader->loss_rate_pct ?? 5.00 }}" required>
                        <div style="font-size:11px; color:var(--text-muted); margin-top:3px;">Loss per losing trade (% of allocation)</div>
                    </div>
                    <div class="col-md-6">
                        <label style="font-size:12px; font-weight:600; margin-bottom:4px;">Trades Per Day</label>
                        <input type="number" name="trades_per_day" class="form-control" min="1" max="50"
                            value="{{ $masterTrader->trades_per_day ?? 6 }}" required>
                        <div style="font-size:11px; color:var(--text-muted); margin-top:3px;">How many copied trades per day for each follower</div>
                    </div>
                    <div class="col-md-6">
                        <label style="font-size:12px; font-weight:600; margin-bottom:4px;">Profit Variance %</label>
                        <input type="number" name="profit_variance" class="form-control" min="0" max="100" step="0.01"
                            value="{{ $masterTrader->profit_variance ?? 15.00 }}" required>
                        <div style="font-size:11px; color:var(--text-muted); margin-top:3px;">Randomness around the base profit (0 = exact, higher = more varied)</div>
                    </div>
                </div>

                <div class="mt-3" style="padding:10px; background:rgba(99,102,241,0.05); border-radius:8px;">
                    <div style="font-size:12px; color:var(--text-muted);">
                        <b>Example:</b> A follower allocates $1,000. Win rate 85%, Daily profit 2.5%, 6 trades/day.
                        <br>→ ~5 wins + ~1 loss per day ≈ +$" . number_format((1000 * 0.025 * 0.85) - (1000 * 0.05 * 0.15), 2) . " daily profit
                    </div>
                </div>
            </div>

            {{-- Monthly return --}}
            <div class="mb-3">
                <label style="font-size:13px; font-weight:600; margin-bottom:6px;">Monthly Return (%) <span style="color:var(--text-muted); font-weight:400;">— displayed to users</span></label>
                <input type="number" name="monthly_return" class="form-control" min="0" max="100" step="0.1"
                    value="{{ $masterTrader->monthly_return ?? '' }}" placeholder="e.g. 15.5">
            </div>

            {{-- Stats override --}}
            <div style="border:1px solid var(--border); border-radius:10px; padding:16px; margin-bottom:16px;">
                <div style="font-weight:600; font-size:14px; margin-bottom:10px;">Trading Stats (manual override)</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label style="font-size:12px; color:var(--text-muted);">Total Trades</label>
                        <input type="number" name="total_trades" class="form-control" min="0" value="{{ $masterTrader->total_trades }}">
                    </div>
                    <div class="col-md-4">
                        <label style="font-size:12px; color:var(--text-muted);">Winning Trades</label>
                        <input type="number" name="winning_trades" class="form-control" min="0" value="{{ $masterTrader->winning_trades }}">
                    </div>
                    <div class="col-md-4">
                        <label style="font-size:12px; color:var(--text-muted);">Total Profit ($)</label>
                        <input type="number" name="total_profit" class="form-control" min="0" step="0.01" value="{{ $masterTrader->total_profit }}">
                    </div>
                </div>
                <div style="font-size:11px; color:var(--text-muted); margin-top:6px;">Leave blank to keep existing values.</div>
            </div>

            {{-- Max followers --}}
            <div class="mb-3">
                <label style="font-size:13px; font-weight:600; margin-bottom:6px;">Max Followers (0 = unlimited)</label>
                <input type="number" name="max_followers" class="form-control" min="0" value="{{ $masterTrader->max_followers }}">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-gradient" style="padding:10px 28px;"><i class="fas fa-save"></i> Save Changes</button>
                <a href="{{ route('admin.master-traders.index') }}" class="btn" style="background:var(--bg-card); border:1px solid var(--border); padding:10px 20px;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('manual-toggle').addEventListener('change', function() {
    document.getElementById('manual-win-rate-field').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('avatar-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const placeholder = document.getElementById('avatar-placeholder');
            const existing = document.getElementById('avatar-preview');
            if (existing) {
                existing.src = event.target.result;
            } else if (placeholder) {
                const img = document.createElement('img');
                img.src = event.target.result;
                img.className = 'avatar-preview';
                img.id = 'avatar-preview';
                placeholder.replaceWith(img);
            }
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endsection
