@extends('layouts.dashboard')

@section('page-title', 'My Reports')

@section('content')
<div class="fade-in">
<div class="card-custom" style="text-align: center; padding: 30px; margin-bottom: 20px;">
    <i class="fas fa-file-invoice" style="font-size: 40px; color: var(--purple-3); margin-bottom: 12px;"></i>
    <h5 style="font-weight: 700; margin-bottom: 8px;">Monthly Account Statement</h5>
    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 16px;">Download your full account statement with all transactions, deposits, withdrawals, and investments.</p>
    <a href="{{ route('dashboard.statement') }}?month={{ now()->format('Y-m') }}" target="_blank" class="btn" style="background: linear-gradient(135deg, #6366f1, #7c3aed); border: none; color: white; font-weight: 600; text-decoration: none;">
        <i class="fas fa-download"></i> Download This Month's Statement
    </a>
    <div style="margin-top: 12px;">
        @php $lastMonth = now()->subMonth()->format('Y-m'); @endphp
        <a href="{{ route('dashboard.statement') }}?month={{ $lastMonth }}" target="_blank" style="font-size: 12px; color: var(--text-muted);">
            Or download last month's statement
        </a>
    </div>
</div>


    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="page-title mb-1">
                <i class="fas fa-chart-bar me-2" style="color: var(--purple-1);"></i>
                My Performance Report
            </h2>
            <p class="text-muted mb-0" style="font-size: 14px;">Track your investment performance and earnings</p>
        </div>
        <select class="form-select form-select-sm" style="width: auto; background: var(--bg-card); border: 1px solid var(--border); color: var(--text);"
                onchange="window.location.href='{{ route('dashboard.reports.index') }}?period=' + this.value">
            <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 days</option>
            <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 days</option>
            <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 days</option>
            <option value="365" {{ $period == '365' ? 'selected' : '' }}>Last 365 days</option>
        </select>
    </div>

    <!-- Portfolio Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="custom-card p-4 text-center">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #6366f1, #7c3aed); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                    <i class="fas fa-wallet" style="color: white; font-size: 20px;"></i>
                </div>
                <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Total Invested</div>
                <div style="font-size: 24px; font-weight: 700; color: var(--text-bright);">${{ number_format($totalInvested, 2) }}</div>
                <small style="color: var(--text-dim);">{{ $activeInvestments->count() }} active plans</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="custom-card p-4 text-center">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                    <i class="fas fa-coins" style="color: white; font-size: 20px;"></i>
                </div>
                <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Total Earned</div>
                <div style="font-size: 24px; font-weight: 700; color: #10b981;">${{ number_format($totalEarned, 2) }}</div>
                <small style="color: var(--text-dim);">From all sources</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="custom-card p-4 text-center">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6, #2563eb); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                    <i class="fas fa-percentage" style="color: white; font-size: 20px;"></i>
                </div>
                <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">ROI</div>
                <div style="font-size: 24px; font-weight: 700; color: var(--purple-1);">{{ number_format($roi, 2) }}%</div>
                <small style="color: {{ $roi >= 0 ? '#10b981' : '#ef4444' }};">{{ $roi >= 0 ? 'Profitable' : 'Below par' }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="custom-card p-4 text-center">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #a855f7, #7c3aed); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                    <i class="fas fa-clock" style="color: white; font-size: 20px;"></i>
                </div>
                <div style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Period Earnings</div>
                <div style="font-size: 24px; font-weight: 700; color: var(--purple-3);">${{ number_format($periodEarnings, 2) }}</div>
                <small style="color: var(--text-dim);">Last {{ $period }} days</small>
            </div>
        </div>
    </div>

    <!-- Earnings Chart -->
    <div class="custom-card p-4 mb-4">
        <h5 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
            <i class="fas fa-chart-area me-2" style="color: var(--purple-1);"></i>
            Earnings Over Time ({{ $period }} days)
        </h5>
        <div id="earningsChart"></div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Earnings Breakdown -->
        <div class="col-lg-5">
            <div class="custom-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-chart-pie me-2" style="color: var(--purple-2);"></i>
                    Earnings Breakdown
                </h6>
                <div id="earningsDonut"></div>
            </div>
        </div>

        <!-- Monthly Performance -->
        <div class="col-lg-7">
            <div class="custom-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-calendar-alt me-2" style="color: var(--purple-1);"></i>
                    Monthly Performance (6 months)
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm" style="color: var(--text);">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border);">
                                <th style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; border: none;">Month</th>
                                <th style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; border: none;">Invested</th>
                                <th style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; border: none;">Earned</th>
                                <th style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; border: none;">Deposited</th>
                                <th style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; border: none;">Withdrawn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyPerformance as $month)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="border: none; color: var(--text-bright); font-weight: 500; font-size: 13px;">{{ $month['label'] }}</td>
                                <td style="border: none; color: var(--purple-1); font-weight: 600; font-size: 13px;">${{ number_format($month['invested'], 0) }}</td>
                                <td style="border: none; color: #10b981; font-weight: 600; font-size: 13px;">${{ number_format($month['earned'], 0) }}</td>
                                <td style="border: none; color: var(--text-muted); font-size: 13px;">${{ number_format($month['deposited'], 0) }}</td>
                                <td style="border: none; color: #ef4444; font-size: 13px;">${{ number_format($month['withdrawn'], 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Investment Performance by Package -->
    <div class="custom-card p-4 mb-4">
        <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
            <i class="fas fa-chart-line me-2" style="color: var(--purple-1);"></i>
            Investment Performance by Package
        </h6>
        <div class="table-responsive">
            <table class="table" style="color: var(--text);">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Package</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Category</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Rate</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;"># Plans</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Invested</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Earned</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Avg Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($investmentPerformance as $pkg)
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="border: none; color: var(--text-bright); font-weight: 600; font-size: 13px;">{{ $pkg->name ?? 'Unknown' }}</td>
                        <td style="border: none;"><span class="badge" style="background: var(--purple-1); color: white; font-size: 10px;">{{ strtoupper($pkg->category ?? 'N/A') }}</span></td>
                        <td style="border: none; color: var(--text-muted); font-size: 13px;">{{ $pkg->return_rate ?? 0 }}%</td>
                        <td style="border: none; color: var(--text); font-size: 13px;">{{ $pkg->count }}</td>
                        <td style="border: none; color: var(--purple-1); font-weight: 700; font-size: 13px;">${{ number_format($pkg->invested, 2) }}</td>
                        <td style="border: none; color: #10b981; font-weight: 700; font-size: 13px;">${{ number_format($pkg->earned, 2) }}</td>
                        <td style="border: none; color: var(--text-muted); font-size: 13px;">${{ number_format($pkg->avg_amount, 2) }}</td>
                    </tr>
                    @endforeach
                    @if($investmentPerformance->isEmpty())
                    <tr><td colspan="7" style="border: none; text-align: center; padding: 30px; color: var(--text-muted);">No investment data yet</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3">
        <div class="col-md-4">
            <div class="custom-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 12px;">
                    <i class="fas fa-exchange-alt me-2" style="color: var(--purple-1);"></i> Financial Summary
                </h6>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border);"><span style="color: var(--text-muted); font-size: 13px;">Total Deposits</span><span style="color: #10b981; font-weight: 600;">${{ number_format($totalDeposits, 2) }}</span></div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border);"><span style="color: var(--text-muted); font-size: 13px;">Total Withdrawals</span><span style="color: #ef4444; font-weight: 600;">${{ number_format($totalWithdrawals, 2) }}</span></div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border);"><span style="color: var(--text-muted); font-size: 13px;">Total Invested</span><span style="color: var(--purple-1); font-weight: 600;">${{ number_format($totalInvested, 2) }}</span></div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border);"><span style="color: var(--text-muted); font-size: 13px;">Total Earned</span><span style="color: #10b981; font-weight: 600;">${{ number_format($totalEarned, 2) }}</span></div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0;"><span style="color: var(--text-muted); font-size: 13px;">Net Position</span><span style="color: {{ ($totalEarned + $totalWithdrawn) >= $totalInvested ? '#10b981' : '#ef4444' }}; font-weight: 700;">${{ number_format($totalEarned + $totalWithdrawn - $totalInvested, 2) }}</span></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="custom-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 12px;">
                    <i class="fas fa-list me-2" style="color: var(--purple-2);"></i> Transaction Summary
                </h6>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border);"><span style="color: var(--text-muted); font-size: 13px;">Total Transactions</span><span style="color: var(--text-bright); font-weight: 600;">{{ number_format($transactionSummary['total']) }}</span></div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border);"><span style="color: var(--text-muted); font-size: 13px;">Credits</span><span style="color: #10b981; font-weight: 600;">${{ number_format($transactionSummary['credits'], 2) }}</span></div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0;"><span style="color: var(--text-muted); font-size: 13px;">Debits</span><span style="color: #ef4444; font-weight: 600;">${{ number_format($transactionSummary['debits'], 2) }}</span></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="custom-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 12px;">
                    <i class="fas fa-users me-2" style="color: var(--purple-3);"></i> Referral Summary
                </h6>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border);"><span style="color: var(--text-muted); font-size: 13px;">Direct Referrals</span><span style="color: var(--text-bright); font-weight: 600;">{{ $directReferrals }}</span></div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0;"><span style="color: var(--text-muted); font-size: 13px;">Referral Earnings</span><span style="color: #10b981; font-weight: 600;">${{ number_format($referralEarnings, 2) }}</span></div>
            </div>
        </div>
    </div>
</div>

<script>
// Earnings Area Chart
var earnData = @json($earningsChart);
new ApexCharts(document.getElementById('earningsChart'), {
    series: [{ name: 'Earnings', data: earnData.values }],
    chart: { type: 'area', height: 300, background: 'transparent', fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
    theme: { mode: 'dark' },
    colors: ['#6366f1'],
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
    grid: { borderColor: '#334155', strokeDashArray: 4 },
    xaxis: { categories: earnData.labels, labels: { style: { colors: '#94a3b8', fontSize: '11px' } } },
    yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: function(v) { return '$' + v.toFixed(0); } } },
    tooltip: { theme: 'dark', y: { formatter: function(v) { return '$' + v.toFixed(2); } } },
    dataLabels: { enabled: false },
    markers: { size: 0, hover: { size: 5 } },
}).render();

// Earnings Donut
var ebData = @json($earningsBreakdown);
var ebLabels = Object.keys(ebData).map(function(k) {
    return k.split('_').map(function(w) { return w.charAt(0).toUpperCase() + w.slice(1); }).join(' ');
});
var ebValues = Object.values(ebData);
new ApexCharts(document.getElementById('earningsDonut'), {
    series: ebValues.length ? ebValues : [1],
    chart: { type: 'donut', height: 280, background: 'transparent', fontFamily: 'Inter, sans-serif' },
    theme: { mode: 'dark' },
    colors: ['#6366f1', '#a855f7', '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#06b6d4'],
    labels: ebLabels.length ? ebLabels : ['No Data'],
    legend: { position: 'bottom', labels: { colors: '#94a3b8' } },
    dataLabels: { enabled: true, formatter: function(val) { return val.toFixed(1) + '%'; } },
    tooltip: { y: { formatter: function(v) { return '$' + v.toFixed(2); } } },
    plotOptions: { pie: { donut: { size: '65%' } } },
}).render();
</script>
@endsection
