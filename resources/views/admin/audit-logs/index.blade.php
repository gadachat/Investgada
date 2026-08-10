@extends('layouts.admin')
@section('title', 'Audit Logs')

@push('styles')
<style>
    .log-row { transition: background 0.15s; }
    .log-row:hover { background: var(--bg-hover, #f8fafc); }
    .action-badge { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .action-login { background: #dbeafe; color: #2563eb; }
    .action-logout { background: #e0e7ff; color: #6366f1; }
    .action-password_changed { background: #fef3c7; color: #d97706; }
    .action-settings_changed { background: #f3e8ff; color: #7c3aed; }
    .action-withdrawal_requested { background: #fee2e2; color: #dc2626; }
    .action-deposit_submitted { background: #d1fae5; color: #059669; }
    .filter-form .form-control { font-size: 13px; }
</style>
@endpush

@section('content')
<div class="fade-in">
    <h4 style="font-weight:700; margin-bottom:20px;"><i class="fas fa-history me-2"></i> Audit Logs</h4>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card-custom stat-card">
                <p style="color:var(--text-muted); font-size:12px; margin-bottom:4px;">Total Events</p>
                <p style="font-size:24px; font-weight:700; color:var(--text-bright);">{{ number_format($stats['total']) }}</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card-custom stat-card">
                <p style="color:var(--text-muted); font-size:12px; margin-bottom:4px;">Logins Today</p>
                <p style="font-size:24px; font-weight:700; color:#3b82f6;">{{ $stats['logins_today'] }}</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card-custom stat-card">
                <p style="color:var(--text-muted); font-size:12px; margin-bottom:4px;">Failed Logins</p>
                <p style="font-size:24px; font-weight:700; color:#dc2626;">{{ $stats['failed'] }}</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card-custom stat-card">
                <p style="color:var(--text-muted); font-size:12px; margin-bottom:4px;">Actions Today</p>
                <p style="font-size:24px; font-weight:700; color:#7c3aed;">{{ $stats['actions_today'] }}</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card-custom mb-3 filter-form">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label style="font-size:12px; color:var(--text-muted);">Action</label>
                <select name="action" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach($actions as $act)
                    <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ str_replace('_', ' ', $act) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label style="font-size:12px; color:var(--text-muted);">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label style="font-size:12px; color:var(--text-muted);">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3">
                <label style="font-size:12px; color:var(--text-muted);">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Email, IP, description..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-gradient"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('admin.audit-logs.export', request()->query()) }}" class="btn btn-sm" style="background:var(--bg-card); border:1px solid var(--border);"><i class="fas fa-download"></i> Export CSV</a>
                <button type="button" class="btn btn-sm" style="background:#fee2e2; color:#dc2626;" onclick="clearLogs()">Clear</button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card-custom" style="overflow-x:auto;">
        <table class="table table-custom mb-0" style="font-size:13px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);">
                    <th>Date</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Device</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="log-row">
                    <td style="color:var(--text-dim); white-space:nowrap;">{{ $log->created_at->format('M d, Y H:i') }}</td>
                    <td>{{ $log->user?->name ?? ($log->admin?->name ?? 'System') }}</td>
                    <td><span class="action-badge action-{{ $log->action }}">{{ str_replace('_', ' ', $log->action) }}</span></td>
                    <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis;">{{ $log->description }}</td>
                    <td style="font-family:monospace; font-size:12px;">{{ $log->ip_address }}</td>
                    <td style="font-size:11px; color:var(--text-dim); max-width:150px; overflow:hidden; text-overflow:ellipsis;" title="{{ $log->user_agent }}">{{ Str::limit($log->user_agent, 25) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted);">No logs found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->withQueryString()->links() }}
</div>

<script>
function clearLogs() {
    const days = prompt('Delete logs older than how many days?', '90');
    if (days && confirm('Are you sure? This cannot be undone.')) {
        fetch('{{ route("admin.audit-logs.clear") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({days: parseInt(days)})
        }).then(r => r.json()).then(() => location.reload());
    }
}
</script>
@endsection
