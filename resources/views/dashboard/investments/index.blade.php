@extends('layouts.dashboard')

@section('page-title', 'My Investments')

@section('content')
<div class="fade-in">

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0; font-size: 22px;">
                <i class="fas fa-chart-pie" style="color: var(--purple-3);"></i> My Investments
            </h2>
            <p style="color: var(--text-muted); margin: 4px 0 0; font-size: 14px;">Track all your active and past investments</p>
        </div>
        <a href="{{ route('dashboard.packages.index') }}" class="btn-gradient" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fas fa-plus-circle"></i> New Investment
        </a>
    </div>

    @if(session('success'))
    <div style="background: var(--green-bg); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--green); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @if($investments->count() > 0)
    <div class="row g-3">
        @foreach($investments as $inv)
        @php
            $progress = $inv->expected_return > 0 ? min(100, ($inv->earned_so_far / $inv->expected_return) * 100) : 0;
            $daysLeft = $inv->matures_at ? max(0, now()->diffInDays($inv->matures_at, false)) : 0;
            $isMatured = $inv->matures_at && now()->gte($inv->matures_at);
            $statusColors = [
                'active'    => ['badge-up', '#10b981', 'Active'],
                'pending'   => ['badge-pending', '#f59e0b', 'Pending'],
                'completed' => ['badge-info', '#3b82f6', 'Completed'],
                'cancelled' => ['badge-down', '#ef4444', 'Cancelled'],
                'failed'    => ['badge-down', '#ef4444', 'Failed'],
            ];
            $sc = $statusColors[$inv->status] ?? $statusColors['pending'];
            $catIcons = [
                'crypto' => 'bitcoin-sign',
                'forex' => 'dollar-sign',
                'stocks' => 'chart-line',
                'bonds' => 'landmark',
                'binary' => 'layer-group',
                'mixed' => 'chart-pie',
            ];
            $catIcon = $catIcons[$inv->package?->category ?? 'mixed'] ?? 'chart-pie';
        @endphp
        <div class="col-lg-6 col-md-6 col-12 col-md-6 col-12">
            <div class="card-custom" style="padding: 0; overflow: hidden;">
                <div style="height: 3px; background: {{ $sc[1] === '#10b981' ? 'linear-gradient(90deg, #10b981, #14b8a6)' : ($sc[1] === '#3b82f6' ? 'linear-gradient(90deg, #3b82f6, #6366f1)' : 'linear-gradient(90deg, #f59e0b, #ef4444)') }};"></div>

                <div style="padding: 20px;">
                    <!-- Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                                <i class="fas fa-{{ $catIcon }}"></i>
                            </div>
                            <div>
                                <h6 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text-bright);">{{ $inv->package?->name ?? 'Custom Package' }}</h6>
                                <div style="font-size: 11px; color: var(--text-dim); font-family: monospace; margin-top: 2px;">{{ $inv->reference }}</div>
                            </div>
                        </div>
                        <span class="badge-custom {{ $sc[0] }}" style="font-size: 11px;">
                            <i class="fas fa-circle" style="font-size: 6px;"></i> {{ $sc[2] }}
                        </span>
                    </div>

                    <!-- Amount + Return grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 16px;">
                        <div style="padding: 12px; background: var(--bg-input); border-radius: 10px; text-align: center;">
                            <div style="font-size: 10px; color: var(--text-dim); text-transform: uppercase;">Invested</div>
                            <div style="font-size: 16px; font-weight: 700; color: var(--text-bright); margin-top: 2px;">${{ number_format($inv->amount, 2) }}</div>
                        </div>
                        <div style="padding: 12px; background: var(--bg-input); border-radius: 10px; text-align: center;">
                            <div style="font-size: 10px; color: var(--text-dim); text-transform: uppercase;">Earned</div>
                            <div style="font-size: 16px; font-weight: 700; color: var(--green); margin-top: 2px;">${{ number_format($inv->earned_so_far, 2) }}</div>
                        </div>
                        <div style="padding: 12px; background: var(--bg-input); border-radius: 10px; text-align: center;">
                            <div style="font-size: 10px; color: var(--text-dim); text-transform: uppercase;">Expected</div>
                            <div style="font-size: 16px; font-weight: 700; color: var(--purple-3); margin-top: 2px;">${{ number_format($inv->expected_return, 2) }}</div>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div style="margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-bottom: 6px;">
                            <span>Progress</span>
                            <span style="color: var(--text-bright); font-weight: 600;">{{ number_format($progress, 1) }}%</span>
                        </div>
                        <div style="height: 8px; background: var(--bg-input); border-radius: 4px; overflow: hidden;">
                            <div style="width: {{ $progress }}%; height: 100%; background: var(--gradient-primary); border-radius: 4px; transition: width 0.5s;"></div>
                        </div>
                    </div>

                    <!-- Timeline info -->
                    <div style="display: flex; justify-content: space-between; padding-top: 14px; border-top: 1px solid rgba(51,65,85,0.3); font-size: 12px;">
                        <div>
                            <span style="color: var(--text-dim);">Started:</span>
                            <span style="color: var(--text-bright); font-weight: 500;">{{ $inv->activated_at?->format('M d, Y') ?? '—' }}</span>
                        </div>
                        <div>
                            @if($isMatured)
                            <span style="color: var(--green);"><i class="fas fa-check-circle"></i> Matured</span>
                            @elseif($inv->status === 'active')
                            <span style="color: var(--text-dim);">Days left:</span>
                            <span style="color: var(--text-bright); font-weight: 500;">{{ $daysLeft }}</span>
                            @else
                            <span style="color: var(--text-dim);">{{ ucfirst($inv->status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
                        <div style="margin-top: 8px;">
                            <a href="{{ route('dashboard.invoice.investment', $inv) }}" target="_blank" style="font-size: 11px; color: var(--purple-3); text-decoration: none;">
                                <i class="fas fa-download"></i> Download Receipt
                            </a>
                        </div>
@endforeach
    </div>

    <!-- Pagination -->
    <div style="margin-top: 20px; display: flex; justify-content: center;">
        {{ $investments->links() }}
    </div>
    @else
    <div class="card-custom" style="text-align: center; padding: 60px 0;">
        <i class="fas fa-chart-pie" style="font-size: 48px; color: var(--border); margin-bottom: 16px;"></i>
        <h4 style="color: var(--text-muted); margin-bottom: 8px;">No investments yet</h4>
        <p style="font-size: 14px; color: var(--text-dim); margin-bottom: 20px;">Start your first investment and watch your money grow.</p>
        <a href="{{ route('dashboard.packages.index') }}" class="btn-gradient" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 12px 28px;">
            <i class="fas fa-rocket"></i> Browse Packages
        </a>
    </div>
    @endif
</div>
@endsection
