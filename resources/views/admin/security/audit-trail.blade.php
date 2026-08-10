@extends('layouts.admin')

@section('page-title', 'Audit Trail')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px; font-size: 22px;">
                <i class="fas fa-clipboard-list" style="color: #a855f7;"></i> Audit Trail
            </h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Complete log of every admin action and security event.</p>
            @if($logs->total() > 0)
            <p style="color: var(--text-dim); font-size: 11px; margin-top: 4px;">Showing {{ $logs->total() }} total records</p>
            @endif
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.security.index') }}" class="btn btn-sm" style="background: var(--bg-card); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 8px 16px; font-size: 12px; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
            <!-- Clear old logs -->
            <form method="POST" action="{{ route('admin.security.clear-logs') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-sm" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; border-radius: 10px; padding: 8px 16px; font-size: 12px;" onclick="return confirm('Clear all logs older than 90 days?')">
                    <i class="fas fa-trash"></i> Clear Old Logs
                </button>
                <input type="hidden" name="days" value="90">
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <!-- Filters -->
    <div class="card-custom mb-4" style="padding: 20px;">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Severity</label>
                <select name="severity" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
                    <option value="">All</option>
                    @foreach($severities as $sev)
                    <option value="{{ $sev }}" {{ request('severity') === $sev ? 'selected' : '' }}>{{ ucfirst($sev) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Module</label>
                <select name="module" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
                    <option value="">All</option>
                    @foreach($modules as $mod)
                    <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
            </div>
            <div class="col-md-2">
                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn w-100" style="background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; border: none; border-radius: 10px; padding: 10px;">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.security.audit-trail') }}" class="btn w-100" style="background: var(--bg-card); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 10px; text-decoration: none; font-size: 13px;">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Log Table -->
    <div class="card-custom" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="table mb-0" style="color: var(--text);">
                <thead>
                    <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim); background: rgba(30,35,55,0.5);">
                        <th style="padding: 12px 16px;">Severity</th>
                        <th style="padding: 12px 16px;">Action</th>
                        <th style="padding: 12px 16px;">Description</th>
                        <th style="padding: 12px 16px;">User</th>
                        <th style="padding: 12px 16px;">Module</th>
                        <th style="padding: 12px 16px;">IP</th>
                        <th style="padding: 12px 16px;">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    @php $sevColors = \App\Models\SecurityLog::severityColors(); @endphp
                    <tr style="font-size: 13px; border-bottom: 1px solid rgba(51,65,85,0.2);">
                        <td style="padding: 12px 16px;">
                            <span style="background: {{ ($sevColors[$log->severity] ?? '#6366f1') }}20; color: {{ $sevColors[$log->severity] ?? '#6366f1' }}; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 600; text-transform: uppercase;">
                                {{ $log->severity }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px; color: var(--text-bright); font-weight: 500;">{{ $log->action }}</td>
                        <td style="padding: 12px 16px; color: var(--text-muted); max-width: 300px;">{{ $log->description ?? '—' }}</td>
                        <td style="padding: 12px 16px; color: var(--text);">
                            @if($log->user)
                            {{ $log->user->name }}
                            <span style="font-size: 10px; color: var(--text-dim);">({{ $log->user->role }})</span>
                            @else
                            <span style="color: var(--text-dim);">System</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px;">
                            @if($log->module)
                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 6px; background: rgba(99,102,241,0.1); color: #818cf8;">{{ $log->module }}</span>
                            @else
                            <span style="color: var(--text-dim);">—</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px;"><code style="color: var(--text-dim); font-size: 12px;">{{ $log->ip_address }}</code></td>
                        <td style="padding: 12px 16px; color: var(--text-dim); font-size: 12px;">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-dim);">No security logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    {{ $logs->links() }}
</div>
@endsection