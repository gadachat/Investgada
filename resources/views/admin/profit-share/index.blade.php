@extends('layouts.admin')

@section('page-title', 'Profit Sharing — Admin')

@section('content')
<div class="fade-in">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px;">
                <i class="fas fa-coins me-2" style="color: var(--purple-1);"></i>
                Profit Sharing Engine
            </h2>
            <p style="color: var(--text-muted); margin: 0; font-size: 14px;">Manage profit distribution cycles and settings</p>
        </div>
        <div>
            @if($settings['profit_share_enabled'])
            <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 6px 12px; font-size: 12px;">
                <i class="fas fa-circle me-1" style="font-size: 6px;"></i> Active
            </span>
            @else
            <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #ef4444; padding: 6px 12px; font-size: 12px;">
                <i class="fas fa-circle me-1" style="font-size: 6px;"></i> Disabled
            </span>
            @endif
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="admin-card p-3 text-center">
                <div style="font-size: 28px; font-weight: 700; color: #10b981;">${{ number_format($totalDistributed, 2) }}</div>
                <small style="color: var(--text-dim);">Total Distributed</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card p-3 text-center">
                <div style="font-size: 28px; font-weight: 700; color: var(--purple-1);">{{ $totalCyclesRun }}</div>
                <small style="color: var(--text-dim);">Cycles Run</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card p-3 text-center">
                <div style="font-size: 28px; font-weight: 700; color: var(--purple-2);">${{ number_format($totalActiveCapital, 2) }}</div>
                <small style="color: var(--text-dim);">Active Capital</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card p-3 text-center">
                <div style="font-size: 28px; font-weight: 700; color: var(--purple-3);">${{ number_format($weightedCapital, 2) }}</div>
                <small style="color: var(--text-dim);">Weighted Capital</small>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Run Cycle -->
        <div class="col-lg-5">
            <div class="admin-card p-4 mb-3">
                <h5 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-play-circle me-2" style="color: var(--purple-2);"></i>
                    Run Profit Cycle
                </h5>
                <form action="{{ route('admin.profit-share.run') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-muted); font-size: 13px;">Pool Amount ($)</label>
                        <input type="number" name="pool_amount" step="0.01" min="0.01" class="form-control" required
                               style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);"
                               placeholder="10000.00"
                               oninput="previewDistribution(this.value)">
                        <small style="color: var(--text-dim);">Max: ${{ number_format($settings['max_daily_payout'], 2) }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-muted); font-size: 13px;">Cycle Note (optional)</label>
                        <input type="text" name="cycle_note" class="form-control"
                               style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);"
                               placeholder="e.g. Daily trading profits Aug 2">
                    </div>

                    <!-- Live Preview -->
                    <div id="previewBox" style="display: none; padding: 14px; border-radius: 10px; background: var(--bg-input); margin-bottom: 12px;">
                        <small style="color: var(--text-muted); font-weight: 600;">Distribution Preview (top 5)</small>
                        <div id="previewList" style="margin-top: 8px;"></div>
                    </div>

                    <button type="submit" class="btn btn-gradient w-100"
                            onclick="return confirm('Run profit-sharing cycle with this pool amount? This will distribute funds to all eligible users.')">
                        <i class="fas fa-bolt me-1"></i> Run Cycle
                    </button>
                </form>
                @if(session('error'))
                <div class="alert alert-danger mt-3" style="font-size: 13px;">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                <div class="alert alert-success mt-3" style="font-size: 13px;">{{ session('success') }}</div>
                @endif
            </div>

            <!-- Settings -->
            <div class="admin-card p-4">
                <h6 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-cog me-2" style="color: var(--purple-1);"></i>
                    Profit Settings
                </h6>
                <form action="{{ route('admin.profit-share.settings') }}" method="POST">
                    @csrf
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="profit_share_enabled" id="psEnabled"
                               {{ $settings['profit_share_enabled'] ? 'checked' : '' }}>
                        <label class="form-check-label" for="psEnabled" style="color: var(--text);">Enable Profit Sharing</label>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="color: var(--text-muted); font-size: 12px;">Pool Allocation (%)</label>
                        <input type="number" name="profit_pool_percentage" step="0.1" min="0" max="100" class="form-control form-control-sm"
                               style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);"
                               value="{{ $settings['profit_pool_percentage'] }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="color: var(--text-muted); font-size: 12px;">Cycle Frequency</label>
                        <select name="profit_cycle_frequency" class="form-select form-select-sm" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);">
                            <option value="daily" {{ $settings['profit_cycle_frequency'] === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ $settings['profit_cycle_frequency'] === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="biweekly" {{ $settings['profit_cycle_frequency'] === 'biweekly' ? 'selected' : '' }}>Bi-Weekly</option>
                            <option value="monthly" {{ $settings['profit_cycle_frequency'] === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="color: var(--text-muted); font-size: 12px;">Min Active Capital ($)</label>
                        <input type="number" name="min_active_capital" step="1" min="0" class="form-control form-control-sm"
                               style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);"
                               value="{{ $settings['min_active_capital'] }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-muted); font-size: 12px;">Max Daily Payout ($)</label>
                        <input type="number" name="max_daily_payout" step="1" min="0" class="form-control form-control-sm"
                               style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text);"
                               value="{{ $settings['max_daily_payout'] }}">
                    </div>
                    <button type="submit" class="btn btn-gradient w-100 btn-sm">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>

        <!-- Eligible Users + History -->
        <div class="col-lg-7">
            <!-- Eligible Users -->
            <div class="admin-card p-4 mb-3">
                <h5 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-users me-2" style="color: var(--purple-1);"></i>
                    Eligible Users
                    <span class="badge ms-2" style="background: var(--purple-2); color: white;">{{ count($eligibleUsers) }}</span>
                </h5>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-hover" style="color: var(--text);">
                        <thead style="position: sticky; top: 0; background: var(--bg-card); z-index: 1;">
                            <tr style="border-bottom: 2px solid var(--border);">
                                <th style="color: var(--text-muted); font-size: 11px; border: none;">USER</th>
                                <th style="color: var(--text-muted); font-size: 11px; border: none;">CAPITAL</th>
                                <th style="color: var(--text-muted); font-size: 11px; border: none;">WEIGHTED</th>
                                <th style="color: var(--text-muted); font-size: 11px; border: none;">SHARE</th>
                                <th style="color: var(--text-muted); font-size: 11px; border: none;">EST. PAYOUT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(collect($eligibleUsers)->sortByDesc('weighted_capital')->take(15) as $uid => $data)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="border: none; font-size: 12px;">
                                    <div style="color: var(--text-bright); font-weight: 500;">{{ $data['user_name'] }}</div>
                                    <small style="color: var(--text-dim);">{{ $data['user_email'] }}</small>
                                </td>
                                <td style="border: none; font-size: 12px; color: var(--text-muted);">${{ number_format($data['raw_capital'], 0) }}</td>
                                <td style="border: none; font-size: 12px; color: var(--purple-3);">${{ number_format($data['weighted_capital'], 0) }}</td>
                                <td style="border: none; font-size: 12px;">
                                    <span style="color: var(--purple-1); font-weight: 600;">{{ number_format($data['share_percentage'], 2) }}%</span>
                                </td>
                                <td style="border: none; font-size: 12px; font-weight: 700; color: #10b981;">
                                    <span class="est-payout" data-share="{{ $data['share_percentage'] }}">—</span>
                                </td>
                            </tr>
                            @endforeach
                            @if(count($eligibleUsers) === 0)
                            <tr><td colspan="5" style="border: none; text-align: center; padding: 30px; color: var(--text-muted);">No eligible users</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @if(count($eligibleUsers) > 15)
                <p style="text-align: center; color: var(--text-dim); font-size: 12px; margin-top: 8px;">Showing top 15 of {{ count($eligibleUsers) }}</p>
                @endif
            </div>

            <!-- Cycle History -->
            <div class="admin-card p-4">
                <h5 style="color: var(--text-bright); font-weight: 600; margin-bottom: 16px;">
                    <i class="fas fa-history me-2" style="color: var(--purple-2);"></i>
                    Cycle History
                </h5>
                @forelse($cycleHistory as $cycle)
                <div class="d-flex justify-content-between align-items-center" style="padding: 10px 0; border-bottom: 1px solid var(--border);">
                    <div>
                        <span style="color: var(--text-bright); font-weight: 600; font-size: 13px; font-family: monospace;">{{ $cycle->cycle_id }}</span>
                        <br>
                        <small style="color: var(--text-dim);">{{ \Carbon\Carbon::parse($cycle->date)->format('M j, Y g:i A') }}</small>
                    </div>
                    <div class="text-end">
                        <div style="color: #10b981; font-weight: 700; font-size: 14px;">${{ number_format($cycle->total, 2) }}</div>
                        <small style="color: var(--text-dim);">{{ $cycle->recipients }} payouts</small>
                    </div>
                </div>
                @empty
                <p style="color: var(--text-muted); font-size: 13px; text-align: center; padding: 20px 0;">No cycles run yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
const eligibleShares = {
    @foreach($eligibleUsers as $uid => $data)
    "{{ $uid }}": {{ $data['share_percentage'] }},
    @endforeach
};

function previewDistribution(poolAmount) {
    if (!poolAmount || poolAmount <= 0) {
        document.getElementById('previewBox').style.display = 'none';
        return;
    }

    // Update estimated payouts in table
    document.querySelectorAll('.est-payout').forEach(el => {
        const share = parseFloat(el.dataset.share);
        el.textContent = '$' + (poolAmount * share / 100).toFixed(2);
    });

    // Preview top 5
    const entries = Object.entries(eligibleShares).sort((a, b) => b[1] - a[1]).slice(0, 5);
    let html = '';
    entries.forEach(([uid, share]) => {
        const amount = (poolAmount * share / 100).toFixed(2);
        html += `<div style="display: flex; justify-content: space-between; font-size: 12px; padding: 4px 0;">
            <span style="color: var(--text-muted);">User #${uid} (${share.toFixed(2)}%)</span>
            <span style="color: #10b981; font-weight: 600;">$${amount}</span>
        </div>`;
    });
    document.getElementById('previewList').innerHTML = html;
    document.getElementById('previewBox').style.display = 'block';
}
</script>
@endsection
