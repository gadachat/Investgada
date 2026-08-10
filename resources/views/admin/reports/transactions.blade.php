@extends('layouts.admin')

@section('page-title', 'Transaction Report')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px;">
        <i class="fas fa-list me-2" style="color: var(--purple-1);"></i> Transaction Report
    </h2>
    <p style="color: var(--text-muted); margin: 0 0 24px; font-size: 14px;">Detailed transaction log with filtering</p>

    <!-- Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="admin-card p-3 text-center"><div style="font-size: 20px; font-weight: 700; color: var(--purple-1);">{{ number_format($summary['total']) }}</div><small style="color: var(--text-dim);">Total Transactions</small></div></div>
        <div class="col-md-3"><div class="admin-card p-3 text-center"><div style="font-size: 20px; font-weight: 700; color: #10b981;">${{ number_format($summary['credits'], 2) }}</div><small style="color: var(--text-dim);">Total Credits</small></div></div>
        <div class="col-md-3"><div class="admin-card p-3 text-center"><div style="font-size: 20px; font-weight: 700; color: #ef4444;">${{ number_format($summary['debits'], 2) }}</div><small style="color: var(--text-dim);">Total Debits</small></div></div>
        <div class="col-md-3"><div class="admin-card p-3 text-center"><div style="font-size: 20px; font-weight: 700; color: var(--purple-3);">${{ number_format($summary['volume'], 2) }}</div><small style="color: var(--text-dim);">Total Volume</small></div></div>
    </div>

    <!-- Filters -->
    <div class="admin-card p-3 mb-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label style="font-size: 11px; color: var(--text-muted);">Type</label>
                <select name="type" class="form-select form-select-sm" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
                    <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="deposit" {{ $type === 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="withdrawal" {{ $type === 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                    <option value="investment" {{ $type === 'investment' ? 'selected' : '' }}>Investment</option>
                    <option value="profit_share" {{ $type === 'profit_share' ? 'selected' : '' }}>Profit Share</option>
                    <option value="referral_commission" {{ $type === 'referral_commission' ? 'selected' : '' }}>Referral</option>
                    <option value="matching_bonus" {{ $type === 'matching_bonus' ? 'selected' : '' }}>Matching Bonus</option>
                    <option value="transfer" {{ $type === 'transfer' ? 'selected' : '' }}>Transfer</option>
                </select>
            </div>
            <div class="col-md-2">
                <label style="font-size: 11px; color: var(--text-muted);">Status</label>
                <select name="status" class="form-select form-select-sm" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label style="font-size: 11px; color: var(--text-muted);">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
            </div>
            <div class="col-md-2">
                <label style="font-size: 11px; color: var(--text-muted);">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
            </div>
            <div class="col-md-2">
                <label style="font-size: 11px; color: var(--text-muted);">Search</label>
                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);" placeholder="Name, email, ref...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-gradient btn-sm w-100"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="admin-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="color: var(--text);">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Date</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">User</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Type</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Direction</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Amount</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Reference</th>
                        <th style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; border: none;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="border: none; font-size: 12px; color: var(--text-muted);">{{ \Carbon\Carbon::parse($txn->created_at)->format('M j, Y g:i A') }}</td>
                        <td style="border: none;">
                            <div style="color: var(--text-bright); font-weight: 500; font-size: 13px;">{{ $txn->user_name ?? '—' }}</div>
                            <small style="color: var(--text-dim);">{{ $txn->user_email ?? '' }}</small>
                        </td>
                        <td style="border: none;"><span class="badge" style="background: var(--purple-1); color: white; font-size: 10px;">{{ $txn->type }}</span></td>
                        <td style="border: none;">
                            @if($txn->direction === 'credit')
                            <span style="color: #10b981; font-size: 13px;"><i class="fas fa-arrow-down"></i> Credit</span>
                            @else
                            <span style="color: #ef4444; font-size: 13px;"><i class="fas fa-arrow-up"></i> Debit</span>
                            @endif
                        </td>
                        <td style="border: none; font-weight: 700; color: {{ $txn->direction === 'credit' ? '#10b981' : '#ef4444' }}; font-size: 13px;">
                            {{ $txn->direction === 'credit' ? '+' : '-' }}${{ number_format($txn->amount, 2) }}
                        </td>
                        <td style="border: none; font-family: monospace; font-size: 11px; color: var(--text-muted);">{{ $txn->reference ?? '—' }}</td>
                        <td style="border: none;">
                            @if($txn->status === 'completed')<span class="badge" style="background: rgba(16,185,129,0.15); color: #10b981;">Completed</span>
                            @elseif($txn->status === 'pending')<span class="badge" style="background: rgba(245,158,11,0.15); color: #f59e0b;">Pending</span>
                            @else<span class="badge" style="background: rgba(239,68,68,0.15); color: #ef4444;">{{ ucfirst($txn->status) }}</span>@endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="border: none; text-align: center; padding: 40px; color: var(--text-muted);">No transactions found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $transactions->links() }}
</div>
@endsection
