@extends('layouts.admin')

@section('page-title', 'IP Management')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px; font-size: 22px;">
                <i class="fas fa-ban" style="color: #ef4444;"></i> IP Management
            </h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Block malicious IPs, whitelist trusted ones, and monitor access patterns.</p>
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

    <!-- Block & Whitelist Forms -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card-custom" style="padding: 20px; border-color: rgba(239,68,68,0.2);">
                <h6 style="color: #ef4444; font-weight: 700; margin-bottom: 12px; font-size: 14px;"><i class="fas fa-ban"></i> Block IP Address</h6>
                <form method="POST" action="{{ route('admin.security.block-ip') }}">
                    @csrf
                    <div class="mb-2">
                        <input type="text" name="ip_address" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;" placeholder="e.g., 192.168.1.1" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <select name="duration" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;">
                                <option value="1h">1 hour</option>
                                <option value="6h">6 hours</option>
                                <option value="24h">24 hours</option>
                                <option value="7d">7 days</option>
                                <option value="permanent">Permanent</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <input type="text" name="reason" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;" placeholder="Reason (optional)">
                        </div>
                    </div>
                    <button type="submit" class="btn w-100 mt-2" style="background: #ef4444; color: white; border: none; border-radius: 10px; padding: 10px; font-size: 13px;">
                        <i class="fas fa-ban"></i> Block This IP
                    </button>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-custom" style="padding: 20px; border-color: rgba(16,185,129,0.2);">
                <h6 style="color: #10b981; font-weight: 700; margin-bottom: 12px; font-size: 14px;"><i class="fas fa-check-circle"></i> Whitelist IP Address</h6>
                <form method="POST" action="{{ route('admin.security.whitelist-ip') }}">
                    @csrf
                    <div class="mb-2">
                        <input type="text" name="ip_address" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;" placeholder="e.g., 192.168.1.1" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="reason" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; font-size: 13px;" placeholder="Reason (optional)">
                    </div>
                    <button type="submit" class="btn w-100" style="background: #10b981; color: white; border: none; border-radius: 10px; padding: 10px; font-size: 13px;">
                        <i class="fas fa-check"></i> Whitelist This IP
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Blocked IPs Table -->
    <div class="card-custom mb-4" style="padding: 0; overflow: hidden;">
        <div style="padding: 18px 20px; border-bottom: 1px solid var(--border);">
            <h5 style="color: #ef4444; font-weight: 700; margin: 0; font-size: 15px;"><i class="fas fa-ban"></i> Blocked IPs</h5>
        </div>
        <div style="overflow-x: auto;">
            <table class="table mb-0" style="color: var(--text);">
                <thead>
                    <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim); background: rgba(30,35,55,0.5);">
                        <th style="padding: 12px 16px;">IP Address</th>
                        <th style="padding: 12px 16px;">Reason</th>
                        <th style="padding: 12px 16px;">Blocked By</th>
                        <th style="padding: 12px 16px;">Expires</th>
                        <th style="padding: 12px 16px;">Status</th>
                        <th style="padding: 12px 16px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($blockedIps as $ip)
                    <tr style="font-size: 13px; border-bottom: 1px solid rgba(51,65,85,0.2);">
                        <td style="padding: 12px 16px;"><code style="color: var(--text-bright);">{{ $ip->ip_address }}</code></td>
                        <td style="padding: 12px 16px; color: var(--text-muted);">{{ $ip->reason ?? '—' }}</td>
                        <td style="padding: 12px 16px; color: var(--text-muted);">{{ $ip->blockedBy?->name ?? 'System' }}</td>
                        <td style="padding: 12px 16px; color: var(--text-dim); font-size: 12px;">
                            {{ $ip->expires_at ? $ip->expires_at->diffForHumans() : 'Permanent' }}
                        </td>
                        <td style="padding: 12px 16px;">
                            @if($ip->is_active && (!$ip->expires_at || $ip->expires_at > now()))
                            <span style="background: rgba(239,68,68,0.15); color: #ef4444; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Active</span>
                            @else
                            <span style="background: rgba(100,116,139,0.15); color: #64748b; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Expired</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px;">
                            <form method="POST" action="{{ route('admin.security.remove-ip', $ip) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="background: none; border: 1px solid var(--border); color: var(--text-muted); border-radius: 8px; padding: 4px 10px; font-size: 11px;" onclick="return confirm('Remove this IP?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align: center; padding: 30px; color: var(--text-dim);">No blocked IPs.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Whitelisted IPs Table -->
    <div class="card-custom mb-4" style="padding: 0; overflow: hidden;">
        <div style="padding: 18px 20px; border-bottom: 1px solid var(--border);">
            <h5 style="color: #10b981; font-weight: 700; margin: 0; font-size: 15px;"><i class="fas fa-check-circle"></i> Whitelisted IPs</h5>
        </div>
        <div style="overflow-x: auto;">
            <table class="table mb-0" style="color: var(--text);">
                <thead>
                    <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim); background: rgba(30,35,55,0.5);">
                        <th style="padding: 12px 16px;">IP Address</th>
                        <th style="padding: 12px 16px;">Reason</th>
                        <th style="padding: 12px 16px;">Added By</th>
                        <th style="padding: 12px 16px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($whitelistedIps as $ip)
                    <tr style="font-size: 13px; border-bottom: 1px solid rgba(51,65,85,0.2);">
                        <td style="padding: 12px 16px;"><code style="color: var(--text-bright);">{{ $ip->ip_address }}</code></td>
                        <td style="padding: 12px 16px; color: var(--text-muted);">{{ $ip->reason ?? '—' }}</td>
                        <td style="padding: 12px 16px; color: var(--text-muted);">{{ $ip->blockedBy?->name ?? 'System' }}</td>
                        <td style="padding: 12px 16px;">
                            <form method="POST" action="{{ route('admin.security.remove-ip', $ip) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="background: none; border: 1px solid var(--border); color: var(--text-muted); border-radius: 8px; padding: 4px 10px; font-size: 11px;" onclick="return confirm('Remove this IP?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align: center; padding: 30px; color: var(--text-dim);">No whitelisted IPs.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activity by IP -->
    <div class="card-custom" style="padding: 0; overflow: hidden;">
        <div style="padding: 18px 20px; border-bottom: 1px solid var(--border);">
            <h5 style="color: #f59e0b; font-weight: 700; margin: 0; font-size: 15px;"><i class="fas fa-fire"></i> Most Active IPs (7 Days)</h5>
        </div>
        <div style="overflow-x: auto;">
            <table class="table mb-0" style="color: var(--text);">
                <thead>
                    <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim); background: rgba(30,35,55,0.5);">
                        <th style="padding: 12px 16px;">IP Address</th>
                        <th style="padding: 12px 16px;">Total Attempts</th>
                        <th style="padding: 12px 16px;">Failed</th>
                        <th style="padding: 12px 16px;">Last Seen</th>
                        <th style="padding: 12px 16px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentIps as $ip)
                    <tr style="font-size: 13px; border-bottom: 1px solid rgba(51,65,85,0.2);">
                        <td style="padding: 12px 16px;"><code style="color: var(--text-bright);">{{ $ip->ip_address }}</code></td>
                        <td style="padding: 12px 16px;">{{ $ip->attempts }}</td>
                        <td style="padding: 12px 16px;">
                            <span style="color: {{ $ip->failed > 0 ? '#ef4444' : 'var(--text-muted)' }}; font-weight: 600;">{{ $ip->failed }}</span>
                        </td>
                        <td style="padding: 12px 16px; color: var(--text-dim); font-size: 12px;">{{ \Carbon\Carbon::parse($ip->last_seen)->diffForHumans() }}</td>
                        <td style="padding: 12px 16px;">
                            <form method="POST" action="{{ route('admin.security.block-ip') }}" style="display: inline;">
                                @csrf
                                <input type="hidden" name="ip_address" value="{{ $ip->ip_address }}">
                                <input type="hidden" name="duration" value="24h">
                                <input type="hidden" name="reason" value="High failed login rate">
                                <button type="submit" class="btn btn-sm" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; border-radius: 8px; padding: 4px 10px; font-size: 11px;">
                                    <i class="fas fa-ban"></i> Block
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection