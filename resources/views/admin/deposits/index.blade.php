@extends('layouts.admin')

@section('page-title', 'Deposit Management')

@section('content')
<div class="fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
        <h2 style="color: var(--text-bright); font-weight: 700; margin: 0; font-size: 22px;"><i class="fas fa-arrow-down" style="color: var(--green);"></i> Deposit Management</h2>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="stat-card"><div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div><div class="stat-label">Total Deposits</div><div class="stat-value">${{ number_format($stats['total'], 2) }}</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-icon green"><i class="fas fa-check"></i></div><div class="stat-label">Confirmed</div><div class="stat-value">${{ number_format($stats['confirmed'], 2) }}</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-clock"></i></div><div class="stat-label">Pending Count</div><div class="stat-value">{{ $stats['pending'] }}</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-hourglass-half"></i></div><div class="stat-label">Pending Amount</div><div class="stat-value">${{ number_format($stats['pending_amt'], 2) }}</div></div></div>
    </div>

    <!-- Filter -->
    <div class="card-custom mb-3">
        <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width:100%;max-width:150px;">
                <label style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">Status</label>
                <select name="status" class="form-control" style="font-size: 13px;">
                    <option value="all" @selected(request('status') === 'all')>All</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>
            </div>
            <div style="flex: 1; min-width:100%;max-width:150px;">
                <label style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="User name, email, or reference..." style="font-size: 13px;">
            </div>
            <button type="submit" class="btn-gradient" style="padding: 8px 20px; font-size: 13px;"><i class="fas fa-filter"></i> Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr><th>User</th><th>Reference</th><th>Method</th><th>Amount</th><th>Fee</th><th>Net</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($deposits as $dep)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">{{ strtoupper(substr($dep->user->name, 0, 1)) }}</div>
                            <div><div style="font-weight: 600; color: var(--text-bright); font-size: 13px;">{{ $dep->user->name }}</div><div style="font-size: 11px; color: var(--text-dim);">{{ $dep->user->email }}</div></div>
                        </div>
                    </td>
                    <td style="font-family: monospace; font-size: 12px; color: var(--text-muted);">{{ $dep->reference }}</td>
                    <td><span class="badge-custom badge-purple">{{ $dep->method }}</span></td>
                    <td style="font-weight: 600; color: var(--text-bright);">${{ number_format($dep->amount, 2) }}</td>
                    <td style="color: var(--text-muted);">${{ number_format($dep->fee, 2) }}</td>
                    <td style="font-weight: 600; color: var(--green);">${{ number_format($dep->net_amount, 2) }}</td>
                    <td>
                        @if($dep->status === 'confirmed')<span class="badge-custom badge-up">Confirmed</span>
                        @elseif($dep->status === 'pending')<span class="badge-custom badge-pending">Pending</span>
                        @else<span class="badge-custom badge-down">{{ ucfirst($dep->status) }}</span>@endif
                    </td>
                    <td style="font-size: 12px; color: var(--text-muted);">{{ $dep->created_at->format('M d, H:i') }}</td>
                    <td>
                        @if($dep->status === 'pending')
                        <div style="display: flex; gap: 6px;">
                            <form method="POST" action="{{ route('admin.deposits.approve', $dep) }}" style="display: inline;">
                                @csrf <button type="submit" class="btn-gradient" style="padding: 5px 12px; font-size: 11px;" onclick="return confirm('Approve this deposit?')"><i class="fas fa-check"></i> Approve</button>
                            </form>
                            <button class="btn-outline-custom" style="padding: 5px 12px; font-size: 11px; color: var(--red); border-color: rgba(239,68,68,0.3);" onclick="rejectDeposit('{{ $dep->id }}', '{{ $dep->reference }}')"><i class="fas fa-times"></i> Reject</button>
                        </div>
                        @else
                        <span style="font-size: 11px; color: var(--text-dim);">Processed</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 16px; border-top: 1px solid var(--border); display: flex; justify-content: center;">{{ $deposits->links() }}</div>
    </div>
</div>

<!-- Reject modal -->
<div id="rejectModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); z-index:9999; align-items:center; justify-content:center;">
    <div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 24px; width: 100%; max-width:100%;max-width:420px; margin: 20px;">
        <h5 style="color: var(--text-bright); margin-bottom: 16px;"><i class="fas fa-times-circle" style="color: var(--red);"></i> Reject Deposit</h5>
        <form id="rejectForm" method="POST">
            @csrf
            <input type="hidden" name="admin_note" id="rejectNote">
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">Reference: <span id="rejectRef" style="color: var(--text-bright);"></span></p>
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="font-size: 13px; color: var(--text-muted);">Reason for rejection</label>
                <textarea name="reason" id="rejectReason" class="form-control" rows="3" placeholder="Enter reason..." required></textarea>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-gradient" style="background: linear-gradient(135deg, #ef4444, #dc2626); flex: 1;"><i class="fas fa-times"></i> Reject</button>
                <button type="button" class="btn-outline-custom" onclick="closeRejectModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function rejectDeposit(id, ref) {
    document.getElementById('rejectRef').textContent = ref;
    document.getElementById('rejectForm').action = '{{ url("admin/deposits") }}/' + id + '/reject';
    document.getElementById('rejectModal').style.display = 'flex';
}
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
@endsection
