@extends('layouts.admin')

@section('page-title', 'Audit Log Details')

@section('content')
<div class="fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 8px; font-size: 22px;">
                <i class="fas fa-history" style="color: var(--purple-3);"></i> Audit Log #{{ $log->id }}
            </h2>
            <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                <a href="{{ route('admin.audit-logs.index') }}" style="color: var(--purple-1);">← Back to Audit Logs</a>
            </p>
        </div>
    </div>

    <div class="card-custom" style="padding: 24px;">
        <div class="row g-3">
            <div class="col-md-6">
                <label style="font-size: 12px; color: var(--text-dim); font-weight: 600;">User</label>
                <p style="font-size: 14px; color: var(--text-bright);">{{ $log->user?->name ?? 'System' }} ({{ $log->user?->email ?? 'N/A' }})</p>
            </div>
            <div class="col-md-6">
                <label style="font-size: 12px; color: var(--text-dim); font-weight: 600;">Action</label>
                <p style="font-size: 14px; color: var(--text-bright);">
                    <span class="badge" style="background: rgba(99,102,241,0.15); color: var(--purple-1); padding: 4px 10px; border-radius: 6px; font-size: 11px;">{{ $log->action }}</span>
                </p>
            </div>
            <div class="col-md-6">
                <label style="font-size: 12px; color: var(--text-dim); font-weight: 600;">Module</label>
                <p style="font-size: 14px; color: var(--text-bright);">{{ $log->module ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <label style="font-size: 12px; color: var(--text-dim); font-weight: 600;">Severity</label>
                <p style="font-size: 14px;">
                    @php $sevColors = ['critical' => '#ef4444', 'warning' => '#f59e0b', 'info' => '#3b82f6', 'low' => '#6b7280']; @endphp
                    <span class="badge" style="background: {{ $sevColors[$log->severity] ?? '#6b7280' }}20; color: {{ $sevColors[$log->severity] ?? '#6b7280' }}; padding: 4px 10px; border-radius: 6px; font-size: 11px; text-transform: uppercase;">{{ $log->severity ?? 'info' }}</span>
                </p>
            </div>
            <div class="col-md-6">
                <label style="font-size: 12px; color: var(--text-dim); font-weight: 600;">IP Address</label>
                <p style="font-size: 14px; color: var(--text-bright);">{{ $log->ip_address ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <label style="font-size: 12px; color: var(--text-dim); font-weight: 600;">Date & Time</label>
                <p style="font-size: 14px; color: var(--text-bright);">{{ $log->created_at?->format('M d, Y H:i:s') ?? 'N/A' }}</p>
            </div>
            <div class="col-12">
                <label style="font-size: 12px; color: var(--text-dim); font-weight: 600;">Description</label>
                <p style="font-size: 14px; color: var(--text-bright); padding: 12px; background: var(--bg-input); border-radius: 10px; border: 1px solid var(--border);">{{ $log->description ?? 'No description' }}</p>
            </div>
            @if($log->metadata)
            <div class="col-12">
                <label style="font-size: 12px; color: var(--text-dim); font-weight: 600;">Metadata (JSON)</label>
                <pre style="font-size: 12px; color: var(--text-muted); padding: 12px; background: var(--bg-input); border-radius: 10px; border: 1px solid var(--border); overflow-x: auto; max-height: 300px;"><code>{{ json_encode(json_decode($log->metadata), JSON_PRETTY_PRINT) }}</code></pre>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
