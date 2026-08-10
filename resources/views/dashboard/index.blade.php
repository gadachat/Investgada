@extends('layouts.dashboard')

@section('page-title', 'Dashboard Overview')

@section('content')
<div class="fade-in">

    <!-- ========== HERO WELCOME BANNER ========== -->
    <div style="background: linear-gradient(135deg, #6366f1 0%, #7c3aed 40%, #a855f7 100%); border-radius: 16px; padding: 28px; margin-bottom: 24px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -40px; right: 60px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(168, 85, 247, 0.2) 0%, transparent 70%); border-radius: 50%;"></div>

        <div style="position: relative; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h2 style="color: white; font-weight: 700; margin: 0 0 4px;">
                    Welcome back, {{ auth()->user()->name }}! 👋
                </h2>
                <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 14px;">
                    Here's what's happening with your investments today.
                </p>
                @if($rank)
                <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 6px 14px; border-radius: 20px; margin-top: 12px;">
                    <i class="fas fa-medal" style="color: {{ $rank->badge_color }};"></i>
                    <span style="color: white; font-size: 13px; font-weight: 600;">Rank: {{ $rank->name }}</span>
                </div>
                @endif
            </div>
            <div style="text-align: right;">
                <div style="color: rgba(255,255,255,0.7); font-size: 13px; margin-bottom: 4px;">Total Portfolio Value</div>
                <div style="color: white; font-size: 32px; font-weight: 800; font-variant-numeric: tabular-nums;">${{ number_format($totalBalance + $totalEarnedFromInvestments, 2) }}</div>
                <div style="color: rgba(255,255,255,0.7); font-size: 12px; margin-top: 4px;">
                    <i class="fas fa-arrow-up"></i> +{{ number_format($totalEarnedFromInvestments, 2) }} all-time earnings
                </div>
            </div>
        </div>
    </div>

    <!-- ========== STAT CARDS ========== -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-wallet"></i></div>
                <div class="stat-label">Total Balance</div>
                <div class="stat-value">${{ number_format($totalBalance, 2) }}</div>
                <div class="stat-sub up"><i class="fas fa-arrow-up"></i> Across all wallets</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-chart-pie"></i></div>
                <div class="stat-label">Active Investments</div>
                <div class="stat-value">{{ $activeInvestments->count() }}</div>
                <div class="stat-sub"><span style="color: var(--text-muted)">${{ number_format($totalInvested, 2) }} invested</span></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-coins"></i></div>
                <div class="stat-label">Total Earnings</div>
                <div class="stat-value">${{ number_format($totalEarnedFromInvestments, 2) }}</div>
                <div class="stat-sub up">
                    <i class="fas fa-arrow-up"></i>
                    {{ $totalExpectedReturn > 0 ? number_format(($totalEarnedFromInvestments / $totalExpectedReturn) * 100, 1) : 0 }}% of expected
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-users"></i></div>
                <div class="stat-label">Referral Earnings</div>
                <div class="stat-value">${{ number_format($referralEarnings, 2) }}</div>
                <div class="stat-sub"><span style="color: var(--text-muted)">{{ $directReferrals }} direct referrals</span></div>
            </div>
        </div>
    </div>

    <!-- ========== REAL-TIME CRYPTO CHART ========== -->
    <div class="mb-4">
        @include('dashboard.partials._crypto-chart')
    </div>

    <!-- ========== MAIN GRID ========== -->
    <div class="row g-3">

        <!-- LEFT COLUMN (2/3) -->
        <div class="col-lg-8">

            <!-- EARNINGS CHART -->
            <div class="card-custom mb-3">
                <div class="section-header">
                    <h5><i class="fas fa-chart-area" style="color: var(--purple-3);"></i> Earnings Overview</h5>
                    <select class="form-control form-control-sm" id="chartRange" onchange="updateChart(this.value)" style="width: auto; background: var(--bg-input); border: 1px solid var(--border); color: var(--text); font-size: 12px; border-radius: 8px;">
                        <option value="7">Last 7 Days</option>
                        <option value="30">Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                </div>
                <div id="earningsChart"></div>
            </div>

            <!-- LIVE PRICE TICKER -->
            <div class="price-ticker mb-3">
                <div class="ticker-header">
                    <h6>
                        <i class="fas fa-broadcast-tower" style="color: var(--purple-3);"></i>
                        Live Market Prices
                        <span class="live-badge">LIVE</span>
                    </h6>
                    <div class="ticker-tabs">
                        <button class="ticker-tab active" onclick="switchMarketTab('crypto', this)">Crypto</button>
                        <button class="ticker-tab" onclick="switchMarketTab('forex', this)">Forex</button>
                        <button class="ticker-tab" onclick="switchMarketTab('indices', this)">Indices</button>
                    </div>
                </div>
                <div class="ticker-body" id="tickerBody">
                    <div style="text-align: center; padding: 40px 0; color: var(--text-dim);">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <p style="margin-top: 8px; font-size: 13px;">Loading live prices...</p>
                    </div>
                </div>
                <div style="padding: 8px 16px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 11px; color: var(--text-dim);" id="lastUpdate">Updating...</span>
                    <span style="font-size: 11px; color: var(--text-dim);">
                        <i class="fas fa-info-circle"></i> Prices update every 30 seconds
                    </span>
                </div>
            </div>

            <!-- ACTIVE INVESTMENTS -->
            <div class="card-custom mb-3">
                <div class="section-header">
                    <h5><i class="fas fa-chart-pie" style="color: var(--blue-1);"></i> Active Investments</h5>
                    <a href="{{ route('dashboard.investments.index') }}" class="section-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                @if($activeInvestments->count() > 0)
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Package</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Progress</th>
                                <th>Earned</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeInvestments as $inv)
                            @php
                                $progress = $inv->expected_return > 0 ? min(100, ($inv->earned_so_far / $inv->expected_return) * 100) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
                                            <i class="fas fa-{{ $inv->package?->category === 'crypto' ? 'bitcoin-sign' : ($inv->package?->category === 'forex' ? 'dollar-sign' : 'chart-line') }}"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-bright); font-size: 13px;">{{ $inv->package?->name ?? 'Custom' }}</div>
                                            <div style="font-size: 11px; color: var(--text-dim);">{{ $inv->reference }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-custom badge-purple">{{ strtoupper($inv->package?->category ?? 'N/A') }}</span>
                                </td>
                                <td style="font-weight: 600; color: var(--text-bright);">${{ number_format($inv->amount, 2) }}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 80px; height: 6px; background: var(--bg-input); border-radius: 3px; overflow: hidden;">
                                            <div style="width: {{ $progress }}%; height: 100%; background: var(--gradient-primary); border-radius: 3px;"></div>
                                        </div>
                                        <span style="font-size: 11px; color: var(--text-muted);">{{ number_format($progress, 0) }}%</span>
                                    </div>
                                </td>
                                <td style="color: var(--green); font-weight: 600;">${{ number_format($inv->earned_so_far, 2) }}</td>
                                <td>
                                    <span class="badge-custom badge-up">
                                        <i class="fas fa-circle" style="font-size: 6px;"></i> Active
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div style="text-align: center; padding: 40px 0; color: var(--text-dim);">
                    <i class="fas fa-chart-pie" style="font-size: 36px; margin-bottom: 12px; color: var(--border);"></i>
                    <p style="font-size: 14px;">No active investments yet</p>
                    <a href="{{ route('dashboard.packages.index') }}" class="btn-gradient" style="display: inline-block; text-decoration: none;">
                        <i class="fas fa-plus-circle"></i> Start Investing
                    </a>
                </div>
                @endif
            </div>

            <!-- RECENT TRANSACTIONS -->
            <div class="card-custom">
                <div class="section-header">
                    <h5><i class="fas fa-list-ul" style="color: var(--purple-3);"></i> Recent Transactions</h5>
                    <a href="{{ route('dashboard.wallet.history') }}" class="section-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                @if($recentTransactions->count() > 0)
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Reference</th>
                                <th>Direction</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $tx)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 28px; height: 28px; border-radius: 7px; background: {{ $tx->direction === 'credit' ? 'var(--green-bg)' : 'var(--red-bg)' }}; display: flex; align-items: center; justify-content: center; color: {{ $tx->direction === 'credit' ? 'var(--green)' : 'var(--red)' }}; font-size: 11px;">
                                            <i class="fas fa-{{ $tx->direction === 'credit' ? 'arrow-down' : 'arrow-up' }}"></i>
                                        </div>
                                        <span style="font-size: 12px; color: var(--text-bright); text-transform: capitalize;">{{ str_replace('_', ' ', $tx->type) }}</span>
                                    </div>
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted);">{{ $tx->reference }}</td>
                                <td>
                                    <span class="badge-custom {{ $tx->direction === 'credit' ? 'badge-up' : 'badge-down' }}">{{ $tx->direction }}</span>
                                </td>
                                <td style="font-weight: 600; color: {{ $tx->direction === 'credit' ? 'var(--green)' : 'var(--red)' }}">
                                    {{ $tx->direction === 'credit' ? '+' : '-' }}${{ number_format($tx->amount, 2) }}
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted);">{{ $tx->created_at->format('M d, H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div style="text-align: center; padding: 30px; color: var(--text-dim);">
                    <i class="fas fa-receipt" style="font-size: 30px; margin-bottom: 10px; color: var(--border);"></i>
                    <p style="font-size: 14px;">No transactions yet</p>
                </div>
                @endif
            </div>
        </div>

        <!-- RIGHT COLUMN (1/3) -->
        <div class="col-lg-4">

            <!-- WALLETS WIDGET -->
            <div class="card-custom mb-3">
                <div class="section-header">
                    <h5><i class="fas fa-wallet" style="color: var(--purple-3);"></i> My Wallets</h5>
                </div>
                @foreach($wallets as $wallet)
                <div class="wallet-card mb-2">
                    <div class="wallet-icon" style="
                        background: {{ $wallet->type === 'deposit' ? 'rgba(59, 130, 246, 0.15)' : ($wallet->type === 'interest' ? 'rgba(16, 185, 129, 0.15)' : ($wallet->type === 'commission' ? 'rgba(245, 158, 11, 0.15)' : ($wallet->type === 'bonus' ? 'rgba(168, 85, 247, 0.15)' : 'rgba(239, 68, 68, 0.15)'))) }};
                        color: {{ $wallet->type === 'deposit' ? 'var(--blue-1)' : ($wallet->type === 'interest' ? 'var(--green)' : ($wallet->type === 'commission' ? 'var(--yellow)' : ($wallet->type === 'bonus' ? 'var(--purple-3)' : 'var(--red)'))) }};">
                        <i class="fas fa-{{ $wallet->type === 'deposit' ? 'wallet' : ($wallet->type === 'interest' ? 'piggy-bank' : ($wallet->type === 'commission' ? 'handshake' : ($wallet->type === 'bonus' ? 'gift' : 'money-bill-wave'))) }}"></i>
                    </div>
                    <div style="flex: 1;">
                        <div class="wallet-label">{{ ucfirst($wallet->type) }} Wallet</div>
                        <div class="wallet-amount">${{ number_format($wallet->balance, 2) }}</div>
                    </div>
                    <div style="font-size: 10px; color: var(--text-dim);">{{ $wallet->currency }}</div>
                </div>
                @endforeach
            </div>

            <!-- BINARY TREE WIDGET -->
            <div class="card-custom mb-3">
                <div class="section-header">
                    <h5><i class="fas fa-sitemap" style="color: var(--blue-1);"></i> Binary Network</h5>
                    <a href="{{ route('dashboard.binary.index') }}" class="section-link">View Tree <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="binary-widget">
                    <div class="binary-leg left">
                        <div class="leg-label"><i class="fas fa-arrow-left"></i> Left Leg</div>
                        <div class="leg-count">{{ $leftCount }}</div>
                        <div class="leg-volume">${{ number_format($leftVolume, 2) }} vol.</div>
                    </div>
                    <div class="binary-leg right">
                        <div class="leg-label">Right Leg <i class="fas fa-arrow-right"></i></div>
                        <div class="leg-count">{{ $rightCount }}</div>
                        <div class="leg-volume">${{ number_format($rightVolume, 2) }} vol.</div>
                    </div>
                </div>
                <div style="margin-top: 12px; padding: 12px; background: var(--bg-input); border-radius: 10px;">
                    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">Matching Bonus</div>
                    <div style="font-size: 18px; font-weight: 700; color: var(--purple-3);">
                        ${{ number_format($binaryNode?->total_matching_bonus ?? 0, 2) }}
                    </div>
                </div>
            </div>

            <!-- MARKET SENTIMENT INDICATOR -->
            <div class="card-custom mb-3">
                <div class="section-header">
                    <h5><i class="fas fa-thermometer-half" style="color: var(--yellow);"></i> Market Sentiment</h5>
                </div>
                <div style="text-align: center; padding: 10px 0;">
                    <div id="sentimentGauge" style="display: inline-block;"></div>
                    <div style="margin-top: 8px;">
                        <span class="badge-custom badge-info" style="font-size: 13px;">
                            <i class="fas fa-bull"></i> Neutral Market
                        </span>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 12px; font-size: 11px; color: var(--text-muted);">
                    <span>Fear: 0%</span>
                    <span>Greed: 0%</span>
                </div>
            </div>

            <!-- PENDING ACTIONS -->
            <div class="card-custom mb-3">
                <div class="section-header">
                    <h5><i class="fas fa-tasks" style="color: var(--yellow);"></i> Pending Actions</h5>
                </div>
                @if($pendingDeposits > 0)
                <div style="display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid rgba(51,65,85,0.3);">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--yellow-bg); color: var(--yellow); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 13px; color: var(--text-bright);">Pending Deposits</div>
                        <div style="font-size: 11px; color: var(--text-dim);">Awaiting confirmation</div>
                    </div>
                    <span class="badge-custom badge-pending">{{ $pendingDeposits }}</span>
                </div>
                @endif
                @if($pendingWithdrawals > 0)
                <div style="display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid rgba(51,65,85,0.3);">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--red-bg); color: var(--red); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 13px; color: var(--text-bright);">Pending Withdrawals</div>
                        <div style="font-size: 11px; color: var(--text-dim);">Awaiting processing</div>
                    </div>
                    <span class="badge-custom badge-pending">{{ $pendingWithdrawals }}</span>
                </div>
                @endif
                @if(auth()->user()->kyc_status !== 'verified')
                <div style="display: flex; align-items: center; gap: 10px; padding: 10px 0;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(99, 102, 241, 0.15); color: var(--purple-3); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 13px; color: var(--text-bright);">KYC Verification</div>
                        <div style="font-size: 11px; color: var(--text-dim);">Required for withdrawals</div>
                    </div>
                    <a href="{{ route('dashboard.kyc.index') }}" class="btn-outline-custom" style="padding: 5px 12px; font-size: 11px;">Verify</a>
                </div>
                @endif
                @if($pendingDeposits === 0 && $pendingWithdrawals === 0 && auth()->user()->kyc_status === 'verified')
                <div style="text-align: center; padding: 20px 0; color: var(--text-dim);">
                    <i class="fas fa-check-circle" style="font-size: 28px; color: var(--green); margin-bottom: 8px;"></i>
                    <p style="font-size: 13px;">All caught up! No pending actions.</p>
                </div>
                @endif
            </div>

            <!-- TOP TRADING INDICATORS -->
            <div class="card-custom">
                <div class="section-header">
                    <h5><i class="fas fa-chart-bar" style="color: var(--purple-3);"></i> Trading Indicators</h5>
                </div>
                <div id="indicatorList">
                    <!-- Loaded via JS -->
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// ========== EARNINGS CHART ==========
var chartLabels = @json($chartLabels);
var chartData = @json($chartData);

var earningsChart = new ApexCharts(document.getElementById("earningsChart"), {
    series: [{
        name: "Earnings",
        data: chartData
    }],
    chart: {
        type: "area",
        height: 280,
        background: "transparent",
        toolbar: { show: false },
        fontFamily: 'Inter, sans-serif',
    },
    theme: { mode: 'dark' },
    colors: ["#6366f1"],
    fill: {
        type: "gradient",
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.05,
            stops: [0, 90, 100],
            gradientToColors: ["#a855f7"],
        }
    },
    dataLabels: { enabled: false },
    stroke: { curve: "smooth", width: 2.5, colors: ["#a855f7"] },
    grid: {
        borderColor: "#334155",
        strokeDashArray: 4,
        xaxis: { lines: { show: false } },
        yaxis: { lines: { show: true } },
    },
    xaxis: {
        categories: chartLabels,
        labels: { style: { colors: "#94a3b8", fontSize: "12px" } },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            style: { colors: "#94a3b8", fontSize: "12px" },
            formatter: function(val) { return "$" + val.toFixed(0); }
        }
    },
    tooltip: {
        theme: "dark",
        style: { fontSize: "13px" },
        y: { formatter: function(val) { return "$" + val.toFixed(2); } }
    },
    markers: {
        size: 4,
        colors: ["#a855f7"],
        strokeColors: "#6366f1",
        strokeWidth: 2,
        hover: { size: 6 }
    }
});
earningsChart.render();

function updateChart(range) {
    var newData = chartData.map(v => v + Math.random() * 50);
    if (range === "30") newData = Array.from({length: 30}, () => Math.random() * 200 + 20);
    if (range === "90") newData = Array.from({length: 90}, () => Math.random() * 300 + 10);
    var labels = range === "7" ? chartLabels :
                 range === "30" ? Array.from({length: 30}, (_, i) => "Day " + (i+1)) :
                 Array.from({length: 90}, (_, i) => "Day " + (i+1));
    earningsChart.updateSeries([{ data: newData }]);
    earningsChart.updateOptions({ xaxis: { categories: labels } });
}

// ========== LIVE MARKET PRICES ==========
var currentCategory = 'crypto';
var refreshInterval;

function switchMarketTab(category, btn) {
    document.querySelectorAll('.ticker-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    currentCategory = category;
    loadPrices();
}

function loadPrices() {
    var body = document.getElementById('tickerBody');
    body.innerHTML = '<div style="text-align: center; padding: 30px 0; color: var(--text-dim);"><div class="spinner-border spinner-border-sm"></div><p style="margin-top: 8px; font-size: 13px;">Loading...</p></div>';

    fetch("{{ route('dashboard.live-prices') }}?category=" + currentCategory, {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                   'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.prices) {
            renderPrices(data.prices);
            document.getElementById('lastUpdate').textContent = "Last updated: " + new Date(data.updated_at).toLocaleTimeString();
        }
    })
    .catch(err => {
        body.innerHTML = '<div style="text-align: center; padding: 20px; color: var(--red);"><i class="fas fa-exclamation-triangle"></i> Failed to load prices</div>';
    });
}

function renderPrices(prices) {
    var html = '';
    prices.forEach(function(p, i) {
        var up = p.trend === 'up';
        var color = up ? 'var(--green)' : 'var(--red)';
        var iconBg = p.color || '#6366f1';

        var sparkData = Array.from({length: 8}, function() {
            return p.price * (1 + (Math.random() - 0.5) * 0.02);
        });
        var sparkPath = generateSparkPath(sparkData);

        html += '<div class="price-item">' +
            '<div class="price-item-left">' +
                '<div class="coin-badge" style="background: ' + iconBg + ';">' + p.symbol.substring(0, 3) + '</div>' +
                '<div class="price-item-info">' +
                    '<h6>' + p.symbol + '</h6>' +
                    '<p>' + p.name + '</p>' +
                '</div>' +
            '</div>' +
            '<svg class="mini-spark" viewBox="0 0 50 24"><path d="' + sparkPath + '" fill="none" stroke="' + color + '" stroke-width="1.5"/></svg>' +
            '<div class="price-item-right">' +
                '<div class="price-value">$' + formatPrice(p.price) + '</div>' +
                '<div class="price-change ' + (up ? 'up' : 'down') + '">' +
                    '<i class="fas fa-caret-' + (up ? 'up' : 'down') + '"></i> ' +
                    Math.abs(p.change_pct).toFixed(2) + '%' +
                '</div>' +
            '</div>' +
        '</div>';
    });
    document.getElementById('tickerBody').innerHTML = html;
}

function generateSparkPath(data) {
    var max = Math.max.apply(null, data);
    var min = Math.min.apply(null, data);
    var range = max - min || 1;
    var step = 50 / (data.length - 1);
    var path = '';
    data.forEach(function(v, i) {
        var x = i * step;
        var y = 24 - ((v - min) / range) * 22 - 1;
        path += (i === 0 ? 'M' : 'L') + x.toFixed(1) + ',' + y.toFixed(1) + ' ';
    });
    return path.trim();
}

function formatPrice(price) {
    if (price < 1) return price.toFixed(4);
    if (price < 100) return price.toFixed(2);
    return price.toLocaleString('en-US', { maximumFractionDigits: 2 });
}

// Initial load
loadPrices();
refreshInterval = setInterval(loadPrices, 30000);

// ========== MARKET SENTIMENT GAUGE ==========
var sentimentGauge = new ApexCharts(document.getElementById("sentimentGauge"), {
    series: [65],
    chart: {
        type: "radialBar",
        height: 160,
        sparkline: { enabled: true },
        background: "transparent",
        fontFamily: 'Inter, sans-serif',
    },
    theme: { mode: 'dark' },
    colors: ["#a855f7"],
    plotOptions: {
        radialBar: {
            startAngle: -135,
            endAngle: 135,
            hollow: { size: "60%" },
            track: { background: "#334155", strokeWidth: "100%" },
            dataLabels: {
                name: { fontSize: "13px", color: "#94a3b8", offsetY: -5 },
                value: { fontSize: "28px", fontWeight: 700, color: "#e2e8f0", formatter: function(v) { return v + "%"; } }
            }
        }
    },
    labels: ["Greed Index"],
});
sentimentGauge.render();

// ========== TRADING INDICATORS ==========
var indicators = [
    { name: 'RSI (14)', value: 58.3, signal: 'neutral', desc: 'Mildly bullish' },
    { name: 'MACD', value: 0, signal: 'neutral', desc: 'No data' },
    { name: 'Stochastic', value: 72, signal: 'neutral', desc: 'Overbought zone' },
    { name: 'Bollinger', value: 0, signal: 'sell', desc: 'Upper band touched' },
    { name: 'EMA 200', value: 0, signal: 'buy', desc: 'Price above EMA' },
    { name: 'Volume', value: 0, signal: 'neutral', desc: 'Normal volume' },
];

var indicatorHtml = '';
indicators.forEach(function(ind) {
    var color = ind.signal === 'buy' ? 'var(--green)' : (ind.signal === 'sell' ? 'var(--red)' : 'var(--yellow)');
    var bgColor = ind.signal === 'buy' ? 'var(--green-bg)' : (ind.signal === 'sell' ? 'var(--red-bg)' : 'var(--yellow-bg)');
    var label = ind.signal === 'buy' ? 'BUY' : (ind.signal === 'sell' ? 'SELL' : 'NEUTRAL');
    var icon = ind.signal === 'buy' ? 'arrow-up' : (ind.signal === 'sell' ? 'arrow-down' : 'minus');

    indicatorHtml += '<div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(51,65,85,0.3);">' +
        '<div>' +
            '<div style="font-size: 13px; font-weight: 600; color: var(--text-bright);">' + ind.name + '</div>' +
            '<div style="font-size: 11px; color: var(--text-dim);">' + ind.desc + '</div>' +
        '</div>' +
        '<div style="display: flex; align-items: center; gap: 8px;">' +
            (ind.value > 0 ? '<span style="font-size: 13px; color: var(--text-bright); font-weight: 600;">' + ind.value + '</span>' : '') +
            '<span class="badge-custom" style="background: ' + bgColor + '; color: ' + color + '; display: inline-flex; align-items: center; gap: 4px;">' +
                '<i class="fas fa-' + icon + '" style="font-size: 9px;"></i> ' + label +
            '</span>' +
        '</div>' +
    '</div>';
});
document.getElementById('indicatorList').innerHTML = indicatorHtml;
</script>
@endpush
@endsection
