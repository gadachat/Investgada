@extends('layouts.admin')

@section('title', 'Fund Management')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h2 class="mb-1" style="font-weight:700;color:var(--text)">
                    <i class="fas fa-hand-holding-usd me-2" style="color:var(--primary)"></i> Fund Applications
                </h2>
                <p style="color:var(--text-muted);font-size:14px">Review and manage marketer/leader fund requests</p>
            </div>
            <a href="{{ route('admin.funds.settings') }}" class="btn btn-outline-primary">
                <i class="fas fa-cog me-1"></i> Settings
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b"><i class="fas fa-clock"></i></div>
                    <div>
                        <p class="stat-label">Pending</p>
                        <h3 class="stat-value">{{ $stats['pending'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(59,130,246,0.1);color:#3b82f6"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <p class="stat-label">Active</p>
                        <h3 class="stat-value">{{ $stats['approved'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(16,185,129,0.1);color:#10b981"><i class="fas fa-trophy"></i></div>
                    <div>
                        <p class="stat-label">Completed</p>
                        <h3 class="stat-value">{{ $stats['completed'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(168,85,247,0.1);color:#a855f7"><i class="fas fa-dollar-sign"></i></div>
                    <div>
                        <p class="stat-label">Total Funded</p>
                        <h3 class="stat-value" style="font-size:20px">${{ number_format($stats['total_funded'], 0) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" class="d-flex flex-wrap gap-2 mb-3">
            <select name="status" class="form-select" style="width:auto;background:var(--bg);border:1px solid var(--border);color:var(--text)">
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Active</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
            </select>
            <select name="type" class="form-select" style="width:auto;background:var(--bg);border:1px solid var(--border);color:var(--text)">
                <option value="all" {{ request('type') === 'all' ? 'selected' : '' }}>All Types</option>
                <option value="marketer" {{ request('type') === 'marketer' ? 'selected' : '' }}>Marketer</option>
                <option value="leader" {{ request('type') === 'leader' ? 'selected' : '' }}>Leader</option>
            </select>
            <input type="text" name="search" class="form-control" style="width:auto;flex:1;min-width:200px;background:var(--bg);border:1px solid var(--border);color:var(--text)"
                   placeholder="Search by name or reference..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        </form>

        {{-- Table --}}
        <div class="card-custom">
            @if($applications->count() > 0)
            <div style="overflow-x:auto">
                <table class="table table-hover mb-0" style="color:var(--text)">
                    <thead>
                        <tr style="color:var(--text-muted);font-size:12px;text-transform:uppercase">
                            <th>Reference</th>
                            <th>Applicant</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $app)
                        <tr>
                            <td style="font-weight:600;font-size:13px">{{ $app->reference }}</td>
                            <td style="font-size:13px">
                                <div>{{ $app->user->name }}</div>
                                <small style="color:var(--text-muted)">{{ $app->user->email }}</small>
                            </td>
                            <td>
                                <span class="badge" style="background:rgba(99,102,241,0.15);color:#a855f7">{{ ucfirst($app->applicant_type) }}</span>
                            </td>
                            <td style="font-weight:600;font-size:13px">
                                {{ $app->approved_amount ? '$' . number_format($app->approved_amount, 2) : '$' . number_format($app->requested_amount, 2) }}
                            </td>
                            <td style="font-size:13px">
                                @if(in_array($app->status, ['approved', 'completed']))
                                    <div style="display:flex;align-items:center;gap:6px">
                                        <div style="background:rgba(99,102,241,0.1);border-radius:6px;height:6px;width:60px;overflow:hidden">
                                            <div style="background:linear-gradient(90deg,#6366f1,#a855f7);height:100%;width:{{ $app->progressPercent() }}%;border-radius:6px"></div>
                                        </div>
                                        <span style="font-size:11px">{{ $app->progressPercent() }}%</span>
                                    </div>
                                @else
                                    <span style="color:var(--text-muted)">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $colors = [
                                        'pending'   => ['bg' => 'rgba(245,158,11,0.15)', 'color' => '#f59e0b', 'icon' => 'clock'],
                                        'approved'  => ['bg' => 'rgba(59,130,246,0.15)', 'color' => '#3b82f6', 'icon' => 'check-circle'],
                                        'completed' => ['bg' => 'rgba(16,185,129,0.15)', 'color' => '#10b981', 'icon' => 'trophy'],
                                        'rejected'  => ['bg' => 'rgba(239,68,68,0.15)', 'color' => '#ef4444', 'icon' => 'times-circle'],
                                        'revoked'   => ['bg' => 'rgba(239,68,68,0.15)', 'color' => '#ef4444', 'icon' => 'ban'],
                                    ];
                                    $s = $colors[$app->status] ?? $colors['pending'];
                                @endphp
                                <span class="badge" style="background:{{ $s['bg'] }};color:{{ $s['color'] }}">
                                    <i class="fas fa-{{ $s['icon'] }} me-1"></i>{{ ucfirst($app->status) }}
                                </span>
                            </td>
                            <td style="font-size:13px;color:var(--text-muted)">{{ $app->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.funds.show', $app) }}" class="btn btn-sm" style="background:rgba(99,102,241,0.1);color:var(--primary);font-size:12px">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-folder-open" style="font-size:48px;color:var(--text-muted);opacity:0.3"></i>
                <p style="color:var(--text-muted);margin-top:12px">No fund applications found</p>
            </div>
            @endif
        </div>

        {{ $applications->links() }}
    </div>
</div>
@endsection
