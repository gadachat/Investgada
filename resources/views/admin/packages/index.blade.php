@extends('layouts.admin')

@section('page-title', 'Investment Packages')

@section('content')
<div class="fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="color: var(--text-bright); font-weight: 700; margin: 0; font-size: 22px;"><i class="fas fa-chart-pie" style="color: var(--purple-3);"></i> Investment Packages</h2>
        <a href="{{ route('admin.packages.create') }}" class="btn-gradient" style="text-decoration: none;"><i class="fas fa-plus"></i> New Package</a>
    </div>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>Name</th><th>Category</th><th>Return Rate</th><th>Min/Max</th><th>Duration</th><th>Cycle</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @foreach($packages as $pkg)
                <tr>
                    <td style="font-weight: 600; color: var(--text-bright);">{{ $pkg->name }}@if($pkg->featured) <span class="badge-custom badge-purple"><i class="fas fa-star"></i></span>@endif</td>
                    <td><span class="badge-custom badge-purple">{{ strtoupper($pkg->category) }}</span></td>
                    <td style="color: var(--green); font-weight: 600;">{{ $pkg->return_rate }}% / {{ $pkg->return_type }}</td>
                    <td>${{ number_format($pkg->min_amount) }} - {{ $pkg->max_amount ? '$' . number_format($pkg->max_amount) : '∞' }}</td>
                    <td>{{ $pkg->duration_days }}d</td>
                    <td>{{ $pkg->cycle_days }}d</td>
                    <td>
                        @if($pkg->is_active)<span class="badge-custom badge-up">Active</span>@else<span class="badge-custom badge-down">Inactive</span>@endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 4px;">
                            <a href="{{ route('admin.packages.edit', $pkg) }}" class="btn-outline-custom" style="padding: 4px 10px; font-size: 11px; text-decoration: none;"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('admin.packages.toggle', $pkg) }}" style="display: inline;">@csrf
                                <button type="submit" class="btn-outline-custom" style="padding: 4px 10px; font-size: 11px;"><i class="fas fa-toggle-{{ $pkg->is_active ? 'on' : 'off' }}"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.packages.destroy', $pkg) }}" style="display: inline;">@csrf @method('DELETE')
                                <button type="submit" class="btn-outline-custom" style="padding: 4px 10px; font-size: 11px; color: var(--red); border-color: rgba(239,68,68,0.3);" onclick="return confirm('Delete this package?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 16px; border-top: 1px solid var(--border); display: flex; justify-content: center;">{{ $packages->links() }}</div>
    </div>
</div>
@endsection
