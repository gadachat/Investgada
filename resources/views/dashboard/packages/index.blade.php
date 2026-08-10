@extends('layouts.dashboard')

@section('page-title', 'Investment Packages')

@section('content')
<div class="fade-in">

    <!-- Page header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0; font-size: 22px;">
                <i class="fas fa-chart-pie" style="color: var(--purple-3);"></i> Investment Packages
            </h2>
            <p style="color: var(--text-muted); margin: 4px 0 0; font-size: 14px;">Choose a plan and start earning passive income</p>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <div class="card-custom" style="padding: 12px 18px; display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">Deposit Wallet</div>
                    <div style="font-size: 16px; font-weight: 700; color: var(--text-bright);">${{ number_format($depositWallet?->balance ?? 0, 2) }}</div>
                </div>
            </div>
            <div class="card-custom" style="padding: 12px 18px; display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); color: var(--green); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">Active Plans</div>
                    <div style="font-size: 16px; font-weight: 700; color: var(--text-bright);">{{ $activeInvestments }} / {{ $maxActive }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div style="background: var(--red-bg); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--red); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <!-- Category tabs -->
    <div style="display: flex; gap: 6px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 4px;" id="categoryTabs">
        <button class="cat-tab active" onclick="filterCategory('all', this)">All Packages</button>
        @foreach($packages->keys() as $cat)
        <button class="cat-tab" onclick="filterCategory('{{ $cat }}', this)">
            <i class="fas fa-{{ $cat === 'crypto' ? 'bitcoin-sign' : ($cat === 'forex' ? 'dollar-sign' : ($cat === 'stocks' ? 'chart-line' : ($cat === 'bonds' ? 'landmark' : 'layer-group'))) }}"></i>
            {{ ucfirst($cat) }}
        </button>
        @endforeach
    </div>

    <!-- Packages grid -->
    <div class="row g-3">
        @foreach($packages as $category => $catPackages)
        @foreach($catPackages as $package)
        @php
            $catColors = [
                'crypto' => ['gradient' => 'linear-gradient(135deg, #f7931a, #f3ba2f)', 'icon' => 'bitcoin-sign', 'bg' => 'rgba(247, 147, 26, 0.1)'],
                'forex'  => ['gradient' => 'linear-gradient(135deg, #3b82f6, #6366f1)', 'icon' => 'dollar-sign', 'bg' => 'rgba(59, 130, 246, 0.1)'],
                'stocks' => ['gradient' => 'linear-gradient(135deg, #10b981, #14b8a6)', 'icon' => 'chart-line', 'bg' => 'rgba(16, 185, 129, 0.1)'],
                'bonds'  => ['gradient' => 'linear-gradient(135deg, #8b5cf6, #a855f7)', 'icon' => 'landmark', 'bg' => 'rgba(139, 92, 246, 0.1)'],
                'mixed'  => ['gradient' => 'linear-gradient(135deg, #6366f1, #a855f7)', 'icon' => 'layer-group', 'bg' => 'rgba(99, 102, 241, 0.1)'],
            ];
            $cc = $catColors[$category] ?? $catColors['mixed'];
            $cycles = intdiv($package->duration_days, $package->cycle_days);
            $perCycleReturn = bcmul($package->min_amount, bcdiv($package->return_rate, 100, 8), 2);
        @endphp
        <div class="col-xl-4 col-lg-6 col-md-6 col-12 col-md-6 col-12 package-card" data-category="{{ $category }}">
            <div class="card-custom" style="padding: 0; overflow: hidden; height: 100%; display: flex; flex-direction: column;">
                <!-- Top accent bar -->
                <div style="height: 4px; background: {{ $cc['gradient'] }};"></div>

                <!-- Card body -->
                <div style="padding: 22px; flex: 1; display: flex; flex-direction: column;">
                    <!-- Package header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $cc['gradient'] }}; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                                <i class="fas fa-{{ $cc['icon'] }}"></i>
                            </div>
                            <div>
                                <h5 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-bright);">{{ $package->name }}</h5>
                                <span class="badge-custom badge-purple" style="margin-top: 4px;">{{ strtoupper($category) }}</span>
                            </div>
                        </div>
                        @if($package->featured)
                        <span style="background: var(--gradient-primary); color: white; font-size: 10px; font-weight: 600; padding: 4px 10px; border-radius: 8px;">
                            <i class="fas fa-star"></i> POPULAR
                        </span>
                        @endif
                    </div>

                    @if($package->description)
                    <p style="font-size: 13px; color: var(--text-muted); margin: 0 0 16px; line-height: 1.5;">{{ $package->description }}</p>
                    @endif

                    <!-- Return rate highlight -->
                    <div style="background: {{ $cc['bg'] }}; border-radius: 12px; padding: 16px; margin-bottom: 16px; text-align: center;">
                        <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Return Rate</div>
                        <div style="font-size: 32px; font-weight: 800; color: var(--text-bright);">{{ $package->return_rate }}%</div>
                        <div style="font-size: 12px; color: var(--text-muted);">per {{ $package->return_type }} cycle</div>
                    </div>

                    <!-- Package stats grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px;">
                        <div style="padding: 10px 12px; background: var(--bg-input); border-radius: 10px;">
                            <div style="font-size: 10px; color: var(--text-dim); text-transform: uppercase;">Min / Max</div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--text-bright);">${{ number_format($package->min_amount, 0) }} - {{ $package->max_amount ? '$' . number_format($package->max_amount, 0) : '∞' }}</div>
                        </div>
                        <div style="padding: 10px 12px; background: var(--bg-input); border-radius: 10px;">
                            <div style="font-size: 10px; color: var(--text-dim); text-transform: uppercase;">Duration</div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--text-bright);">{{ $package->duration_days }} days</div>
                        </div>
                        <div style="padding: 10px 12px; background: var(--bg-input); border-radius: 10px;">
                            <div style="font-size: 10px; color: var(--text-dim); text-transform: uppercase;">Payout Cycle</div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--text-bright);">{{ $package->cycle_days }} day(s)</div>
                        </div>
                        <div style="padding: 10px 12px; background: var(--bg-input); border-radius: 10px;">
                            <div style="font-size: 10px; color: var(--text-dim); text-transform: uppercase;">Total Cycles</div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--text-bright);">{{ $cycles }}</div>
                        </div>
                    </div>

                    <!-- Features list -->
                    <div style="margin-bottom: 16px; flex: 1;">
                        @if($package->principal_return)
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 13px; color: var(--green);">
                            <i class="fas fa-check-circle"></i> Principal returned at maturity
                        </div>
                        @endif
                        @if($package->compounding)
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 13px; color: var(--green);">
                            <i class="fas fa-check-circle"></i> Compounding enabled
                        </div>
                        @endif
                        @if($package->total_return_cap > 0)
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 13px; color: var(--text-muted);">
                            <i class="fas fa-check-circle" style="color: var(--blue-1);"></i> Total return cap: {{ $package->total_return_cap }}%
                        </div>
                        @endif
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 13px; color: var(--text-muted);">
                            <i class="fas fa-check-circle" style="color: var(--blue-1);"></i> Type: {{ ucfirst($package->type) }}
                        </div>
                    </div>

                    <!-- Invest button -->
                    <a href="{{ route('dashboard.packages.show', $package->slug) }}" class="btn-gradient" style="display: block; text-align: center; text-decoration: none; padding: 14px;">
                        <i class="fas fa-rocket"></i> Invest Now
                    </a>
                </div>
            </div>
        </div>
        @endforeach
        @endforeach
    </div>

    @if($packages->flatten()->isEmpty())
    <div style="text-align: center; padding: 60px 0; color: var(--text-dim);">
        <i class="fas fa-chart-pie" style="font-size: 48px; color: var(--border); margin-bottom: 16px;"></i>
        <h4 style="color: var(--text-muted);">No packages available</h4>
        <p style="font-size: 14px;">Check back soon for new investment opportunities.</p>
    </div>
    @endif
</div>

<style>
.cat-tab {
    background: var(--bg-input);
    border: 1px solid var(--border);
    color: var(--text-muted);
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
}
.cat-tab:hover { border-color: var(--purple-1); color: var(--text-bright); }
.cat-tab.active {
    background: var(--gradient-primary);
    border-color: transparent;
    color: white;
}
</style>

<script>
function filterCategory(cat, btn) {
    document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.package-card').forEach(card => {
        if (cat === 'all' || card.dataset.category === cat) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
@endsection
