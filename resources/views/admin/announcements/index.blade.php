@extends('layouts.admin')

@section('page-title', 'Announcements & Broadcasts')

@section('content')
<div class="fade-in">
    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-bullhorn"></i></div>
                <div class="stat-label">Total Announcements</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-label">Active Now</div>
                <div class="stat-value">{{ $stats['active'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-clock"></i></div>
                <div class="stat-label">Scheduled</div>
                <div class="stat-value">{{ $stats['scheduled'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-calendar-times"></i></div>
                <div class="stat-label">Expired</div>
                <div class="stat-value">{{ $stats['expired'] }}</div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight: 700;"><i class="fas fa-bullhorn" style="color: var(--purple-3);"></i> System Announcements</h4>
        <a href="{{ route('admin.announcements.create') }}" class="btn btn-gradient">
            <i class="fas fa-plus"></i> New Announcement
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 10px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <!-- Announcements Table -->
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Target Audience</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th>Dismissible</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                    <tr>
                        <td style="font-weight: 600; color: var(--text-bright); max-width: 250px;">
                            {{ $announcement->title }}
                        </td>
                        <td>
                            @php
                                $typeBadges = [
                                    'info' => 'badge-info',
                                    'success' => 'badge-up',
                                    'warning' => 'badge-pending',
                                    'danger' => 'badge-down',
                                    'maintenance' => 'badge-purple',
                                ];
                            @endphp
                            <span class="badge-custom {{ $typeBadges[$announcement->type] ?? 'badge-info' }}" style="text-transform: uppercase;">
                                {{ $announcement->type }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-custom badge-purple">
                                @if($announcement->target === 'all')
                                    All Users
                                @elseif($announcement->target === 'verified')
                                    Verified Users
                                @elseif($announcement->target === 'investors')
                                    Investors
                                @elseif($announcement->target === 'traders')
                                    Traders
                                @elseif($announcement->target === 'specific')
                                    Specific User ({{ $announcement->targetUser->name ?? 'ID: '.$announcement->target_user_id }})
                                @endif
                            </span>
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);">
                            @if($announcement->starts_at || $announcement->ends_at)
                                <div><i class="fas fa-play" style="font-size: 10px;"></i> {{ $announcement->starts_at ? $announcement->starts_at->format('M d, Y H:i') : 'Immediate' }}</div>
                                <div><i class="fas fa-stop" style="font-size: 10px;"></i> {{ $announcement->ends_at ? $announcement->ends_at->format('M d, Y H:i') : 'No end date' }}</div>
                            @else
                                <span style="color: var(--text-dim);">Always Active</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.announcements.toggle', $announcement) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm border-0 p-0" title="Click to toggle status">
                                    @if($announcement->is_active)
                                        <span class="badge-custom badge-up"><i class="fas fa-circle" style="font-size: 6px;"></i> Active</span>
                                    @else
                                        <span class="badge-custom" style="background: rgba(100,116,139,0.2); color: #94a3b8;">Inactive</span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td>
                            @if($announcement->is_dismissible)
                                <span class="badge-custom badge-up">Yes</span>
                            @else
                                <span class="badge-custom badge-down">No</span>
                            @endif
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);">
                            {{ $announcement->creator->name ?? 'Admin' }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm" style="background: rgba(99,102,241,0.15); color: var(--purple-3); border: 1px solid rgba(99,102,241,0.3); font-size: 12px;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this announcement?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); font-size: 12px;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4" style="color: var(--text-dim);">
                            <i class="fas fa-bullhorn mb-2" style="font-size: 24px; opacity: 0.5;"></i>
                            <p class="m-0">No announcements found. Click "New Announcement" to create one.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $announcements->links() }}
        </div>
    </div>
</div>
@endsection
