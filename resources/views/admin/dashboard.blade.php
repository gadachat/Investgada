@extends('layouts.admin')

@section('page-title', 'Admin Dashboard')

@section('content')
<div class="fade-in">

    <!-- Top stats row -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-users"></i></div>
                <div class="stat-label">Total Users</div>
                <div class="stat-value">{{ number_format($totalUsers) }}</div>
                <div class="stat-sub up"><i class="fas fa-user-plus"></i> {{ $newUsersToday }} new today</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-arrow-down"></i></div>
                <div class="stat-label">Total Deposits</div>
                <div class="stat-value">${{ number_format($totalDeposits, 2) }}</div>
                <div class="stat-sub up"><i class="fas fa-clock"></i> {{ $pendingDeposits }} pending (${{
                    number_format($pendingDepositsAmount, 2) }})</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-arrow-up"></i></div>
                <div class="stat-label">Total Withdrawals</div>
                <div class="stat-value">${{ number_format($totalWithdrawals, 2) }}</div>
                <div class="stat-sub down"><i class="fas fa-clock"></i> {{ $pendingWithdrawals }} pending (${{
                    number_format($pendingWithdrawalsAmount, 2) }})</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-chart-pie"></i></div>
                <div class="stat-label">Active Investments</div>
                <div class="stat-value">${{ number_format($totalInvestments, 2) }}</div>
                <div class="stat-sub">{{ $activeInvestments }} active plans</div>
            </div>
        </div>
    </div>

    <!-- Chart row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card-custom">
                <div class="section-header">
                    <h5><i class="fas fa-chart-area" style="color: var(--purple-3);"></i> Deposits vs Withdrawals (30 days)</h5>
                </div>
                <div id="flowChart"></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-custom mb-3">
                <div class="section-header"><h5><i class="fas fa-coins" style="color: var(--green);"></i> Payouts</h5></div>
                <div style="font-size: 28px; font-weight: 700; color: var(--green);">${{ number_format($totalPayouts, 2) }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Total investment payouts distributed</div>
            </div>
            <div class="card-custom mb-3">
                <div class="section-header"><h5><i class="fas fa-handshake" style="color: var(--yellow);"></i> Commissions</h5></div>
                <div style="font-size: 28px; font-weight: 700; color: var(--yellow);">${{ number_format($totalCommissions, 2) }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Referral + matching commissions</div>
            </div>
            <div class="card-custom">
                <div class="section-header"><h5><i class="fas fa-user-check" style="color: var(--blue-1);"></i> Active Users</h5></div>
                <div style="font-size: 28px; font-weight: 700; color: var(--blue-1);">{{ number_format($activeUsers) }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">out of {{ number_format($totalUsers) }} total</div>
            </div>
        </div>
    </div>

    <!-- Pending items + recent activity -->
    <div class="row g-3">
        <!-- Pending Deposits -->
        <div class="col-lg-6">
            <div class="card-custom">
                <div class="section-header">
                    <h5><i class="fas fa-arrow-down" style="color: var(--green);"></i> Recent Deposits</h5>
                    <a href="{{ route('admin.deposits.index') }}" style="font-size: 12px; color: var(--purple-3); text-decoration: none;">View All →</a>
                </div>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead><tr><th>User</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach($recentDeposits as $dep)
                        <tr>
                            <td>{{ $dep->user->name }}</td>
                            <td style="font-weight: 600; color: var(--green);">${{ number_format($dep->amount, 2) }}</td>
                            <td><span class="badge-custom badge-purple">{{ $dep->method }}</span></td>
                            <td>
                                @if($dep->status === 'confirmed')
                                <span class="badge-custom badge-up">Confirmed</span>
                                @elseif($dep->status === 'pending')
                                <span class="badge-custom badge-pending">Pending</span>
                                @else
                                <span class="badge-custom badge-down">{{ ucfirst($dep->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Withdrawals -->
        <div class="col-lg-6">
            <div class="card-custom">
                <div class="section-header">
                    <h5><i class="fas fa-arrow-up" style="color: var(--red);"></i> Recent Withdrawals</h5>
                    <a href="{{ route('admin.withdrawals.index') }}" style="font-size: 12px; color: var(--purple-3); text-decoration: none;">View All →</a>
                </div>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead><tr><th>User</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach($recentWithdrawals as $wdr)
                        <tr>
                            <td>{{ $wdr->user->name }}</td>
                            <td style="font-weight: 600; color: var(--red);">${{ number_format($wdr->amount, 2) }}</td>
                            <td><span class="badge-custom badge-purple">{{ $wdr->method }}</span></td>
                            <td>
                                @if($wdr->status === 'completed')
                                <span class="badge-custom badge-up">Completed</span>
                                @elseif($wdr->status === 'pending')
                                <span class="badge-custom badge-pending">Pending</span>
                                @elseif($wdr->status === 'processing')
                                <span class="badge-custom badge-info">Processing</span>
                                @else
                                <span class="badge-custom badge-down">{{ ucfirst($wdr->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Support Tickets + Security Overview -->
    <div class="row g-3 mt-1">
        <!-- Support Tickets Widget -->
        <div class="col-lg-8">
            <div class="card-custom">
                <div class="section-header">
                    <h5><i class="fas fa-headset" style="color: var(--purple-1);"></i> Support Tickets</h5>
                    <a href="{{ route('admin.support.index') }}" style="font-size: 12px; color: var(--purple-3); text-decoration: none;">View All →</a>
                </div>

                <!-- Mini stats -->
                <div class="row g-2 mb-3">
                    <div class="col-3">
                        <div style="text-align: center; padding: 10px; border-radius: 10px; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.15);">
                            <div style="font-size: 22px; font-weight: 700; color: #3b82f6;">{{ $ticketStats['open'] }}</div>
                            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">Open</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div style="text-align: center; padding: 10px; border-radius: 10px; background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.15);">
                            <div style="font-size: 22px; font-weight: 700; color: #10b981;">{{ $ticketStats['answered'] }}</div>
                            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">Answered</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div style="text-align: center; padding: 10px; border-radius: 10px; background: {{ $ticketStats['urgent'] > 0 ? 'rgba(239,68,68,0.08)' : 'rgba(100,116,139,0.08)' }}; border: 1px solid {{ $ticketStats['urgent'] > 0 ? 'rgba(239,68,68,0.15)' : 'rgba(100,116,139,0.15)' }};">
                            <div style="font-size: 22px; font-weight: 700; color: {{ $ticketStats['urgent'] > 0 ? '#ef4444' : '#64748b' }};">{{ $ticketStats['urgent'] }}</div>
                            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">Urgent</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div style="text-align: center; padding: 10px; border-radius: 10px; background: rgba(168,85,247,0.08); border: 1px solid rgba(168,85,247,0.15);">
                            <div style="font-size: 22px; font-weight: 700; color: #a855f7;">{{ $ticketStats['today'] }}</div>
                            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">New Today</div>
                        </div>
                    </div>
                </div>

                <!-- Recent tickets table -->
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr><th>Ticket</th><th>User</th><th>Subject</th><th>Priority</th><th>Status</th><th>Updated</th></tr>
                        </thead>
                        <tbody>
                        @forelse($recentTickets as $ticket)
                        <tr style="cursor: pointer;" onclick="window.location='{{ route('admin.support.show', $ticket) }}'">
                            <td><code style="color: #818cf8; font-size: 12px;">{{ $ticket->ticket_number }}</code></td>
                            <td style="font-size: 12px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <div style="width: 24px; height: 24px; border-radius: 6px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 10px;">
                                        {{ strtoupper(substr($ticket->user?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    {{ $ticket->user?->name ?? 'Unknown' }}
                                </div>
                            </td>
                            <td style="font-size: 12px; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text);">{{ $ticket->subject }}</td>
                            <td>
                                @php
                                    $pColors = ['low' => '#64748b', 'medium' => '#3b82f6', 'high' => '#f59e0b', 'urgent' => '#ef4444'];
                                    $pColor = $pColors[$ticket->priority] ?? '#64748b';
                                @endphp
                                <span style="font-size: 10px; padding: 2px 8px; border-radius: 6px; background: {{ $pColor }}20; color: {{ $pColor }}; text-transform: capitalize; font-weight: 600;">{{ $ticket->priority }}</span>
                            </td>
                            <td>
                                @php
                                    $sColors = ['open' => '#3b82f6', 'answered' => '#10b981', 'pending' => '#f59e0b', 'closed' => '#64748b'];
                                    $sColor = $sColors[$ticket->status] ?? '#64748b';
                                @endphp
                                <span style="font-size: 10px; padding: 2px 8px; border-radius: 6px; background: {{ $sColor }}20; color: {{ $sColor }}; text-transform: capitalize; font-weight: 600;">
                                    @if($ticket->status === 'answered') Awaiting Reply @else {{ $ticket->status }} @endif
                                </span>
                            </td>
                            <td style="font-size: 11px; color: var(--text-muted);">{{ $ticket->updated_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px;">
                            <i class="fas fa-check-circle" style="color: var(--green); margin-right: 6px;"></i> No open tickets — all caught up!
                        </td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Security Overview Widget -->
        <div class="col-lg-4">
            <div class="card-custom mb-3">
                <div class="section-header">
                    <h5><i class="fas fa-shield-alt" style="color: var(--purple-2);"></i> Security Overview</h5>
                    <a href="{{ route('admin.security.index') }}" style="font-size: 12px; color: var(--purple-3); text-decoration: none;">Details →</a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Failed logins today -->
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: 10px; background: {{ $securityStats['failed_logins_today'] > 0 ? 'rgba(239,68,68,0.06)' : 'rgba(16,185,129,0.06)' }}; border: 1px solid {{ $securityStats['failed_logins_today'] > 0 ? 'rgba(239,68,68,0.15)' : 'rgba(16,185,129,0.15)' }};">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: {{ $securityStats['failed_logins_today'] > 0 ? 'rgba(239,68,68,0.15)' : 'rgba(16,185,129,0.15)' }}; display: flex; align-items: center; justify-content: center;">
                                <i class="fas {{ $securityStats['failed_logins_today'] > 0 ? 'fa-exclamation-triangle' : 'fa-check' }}" style="color: {{ $securityStats['failed_logins_today'] > 0 ? '#ef4444' : '#10b981' }}; font-size: 14px;"></i>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: var(--text-bright); font-weight: 600;">Failed Logins</div>
                                <div style="font-size: 10px; color: var(--text-muted);">Today</div>
                            </div>
                        </div>
                        <div style="font-size: 20px; font-weight: 700; color: {{ $securityStats['failed_logins_today'] > 0 ? '#ef4444' : '#10b981' }};">{{ $securityStats['failed_logins_today'] }}</div>
                    </div>

                    <!-- Critical events today -->
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: 10px; background: {{ $securityStats['critical_events'] > 0 ? 'rgba(239,68,68,0.06)' : 'rgba(16,185,129,0.06)' }}; border: 1px solid {{ $securityStats['critical_events'] > 0 ? 'rgba(239,68,68,0.15)' : 'rgba(16,185,129,0.15)' }};">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: {{ $securityStats['critical_events'] > 0 ? 'rgba(239,68,68,0.15)' : 'rgba(16,185,129,0.15)' }}; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-skull-crossbones" style="color: {{ $securityStats['critical_events'] > 0 ? '#ef4444' : '#10b981' }}; font-size: 14px;"></i>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: var(--text-bright); font-weight: 600;">Critical Events</div>
                                <div style="font-size: 10px; color: var(--text-muted);">Today</div>
                            </div>
                        </div>
                        <div style="font-size: 20px; font-weight: 700; color: {{ $securityStats['critical_events'] > 0 ? '#ef4444' : '#10b981' }};">{{ $securityStats['critical_events'] }}</div>
                    </div>

                    <!-- Blocked IPs -->
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: 10px; background: {{ $securityStats['blocked_ips'] > 0 ? 'rgba(245,158,11,0.06)' : 'rgba(16,185,129,0.06)' }}; border: 1px solid {{ $securityStats['blocked_ips'] > 0 ? 'rgba(245,158,11,0.15)' : 'rgba(16,185,129,0.15)' }};">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: {{ $securityStats['blocked_ips'] > 0 ? 'rgba(245,158,11,0.15)' : 'rgba(16,185,129,0.15)' }}; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-ban" style="color: {{ $securityStats['blocked_ips'] > 0 ? '#f59e0b' : '#10b981' }}; font-size: 14px;"></i>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: var(--text-bright); font-weight: 600;">Blocked IPs</div>
                                <div style="font-size: 10px; color: var(--text-muted);">Active blocks</div>
                            </div>
                        </div>
                        <div style="font-size: 20px; font-weight: 700; color: {{ $securityStats['blocked_ips'] > 0 ? '#f59e0b' : '#10b981' }};">{{ $securityStats['blocked_ips'] }}</div>
                    </div>
                </div>

                <a href="{{ route('admin.security.index') }}" style="display: block; text-align: center; margin-top: 14px; padding: 10px; border-radius: 10px; background: var(--gradient-primary); color: white; font-size: 12px; font-weight: 600; text-decoration: none;">
                    <i class="fas fa-shield-alt"></i> Open Security Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Feature flags quick view -->
    <div class="card-custom mt-3">
        <div class="section-header">
            <h5><i class="fas fa-toggle-on" style="color: var(--purple-3);"></i> Feature Status Overview</h5>
            <a href="{{ route('admin.settings.features') }}" style="font-size: 12px; color: var(--purple-3); text-decoration: none;">Manage →</a>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            @foreach($features as $feature)
            <div style="display: flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 10px; background: var(--bg-input); border: 1px solid {{ $feature->is_enabled ? 'rgba(16,185,129,0.2)' : 'rgba(239,68,68,0.2)' }};">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: {{ $feature->is_enabled ? 'var(--green)' : 'var(--red)' }};"></div>
                <span style="font-size: 12px; color: var(--text-bright); font-weight: 500;">{{ $feature->label }}</span>
                <span style="font-size: 10px; color: {{ $feature->is_enabled ? 'var(--green)' : 'var(--red)' }};">{{ $feature->is_enabled ? 'ON' : 'OFF' }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
var chartLabels = @json($chartLabels);
var depositData = @json($depositData);
var withdrawalData = @json($withdrawalData);

var flowChart = new ApexCharts(document.getElementById("flowChart"), {
    series: [
        { name: "Deposits", data: depositData },
        { name: "Withdrawals", data: withdrawalData },
    ],
    chart: { type: "bar", height: 300, background: "transparent", fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
    theme: { mode: 'dark' },
    colors: ["#6366f1", "#ef4444"],
    plotOptions: { bar: { borderRadius: 6, columnWidth: '60%' } },
    grid: { borderColor: "#334155", strokeDashArray: 4, xaxis: { lines: { show: false } } },
    xaxis: { categories: chartLabels, labels: { style: { colors: "#94a3b8", fontSize: "11px" } }, axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis: { labels: { style: { colors: "#94a3b8", fontSize: "11px" }, formatter: function(v) { return "$" + v.toFixed(0); } } },
    tooltip: { theme: "dark", y: { formatter: function(v) { return "$" + v.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); } } },
    legend: { position: "top", labels: { colors: "#94a3b8" } },
    dataLabels: { enabled: false },
});
flowChart.render();
</script>
@endpush
@endsection