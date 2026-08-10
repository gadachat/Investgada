@extends('layouts.admin')

@section('page-title', 'User Activity Report')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px;">
        <i class="fas fa-users me-2" style="color: var(--purple-1);"></i> User Activity Report
    </h2>
    <p style="color: var(--text-muted); margin: 0 0 24px; font-size: 14px;">User engagement, investment activity, and financial summary</p>

    <!-- Filter -->
    <div class="admin-card p-3 mb-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted);">Status</label>
                <select name="status" class="form-select form-select-sm" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted);">Search</label>
                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);" placeholder="Name or email...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-gradient btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="admin-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="color: var(--text);">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">User</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Status</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">KYC</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Investments</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Deposited</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Invested</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Earned</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Withdrawn</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="border: none;">
                            <div style="color: var(--text-bright); font-weight: 500; font-size: 13px;">{{ $u->name }}</div>
                            <small style="color: var(--text-dim);">{{ $u->email }}</small>
                        </td>
                        <td style="border: none;">
                            @if($u->status === 'active')<span class="badge" style="background: rgba(16,185,129,0.15); color: #10b981;">Active</span>
                            @else<span class="badge" style="background: rgba(239,68,68,0.15); color: #ef4444;">{{ ucfirst($u->status) }}</span>@endif
                        </td>
                        <td style="border: none;">
                            @if($u->kyc_status === 'verified')<span class="badge" style="background: rgba(16,185,129,0.15); color: #10b981; font-size: 10px;">Verified</span>
                            @elseif($u->kyc_status === 'pending')<span class="badge" style="background: rgba(245,158,11,0.15); color: #f59e0b; font-size: 10px;">Pending</span>
                            @else<span class="badge" style="background: rgba(100,116,139,0.15); color: #64748b; font-size: 10px;">—</span>@endif
                        </td>
                        <td style="border: none; color: var(--text); font-size: 13px;">{{ $u->investment_count }}</td>
                        <td style="border: none; color: #10b981; font-weight: 600; font-size: 13px;">${{ number_format($u->total_deposited, 0) }}</td>
                        <td style="border: none; color: var(--purple-1); font-weight: 600; font-size: 13px;">${{ number_format($u->total_invested ?? 0, 0) }}</td>
                        <td style="border: none; color: var(--purple-3); font-weight: 600; font-size: 13px;">${{ number_format($u->total_earned ?? 0, 0) }}</td>
                        <td style="border: none; color: #ef4444; font-weight: 600; font-size: 13px;">${{ number_format($u->total_withdrawn_amt, 0) }}</td>
                        <td style="border: none; font-size: 12px; color: var(--text-muted);">{{ \Carbon\Carbon::parse($u->created_at)->format('M j, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="border: none; text-align: center; padding: 40px; color: var(--text-muted);">No users found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $users->links() }}
</div>
@endsection
