@extends('layouts.admin')

@section('page-title', 'User Management')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px; font-size: 22px;">
                <i class="fas fa-users" style="color: #6366f1;"></i> User Management
            </h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">View and manage all user accounts, send test funds, and control access.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-users"></i></div>
                <div class="stat-label">Total Users</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
                <div class="stat-label">Active</div>
                <div class="stat-value">{{ $stats['active'] }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-pause"></i></div>
                <div class="stat-label">Suspended</div>
                <div class="stat-value">{{ $stats['suspended'] }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-ban"></i></div>
                <div class="stat-label">Banned</div>
                <div class="stat-value">{{ $stats['banned'] }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-id-card"></i></div>
                <div class="stat-label">KYC Verified</div>
                <div class="stat-value">{{ $stats['verified'] }}</div>
            </div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="card-custom mb-4" style="padding: 16px 20px;">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label style="font-size: 11px; color: var(--text-muted);">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;" placeholder="Name, email, username, or referral code...">
            </div>
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted);">Status</label>
                <select name="status" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
                </select>
            </div>
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted);">Role</label>
                <select name="role" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
                    <option value="">All Roles</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn w-100" style="background: var(--gradient-primary); color: white; border: none; border-radius: 10px; padding: 10px;"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>

    <!-- User Table -->
    <div class="card-custom" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="table mb-0" style="color: var(--text); font-size: 13px;">
                <thead>
                    <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim); background: rgba(30,35,55,0.5);">
                        <th style="padding: 12px 16px;">User</th>
                        <th style="padding: 12px 16px;">Status</th>
                        <th style="padding: 12px 16px;">Role</th>
                        <th style="padding: 12px 16px;">Invested</th>
                        <th style="padding: 12px 16px;">Total Balance</th>
                        <th style="padding: 12px 16px;">Rank</th>
                        <th style="padding: 12px 16px;">Joined</th>
                        <th style="padding: 12px 16px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    @php
                        $totalBalance = $user->wallets->sum(function($w) { return (float) $w->balance; });
                        $statusColors = ['active' => '#10b981', 'inactive' => '#64748b', 'suspended' => '#f59e0b', 'banned' => '#ef4444'];
                        $roleColors = ['user' => '#818cf8', 'admin' => '#f59e0b', 'super_admin' => '#ef4444'];
                    @endphp
                    <tr style="border-bottom: 1px solid rgba(51,65,85,0.15);">
                        <td style="padding: 12px 16px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 12px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="color: var(--text-bright); font-size: 13px; font-weight: 600;">{{ $user->name }}</div>
                                    <div style="color: var(--text-dim); font-size: 11px;">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span style="font-size: 11px; padding: 2px 10px; border-radius: 20px; background: {{ $statusColors[$user->status] ?? '#64748b' }}20; color: {{ $statusColors[$user->status] ?? '#64748b' }}; font-weight: 600; text-transform: capitalize;">{{ $user->status }}</span>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span style="font-size: 11px; padding: 2px 10px; border-radius: 20px; background: {{ $roleColors[$user->role] ?? '#818cf8' }}20; color: {{ $roleColors[$user->role] ?? '#818cf8' }}; font-weight: 600; text-transform: capitalize;">{{ str_replace('_', ' ', $user->role) }}</span>
                        </td>
                        <td style="padding: 12px 16px; color: var(--text);">${{ number_format((float) $user->total_invested, 2) }}</td>
                        <td style="padding: 12px 16px; color: #10b981; font-weight: 600;">${{ number_format($totalBalance, 2) }}</td>
                        <td style="padding: 12px 16px;">
                            @if($user->rank)
                                <span style="font-size: 11px; padding: 2px 8px; border-radius: 6px; background: {{ $user->rank->badge_color }}20; color: {{ $user->rank->badge_color }};">{{ $user->rank->name }}</span>
                            @else
                                <span style="font-size: 11px; color: var(--text-dim);">—</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; color: var(--text-dim); font-size: 12px;">{{ $user->created_at->format('M d, Y') }}</td>
                        <td style="padding: 12px 16px; text-align: right;">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm" style="background: var(--gradient-primary); color: white; border: none; border-radius: 8px; padding: 5px 12px; font-size: 11px; text-decoration: none;">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align: center; padding: 40px; color: var(--text-dim);">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $users->links() }}
</div>
@endsection
