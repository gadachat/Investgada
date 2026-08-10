@extends('layouts.admin')

@section('page-title', 'Support Ticket Inbox')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px; font-size: 22px;">
                <i class="fas fa-headset" style="color: #6366f1;"></i> Support Inbox
            </h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Manage and respond to user support tickets.</p>
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
                <div class="stat-icon blue"><i class="fas fa-inbox"></i></div>
                <div class="stat-label">Open / Pending</div>
                <div class="stat-value">{{ $stats['open'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-reply"></i></div>
                <div class="stat-label">Answered</div>
                <div class="stat-value">{{ $stats['answered'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-exclamation"></i></div>
                <div class="stat-label">Urgent</div>
                <div class="stat-value" style="color: {{ $stats['urgent'] > 0 ? '#ef4444' : '#fff' }};">{{ $stats['urgent'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-check"></i></div>
                <div class="stat-label">Closed</div>
                <div class="stat-value">{{ $stats['closed'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245,158,11,0.15); color: #f59e0b;"><i class="fas fa-star"></i></div>
                <div class="stat-label">Avg Rating</div>
                <div class="stat-value">{{ isset($stats['avg_rating']) && $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : '—' }}</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card-custom mb-4" style="padding: 16px 20px;">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted);">Status</label>
                <select name="status" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
                    <option value="">All</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Answered</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted);">Priority</label>
                <select name="priority" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
                    <option value="">All</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>
            <div class="col-md-3">
                <label style="font-size: 11px; color: var(--text-muted);">Category</label>
                <select name="category" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
                    <option value="">All</option>
                    @foreach(['general' => 'General', 'deposit' => 'Deposit', 'withdrawal' => 'Withdrawal', 'investment' => 'Investment', 'account' => 'Account', 'technical' => 'Technical', 'referral' => 'Referral'] as $key => $label)
                    <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label style="font-size: 11px; color: var(--text-muted);">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;" placeholder="Ticket # or user...">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn w-100" style="background: linear-gradient(135deg, #6366f1, #7c3aed); color: white; border: none; border-radius: 10px; padding: 10px;">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="card-custom" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="table mb-0" style="color: var(--text);">
                <thead>
                    <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim); background: rgba(30,35,55,0.5);">
                        <th style="padding: 14px 16px;">Ticket #</th>
                        <th style="padding: 14px 16px;">User</th>
                        <th style="padding: 14px 16px;">Subject</th>
                        <th style="padding: 14px 16px;">Category</th>
                        <th style="padding: 14px 16px;">Priority</th>
                        <th style="padding: 14px 16px;">Status</th>
                        <th style="padding: 14px 16px;">Assigned</th>
                        <th style="padding: 14px 16px;">Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr style="font-size: 13px; border-bottom: 1px solid rgba(51,65,85,0.2); cursor: pointer;" onclick="window.location='{{ route('admin.support.show', $ticket) }}'">
                        <td style="padding: 14px 16px;"><code style="color: #818cf8; font-weight: 600;">{{ $ticket->ticket_number }}</code></td>
                        <td style="padding: 14px 16px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 28px; height: 28px; border-radius: 8px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 11px;">
                                    {{ strtoupper(substr($ticket->user?->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="color: var(--text-bright); font-size: 13px;">{{ $ticket->user?->name ?? 'Unknown' }}</div>
                                    <div style="color: var(--text-dim); font-size: 11px;">{{ $ticket->user?->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 14px 16px; color: var(--text); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $ticket->subject }}</td>
                        <td style="padding: 14px 16px;">
                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 6px; background: rgba(99,102,241,0.1); color: #818cf8; text-transform: capitalize;">{{ $ticket->category }}</span>
                        </td>
                        <td style="padding: 14px 16px;">
                            @php $pColors = ['low' => '#64748b', 'medium' => '#3b82f6', 'high' => '#f59e0b', 'urgent' => '#ef4444']; @endphp
                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 6px; background: {{ $pColors[$ticket->priority] ?? '#64748b' }}20; color: {{ $pColors[$ticket->priority] ?? '#64748b' }}; text-transform: capitalize; font-weight: 600;">{{ $ticket->priority }}</span>
                        </td>
                        <td style="padding: 14px 16px;">
                            @php $sColors = ['open' => '#3b82f6', 'answered' => '#10b981', 'pending' => '#f59e0b', 'closed' => '#64748b']; @endphp
                            <span style="font-size: 11px; padding: 3px 10px; border-radius: 20px; background: {{ $sColors[$ticket->status] ?? '#64748b' }}20; color: {{ $sColors[$ticket->status] ?? '#64748b' }}; text-transform: capitalize; font-weight: 600;">{{ $ticket->status }}</span>
                        </td>
                        <td style="padding: 14px 16px; color: var(--text-dim); font-size: 12px;">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</td>
                        <td style="padding: 14px 16px; color: var(--text-dim); font-size: 12px;">{{ $ticket->updated_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align: center; padding: 40px; color: var(--text-dim);">No tickets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $tickets->links() }}
</div>
@endsection