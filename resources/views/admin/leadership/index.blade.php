@extends('layouts.admin')

@section('page-title', 'Leadership Bonus')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px; font-size: 22px;">
                <i class="fas fa-crown" style="color: #f59e0b;"></i> Leadership Bonus Pool
            </h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Distribute monthly company profit pool to qualifying leaders by rank.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245,158,11,0.15); color: #f59e0b;"><i class="fas fa-coins"></i></div>
                <div class="stat-label">Total Distributed</div>
                <div class="stat-value">${{ number_format($totalDistributed, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-calendar"></i></div>
                <div class="stat-label">Cycles Run</div>
                <div class="stat-value">{{ $totalCycles }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-users"></i></div>
                <div class="stat-label">Eligible Users</div>
                <div class="stat-value">{{ $eligibleUsers->count() }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-clock"></i></div>
                <div class="stat-label">Last Cycle</div>
                <div class="stat-value" style="font-size: 14px;">{{ $lastCycle?->paid_at?->format('M d, Y') ?? '—' }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Run Distribution -->
        <div class="col-lg-4">
            <div class="card-custom" style="padding: 24px; position: sticky; top: 20px;">
                <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 16px;">
                    <i class="fas fa-play-circle" style="color: #6366f1;"></i> Run Distribution
                </h5>
                <form method="POST" action="{{ route('admin.leadership.run') }}">
                    @csrf
                    <div class="mb-3">
                        <label style="color: var(--text-bright); font-weight: 600; font-size: 13px; margin-bottom: 6px;">Pool Amount ($)</label>
                        <input type="number" name="pool_amount" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 12px 16px; font-size: 14px;" step="0.01" min="0.01" required placeholder="e.g. 50000">
                    </div>
                    <div class="mb-3">
                        <label style="color: var(--text-bright); font-weight: 600; font-size: 13px; margin-bottom: 6px;">Minimum Qualifying Rank</label>
                        <select name="min_rank_slug" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 12px 16px; font-size: 14px;" required>
                            @foreach($ranks as $rank)
                            <option value="{{ $rank->slug }}">{{ $rank->name }} (Level {{ $rank->sort_order }})</option>
                            @endforeach
                        </select>
                        <p style="color: var(--text-dim); font-size: 11px; margin-top: 4px;">Users at or above this rank will share the pool proportionally.</p>
                    </div>
                    <div class="mb-3">
                        <label style="color: var(--text-bright); font-weight: 600; font-size: 13px; margin-bottom: 6px;">Note (optional)</label>
                        <textarea name="note" rows="2" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 10px 14px; font-size: 13px; resize: vertical;" placeholder="e.g. August 2026 monthly leadership pool"></textarea>
                    </div>
                    <button type="submit" class="btn w-100" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; border-radius: 12px; padding: 12px; font-size: 14px; font-weight: 600;" onclick="return confirm('Run leadership bonus distribution? This will credit wallets immediately.')">
                        <i class="fas fa-paper-plane"></i> Distribute Pool
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: Eligible Users + History -->
        <div class="col-lg-8">
            <!-- Eligible Users -->
            <div class="card-custom mb-4" style="padding: 0; overflow: hidden;">
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
                    <h5 style="color: var(--text-bright); font-weight: 700; margin: 0;">
                        <i class="fas fa-user-check" style="color: #10b981;"></i> Eligible Users ({{ $eligibleUsers->count() }})
                    </h5>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table mb-0" style="color: var(--text); font-size: 13px;">
                        <thead>
                            <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim); background: rgba(30,35,55,0.5);">
                                <th style="padding: 12px 16px;">User</th>
                                <th style="padding: 12px 16px;">Rank</th>
                                <th style="padding: 12px 16px;">Total Earned</th>
                                <th style="padding: 12px 16px;">Last Bonus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($eligibleUsers as $item)
                            <tr style="border-bottom: 1px solid rgba(51,65,85,0.15);">
                                <td style="padding: 12px 16px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 28px; height: 28px; border-radius: 8px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 11px;">
                                            {{ strtoupper(substr($item['user']->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="color: var(--text-bright); font-size: 13px;">{{ $item['user']->name }}</div>
                                            <div style="color: var(--text-dim); font-size: 11px;">{{ $item['user']->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 12px 16px;">
                                    <span style="font-size: 11px; padding: 2px 10px; border-radius: 8px; background: {{ $item['rank']->badge_color ?? '#6366f1' }}20; color: {{ $item['rank']->badge_color ?? '#818cf8' }}; font-weight: 600;">{{ $item['rank']->name ?? 'Unknown' }}</span>
                                </td>
                                <td style="padding: 12px 16px; color: #10b981; font-weight: 600;">${{ number_format($item['total_earned'], 2) }}</td>
                                <td style="padding: 12px 16px; color: var(--text-dim); font-size: 12px;">{{ $item['last_bonus']?->paid_at?->format('M d, Y') ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" style="text-align: center; padding: 30px; color: var(--text-dim);">No eligible users yet. Users need a rank to qualify.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cycle History -->
            <div class="card-custom" style="padding: 0; overflow: hidden;">
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
                    <h5 style="color: var(--text-bright); font-weight: 700; margin: 0;">
                        <i class="fas fa-history" style="color: #818cf8;"></i> Cycle History
                    </h5>
                </div>
                @forelse($cycleHistory as $cycle)
                <div style="padding: 14px 20px; border-bottom: 1px solid rgba(51,65,85,0.15); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 14px; font-weight: 600; color: var(--text-bright);">{{ $cycle->cycle_id }}</div>
                        <div style="font-size: 12px; color: var(--text-dim);">{{ $cycle->recipients }} recipients · {{ optional($cycle->date)->format('M d, Y') }}</div>
                    </div>
                    <div style="font-size: 16px; font-weight: 700; color: #f59e0b;">${{ number_format($cycle->total, 2) }}</div>
                </div>
                @empty
                <div style="padding: 30px; text-align: center; color: var(--text-dim); font-size: 13px;">No distributions yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
