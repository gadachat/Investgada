@extends('layouts.dashboard')
@section('title', 'Copy Trading')

@push('styles')
<style>
    .master-card { transition: transform 0.15s, box-shadow 0.15s; }
    .master-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99,102,241,0.12); }
    .win-bar { height:6px; border-radius:3px; background:#fee2e2; overflow:hidden; }
    .win-bar-fill { height:100%; background:linear-gradient(90deg,#6366f1,#7c3aed); border-radius:3px; }
    .sub-card { border-left:3px solid #6366f1; }
    .master-avatar {
        width: 56px; height: 56px; border-radius: 50%; object-fit: cover;
        border: 2px solid var(--border);
    }
    .master-avatar-placeholder {
        width: 56px; height: 56px; border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #7c3aed);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; font-weight: 700; color: #fff;
        border: 2px solid var(--border);
    }
    .stat-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 8px; border-radius: 6px; font-size: 11px;
        background: var(--bg-card); border: 1px solid var(--border);
    }
</style>
@endpush

@section('content')
<div class="fade-in">
    <h4 style="font-weight:700; margin-bottom:20px;"><i class="fas fa-copy me-2"></i> Copy Trading</h4>

    @if(session('success'))
    <div class="alert alert-success" style="border-radius:10px; font-size:13px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger" style="border-radius:10px; font-size:13px;">{{ session('error') }}</div>
    @endif

    {{-- Active Subscriptions --}}
    @if($subscriptions->count() > 0)
    <h6 style="font-weight:700; color:var(--purple-3); margin-bottom:12px;">Your Active Subscriptions</h6>
    <div class="row mb-4">
        @foreach($subscriptions as $sub)
        <div class="col-md-6 mb-3">
            <div class="card-custom sub-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center gap-2">
                        @if($sub->masterTrader->avatar)
                            <img src="{{ asset('storage/' . $sub->masterTrader->avatar) }}" class="master-avatar">
                        @else
                            <div class="master-avatar-placeholder">{{ strtoupper(substr($sub->masterTrader->user->name, 0, 1)) }}</div>
                        @endif
                        <div>
                            <div style="font-weight:600; font-size:15px;">{{ $sub->masterTrader->user->name }}</div>
                            <div style="font-size:12px; color:var(--text-muted);">{{ $sub->masterTrader->title }}</div>
                        </div>
                    </div>
                    <span class="badge-custom" style="background:#d1fae5; color:#059669;">{{ $sub->status }}</span>
                </div>
                <div class="row mt-3" style="font-size:12px;">
                    <div class="col-4"><span style="color:var(--text-muted);">Allocated:</span><br><b>${{ number_format($sub->allocation_amount, 2) }}</b></div>
                    <div class="col-4"><span style="color:var(--text-muted);">P&L:</span><br><b style="color:{{ $sub->total_pnl >= 0 ? '#059669' : '#dc2626' }};">{{ $sub->total_pnl >= 0 ? '+' : '' }}${{ number_format($sub->total_pnl, 2) }}</b></div>
                    <div class="col-4"><span style="color:var(--text-muted);">Copied:</span><br><b>{{ $sub->total_copied }} trades</b></div>
                </div>
                <form method="POST" action="{{ route('dashboard.copy-trade.unsubscribe', $sub) }}" class="mt-3" onsubmit="return confirm('Stop copying this trader? Your allocation will be returned to your deposit wallet.')">
                    @csrf
                    <button type="submit" class="btn btn-sm w-100" style="background:#fee2e2; color:#dc2626; border:none; padding:8px;">Stop Copying</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Available Master Traders --}}
    <h6 style="font-weight:700; color:var(--purple-3); margin-bottom:12px;">Available Master Traders</h6>
    @if($available > 0)
    <div style="font-size:12px; color:var(--text-muted); margin-bottom:12px;">Deposit wallet balance available for allocation: <b>${{ number_format($available, 2) }}</b></div>
    @else
    <div style="font-size:12px; color:#d97706; margin-bottom:12px;">No deposit wallet balance available. Fund your deposit wallet to start copy trading.</div>
    @endif

    <div class="row">
        @forelse($masters as $master)
        <div class="col-md-4 mb-3">
            <div class="card-custom master-card p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        @if($master->avatar)
                            <img src="{{ asset('storage/' . $master->avatar) }}" class="master-avatar">
                        @else
                            <div class="master-avatar-placeholder">{{ strtoupper(substr($master->user->name, 0, 1)) }}</div>
                        @endif
                        <div>
                            <div style="font-weight:600; font-size:15px;">{{ $master->user->name }}</div>
                            <div style="font-size:12px; color:var(--text-muted);">{{ $master->title }}</div>
                            @if($master->strategy_type)
                            <span style="font-size:10px; color:var(--purple-2); background:#e0e7ff; padding:2px 6px; border-radius:4px;">{{ $master->strategy_type }}</span>
                            @endif
                        </div>
                    </div>
                    @if($master->max_followers > 0 && $master->followers_count >= $master->max_followers)
                    <span class="badge-custom" style="background:#fee2e2; color:#dc2626;">Full</span>
                    @else
                    <span class="badge-custom" style="background:#d1fae5; color:#059669;">Open</span>
                    @endif
                </div>
                @if($master->description)
                <p style="font-size:12px; color:var(--text-dim); margin-bottom:12px;">{{ $master->description }}</p>
                @endif
                <div class="row mb-2" style="font-size:12px;">
                    <div class="col-6"><span style="color:var(--text-muted);">Win Rate:</span></div>
                    <div class="col-6 text-end"><b>{{ number_format($master->display_win_rate, 1) }}%</b></div>
                </div>
                <div class="win-bar mb-2"><div class="win-bar-fill" style="width:{{ min($master->display_win_rate, 100) }}%;"></div></div>
                @if($master->monthly_return)
                <div class="row mb-2" style="font-size:12px;">
                    <div class="col-6"><span style="color:var(--text-muted);">Monthly Return:</span></div>
                    <div class="col-6 text-end"><b style="color:#059669;">+{{ number_format($master->monthly_return, 1) }}%</b></div>
                </div>
                @endif
                <div class="d-flex justify-content-between mt-2" style="font-size:11px;">
                    <span class="stat-badge"><i class="fas fa-chart-bar"></i> {{ $master->total_trades }} trades</span>
                    <span class="stat-badge"><i class="fas fa-users"></i> {{ $master->followers_count }} {{ $master->max_followers > 0 ? "/ {$master->max_followers}" : '' }}</span>
                </div>
                @if($master->max_followers > 0 && $master->followers_count >= $master->max_followers)
                <button disabled class="btn btn-sm w-100 mt-3" style="background:var(--bg-card); color:var(--text-muted); border:1px solid var(--border);">Slots Full</button>
                @else
                <button type="button" class="btn btn-sm btn-gradient w-100 mt-3" onclick="toggleSubscribe('{{ $master->id }}', '{{ $master->user->name }}')">Subscribe</button>
                <div id="subscribe-{{ $master->id }}" style="display:none; margin-top:10px;">
                    <form method="POST" action="{{ route('dashboard.copy-trade.subscribe', $master) }}">
                        @csrf
                        <label style="font-size:11px; color:var(--text-muted);">Allocation Amount ($)</label>
                        <input type="number" name="allocation_amount" class="form-control form-control-sm mb-2" min="10" max="{{ $available }}" placeholder="e.g. 100" required>
                        <button type="submit" class="btn btn-sm btn-gradient w-100">Confirm Subscription</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="col-12 text-center" style="padding:40px; color:var(--text-muted);">
            <i class="fas fa-user-tie" style="font-size:40px; opacity:0.3; margin-bottom:12px;"></i>
            <p>No master traders available yet. Check back soon!</p>
        </div>
        @endforelse
    </div>

    {{ $masters->links() }}
</div>

<script>
function toggleSubscribe(id, name) {
    const el = document.getElementById('subscribe-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endsection
