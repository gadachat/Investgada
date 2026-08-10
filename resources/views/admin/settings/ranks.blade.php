@extends('layouts.admin')

@section('page-title', 'Rank Management')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 8px; font-size: 22px;"><i class="fas fa-medal" style="color: var(--purple-3);"></i> Rank Management</h2>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Configure qualification criteria, bonuses, and rewards for each rank.</p>

    @if(session('success'))
    <div style="background: var(--green-bg); border: 1px solid rgba(16,185,129,0.3); color: var(--green); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    <div class="card-custom mb-3">
        <h5 style="color: var(--text-bright); margin-bottom: 16px;"><i class="fas fa-plus-circle" style="color: var(--green);"></i> Add New Rank</h5>
        <form method="POST" action="{{ route('admin.settings.ranks.store') }}" class="row g-3">
            @csrf
            <div class="col-md-3"><label style="font-size: 12px; color: var(--text-muted);">Rank Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="col-md-2"><label style="font-size: 12px; color: var(--text-muted);">Badge Color</label><input type="color" name="badge_color" value="#6366f1" class="form-control" style="height: 42px;"></div>
            <div class="col-md-2"><label style="font-size: 12px; color: var(--text-muted);">Min Investment ($)</label><input type="number" name="min_investment" class="form-control" value="0" step="0.01"></div>
            <div class="col-md-2"><label style="font-size: 12px; color: var(--text-muted);">Min Direct Refs</label><input type="number" name="min_direct_referrals" class="form-control" value="0"></div>
            <div class="col-md-2"><label style="font-size: 12px; color: var(--text-muted);">Min Team Vol ($)</label><input type="number" name="min_team_volume" class="form-control" value="0" step="0.01"></div>
            <div class="col-md-3"><label style="font-size: 12px; color: var(--text-muted);">Matching %</label><input type="number" name="matching_bonus_percent" class="form-control" value="5" step="0.01" required></div>
            <div class="col-md-3"><label style="font-size: 12px; color: var(--text-muted);">Direct Referral %</label><input type="number" name="direct_referral_percent" class="form-control" value="5" step="0.01" required></div>
            <div class="col-md-3"><label style="font-size: 12px; color: var(--text-muted);">Profit Share %</label><input type="number" name="profit_share_percent" class="form-control" value="0" step="0.01"></div>
            <div class="col-md-3"><label style="font-size: 12px; color: var(--text-muted);">Salary Bonus ($)</label><input type="number" name="salary_bonus" class="form-control" value="0" step="0.01"></div>
            <div class="col-12"><button type="submit" class="btn-gradient"><i class="fas fa-plus"></i> Create Rank</button></div>
        </form>
    </div>

    <div class="row g-3">
        @foreach($ranks as $rank)
        <div class="col-lg-4 col-md-6">
            <div class="card-custom" style="padding: 0; overflow: hidden;">
                <div style="height: 4px; background: {{ $rank->badge_color }};"></div>
                <div style="padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: {{ $rank->badge_color }}; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px;"><i class="fas fa-medal"></i></div>
                            <div><h6 style="margin: 0; color: var(--text-bright); font-size: 15px;">{{ $rank->name }}</h6><span style="font-size: 11px; color: var(--text-dim);">Rank #{{ $rank->sort_order }}</span></div>
                        </div>
                        <span class="badge-custom {{ $rank->is_active ? 'badge-up' : 'badge-down' }}">{{ $rank->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div style="padding: 8px 10px; background: var(--bg-input); border-radius: 8px;"><div style="font-size: 10px; color: var(--text-dim);">Min Invest</div><div style="font-size: 13px; font-weight: 600; color: var(--text-bright);">${{ number_format($rank->min_investment) }}</div></div>
                        <div style="padding: 8px 10px; background: var(--bg-input); border-radius: 8px;"><div style="font-size: 10px; color: var(--text-dim);">Min Refs</div><div style="font-size: 13px; font-weight: 600; color: var(--text-bright);">{{ $rank->min_direct_referrals }}</div></div>
                        <div style="padding: 8px 10px; background: var(--bg-input); border-radius: 8px;"><div style="font-size: 10px; color: var(--text-dim);">Matching %</div><div style="font-size: 13px; font-weight: 600; color: var(--purple-3);">{{ $rank->matching_bonus_percent }}%</div></div>
                        <div style="padding: 8px 10px; background: var(--bg-input); border-radius: 8px;"><div style="font-size: 10px; color: var(--text-dim);">Direct %</div><div style="font-size: 13px; font-weight: 600; color: var(--blue-1);">{{ $rank->direct_referral_percent }}%</div></div>
                        <div style="padding: 8px 10px; background: var(--bg-input); border-radius: 8px;"><div style="font-size: 10px; color: var(--text-dim);">Profit Share</div><div style="font-size: 13px; font-weight: 600; color: var(--green);">{{ $rank->profit_share_percent }}%</div></div>
                        <div style="padding: 8px 10px; background: var(--bg-input); border-radius: 8px;"><div style="font-size: 10px; color: var(--text-dim);">Salary</div><div style="font-size: 13px; font-weight: 600; color: var(--text-bright);">${{ number_format($rank->salary_bonus) }}</div></div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
