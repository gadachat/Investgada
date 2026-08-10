@extends('layouts.admin')

@section('page-title', 'Withdrawal Management')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 24px; font-size: 22px;"><i class="fas fa-arrow-up" style="color: var(--red);"></i> Withdrawal Management</h2>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="stat-card"><div class="stat-icon red"><i class="fas fa-dollar-sign"></i></div><div class="stat-label">Total Paid Out</div><div class="stat-value">${{ number_format($stats['total'], 2) }}</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-clock"></i></div><div class="stat-label">Pending Count</div><div class="stat-value">{{ $stats['pending'] }}</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-hourglass-half"></i></div><div class="stat-label">Pending Amount</div><div class="stat-value">${{ number_format($stats['pending_amt'], 2) }}</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-icon blue"><i class="fas fa-cog"></i></div><div class="stat-label">Processing</div><div class="stat-value">{{ $stats['processing'] }}</div></div></div>
    </div>

    <div class="card-custom mb-3">
        <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 150px;">
                <label style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">Status</label>
                <select name="status" class="form-control" style="font-size: 13px;">
                    <option value="all" @selected(request('status') === 'all')>All</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="processing" @selected(request('status') === 'processing')>Processing</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="User name, email, or reference..." style="font-size: 13px;">
            </div>
            <button type="submit" class="btn-gradient" style="padding: 8px 20px; font-size: 13px;"><i class="fas fa-filter"></i> Filter</button>
        </form>
    </div>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr><th>User</th><th>Reference</th><th>Method</th><th>Amount</th><th>Fee</th><th>Net</th><th>Destination</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($withdrawals as $wdr)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">{{ strtoupper(substr($wdr->user->name, 0, 1)) }}</div>
                            <div><div style="font-weight: 600; color: var(--text-bright); font-size: 13px;">{{ $wdr->user->name }}</div><div style="font-size: 11px; color: var(--text-dim);">{{ $wdr->user->email }}</div></div>
                        </div>
                    </td>
                    <td style="font-family: monospace; font-size: 12px; color: var(--text-muted);">{{ $wdr->reference }}</td>
                    <td><span class="badge-custom badge-purple">{{ $wdr->method }}</span></td>
                    <td style="font-weight: 600; color: var(--red);">${{ number_format($wdr->amount, 2) }}</td>
                    <td style="color: var(--text-muted);">${{ number_format($wdr->fee, 2) }}</td>
                    <td style="font-weight: 600; color: var(--text-bright);">${{ number_format($wdr->net_amount, 2) }}</td>
                    <td style="font-size: 11px; color: var(--text-muted); max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $wdr->wallet_address ? $wdr->wallet_address : ($wdr->bank_name ? $wdr->bank_name . ' (' . $wdr->bank_account_number . ')' : '—') }}
                    </td>
                    <td>
                        @if($wdr->status === 'completed')<span class="badge-custom badge-up">Completed</span>
                        @elseif($wdr->status === 'pending')<span class="badge-custom badge-pending">Pending</span>
                        @elseif($wdr->status === 'processing')<span class="badge-custom badge-info">Processing</span>
                        @else<span class="badge-custom badge-down">{{ ucfirst($wdr->status) }}</span>@endif
                    </td>
                    <td>
                        @if($wdr->status === 'pending')
                        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                            <form method="POST" action="{{ route('admin.withdrawals.approve', $wdr) }}" style="display: inline;">@csrf
                                <button type="submit" class="btn-gradient" style="padding: 4px 10px; font-size: 11px;" onclick="return confirm('Approve this withdrawal?')"><i class="fas fa-check"></i> Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.withdrawals.reject', $wdr) }}" style="display: inline;">@csrf
                                <button type="submit" name="admin_note" value="Rejected by admin" class="btn-outline-custom" style="padding: 4px 10px; font-size: 11px; color: var(--red); border-color: rgba(239,68,68,0.3);" onclick="return confirm('Reject this withdrawal? Funds will be returned to user.')"><i class="fas fa-times"></i> Reject</button>
                            </form>
                        </div>
                        @elseif($wdr->status === 'processing')
                        <div style="display: flex; gap: 4px;">
                            <form method="POST" action="{{ route('admin.withdrawals.complete', $wdr) }}" style="display: inline;">@csrf
                                <button type="submit" class="btn-gradient" style="padding: 4px 10px; font-size: 11px;" onclick="return confirm('Mark this withdrawal as completed?')"><i class="fas fa-check-double"></i> Complete</button>
                            </form>
                            <form method="POST" action="{{ route('admin.withdrawals.reject', $wdr) }}" style="display: inline;">@csrf
                                <button type="submit" name="admin_note" value="Cancelled by admin" class="btn-outline-custom" style="padding: 4px 10px; font-size: 11px; color: var(--red); border-color: rgba(239,68,68,0.3);" onclick="return confirm('Cancel and refund?')"><i class="fas fa-undo"></i></button>
                            </form>
                        </div>
                        @else
                        <span style="font-size: 11px; color: var(--text-dim);">Done</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 16px; border-top: 1px solid var(--border); display: flex; justify-content: center;">{{ $withdrawals->links() }}</div>
    </div>
</div>
@endsection
