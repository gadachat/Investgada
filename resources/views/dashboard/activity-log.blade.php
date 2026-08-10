@extends('layouts.dashboard')
@section('title', 'Activity Log')

@section('content')
<div class="fade-in">
    <h4 style="font-weight:700; margin-bottom:20px;"><i class="fas fa-history me-2"></i> My Activity Log</h4>

    <div class="card-custom mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label style="font-size:12px; color:var(--text-muted);">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3">
                <label style="font-size:12px; color:var(--text-muted);">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-gradient"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </form>
    </div>

    <div class="card-custom" style="overflow-x:auto;">
        <table class="table table-custom mb-0" style="font-size:13px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);">
                    <th>Date & Time</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="color:var(--text-dim); white-space:nowrap;">{{ $log->created_at->format('M d, Y H:i') }}</td>
                    <td style="text-transform:capitalize;">{{ str_replace('_', ' ', $log->action) }}</td>
                    <td>{{ $log->description }}</td>
                    <td style="font-family:monospace; font-size:12px;">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted);">No activity logged</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->withQueryString()->links() }}
</div>
@endsection
