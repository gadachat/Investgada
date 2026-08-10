@extends('layouts.dashboard')

@section('page-title', 'Commission History')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px; font-size: 22px;">
                <i class="fas fa-hand-holding-usd" style="color: #818cf8;"></i> Commission History
            </h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">All your earnings: referrals, matching, leadership, rank & profit share</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('dashboard.history.commissions.export', array_merge(request()->all(), ['format' => 'excel'])) }}" class="btn" style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 10px; padding: 8px 16px; font-size: 13px; font-weight: 600; text-decoration: none;">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('dashboard.history.commissions.export', array_merge(request()->all(), ['format' => 'pdf'])) }}" target="_blank" class="btn" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; border-radius: 10px; padding: 8px 16px; font-size: 13px; font-weight: 600; text-decoration: none;">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>

    <!-- Commission breakdown -->
    <div class="row g-3 mb-4">
        @php
            $typeLabels = [
                'referral_commission'   => 'Direct Referral',
                'direct_referral_bonus'  => 'Direct Bonus',
                'matching_bonus'         => 'Matching Bonus',
                'leadership_bonus'       => 'Leadership',
                'rank_promotion_bonus'   => 'Rank Promotion',
                'profit_share'           => 'Profit Share',
            ];
            $typeIcons = [
                'referral_commission'   => 'fa-user-plus',
                'direct_referral_bonus'  => 'fa-user-check',
                'matching_bonus'         => 'fa-code-branch',
                'leadership_bonus'       => 'fa-crown',
                'rank_promotion_bonus'   => 'fa-trophy',
                'profit_share'           => 'fa-chart-pie',
            ];
            $typeColors = [
                'referral_commission'   => '#818cf8',
                'direct_referral_bonus'  => '#6366f1',
                'matching_bonus'         => '#a855f7',
                'leadership_bonus'       => '#f59e0b',
                'rank_promotion_bonus'   => '#ef4444',
                'profit_share'           => '#10b981',
            ];
        @endphp

        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-coins"></i></div>
                <div class="stat-label">Total Earned</div>
                <div class="stat-value" style="font-size: 16px;">${{ number_format($totalEarned, 2) }}</div>
            </div>
        </div>

        @foreach($breakdown as $type => $data)
        @if($data['count'] > 0)
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: {{ ($typeColors[$type] ?? '#6366f1') }}20; color: {{ $typeColors[$type] ?? '#6366f1' }};">
                    <i class="fas {{ $typeIcons[$type] ?? 'fa-coins' }}"></i>
                </div>
                <div class="stat-label">{{ $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)) }}</div>
                <div class="stat-value" style="font-size: 16px;">${{ number_format($data['total'], 2) }}</div>
                <div style="font-size: 10px; color: var(--text-dim); margin-top: 2px;">{{ $data['count'] }} transactions</div>
            </div>
        </div>
        @endif
        @endforeach
    </div>

    <!-- Filter -->
    <div class="card-custom mb-4" style="padding: 16px 20px;">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label style="font-size: 11px; color: var(--text-muted);">Commission Type</label>
                <select name="type" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
                    <option value="all">All Types</option>
                    @foreach($typeLabels as $value => $label)
                    <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted);">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
            </div>
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted);">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn w-100" style="background: var(--gradient-primary); color: white; border: none; border-radius: 10px; padding: 10px;"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card-custom" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="table mb-0" style="color: var(--text); font-size: 13px;">
                <thead>
                    <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim); background: rgba(30,35,55,0.5);">
                        <th style="padding: 12px 16px;">Date</th>
                        <th style="padding: 12px 16px;">Type</th>
                        <th style="padding: 12px 16px;">Description</th>
                        <th style="padding: 12px 16px;">Reference</th>
                        <th style="padding: 12px 16px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $commission)
                    @php $label = $typeLabels[$commission->type] ?? ucfirst(str_replace('_', ' ', $commission->type)); @endphp
                    <tr style="border-bottom: 1px solid rgba(51,65,85,0.15);">
                        <td style="padding: 12px 16px; color: var(--text-dim); font-size: 12px;">{{ $commission->created_at->format('M d, Y H:i') }}</td>
                        <td style="padding: 12px 16px;">
                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 6px; background: {{ $typeColors[$commission->type] ?? '#6366f1' }}20; color: {{ $typeColors[$commission->type] ?? '#6366f1' }}; font-weight: 600;">{{ $label }}</span>
                        </td>
                        <td style="padding: 12px 16px; color: var(--text); font-size: 12px;">{{ $commission->description ?? '—' }}</td>
                        <td style="padding: 12px 16px; font-family: monospace; font-size: 12px; color: #818cf8;">{{ $commission->reference }}</td>
                        <td style="padding: 12px 16px; font-weight: 700; color: #10b981;">+${{ number_format((float) $commission->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--text-dim);">No commissions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $commissions->links() }}
</div>
@endsection
