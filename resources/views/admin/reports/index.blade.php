@extends('layouts.admin')

@section('page-title', 'Reports & Analytics')

@section('content')
<div class="fade-in">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px;">
                <i class="fas fa-chart-bar me-2" style="color: var(--purple-1);"></i>
                Reports & Analytics
            </h2>
            <p style="color: var(--text-muted); margin: 0; font-size: 14px;">Platform-wide performance and financial reports</p>
        </div>
        <div class="d-flex gap-2">
            <!-- Period selector -->
            <select class="form-select form-select-sm" style="width: auto; background: var(--bg-card); border: 1px solid var(--border); color: var(--text);"
                    onchange="window.location.href='{{ route('admin.reports.index') }}?period=' + this.value">
                <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 days</option>
                <option value="365" {{ $period == '365' ? 'selected' : '' }}>Last 365 days</option>
            </select>
            <div class="dropdown">
                <button class="btn btn-sm dropdown-toggle" style="background: var(--bg-card); border: 1px solid var(--border); color: var(--text);"
                        data-bs-toggle="dropdown">
                    <i class="fas fa-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="background: var(--bg-card); border: 1px solid var(--border);">
                    <li class="px-3 py-1" style="font-size: 10px; text-transform: uppercase; color: var(--text-dim); font-weight: 700;">Excel (.xls)</li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'transactions', 'format' => 'excel']) }}"><i class="fas fa-file-excel me-2" style="color: #10b981;"></i> Transactions</a></li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'users', 'format' => 'excel']) }}"><i class="fas fa-file-excel me-2" style="color: #10b981;"></i> Users</a></li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'deposits', 'format' => 'excel']) }}"><i class="fas fa-file-excel me-2" style="color: #10b981;"></i> Deposits</a></li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'withdrawals', 'format' => 'excel']) }}"><i class="fas fa-file-excel me-2" style="color: #10b981;"></i> Withdrawals</a></li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'commissions', 'format' => 'excel']) }}"><i class="fas fa-file-excel me-2" style="color: #10b981;"></i> Commissions</a></li>
                    <li><hr class="dropdown-divider" style="border-color: var(--border);"></li>
                    <li class="px-3 py-1" style="font-size: 10px; text-transform: uppercase; color: var(--text-dim); font-weight: 700;">PDF (print)</li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'transactions', 'format' => 'pdf']) }}" target="_blank"><i class="fas fa-file-pdf me-2" style="color: #ef4444;"></i> Transactions</a></li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'users', 'format' => 'pdf']) }}" target="_blank"><i class="fas fa-file-pdf me-2" style="color: #ef4444;"></i> Users</a></li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'deposits', 'format' => 'pdf']) }}" target="_blank"><i class="fas fa-file-pdf me-2" style="color: #ef4444;"></i> Deposits</a></li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'withdrawals', 'format' => 'pdf']) }}" target="_blank"><i class="fas fa-file-pdf me-2" style="color: #ef4444;"></i> Withdrawals</a></li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'commissions', 'format' => 'pdf']) }}" target="_blank"><i class="fas fa-file-pdf me-2" style="color: #ef4444;"></i> Commissions</a></li>
                    <li><hr class="dropdown-divider" style="border-color: var(--border);"></li>
                    <li class="px-3 py-1" style="font-size: 10px; text-transform: uppercase; color: var(--text-dim); font-weight: 700;">CSV</li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'transactions', 'format' => 'csv']) }}"><i class="fas fa-file-csv me-2"></i> Transactions</a></li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'users', 'format' => 'csv']) }}"><i class="fas fa-file-csv me-2"></i> Users</a></li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'deposits', 'format' => 'csv']) }}"><i class="fas fa-file-csv me-2"></i> Deposits</a></li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'withdrawals', 'format' => 'csv']) }}"><i class="fas fa-file-csv me-2"></i> Withdrawals</a></li>
                    <li><a class="dropdown-item" style="color: var(--text);" href="{{ route('admin.reports.export', ['report' => 'commissions', 'format' => 'csv']) }}"><i class="fas fa-file-csv me-2"></i> Commissions</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="admin-card p-3">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(99,102,241,0.15); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-dollar-sign" style="color: var(--purple-1);"></i>
                    </div>
                    <span style="color: var(--text-muted); font-size: 12px; font-weight: 500;">Total Revenue</span>
                </div>
                <div style="font-size: 24px; font-weight: 700; color: var(--text-bright);">${{ number_format($totalRevenue, 2) }}</div>
                <small style="color: var(--text-dim);">Net: <span style="color: {{ $netRevenue >= 0 ? '#10b981' : '#ef4444' }}; font-weight: 600;">${{ number_format($netRevenue, 2) }}</span></small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card p-3">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16,185,129,0.15); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-coins" style="color: #10b981;"></i>
                    </div>
                    <span style="color: var(--text-muted); font-size: 12px; font-weight: 500;">Total Payouts</span>
                </div>
                <div style="font-size: 24px; font-weight: 700; color: var(--text-bright);">${{ number_format($totalPayouts, 2) }}</div>
                <small style="color: var(--text-dim);">Profit sharing distributed</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card p-3">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(245,158,11,0.15); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-handshake" style="color: #f59e0b;"></i>
                    </div>
                    <span style="color: var(--text-muted); font-size: 12px; font-weight: 500;">Commissions</span>
                </div>
                <div style="font-size: 24px; font-weight: 700; color: var(--text-bright);">${{ number_format($totalCommissions, 2) }}</div>
                <small style="color: var(--text-dim);">Referral + matching</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card p-3">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(239,68,68,0.15); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-arrow-up" style="color: #ef4444;"></i>
                    </div>
                    <span style="color: var(--text-muted); font-size: 12px; font-weight: 500;">Withdrawals</span>
                </div>
                <div style="font-size: 24px; font-weight: 700; color: var(--text-bright);">${{ number_format($totalWithdrawals, 2) }}</div>
                <small style="color: var(--text-dim);">Completed payouts</small>
            </div>
        </div>
    </div>

    <!-- Period Stats Bar -->
    <div class="admin-card p-3 mb-4">
        <div class="row text-center">
            <div class="col-md-3 col-6" style="border-right: 1px solid var(--border);">
                <div style="font-size: 20px; font-weight: 700; color: var(--purple-1);">${{ number_format($periodDeposits, 0) }}</div>
                <small style="color: var(--text-dim);">Deposits ({{ $period }}d)</small>
            </div>
            <div class="col-md-3 col-6" style="border-right: 1px solid var(--border);">
                <div style="font-size: 20px; font-weight: 700; color: #ef4444;">${{ number_format($periodWithdrawals, 0) }}</div>
                <small style="color: var(--text-dim);">Withdrawals ({{ $period }}d)</small>
            </div>
            <div class="col-md-3 col-6" style="border-right: 1px solid var(--border);">
                <div style="font-size: 20px; font-weight: 700; color: #10b981;">{{ $periodUsers }}</div>
                <small style="color: var(--text-dim);">New Users ({{ $period }}d)</small>
            </div>
            <div class="col-md-3 col-6">
                <div style="font-size: 20px; font-weight: 700; color: var(--purple-3);">${{ number_format($periodInvestments, 0) }}</div>
                <small style="color: var(--text-dim);">Investments ({{ $period }}d)</small>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="admin-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-chart-area me-2" style="color: var(--purple-1);"></i>
                    Revenue Breakdown ({{ $period }} days)
                </h6>
                <div id="revenueChart"></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="admin-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-chart-pie me-2" style="color: var(--purple-2);"></i>
                    Investment by Category
                </h6>
                <div id="categoryChart"></div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="admin-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-exchange-alt me-2" style="color: var(--purple-1);"></i>
                    Deposits vs Withdrawals
                </h6>
                <div id="flowChart"></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="admin-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-user-plus me-2" style="color: var(--purple-3);"></i>
                    User Growth
                </h6>
                <div id="userGrowthChart"></div>
            </div>
        </div>
    </div>

    <!-- Top Investors & Earners -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="admin-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-trophy me-2" style="color: #f59e0b;"></i>
                    Top Investors
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm" style="color: var(--text);">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <th style="color: var(--text-muted); font-size: 11px; border: none;">#</th>
                                <th style="color: var(--text-muted); font-size: 11px; border: none;">USER</th>
                                <th style="color: var(--text-muted); font-size: 11px; border: none;">INVESTED</th>
                                <th style="color: var(--text-muted); font-size: 11px; border: none;">EARNED</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topInvestors as $i => $user)
                            <tr style="border-bottom: 1px solid rgba(51,65,85,0.3);">
                                <td style="border: none; color: var(--text-dim); font-weight: 600;">{{ $i + 1 }}</td>
                                <td style="border: none;">
                                    <div style="color: var(--text-bright); font-weight: 500; font-size: 13px;">{{ $user->name }}</div>
                                    <small style="color: var(--text-dim);">{{ $user->email }}</small>
                                </td>
                                <td style="border: none; color: var(--purple-1); font-weight: 700; font-size: 13px;">${{ number_format($user->total_invested ?? 0, 0) }}</td>
                                <td style="border: none; color: #10b981; font-weight: 600; font-size: 13px;">${{ number_format($user->total_earned ?? 0, 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="admin-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-medal me-2" style="color: #f43f5e;"></i>
                    Top Earners
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm" style="color: var(--text);">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <th style="color: var(--text-muted); font-size: 11px; border: none;">#</th>
                                <th style="color: var(--text-muted); font-size: 11px; border: none;">USER</th>
                                <th style="color: var(--text-muted); font-size: 11px; border: none;">EARNED</th>
                                <th style="color: var(--text-muted); font-size: 11px; border: none;">INVESTED</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topEarners as $i => $user)
                            <tr style="border-bottom: 1px solid rgba(51,65,85,0.3);">
                                <td style="border: none; color: var(--text-dim); font-weight: 600;">{{ $i + 1 }}</td>
                                <td style="border: none;">
                                    <div style="color: var(--text-bright); font-weight: 500; font-size: 13px;">{{ $user->name }}</div>
                                    <small style="color: var(--text-dim);">{{ $user->email }}</small>
                                </td>
                                <td style="border: none; color: #10b981; font-weight: 700; font-size: 13px;">${{ number_format($user->total_earned ?? 0, 0) }}</td>
                                <td style="border: none; color: var(--purple-1); font-weight: 600; font-size: 13px;">${{ number_format($user->total_invested ?? 0, 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Performance -->
    <div class="admin-card p-4 mb-4">
        <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
            <i class="fas fa-chart-line me-2" style="color: var(--purple-1);"></i>
            Package Performance
        </h6>
        <div class="table-responsive">
            <table class="table" style="color: var(--text);">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">PACKAGE</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">CATEGORY</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">RETURN</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;"># INVESTMENTS</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">TOTAL VOLUME</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">ACTIVE VOLUME</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">EARNED</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packagePerformance as $pkg)
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="border: none; color: var(--text-bright); font-weight: 600; font-size: 13px;">{{ $pkg->name }}</td>
                        <td style="border: none;"><span class="badge" style="background: var(--purple-1); color: white; font-size: 10px;">{{ strtoupper($pkg->category) }}</span></td>
                        <td style="border: none; color: var(--text-muted); font-size: 13px;">{{ $pkg->return_rate }}%</td>
                        <td style="border: none; color: var(--text); font-size: 13px;">{{ $pkg->total_investments }}</td>
                        <td style="border: none; color: var(--purple-3); font-weight: 700; font-size: 13px;">${{ number_format($pkg->total_volume, 0) }}</td>
                        <td style="border: none; color: var(--purple-1); font-weight: 600; font-size: 13px;">${{ number_format($pkg->active_volume, 0) }}</td>
                        <td style="border: none; color: #10b981; font-weight: 600; font-size: 13px;">${{ number_format($pkg->total_earned, 0) }}</td>
                    </tr>
                    @endforeach
                    @if($packagePerformance->isEmpty())
                    <tr><td colspan="7" style="border: none; text-align: center; padding: 30px; color: var(--text-muted);">No package data</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- User Status & KYC -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="admin-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-users me-2" style="color: var(--purple-1);"></i>
                    User Status Breakdown
                </h6>
                @foreach($userStatusBreakdown as $status)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 10px; height: 10px; border-radius: 50%; background: {{ $status->status === 'active' ? '#10b981' : '#f59e0b' }};"></div>
                        <span style="color: var(--text-bright); font-size: 14px; text-transform: capitalize;">{{ $status->status }}</span>
                    </div>
                    <span style="color: var(--text-bright); font-weight: 700;">{{ $status->count }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="col-lg-6">
            <div class="admin-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-id-card me-2" style="color: #f59e0b;"></i>
                    KYC Verification Status
                </h6>
                @foreach($kycStats as $key => $count)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @php $colors = ['verified' => '#10b981', 'pending' => '#f59e0b', 'rejected' => '#ef4444', 'not_submitted' => '#64748b']; @endphp
                        <div style="width: 10px; height: 10px; border-radius: 50%; background: {{ $colors[$key] }};"></div>
                        <span style="color: var(--text-bright); font-size: 14px; text-transform: capitalize;">{{ str_replace('_', ' ', $key) }}</span>
                    </div>
                    <span style="color: var(--text-bright); font-weight: 700;">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="admin-card p-4">
        <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
            <i class="fas fa-link me-2" style="color: var(--purple-1);"></i>
            Detailed Reports
        </h6>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.reports.transactions') }}" class="btn btn-sm" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
                <i class="fas fa-list me-1"></i> Transaction Report
            </a>
            <a href="{{ route('admin.reports.users') }}" class="btn btn-sm" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
                <i class="fas fa-users me-1"></i> User Activity Report
            </a>
            <a href="{{ route('admin.reports.export', ['report' => 'transactions']) }}" class="btn btn-sm" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
                <i class="fas fa-file-csv me-1"></i> Export Transactions
            </a>
            <a href="{{ route('admin.reports.export', ['report' => 'users']) }}" class="btn btn-sm" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
                <i class="fas fa-file-export me-1"></i> Export Users
            </a>
        </div>
    </div>
</div>

<script>
// Revenue Chart
var revData = @json($revenueChart);
new ApexCharts(document.getElementById('revenueChart'), {
    series: [
        { name: 'Deposits', data: revData.deposits },
        { name: 'Payouts', data: revData.payouts },
        { name: 'Commissions', data: revData.commissions },
    ],
    chart: { type: 'bar', height: 320, background: 'transparent', fontFamily: 'Inter, sans-serif', toolbar: { show: false }, stacked: false },
    theme: { mode: 'dark' },
    colors: ['#6366f1', '#ef4444', '#f59e0b'],
    plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
    grid: { borderColor: '#334155', strokeDashArray: 4 },
    xaxis: { categories: revData.labels, labels: { style: { colors: '#94a3b8', fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: function(v) { return '$' + v.toFixed(0); } } },
    tooltip: { theme: 'dark', y: { formatter: function(v) { return '$' + v.toFixed(2); } } },
    legend: { labels: { colors: '#94a3b8' } },
    dataLabels: { enabled: false },
}).render();

// Category Donut
var catData = @json($categoryBreakdown);
var catLabels = Object.keys(catData).map(k => k.charAt(0).toUpperCase() + k.slice(1));
var catValues = Object.values(catData);
new ApexCharts(document.getElementById('categoryChart'), {
    series: catValues.length ? catValues : [1],
    chart: { type: 'donut', height: 280, background: 'transparent', fontFamily: 'Inter, sans-serif' },
    theme: { mode: 'dark' },
    colors: ['#6366f1', '#3b82f6', '#10b981', '#a855f7', '#f59e0b', '#ef4444'],
    labels: catLabels.length ? catLabels : ['No Data'],
    legend: { position: 'bottom', labels: { colors: '#94a3b8' } },
    dataLabels: { enabled: true, formatter: function(val) { return val.toFixed(0) + '%'; } },
    tooltip: { y: { formatter: function(v) { return '$' + v.toFixed(2); } } },
    plotOptions: { pie: { donut: { size: '65%' } } },
}).render();

// Flow Chart
var flowData = @json($flowChart);
new ApexCharts(document.getElementById('flowChart'), {
    series: [
        { name: 'Deposits', data: flowData.deposits },
        { name: 'Withdrawals', data: flowData.withdrawals },
    ],
    chart: { type: 'area', height: 280, background: 'transparent', fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
    theme: { mode: 'dark' },
    colors: ['#6366f1', '#ef4444'],
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
    grid: { borderColor: '#334155', strokeDashArray: 4 },
    xaxis: { categories: flowData.labels, labels: { style: { colors: '#94a3b8', fontSize: '11px' } } },
    yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: function(v) { return '$' + v.toFixed(0); } } },
    tooltip: { theme: 'dark', y: { formatter: function(v) { return '$' + v.toFixed(2); } } },
    legend: { labels: { colors: '#94a3b8' } },
    dataLabels: { enabled: false },
}).render();

// User Growth
var growthData = @json($userGrowthChart);
new ApexCharts(document.getElementById('userGrowthChart'), {
    series: [
        { name: 'New Users', type: 'column', data: growthData.new },
        { name: 'Total Users', type: 'line', data: growthData.cumulative },
    ],
    chart: { height: 280, background: 'transparent', fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
    theme: { mode: 'dark' },
    colors: ['#a855f7', '#6366f1'],
    plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
    stroke: { curve: 'smooth', width: [0, 3] },
    grid: { borderColor: '#334155', strokeDashArray: 4 },
    xaxis: { categories: growthData.labels, labels: { style: { colors: '#94a3b8', fontSize: '10px' } } },
    yaxis: [{ labels: { style: { colors: '#94a3b8' } } }, { opposite: true, labels: { style: { colors: '#94a3b8' } } }],
    tooltip: { theme: 'dark' },
    legend: { labels: { colors: '#94a3b8' } },
    dataLabels: { enabled: false },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.2 } },
}).render();
</script>
@endsection
