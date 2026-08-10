@extends('layouts.dashboard')

@section('page-title', 'Deposit History')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px; font-size: 22px;">
                <i class="fas fa-arrow-down" style="color: #10b981;"></i> Deposit History
            </h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Track all your deposit transactions</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('dashboard.history.deposits.export', array_merge(request()->all(), ['format' => 'excel'])) }}" class="btn" style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 10px; padding: 8px 16px; font-size: 13px; font-weight: 600; text-decoration: none;">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('dashboard.history.deposits.export', array_merge(request()->all(), ['format' => 'pdf'])) }}" target="_blank" class="btn" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; border-radius: 10px; padding: 8px 16px; font-size: 13px; font-weight: 600; text-decoration: none;">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-arrow-down"></i></div>
                <div class="stat-label">Total Deposits</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-label">Completed</div>
                <div class="stat-value">{{ $stats['completed'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-clock"></i></div>
                <div class="stat-label">Pending</div>
                <div class="stat-value">{{ $stats['pending'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-label">Total Deposited</div>
                <div class="stat-value" style="font-size: 16px;">${{ number_format($stats['total_amount'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card-custom mb-4" style="padding: 16px 20px;">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted);">Status</label>
                <select name="status" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
                    <option value="all">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted);">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
            </div>
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted);">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn w-100" style="background: var(--gradient-primary); color: white; border: none; border-radius: 10px; padding: 10px;"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card-custom" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="table mb-0" style="color: var(--text); font-size: 13px;">
                <thead>
                    <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim); background: rgba(30,35,55,0.5);">
                        <th style="padding: 12px 16px;">Date</th>
                        <th style="padding: 12px 16px;">Reference</th>
                        <th style="padding: 12px 16px;">Method</th>
                        <th style="padding: 12px 16px;">Amount</th>
                        <th style="padding: 12px 16px;">Status</th>
                        <th style="padding: 12px 16px;">TX Hash</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deposits as $deposit)
                    @php $statusColors = ['pending' => '#f59e0b', 'completed' => '#10b981', 'failed' => '#ef4444']; @endphp
                    <tr style="border-bottom: 1px solid rgba(51,65,85,0.15);">
                        <td style="padding: 12px 16px; color: var(--text-dim); font-size: 12px;">{{ $deposit->created_at->format('M d, Y H:i') }}</td>
                        <td style="padding: 12px 16px; font-family: monospace; font-size: 12px; color: #818cf8;">{{ $deposit->reference }}</td>
                        <td style="padding: 12px 16px;">{{ ucfirst($deposit->payment_method ?? '—') }}</td>
                        <td style="padding: 12px 16px; font-weight: 600; color: #10b981;">${{ number_format((float) $deposit->amount, 2) }}</td>
                        <td style="padding: 12px 16px;">
                            <span style="font-size: 11px; padding: 2px 10px; border-radius: 20px; background: {{ $statusColors[$deposit->status] ?? '#64748b' }}20; color: {{ $statusColors[$deposit->status] ?? '#64748b' }}; font-weight: 600; text-transform: capitalize;">{{ $deposit->status }}</span>
                            @if(in_array($deposit->status, ['confirmed', 'completed']))
                            <a href="{{ route('dashboard.invoice.deposit', $deposit) }}" target="_blank" style="font-size: 11px; color: var(--purple-3); margin-left: 6px;"><i class="fas fa-download"></i></a>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; font-family: monospace; font-size: 11px; color: var(--text-dim);">{{ substr($deposit->tx_hash ?? '—', 0, 20) }}{{ (strlen($deposit->tx_hash ?? '') > 20) ? '...' : '' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align: center; padding: 40px; color: var(--text-dim);">No deposits found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $deposits->links() }}
</div>
@endsection
