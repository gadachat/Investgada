@extends('layouts.admin')

@section('title', 'Trading Management')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h2 class="mb-1" style="font-weight:700;color:var(--text)">
                    <i class="fas fa-chart-line me-2" style="color:var(--primary)"></i> Trading Management
                </h2>
                <p style="color:var(--text-muted);font-size:14px">Monitor all user trading positions</p>
            </div>
            <a href="{{ route('admin.trading.settings') }}" class="btn btn-outline-primary">
                <i class="fas fa-cog me-1"></i> Settings
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-md-2 col-6">
                <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                    <p style="font-size:11px;color:var(--text-muted);margin:0">Open</p>
                    <h4 style="font-weight:700;color:#3b82f6;margin:0">{{ $stats['open'] }}</h4>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                    <p style="font-size:11px;color:var(--text-muted);margin:0">Closed</p>
                    <h4 style="font-weight:700;color:var(--text);margin:0">{{ $stats['closed'] }}</h4>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                    <p style="font-size:11px;color:var(--text-muted);margin:0">Wins</p>
                    <h4 style="font-weight:700;color:#10b981;margin:0">{{ $stats['wins'] }}</h4>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                    <p style="font-size:11px;color:var(--text-muted);margin:0">Losses</p>
                    <h4 style="font-weight:700;color:#ef4444;margin:0">{{ $stats['losses'] }}</h4>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                    <p style="font-size:11px;color:var(--text-muted);margin:0">Volume</p>
                    <h4 style="font-weight:700;color:var(--primary);margin:0;font-size:18px">${{ number_format($stats['total_volume'], 0) }}</h4>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                    <p style="font-size:11px;color:var(--text-muted);margin:0">Total P&L</p>
                    <h4 style="font-weight:700;color:{{ $stats['total_pnl'] >= 0 ? '#10b981' : '#ef4444' }};margin:0;font-size:18px">
                        {{ $stats['total_pnl'] >= 0 ? '+' : '' }}${{ number_format(abs($stats['total_pnl']), 0) }}
                    </h4>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" class="d-flex flex-wrap gap-2 mb-3">
            <select name="status" class="form-select" style="width:auto;background:var(--bg);border:1px solid var(--border);color:var(--text)">
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                <option value="tp_hit" {{ request('status') === 'tp_hit' ? 'selected' : '' }}>TP Hit</option>
                <option value="sl_hit" {{ request('status') === 'sl_hit' ? 'selected' : '' }}>SL Hit</option>
                <option value="liquidated" {{ request('status') === 'liquidated' ? 'selected' : '' }}>Liquidated</option>
            </select>
            <input type="text" name="search" class="form-control" style="width:auto;flex:1;min-width:200px;background:var(--bg);border:1px solid var(--border);color:var(--text)"
                   placeholder="Search by user, reference, or symbol..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        </form>

        {{-- Table --}}
        <div class="card-custom">
            @if($positions->count() > 0)
            <div style="overflow-x:auto">
                <table class="table table-hover mb-0" style="color:var(--text)">
                    <thead>
                        <tr style="color:var(--text-muted);font-size:12px;text-transform:uppercase">
                            <th>Reference</th>
                            <th>User</th>
                            <th>Symbol</th>
                            <th>Dir</th>
                            <th>Entry</th>
                            <th>Lev</th>
                            <th>Margin</th>
                            <th>P&L</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($positions as $pos)
                        <tr>
                            <td style="font-size:12px;font-weight:600">{{ $pos->reference }}</td>
                            <td style="font-size:13px">{{ $pos->user->name }}</td>
                            <td style="font-size:13px;font-weight:600">{{ $pos->symbol }}</td>
                            <td style="font-size:12px">
                                @if($pos->direction === 'buy')<span style="color:#10b981">Long</span>@else<span style="color:#ef4444">Short</span>@endif
                            </td>
                            <td style="font-size:13px">{{ number_format((float)$pos->entry_price, (float)$pos->entry_price < 1 ? 4 : 2) }}</td>
                            <td style="font-size:13px">{{ $pos->leverage }}x</td>
                            <td style="font-size:13px">${{ number_format((float)$pos->amount, 2) }}</td>
                            <td style="font-size:13px;font-weight:600">
                                @php
                                    $pnl = $pos->status === 'open' ? (float)$pos->pnl : (float)$pos->close_pnl;
                                @endphp
                                <span style="color:{{ $pnl >= 0 ? '#10b981' : '#ef4444' }}">{{ $pnl >= 0 ? '+' : '' }}${{ number_format(abs($pnl), 2) }}</span>
                            </td>
                            <td>
                                @php
                                    $sc = [
                                        'open'       => ['bg' => 'rgba(59,130,246,0.15)', 'color' => '#3b82f6'],
                                        'closed'     => ['bg' => 'rgba(99,102,241,0.15)', 'color' => '#6366f1'],
                                        'tp_hit'     => ['bg' => 'rgba(16,185,129,0.15)', 'color' => '#10b981'],
                                        'sl_hit'     => ['bg' => 'rgba(239,68,68,0.15)', 'color' => '#ef4444'],
                                        'liquidated' => ['bg' => 'rgba(239,68,68,0.2)', 'color' => '#ef4444'],
                                    ][$pos->status] ?? ['bg' => 'rgba(99,102,241,0.15)', 'color' => '#6366f1'];
                                @endphp
                                <span class="badge" style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};font-size:11px">
                                    {{ strtoupper(str_replace('_', ' ', $pos->status)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.trading.show', $pos) }}" class="btn btn-sm" style="background:rgba(99,102,241,0.1);color:var(--primary);font-size:11px">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($pos->status === 'open')
                                <button type="button" class="btn btn-sm" style="background:rgba(239,68,68,0.1);color:#ef4444;font-size:11px" onclick="forceClose('{{ $pos->id }}', '{{ $pos->reference }}')">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-folder-open" style="font-size:48px;color:var(--text-muted);opacity:0.3"></i>
                <p style="color:var(--text-muted);margin-top:12px">No trading positions found</p>
            </div>
            @endif
        </div>

        {{ $positions->links() }}
    </div>
</div>

{{-- Force Close Modal --}}
<div class="modal fade" id="forceCloseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:var(--bg);border:1px solid var(--border)">
            <div class="modal-header" style="border-bottom:1px solid var(--border)">
                <h5 class="modal-title" style="color:var(--text);font-weight:600">Force Close Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="forceCloseForm" action="">
                @csrf
                <div class="modal-body">
                    <p style="font-size:13px;color:var(--text-muted)">Reference: <span id="fcRef" style="font-weight:600;color:var(--text)"></span></p>
                    <div class="mb-2">
                        <label style="font-size:13px;color:var(--text-muted)">Reason</label>
                        <textarea name="reason" rows="3" class="form-control" required style="background:var(--bg);border:1px solid var(--border);color:var(--text)" placeholder="Reason for force close..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border)">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Force Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function forceClose(id, ref) {
    document.getElementById('fcRef').textContent = ref;
    document.getElementById('forceCloseForm').action = `{{ route('admin.trading.force-close', 'PLACEHOLDER') }}`.replace('PLACEHOLDER', id);
    new bootstrap.Modal(document.getElementById('forceCloseModal')).show();
}
</script>
@endsection
