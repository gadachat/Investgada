@extends('layouts.dashboard')

@section('page-title', 'Profit Sharing')

@section('content')
<div class="fade-in">

    <!-- Header -->
    <div class="mb-4">
        <h2 class="page-title mb-1">
            <i class="fas fa-coins me-2" style="color: var(--purple-1);"></i>
            Profit Sharing
        </h2>
        <p class="text-muted mb-0" style="font-size: 14px;">Your share of the platform's trading profits</p>
    </div>

    @if(!$settings['profit_share_enabled'])
    <div class="custom-card" style="border: 1px solid #f59e0b; background: rgba(245, 158, 11, 0.05); padding: 16px 20px; margin-bottom: 24px;">
        <div class="d-flex align-items-center gap-3">
            <i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 24px;"></i>
            <div>
                <span style="color: var(--text-bright); font-weight: 600;">Profit sharing is currently disabled</span>
                <p style="color: var(--text-muted); margin: 2px 0 0; font-size: 13px;">Check back later or contact support for more information.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Earned -->
        <div class="col-md-3">
            <div class="custom-card p-4 h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-dollar-sign" style="color: #10b981;"></i>
                    </div>
                    <span style="color: var(--text-muted); font-size: 13px; font-weight: 500;">Total Earned</span>
                </div>
                <h3 style="color: var(--text-bright); margin: 0; font-weight: 700; font-size: 28px;">
                    ${{ number_format($totalEarned, 2) }}
                </h3>
                <small style="color: var(--text-dim);">From {{ $totalCycles }} cycle(s)</small>
            </div>
        </div>

        <!-- Active Capital -->
        <div class="col-md-3">
            <div class="custom-card p-4 h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(99, 102, 241, 0.15); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-wallet" style="color: var(--purple-1);"></i>
                    </div>
                    <span style="color: var(--text-muted); font-size: 13px; font-weight: 500;">Active Capital</span>
                </div>
                <h3 style="color: var(--text-bright); margin: 0; font-weight: 700; font-size: 28px;">
                    ${{ number_format($totalActiveCapital, 2) }}
                </h3>
                <small style="color: var(--text-dim);">{{ $activeInvestments->count() }} active investment(s)</small>
            </div>
        </div>

        <!-- Weighted Capital -->
        <div class="col-md-3">
            <div class="custom-card p-4 h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(124, 58, 237, 0.15); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-balance-scale" style="color: var(--purple-2);"></i>
                    </div>
                    <span style="color: var(--text-muted); font-size: 13px; font-weight: 500;">Weighted Capital</span>
                </div>
                <h3 style="color: var(--text-bright); margin: 0; font-weight: 700; font-size: 28px;">
                    ${{ number_format($weightedCapital, 2) }}
                </h3>
                <small style="color: var(--text-dim);">Used for share calculation</small>
            </div>
        </div>

        <!-- Next Cycle -->
        <div class="col-md-3">
            <div class="custom-card p-4 h-100" style="background: linear-gradient(135deg, var(--purple-1) 0%, var(--purple-2) 100%);">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clock" style="color: white;"></i>
                    </div>
                    <span style="color: rgba(255,255,255,0.8); font-size: 13px; font-weight: 500;">Next Cycle</span>
                </div>
                <h3 style="color: white; margin: 0; font-weight: 700; font-size: 20px;">
                    {{ ucfirst($settings['profit_cycle_frequency']) }}
                </h3>
                <small style="color: rgba(255,255,255,0.7);">
                    {{ $nextCycle->format('M j, Y H:i') }}
                </small>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Distribution History -->
        <div class="col-lg-8">
            <div class="custom-card p-4">
                <h5 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-history me-2" style="color: var(--purple-1);"></i>
                    Distribution History
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover" style="color: var(--text);">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border);">
                                <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border: none;">Cycle ID</th>
                                <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border: none;">Date</th>
                                <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border: none;">Pool Size</th>
                                <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border: none;">Your Share</th>
                                <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border: none;">%</th>
                                <th style="border: none;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($distributions as $d)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="border: none; font-family: monospace; font-size: 13px; color: var(--text-muted);">
                                    {{ \Str::limit($d->cycle_id, 14) }}
                                </td>
                                <td style="border: none; font-size: 13px;">
                                    {{ \Carbon\Carbon::parse($d->created_at)->format('M j, Y g:i A') }}
                                </td>
                                <td style="border: none; font-size: 13px; color: var(--text-muted);">
                                    ${{ number_format($d->pool_amount, 2) }}
                                </td>
                                <td style="border: none; font-weight: 700; color: #10b981;">
                                    +${{ number_format($d->amount, 2) }}
                                </td>
                                <td style="border: none;">
                                    <span style="color: var(--purple-3); font-weight: 600; font-size: 13px;">
                                        {{ number_format($d->share_percentage, 2) }}%
                                    </span>
                                </td>
                                <td style="border: none;">
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; font-size: 11px;">
                                        {{ ucfirst($d->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="border: none; text-align: center; padding: 40px;">
                                    <i class="fas fa-inbox" style="font-size: 36px; color: var(--text-dim); margin-bottom: 12px; display: block;"></i>
                                    <p style="color: var(--text-muted);">No distributions yet</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $distributions->links() }}
            </div>
        </div>

        <!-- Package Breakdown -->
        <div class="col-lg-4">
            <div class="custom-card p-4 mb-3">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-chart-pie me-2" style="color: var(--purple-2);"></i>
                    Eligible Investments
                </h6>
                @forelse($packageBreakdown as $pkg)
                <div style="padding: 12px; border-radius: 10px; background: var(--bg-input); margin-bottom: 8px;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="color: var(--text-bright); font-weight: 600; font-size: 13px;">{{ $pkg['package_name'] }}</span>
                        <span class="badge" style="background: var(--purple-1); color: white; font-size: 10px;">{{ ucfirst($pkg['category']) }}</span>
                    </div>
                    <div class="d-flex justify-content-between" style="font-size: 12px; color: var(--text-muted);">
                        <span>Amount: ${{ number_format($pkg['amount'], 2) }}</span>
                        <span>Weight: {{ number_format($pkg['weight'], 1) }}x</span>
                    </div>
                    <div style="margin-top: 6px; padding-top: 6px; border-top: 1px solid var(--border); font-size: 12px;">
                        <span style="color: var(--text-muted);">Weighted: </span>
                        <span style="color: var(--purple-3); font-weight: 600;">${{ number_format($pkg['weighted'], 2) }}</span>
                    </div>
                </div>
                @empty
                <p style="color: var(--text-muted); font-size: 13px; text-align: center; padding: 20px 0;">No active investments</p>
                @endforelse
            </div>

            <!-- How it works -->
            <div class="custom-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 12px;">
                    <i class="fas fa-info-circle me-2" style="color: var(--purple-1);"></i>
                    How It Works
                </h6>
                <div style="font-size: 13px; color: var(--text-muted); line-height: 1.7;">
                    <div class="d-flex gap-2 mb-2">
                        <span style="color: var(--purple-3); font-weight: 700;">1.</span>
                        <span>Platform allocates {{ $settings['profit_pool_percentage'] }}% of trading profits to the pool</span>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <span style="color: var(--purple-3); font-weight: 700;">2.</span>
                        <span>Your share = (weighted capital ÷ total pool capital) × pool amount</span>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <span style="color: var(--purple-3); font-weight: 700;">3.</span>
                        <span>Higher package weight = larger share of the pool</span>
                    </div>
                    <div class="d-flex gap-2">
                        <span style="color: var(--purple-3); font-weight: 700;">4.</span>
                        <span>Profits credited to your interest wallet every {{ $settings['profit_cycle_frequency'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
