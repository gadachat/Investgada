@extends('layouts.admin')
@section('title', 'Master Traders')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight:700;"><i class="fas fa-user-tie me-2"></i> Master Traders</h4>
        <a href="{{ route('admin.master-traders.create') }}" class="btn btn-gradient btn-sm"><i class="fas fa-plus"></i> Add Master Trader</a>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="border-radius:10px; font-size:13px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger" style="border-radius:10px; font-size:13px;">{{ session('error') }}</div>
    @endif

    {{-- Stats summary --}}
    <div class="row mb-3">
        <div class="col-md-3 mb-2">
            <div class="card-custom p-3 text-center">
                <div style="font-size:11px; color:var(--text-muted);">Total Master Traders</div>
                <div style="font-size:24px; font-weight:700; color:var(--purple-1);">{{ $totalMasters }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card-custom p-3 text-center">
                <div style="font-size:11px; color:var(--text-muted);">Active</div>
                <div style="font-size:24px; font-weight:700; color:#059669;">{{ $activeMasters }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card-custom p-3 text-center">
                <div style="font-size:11px; color:var(--text-muted);">Total Followers</div>
                <div style="font-size:24px; font-weight:700; color:#6366f1;">{{ $totalFollowers }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card-custom p-3 text-center">
                <div style="font-size:11px; color:var(--text-muted);">Avg Win Rate</div>
                <div style="font-size:24px; font-weight:700; color:#7c3aed;">{{ $avgWinRate }}%</div>
            </div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="card-custom mb-3 p-3">
        <form method="GET" action="{{ route('admin.master-traders.index') }}" class="d-flex gap-2 flex-wrap">
            <input type="text" name="search" class="form-control" style="flex:1; min-width:200px;" placeholder="Search by name, email, or title..." value="{{ request('search') }}">
            <select name="status" class="form-control" style="width:auto;">
                <option value="">All Status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <select name="strategy" class="form-control" style="width:auto;">
                <option value="">All Strategies</option>
                @foreach(['Scalper','Day Trader','Swing Trader','Position Trader','Crypto Specialist','Forex Expert','Stocks Analyst','Mixed Strategy'] as $type)
                <option value="{{ $type }}" @selected(request('strategy') === $type)>{{ $type }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-gradient btn-sm"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('admin.master-traders.index') }}" class="btn btn-sm" style="background:var(--bg-card); border:1px solid var(--border);">Clear</a>
        </form>
    </div>

    <div class="card-custom" style="overflow-x:auto;">
        <table class="table table-custom mb-0" style="font-size:13px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);">
                    <th>Trader</th>
                    <th>Photo</th>
                    <th>Title</th>
                    <th>Win Rate</th>
                    <th>Daily %</th>
                    <th>Trades/Day</th>
                    <th>Followers</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($masters as $master)
                <tr>
                    <td>
                        <div style="font-weight:600;">{{ $master->user->name }}</div>
                        <div style="font-size:11px; color:var(--text-muted);">{{ $master->user->email }}</div>
                    </td>
                    <td>
                        @if($master->avatar)
                            <img src="{{ asset('storage/' . $master->avatar) }}" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                        @else
                            <div style="width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#6366f1,#7c3aed); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:14px;">
                                {{ strtoupper(substr($master->user->name, 0, 1)) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        {{ $master->title }}
                        @if($master->strategy_type)
                        <div style="font-size:11px; color:var(--text-muted);">{{ $master->strategy_type }}</div>
                        @endif
                    </td>
                    <td>
                        @if($master->use_manual_win_rate)
                            <span style="font-weight:600; color:#7c3aed;" title="Manual override">
                                {{ number_format($master->manual_win_rate, 1) }}% <i class="fas fa-pen" style="font-size:9px;"></i>
                            </span>
                        @else
                            <span style="font-weight:600; color:{{ $master->win_rate >= 60 ? '#059669' : ($master->win_rate >= 40 ? '#d97706' : '#dc2626') }};" title="Auto-calculated">
                                {{ number_format($master->win_rate, 1) }}%
                            </span>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight:600; color:#059669;">+{{ number_format($master->daily_profit_pct, 2) }}%</span>
                        <span style="font-size:10px; color:var(--text-muted);"> / -{{ number_format($master->loss_rate_pct, 1) }}%</span>
                    </td>
                    <td>{{ $master->trades_per_day }}</td>
                    <td>
                        <span style="font-weight:600;">{{ $master->followers_count }}</span>
                        @if($master->max_followers > 0)
                        <span style="font-size:11px; color:var(--text-muted);">/ {{ $master->max_followers }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-custom" style="background:{{ $master->is_active ? '#d1fae5' : '#fee2e2' }}; color:{{ $master->is_active ? '#059669' : '#dc2626' }};">
                            {{ $master->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('admin.master-traders.edit', $master) }}" class="btn btn-sm" style="background:#e0e7ff; color:#6366f1; border:none; padding:4px 10px; font-size:12px;" title="Edit">
                            <i class="fas fa-pen"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.master-traders.toggle', $master) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm" style="background:var(--bg-card); border:1px solid var(--border); padding:4px 10px; font-size:12px;" title="Toggle Active">{{ $master->is_active ? '⏸' : '▶' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.master-traders.update-stats', $master) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background:#e0e7ff; color:#6366f1; border:none; padding:4px 10px; font-size:12px;" title="Sync Stats from Trade History"><i class="fas fa-sync"></i></button>
                        </form>
                        <form method="POST" action="{{ route('admin.master-traders.destroy', $master) }}" style="display:inline;" onsubmit="return confirm('Remove this master trader? Active subscriptions will be cancelled.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm" style="background:#fee2e2; color:#dc2626; border:none; padding:4px 10px; font-size:12px;" title="Remove"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center; padding:40px; color:var(--text-muted);">
                    @if(request('search') || request('status') || request('strategy'))
                        No master traders match your filters. <a href="{{ route('admin.master-traders.index') }}">Clear filters</a>
                    @else
                        No master traders yet. Click "Add Master Trader" to designate one.
                    @endif
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $masters->links() }}
</div>
@endsection
