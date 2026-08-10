@extends('layouts.dashboard')

@section('page-title', 'Transaction History')

@section('content')
<div class="fade-in">

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0; font-size: 22px;">
                <i class="fas fa-history" style="color: var(--purple-3);"></i> Transaction History
            </h2>
            <p style="color: var(--text-muted); margin: 4px 0 0; font-size: 14px;">Complete record of all your financial activities</p>
        </div>
        <a href="{{ route('dashboard.wallet.index') }}" class="btn-outline-custom" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fas fa-arrow-left"></i> Back to Wallets
        </a>
    </div>

    <!-- Filter bar -->
    <div class="card-custom mb-3">
        <form method="GET" action="{{ route('dashboard.wallet.history') }}" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 140px;">
                <label style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px; display: block;">Type</label>
                <select name="type" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px; font-size: 13px;">
                    @foreach($types as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 120px;">
                <label style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px; display: block;">Direction</label>
                <select name="direction" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px; font-size: 13px;">
                    <option value="all" @selected(request('direction') === 'all')>All</option>
                    <option value="credit" @selected(request('direction') === 'credit')>Credit (In)</option>
                    <option value="debit" @selected(request('direction') === 'debit')>Debit (Out)</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 130px;">
                <label style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px; display: block;">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px; font-size: 13px;">
            </div>
            <div style="flex: 1; min-width: 130px;">
                <label style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px; display: block;">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px; font-size: 13px;">
            </div>
            <button type="submit" class="btn-gradient" style="padding: 8px 20px; font-size: 13px;">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('dashboard.wallet.history') }}" class="btn-outline-custom" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">
                <i class="fas fa-times"></i> Clear
            </a>
        </form>
    </div>

    <!-- Transactions table -->
    <div class="card-custom">
        @if($transactions->count() > 0)
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th>Direction</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: {{ $tx->direction === 'credit' ? 'var(--green-bg)' : 'var(--red-bg)' }}; color: {{ $tx->direction === 'credit' ? 'var(--green)' : 'var(--red)' }}; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                    <i class="fas fa-{{ $tx->direction === 'credit' ? 'arrow-down' : 'arrow-up' }}"></i>
                                </div>
                                <span style="font-size: 13px; color: var(--text-bright); text-transform: capitalize; font-weight: 500;">{{ str_replace('_', ' ', $tx->type) }}</span>
                            </div>
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted); font-family: monospace;">{{ $tx->reference }}</td>
                        <td style="font-size: 12px; color: var(--text-muted); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $tx->description ?? '—' }}</td>
                        <td>
                            <span class="badge-custom {{ $tx->direction === 'credit' ? 'badge-up' : 'badge-down' }}">{{ $tx->direction }}</span>
                        </td>
                        <td style="font-weight: 700; color: {{ $tx->direction === 'credit' ? 'var(--green)' : 'var(--red)' }}">
                            {{ $tx->direction === 'credit' ? '+' : '-' }}${{ number_format($tx->amount, 2) }}
                        </td>
                        <td style="font-size: 13px; color: var(--text-bright);">${{ $tx->balance_after ? number_format($tx->balance_after, 2) : '—' }}</td>
                        <td>
                            <span class="badge-custom {{ $tx->status === 'completed' ? 'badge-up' : ($tx->status === 'pending' ? 'badge-pending' : 'badge-down') }}">{{ $tx->status }}</span>
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 13px; color: var(--text-muted);">
                Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }} transactions
            </div>
            {{ $transactions->links() }}
        </div>
        @else
        <div style="text-align: center; padding: 60px 0; color: var(--text-dim);">
            <i class="fas fa-receipt" style="font-size: 48px; color: var(--border); margin-bottom: 16px;"></i>
            <h4 style="color: var(--text-muted);">No transactions found</h4>
            <p style="font-size: 14px;">Try adjusting your filters or check back later.</p>
        </div>
        @endif
    </div>
</div>
@endsection
