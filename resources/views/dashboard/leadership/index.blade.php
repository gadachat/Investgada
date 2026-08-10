@extends('layouts.dashboard')

@section('page-title', 'Leadership Bonus')

@section('content')
<div class="fade-in">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px; font-size: 22px;">
                <i class="fas fa-crown" style="color: #f59e0b;"></i> Leadership Bonus
            </h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Monthly company profit pool distributed to qualifying leaders.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245,158,11,0.15); color: #f59e0b;"><i class="fas fa-coins"></i></div>
                <div class="stat-label">Total Earned</div>
                <div class="stat-value">${{ number_format($totalEarned, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-label">Cycles Received</div>
                <div class="stat-value">{{ $totalCycles }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-clock"></i></div>
                <div class="stat-label">Last Payout</div>
                <div class="stat-value" style="font-size: 16px;">{{ $lastBonus?->paid_at?->format('M d, Y') ?? '—' }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Rank Progress -->
        <div class="col-lg-5">
            <div class="card-custom" style="padding: 24px;">
                <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 16px;">
                    <i class="fas fa-medal" style="color: #f59e0b;"></i> Rank Progress
                </h5>

                @if($currentRank)
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding: 14px; border-radius: 12px; background: {{ $currentRank->badge_color }}15; border: 1px solid {{ $currentRank->badge_color }}30;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $currentRank->badge_color }}; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                        <i class="fas fa-medal"></i>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--text-dim);">Current Rank</div>
                        <div style="font-size: 16px; font-weight: 700; color: {{ $currentRank->badge_color }};">{{ $currentRank->name }}</div>
                    </div>
                </div>
                @else
                <div style="padding: 14px; border-radius: 12px; background: rgba(100,116,139,0.1); border: 1px solid rgba(100,116,139,0.2); margin-bottom: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">
                    No rank yet — start investing and referring to qualify.
                </div>
                @endif

                @if($nextRank && !$progress['complete'])
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 8px;">Next Rank: <span style="color: {{ $nextRank->badge_color }}; font-weight: 600;">{{ $nextRank->name }}</span></div>

                    @foreach($progress['items'] as $item)
                    @php $pct = $item['required'] > 0 ? min(100, ($item['current'] / $item['required']) * 100) : 100; @endphp
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                            <span style="color: var(--text-muted);">{{ $item['label'] }}</span>
                            <span style="color: {{ $item['met'] ? '#10b981' : 'var(--text-dim)' }}; font-weight: 600;">
                                @if($item['label'] === 'Direct Referrals')
                                    {{ $item['current'] }} / {{ $item['required'] }}
                                @else
                                    ${{ number_format($item['current']) }} / ${{ number_format($item['required']) }}
                                @endif
                                @if($item['met']) <i class="fas fa-check-circle"></i> @endif
                            </span>
                        </div>
                        <div style="height: 6px; border-radius: 3px; background: var(--bg-input); overflow: hidden;">
                            <div style="height: 100%; width: {{ $pct }}%; border-radius: 3px; background: {{ $item['met'] ? '#10b981' : 'linear-gradient(90deg, #6366f1, #7c3aed)' }};"></div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Rank rewards info -->
                <div style="padding: 14px; border-radius: 12px; background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.1);">
                    <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 8px;">Benefits at {{ $nextRank->name }}:</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        @if($nextRank->matching_bonus_percent > 0)
                        <span style="font-size: 11px; padding: 3px 10px; border-radius: 8px; background: rgba(99,102,241,0.1); color: #818cf8;">Matching: {{ $nextRank->matching_bonus_percent }}%</span>
                        @endif
                        @if($nextRank->direct_referral_percent > 0)
                        <span style="font-size: 11px; padding: 3px 10px; border-radius: 8px; background: rgba(59,130,246,0.1); color: #3b82f6;">Direct: {{ $nextRank->direct_referral_percent }}%</span>
                        @endif
                        @if($nextRank->profit_share_percent > 0)
                        <span style="font-size: 11px; padding: 3px 10px; border-radius: 8px; background: rgba(16,185,129,0.1); color: #10b981;">Profit Share: {{ $nextRank->profit_share_percent }}%</span>
                        @endif
                        @if($nextRank->salary_bonus > 0)
                        <span style="font-size: 11px; padding: 3px 10px; border-radius: 8px; background: rgba(245,158,11,0.1); color: #f59e0b;">Salary: ${{ number_format($nextRank->salary_bonus) }}</span>
                        @endif
                    </div>
                </div>
                @elseif($progress['complete'])
                <div style="padding: 20px; border-radius: 12px; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); text-align: center;">
                    <i class="fas fa-trophy" style="font-size: 32px; color: #f59e0b; margin-bottom: 10px;"></i>
                    <div style="color: #10b981; font-weight: 600; font-size: 14px;">All requirements met for {{ $nextRank?->name ?? 'next rank' }}!</div>
                    <div style="color: var(--text-dim); font-size: 12px; margin-top: 4px;">Your rank will be updated on the next evaluation.</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Right: Bonus History -->
        <div class="col-lg-7">
            <div class="card-custom" style="padding: 0; overflow: hidden;">
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
                    <h5 style="color: var(--text-bright); font-weight: 700; margin: 0;">
                        <i class="fas fa-history" style="color: #818cf8;"></i> Bonus History
                    </h5>
                </div>

                @forelse($bonuses as $bonus)
                <div style="padding: 16px 20px; border-bottom: 1px solid rgba(51,65,85,0.15); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 14px; font-weight: 600; color: var(--text-bright);">{{ $bonus->pool_name }}</div>
                        <div style="font-size: 12px; color: var(--text-dim); margin-top: 2px;">
                            <span style="color: {{ $bonus->rank->badge_color ?? '#818cf8' }};">{{ $bonus->rank?->name ?? 'Unknown' }}</span>
                            · {{ $bonus->paid_at?->format('M d, Y') ?? $bonus->created_at->format('M d, Y') }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-dim); margin-top: 2px;">
                            Pool: ${{ number_format($bonus->total_pool_amount) }} · Share: {{ number_format($bonus->user_share_percent, 1) }}% · Team: ${{ number_format($bonus->team_volume) }}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 18px; font-weight: 700; color: #10b981;">+${{ number_format($bonus->bonus_amount, 2) }}</div>
                        <span style="font-size: 10px; padding: 2px 8px; border-radius: 20px; background: {{ $bonus->status === 'paid' ? 'rgba(16,185,129,0.15)' : 'rgba(245,158,11,0.15)' }}; color: {{ $bonus->status === 'paid' ? '#10b981' : '#f59e0b' }}; text-transform: capitalize; font-weight: 600;">{{ $bonus->status }}</span>
                    </div>
                </div>
                @empty
                <div style="padding: 40px; text-align: center; color: var(--text-dim);">
                    <i class="fas fa-crown" style="font-size: 40px; opacity: 0.3; margin-bottom: 12px; display: block;"></i>
                    <p style="font-size: 14px;">No leadership bonuses yet.</p>
                    <p style="font-size: 12px;">Qualify for a higher rank to start receiving monthly pool distributions.</p>
                </div>
                @endforelse
            </div>

            {{ $bonuses->links() }}
        </div>
    </div>
</div>
@endsection
