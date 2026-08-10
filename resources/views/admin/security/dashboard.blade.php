@extends('layouts.admin')

@section('page-title', 'Security Dashboard')

@section('content')
<div class="fade-in">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px; font-size: 22px;">
                <i class="fas fa-shield-alt" style="color: #6366f1;"></i> Security Dashboard
            </h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Real-time monitoring of platform security, login activity, and threats.</p>
        </div>
        <a href="{{ route('admin.security.audit-trail') }}" class="btn btn-sm" style="background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; border: none; border-radius: 10px; padding: 8px 20px; font-size: 12px; text-decoration: none;">
            <i class="fas fa-clipboard-list"></i> View Full Audit Trail
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <!-- Stats Grid -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-label">Failed Logins Today</div>
                <div class="stat-value">{{ number_format($stats['failed_logins_today']) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <div class="stat-label">Successful Logins Today</div>
                <div class="stat-value">{{ number_format($stats['successful_logins_today']) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="stat-label">Blocked IPs</div>
                <div class="stat-value">{{ $stats['blocked_ips'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-laptop"></i>
                </div>
                <div class="stat-label">Active Sessions (15m)</div>
                <div class="stat-value">{{ $stats['active_sessions'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-skull-crossbones"></i>
                </div>
                <div class="stat-label">Critical Events Today</div>
                <div class="stat-value" style="color: {{ $stats['critical_events_today'] > 0 ? '#ef4444' : '#fff' }};">{{ $stats['critical_events_today'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-label">Total Audit Logs</div>
                <div class="stat-value">{{ number_format($stats['total_audit_logs']) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-label">Whitelisted IPs</div>
                <div class="stat-value">{{ $stats['whitelisted_ips'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div class="stat-label">Suspended/Banned Users</div>
                <div class="stat-value">{{ $stats['suspended_users'] }}</div>
            </div>
        </div>
    </div>

    <!-- Login Activity Chart -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card-custom" style="padding: 24px;">
                <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 4px; font-size: 16px;">Login Activity (7 Days)</h5>
                <p style="color: var(--text-muted); font-size: 12px; margin-bottom: 16px;">Successful vs failed login attempts over the last week</p>
                <div id="loginChart" style="height: 280px;"></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-custom" style="padding: 20px;">
                <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 12px; font-size: 16px;">
                    <i class="fas fa-fire" style="color: #ef4444;"></i> Top Failed IPs
                </h5>
                <p style="color: var(--text-muted); font-size: 12px; margin-bottom: 16px;">Most active failed login IPs (7 days)</p>
                <div style="max-height: 260px; overflow-y: auto;">
                    @forelse($topBlockedIps as $ip)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid rgba(51,65,85,0.3);">
                        <div>
                            <code style="color: var(--text); font-size: 13px;">{{ $ip->ip_address }}</code>
                            <div style="font-size: 11px; color: var(--text-dim);">{{ $ip->last_attempt->diffForHumans() }}</div>
                        </div>
                        <div>
                            <span style="background: rgba(239,68,68,0.15); color: #ef4444; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">{{ $ip->attempts }}x</span>
                            <a href="{{ route('admin.security.block-ip') }}" onclick="event.preventDefault(); document.getElementById('block-ip-form').style.display='block'; document.getElementById('quick-block-ip').value='{{ $ip->ip_address }}'" style="margin-left: 6px; color: var(--text-dim); font-size: 11px; cursor: pointer;" title="Block this IP"><i class="fas fa-ban"></i></a>
                        </div>
                    </div>
                    @empty
                    <p style="color: var(--text-dim); font-size: 13px; text-align: center; padding: 20px;">No failed login attempts in the last 7 days.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Block IP Form -->
    <div id="block-ip-form" style="display: none; margin-bottom: 20px;">
        <div class="card-custom" style="padding: 20px; border-color: rgba(239,68,68,0.3);">
            <form method="POST" action="{{ route('admin.security.block-ip') }}">
                @csrf
                <h6 style="color: #ef4444; font-weight: 600; margin-bottom: 12px;"><i class="fas fa-ban"></i> Block IP Address</h6>
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label style="font-size: 12px; color: var(--text-muted);">IP Address</label>
                        <input type="text" name="ip_address" id="quick-block-ip" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px;" required>
                    </div>
                    <div class="col-md-3">
                        <label style="font-size: 12px; color: var(--text-muted);">Duration</label>
                        <select name="duration" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px;">
                            <option value="1h">1 hour</option>
                            <option value="6h">6 hours</option>
                            <option value="24h" selected>24 hours</option>
                            <option value="7d">7 days</option>
                            <option value="permanent">Permanent</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label style="font-size: 12px; color: var(--text-muted);">Reason</label>
                        <input type="text" name="reason" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px;" placeholder="Brute force attempts">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn w-100" style="background: #ef4444; color: white; border: none; border-radius: 10px; padding: 10px;">Block IP</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Login Attempts -->
    <div class="card-custom mb-4" style="padding: 20px;">
        <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 4px; font-size: 16px;">
            <i class="fas fa-key" style="color: #6366f1;"></i> Recent Login Attempts
        </h5>
        <p style="color: var(--text-muted); font-size: 12px; margin-bottom: 16px;">Last 50 login attempts across the platform</p>
        <div style="overflow-x: auto;">
            <table class="table" style="color: var(--text); margin: 0;">
                <thead>
                    <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim);">
                        <th>Email</th>
                        <th>IP Address</th>
                        <th>Status</th>
                        <th>Device</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLogins->take(20) as $attempt)
                    <tr style="font-size: 13px; border-bottom: 1px solid rgba(51,65,85,0.2);">
                        <td style="color: var(--text);">{{ $attempt->email }}</td>
                        <td><code style="color: var(--text-muted);">{{ $attempt->ip_address }}</code></td>
                        <td>
                            @if($attempt->successful)
                            <span style="background: rgba(16,185,129,0.15); color: #10b981; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;"><i class="fas fa-check"></i> Success</span>
                            @else
                            <span style="background: rgba(239,68,68,0.15); color: #ef4444; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;"><i class="fas fa-times"></i> Failed</span>
                            @endif
                        </td>
                        <td style="color: var(--text-dim); font-size: 12px;">{{ \App\Models\ActiveSession::detectDevice($attempt->user_agent) }}</td>
                        <td style="color: var(--text-dim); font-size: 12px;">{{ $attempt->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Security Logs -->
    <div class="card-custom" style="padding: 20px;">
        <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 4px; font-size: 16px;">
            <i class="fas fa-clipboard-list" style="color: #a855f7;"></i> Recent Security Events
        </h5>
        <p style="color: var(--text-muted); font-size: 12px; margin-bottom: 16px;">Latest admin actions and security events</p>
        <div style="overflow-x: auto;">
            <table class="table" style="color: var(--text); margin: 0;">
                <thead>
                    <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim);">
                        <th>Severity</th>
                        <th>Action</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>IP</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLogs->take(20) as $log)
                    <tr style="font-size: 13px; border-bottom: 1px solid rgba(51,65,85,0.2);">
                        <td>
                            @php $sevColors = \App\Models\SecurityLog::severityColors(); @endphp
                            <span style="background: {{ ($sevColors[$log->severity] ?? '#6366f1') }}20; color: {{ $sevColors[$log->severity] ?? '#6366f1' }}; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 600; text-transform: uppercase;">
                                {{ $log->severity }}
                            </span>
                        </td>
                        <td style="color: var(--text);">{{ $log->action }}</td>
                        <td style="color: var(--text-muted);">{{ $log->user?->name ?? 'System' }}</td>
                        <td><span style="font-size: 11px; color: var(--text-dim);">{{ $log->module ?? '—' }}</span></td>
                        <td><code style="color: var(--text-dim); font-size: 12px;">{{ $log->ip_address }}</code></td>
                        <td style="color: var(--text-dim); font-size: 12px;">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Login Activity Chart
const loginChart = new ApexCharts(document.getElementById('loginChart'), {
    series: [
        { name: 'Successful', data: @json($successData), color: '#10b981' },
        { name: 'Failed', data: @json($failedData), color: '#ef4444' },
    ],
    chart: {
        type: 'bar',
        height: 280,
        background: 'transparent',
        toolbar: { show: false },
        stacked: false,
    },
    colors: ['#10b981', '#ef4444'],
    plotOptions: {
        bar: { borderRadius: 6, columnWidth: '60%' }
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 0 },
    xaxis: {
        categories: @json($labels),
        labels: { style: { colors: '#94a3b8', fontSize: '12px' } },
        axisBorder: { show: false },
    },
    yaxis: {
        labels: { style: { colors: '#94a3b8', fontSize: '12px' } },
    },
    grid: {
        borderColor: 'rgba(51,65,85,0.3)',
        strokeDashArray: 3,
    },
    legend: {
        position: 'top',
        labels: { colors: '#94a3b8' },
    },
    tooltip: {
        theme: 'dark',
        y: { formatter: v => v + ' attempts' }
    }
});
loginChart.render();
</script>
@endsection