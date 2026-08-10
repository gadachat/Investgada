@extends('layouts.admin')

@section('page-title', 'Active Sessions')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px; font-size: 22px;">
                <i class="fas fa-laptop" style="color: #6366f1;"></i> Active Sessions
            </h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Monitor and terminate active user sessions across the platform.</p>
        </div>
        <a href="{{ route('admin.security.index') }}" class="btn btn-sm" style="background: var(--bg-card); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 8px 16px; font-size: 12px; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="card-custom" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="table mb-0" style="color: var(--text);">
                <thead>
                    <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim); background: rgba(30,35,55,0.5);">
                        <th style="padding: 12px 16px;">User</th>
                        <th style="padding: 12px 16px;">Role</th>
                        <th style="padding: 12px 16px;">IP Address</th>
                        <th style="padding: 12px 16px;">Device</th>
                        <th style="padding: 12px 16px;">Browser</th>
                        <th style="padding: 12px 16px;">Last Activity</th>
                        <th style="padding: 12px 16px;">Status</th>
                        <th style="padding: 12px 16px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    <tr style="font-size: 13px; border-bottom: 1px solid rgba(51,65,85,0.2);">
                        <td style="padding: 12px 16px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 12px;">
                                    {{ strtoupper(substr($session->user?->name ?? 'U', 0, 1)) }}
                                </div>
                                {{ $session->user?->name ?? 'Unknown' }}
                            </div>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 6px; background: {{ $session->user?->role === 'admin' ? 'rgba(239,68,68,0.1)' : 'rgba(99,102,241,0.1)' }}; color: {{ $session->user?->role === 'admin' ? '#ef4444' : '#818cf8' }};">
                                {{ $session->user?->role ?? '—' }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px;"><code style="color: var(--text-muted); font-size: 12px;">{{ $session->ip_address }}</code></td>
                        <td style="padding: 12px 16px; color: var(--text-muted);">
                            <i class="fas fa-{{ \App\Models\ActiveSession::detectDevice($session->user_agent) === 'Mobile' ? 'mobile-alt' : (\App\Models\ActiveSession::detectDevice($session->user_agent) === 'Tablet' ? 'tablet-alt' : 'desktop') }}"></i>
                            {{ \App\Models\ActiveSession::detectDevice($session->user_agent) }}
                        </td>
                        <td style="padding: 12px 16px; color: var(--text-muted);">{{ \App\Models\ActiveSession::detectBrowser($session->user_agent) }}</td>
                        <td style="padding: 12px 16px; color: var(--text-dim); font-size: 12px;">{{ $session->last_activity->diffForHumans() }}</td>
                        <td style="padding: 12px 16px;">
                            @if($session->last_activity >= now()->subMinutes(15))
                            <span style="background: rgba(16,185,129,0.15); color: #10b981; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;"><i class="fas fa-circle" style="font-size: 7px;"></i> Active</span>
                            @else
                            <span style="background: rgba(100,116,139,0.15); color: #64748b; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Idle</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px;">
                            <form method="POST" action="{{ route('admin.security.terminate-session', $session) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; border-radius: 8px; padding: 4px 10px; font-size: 11px;" onclick="return confirm('Terminate this session? The user will be logged out.')">
                                    <i class="fas fa-sign-out-alt"></i> Terminate
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align: center; padding: 30px; color: var(--text-dim);">No active sessions.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $sessions->links() }}
</div>
@endsection