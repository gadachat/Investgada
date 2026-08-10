@extends('layouts.dashboard')

@section('title', 'Fund Applications')

@section('content')
<div class="page-content">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2 class="mb-1" style="font-weight:700;color:var(--text)">
                <i class="fas fa-hand-holding-usd me-2" style="color:var(--primary)"></i> Fund Applications
            </h2>
            <p style="color:var(--text-muted);font-size:14px">Apply for trading capital as a marketer or leader</p>
        </div>
        @if($canApply)
        <a href="{{ route('dashboard.funds.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Apply for Funds
        </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($activeFund)
    {{-- Active Fund Progress Card --}}
    <div class="card-custom mb-4" style="background:linear-gradient(135deg,rgba(99,102,241,0.08),rgba(168,85,247,0.05));border:1px solid rgba(99,102,241,0.2)">
        <div class="d-flex flex-wrap justify-content-between align-items-start mb-3 gap-2">
            <div>
                <h4 class="mb-1" style="font-weight:700;color:var(--text)">Active Fund — {{ $activeFund->reference }}</h4>
                <p style="color:var(--text-muted);font-size:13px">
                    Approved: {{ $activeFund->approved_at?->format('M d, Y') }} ·
                    Type: <span class="badge" style="background:rgba(99,102,241,0.15);color:#a855f7">{{ ucfirst($activeFund->applicant_type) }}</span>
                </p>
            </div>
            <div class="text-end">
                <h3 class="mb-0" style="font-weight:800;color:var(--primary)">${{ number_format($activeFund->approved_amount, 2) }}</h3>
                <span style="color:var(--text-muted);font-size:12px">Funded Capital</span>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span style="font-size:13px;color:var(--text-muted)">Team Production Progress</span>
                <span style="font-size:13px;font-weight:600;color:var(--text)">
                    ${{ number_format($activeFund->team_production, 2) }} / ${{ number_format($activeFund->target_production, 2) }}
                </span>
            </div>
            <div style="background:rgba(99,102,241,0.1);border-radius:10px;height:12px;overflow:hidden">
                <div style="background:linear-gradient(90deg,#6366f1,#a855f7);height:100%;width:{{ $activeFund->progressPercent() }}%;border-radius:10px;transition:width 0.5s ease"></div>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <span style="font-size:12px;color:var(--text-muted)">{{ $activeFund->progressPercent() }}% complete</span>
                <span style="font-size:12px;color:var(--text-muted)">Remaining: ${{ number_format($activeFund->remainingProduction(), 2) }}</span>
            </div>
        </div>

        {{-- Withdrawal Rules --}}
        <div class="row g-2 mt-1">
            <div class="col-md-4 col-12">
                <div class="rounded-3 p-3" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.15)">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-check-circle" style="color:#10b981;font-size:16px"></i>
                        <span style="font-weight:600;font-size:13px;color:#10b981">Commissions</span>
                    </div>
                    <p style="font-size:12px;color:var(--text-muted);margin:0">Referral & other commissions can be withdrawn anytime</p>
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="rounded-3 p-3" style="background:rgba({{ $activeFund->target_met ? '16,185,129' : '245,158,11' }},0.08);border:1px solid rgba({{ $activeFund->target_met ? '16,185,129' : '245,158,11' }},0.15)">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-{{ $activeFund->target_met ? 'check-circle' : 'lock' }}" style="color:{{ $activeFund->target_met ? '#10b981' : '#f59e0b' }};font-size:16px"></i>
                        <span style="font-weight:600;font-size:13px;color:{{ $activeFund->target_met ? '#10b981' : '#f59e0b' }}">Profits</span>
                    </div>
                    <p style="font-size:12px;color:var(--text-muted);margin:0">
                        @if($activeFund->target_met) Unlocked — withdraw anytime @else Locked until 100% target @endif
                    </p>
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="rounded-3 p-3" style="background:rgba({{ $activeFund->target_met ? '16,185,129' : '245,158,11' }},0.08);border:1px solid rgba({{ $activeFund->target_met ? '16,185,129' : '245,158,11' }},0.15)">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-{{ $activeFund->target_met ? 'check-circle' : 'lock' }}" style="color:{{ $activeFund->target_met ? '#10b981' : '#f59e0b' }};font-size:16px"></i>
                        <span style="font-weight:600;font-size:13px;color:{{ $activeFund->target_met ? '#10b981' : '#f59e0b' }}">Capital</span>
                    </div>
                    <p style="font-size:12px;color:var(--text-muted);margin:0">
                        @if($activeFund->target_met) Unlocked — withdraw anytime @else Locked until 100% target @endif
                    </p>
                </div>
            </div>
        </div>

        @if($activeFund->target_met)
        <div class="alert alert-success mt-3 mb-0" style="font-size:13px">
            <i class="fas fa-trophy me-1"></i> Congratulations! Your team has reached 100% of the target. All withdrawals are now unlocked.
        </div>
        @endif
    </div>
    @endif

    {{-- Applications List --}}
    <div class="card-custom">
        <div class="p-3" style="border-bottom:1px solid var(--border)">
            <h5 class="mb-0" style="font-weight:600;color:var(--text)">Application History</h5>
        </div>
        @if($applications->count() > 0)
        <div style="overflow-x:auto">
            <table class="table table-hover mb-0" style="color:var(--text)">
                <thead>
                    <tr style="color:var(--text-muted);font-size:12px;text-transform:uppercase">
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $app)
                    <tr>
                        <td style="font-weight:600;font-size:13px">{{ $app->reference }}</td>
                        <td>
                            <span class="badge" style="background:rgba(99,102,241,0.15);color:#a855f7">{{ ucfirst($app->applicant_type) }}</span>
                        </td>
                        <td style="font-weight:600">${{ number_format($app->approved_amount ?? $app->requested_amount, 2) }}</td>
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
                        <td>
                            @if(in_array($app->status, ['approved', 'completed']))
                                <span style="font-size:13px">{{ $app->progressPercent() }}%</span>
                            @else
                                <span style="color:var(--text-muted)">—</span>
                            @endif
                        </td>
                        <td style="font-size:13px;color:var(--text-muted)">{{ $app->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('dashboard.funds.show', $app) }}" class="btn btn-sm" style="background:rgba(99,102,241,0.1);color:var(--primary);font-size:12px">
                                <i class="fas fa-eye"></i>
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
            <p style="color:var(--text-muted);margin-top:12px">No fund applications yet</p>
            @if($canApply)
            <a href="{{ route('dashboard.funds.create') }}" class="btn btn-primary mt-2">
                <i class="fas fa-plus me-1"></i> Apply Now
            </a>
            @endif
        </div>
        @endif
    </div>

    {{ $applications->links() }}
</div>
@endsection
