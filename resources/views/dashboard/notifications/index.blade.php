@extends('layouts.dashboard')

@section('page-title', 'Notifications')

@section('content')
<div class="fade-in">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="page-title mb-1">
                <i class="fas fa-bell me-2" style="color: var(--purple-1);"></i>
                Notifications
            </h2>
            <p class="text-muted mb-0" style="font-size: 14px;">Stay updated on all your account activity</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm" style="background: var(--bg-card-2); border: 1px solid var(--border); color: var(--text);"
                    onclick="markAllRead()">
                <i class="fas fa-check-double me-1"></i> Mark all read
            </button>
            <button class="btn btn-sm" style="background: var(--bg-card-2); border: 1px solid var(--border); color: var(--text);"
                    onclick="clearRead()">
                <i class="fas fa-trash me-1"></i> Clear read
            </button>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <a href="{{ route('dashboard.notifications.index') }}" 
           class="btn btn-sm {{ $filter === 'all' ? 'btn-gradient' : '' }}" 
           style="{{ $filter !== 'all' ? 'background: var(--bg-card); border: 1px solid var(--border); color: var(--text);' : '' }}">
            All
        </a>
        <a href="{{ route('dashboard.notifications.index', ['filter' => 'unread']) }}" 
           class="btn btn-sm {{ $filter === 'unread' ? 'btn-gradient' : '' }}"
           style="{{ $filter !== 'unread' ? 'background: var(--bg-card); border: 1px solid var(--border); color: var(--text);' : '' }}">
            Unread <span class="badge" style="background: var(--purple-1); margin-left: 4px;">{{ $unreadCount }}</span>
        </a>
        <a href="{{ route('dashboard.notifications.index', ['filter' => 'read']) }}"
           class="btn btn-sm {{ $filter === 'read' ? 'btn-gradient' : '' }}"
           style="{{ $filter !== 'read' ? 'background: var(--bg-card); border: 1px solid var(--border); color: var(--text);' : '' }}">
            Read
        </a>
        <span class="ms-auto"></span>
        <select class="form-select form-select-sm" style="width: auto; background: var(--bg-card); border: 1px solid var(--border); color: var(--text);"
                onchange="window.location.href = this.value">
            <option value="{{ route('dashboard.notifications.index') }}">All Types</option>
            <option value="{{ route('dashboard.notifications.index', ['type' => 'deposit']) }}" {{ $type === 'deposit' ? 'selected' : '' }}>Deposits</option>
            <option value="{{ route('dashboard.notifications.index', ['type' => 'withdrawal']) }}" {{ $type === 'withdrawal' ? 'selected' : '' }}>Withdrawals</option>
            <option value="{{ route('dashboard.notifications.index', ['type' => 'investment']) }}" {{ $type === 'investment' ? 'selected' : '' }}>Investments</option>
            <option value="{{ route('dashboard.notifications.index', ['type' => 'referral']) }}" {{ $type === 'referral' ? 'selected' : '' }}>Referrals</option>
            <option value="{{ route('dashboard.notifications.index', ['type' => 'profit']) }}" {{ $type === 'profit' ? 'selected' : '' }}>Profit Share</option>
            <option value="{{ route('dashboard.notifications.index', ['type' => 'kyc']) }}" {{ $type === 'kyc' ? 'selected' : '' }}>KYC</option>
            <option value="{{ route('dashboard.notifications.index', ['type' => 'system']) }}" {{ $type === 'system' ? 'selected' : '' }}>System</option>
        </select>
    </div>

    <!-- Notifications List -->
    <div class="custom-card" style="padding: 0; overflow: hidden;">
        @forelse($notifications as $n)
        <div class="notification-item d-flex align-items-start gap-3" 
             style="padding: 16px 20px; border-bottom: 1px solid var(--border); {{ $n->is_read ? '' : 'background: rgba(99, 102, 241, 0.05);' }} cursor: pointer;"
             onclick="markRead({{ $n->id }})">
            <div style="width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                @php
                    $icons = [
                        'deposit' => ['fa-arrow-down', '#3b82f6'],
                        'withdrawal' => ['fa-arrow-up', '#ef4444'],
                        'investment' => ['fa-chart-line', '#6366f1'],
                        'referral' => ['fa-users', '#a855f7'],
                        'profit' => ['fa-coins', '#10b981'],
                        'kyc' => ['fa-id-card', '#f59e0b'],
                        'support' => ['fa-life-ring', '#06b6d4'],
                        'rank' => ['fa-trophy', '#f43f5e'],
                        'system' => ['fa-bell', '#7c3aed'],
                    ];
                    $icon = $icons[$n->type] ?? ['fa-info-circle', '#64748b'];
                @endphp
                <div style="width: 42px; height: 42px; border-radius: 10px; background: {{ $icon[1] }}22; display: flex; align-items: center; justify-content: center;">
                    <i class="fas {{ $icon[0] }}" style="color: {{ $icon[1] }}; font-size: 18px;"></i>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span style="font-weight: 600; color: var(--text-bright); font-size: 15px;">{{ $n->title }}</span>
                        @if(!$n->is_read)
                        <span class="badge ms-2" style="background: var(--purple-1); font-size: 9px; padding: 3px 6px;">NEW</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <small style="color: var(--text-dim); font-size: 12px;">
                            {{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}
                        </small>
                        <form action="{{ route('dashboard.notifications.destroy', $n->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: var(--text-dim); cursor: pointer; padding: 2px 6px; font-size: 14px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <p style="color: var(--text-muted); margin: 4px 0 0; font-size: 13px; line-height: 1.5;">
                    {{ $n->message }}
                </p>
                @if($n->link)
                <a href="{{ $n->link }}" style="color: var(--purple-1); font-size: 13px; font-weight: 500; text-decoration: none;" onclick="event.stopPropagation();">
                    View details <i class="fas fa-arrow-right ms-1" style="font-size: 10px;"></i>
                </a>
                @endif
            </div>
        </div>
        @empty
        <div style="padding: 60px 20px; text-align: center;">
            <i class="fas fa-bell-slash" style="font-size: 48px; color: var(--text-dim); margin-bottom: 16px; display: block;"></i>
            <p style="color: var(--text-muted); font-size: 15px;">No notifications found</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    {{ $notifications->links() }}
</div>

<script>
function markRead(id) {
    fetch('/dashboard/notifications/' + id + '/read', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(r => r.json()).then(data => {
        if (data.success) location.reload();
    });
}

function markAllRead() {
    fetch('/dashboard/notifications/mark-all-read', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(() => location.reload());
}

function clearRead() {
    fetch('/dashboard/notifications/clear-read', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(() => location.reload());
}
</script>
@endsection
