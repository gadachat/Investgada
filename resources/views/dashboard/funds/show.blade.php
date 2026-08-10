@extends('layouts.dashboard')

@section('title', 'Fund Details')

@section('content')
<div class="page-content">
    <div class="mb-4">
        <a href="{{ route('dashboard.funds.index') }}" style="color:var(--text-muted);font-size:13px;text-decoration:none">
            <i class="fas fa-arrow-left me-1"></i> Back to Funds
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        {{-- Fund Details --}}
        <div class="col-lg-8 col-md-10 col-12">
            <div class="card-custom mb-3">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="mb-1" style="font-weight:700;color:var(--text)">{{ $fund->reference }}</h4>
                        <p style="color:var(--text-muted);font-size:13px">
                            Applied: {{ $fund->created_at->format('M d, Y H:i') }} ·
                            Type: <span class="badge" style="background:rgba(99,102,241,0.15);color:#a855f7">{{ ucfirst($fund->applicant_type) }}</span>
                        </p>
                    </div>
                    @php
                        $colors = [
                            'pending'   => ['bg' => 'rgba(245,158,11,0.15)', 'color' => '#f59e0b', 'icon' => 'clock'],
                            'approved'  => ['bg' => 'rgba(59,130,246,0.15)', 'color' => '#3b82f6', 'icon' => 'check-circle'],
                            'completed' => ['bg' => 'rgba(16,185,129,0.15)', 'color' => '#10b981', 'icon' => 'trophy'],
                            'rejected'  => ['bg' => 'rgba(239,68,68,0.15)', 'color' => '#ef4444', 'icon' => 'times-circle'],
                            'revoked'   => ['bg' => 'rgba(239,68,68,0.15)', 'color' => '#ef4444', 'icon' => 'ban'],
                        ];
                        $s = $colors[$fund->status] ?? $colors['pending'];
                    @endphp
                    <span class="badge px-3 py-2" style="background:{{ $s['bg'] }};color:{{ $s['color'] }};font-size:13px">
                        <i class="fas fa-{{ $s['icon'] }} me-1"></i>{{ ucfirst($fund->status) }}
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 col-12">
                        <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                            <p style="font-size:12px;color:var(--text-muted);margin:0 0 4px">Requested Amount</p>
                            <h5 style="font-weight:700;color:var(--text);margin:0">${{ number_format($fund->requested_amount, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                            <p style="font-size:12px;color:var(--text-muted);margin:0 0 4px">Approved Amount</p>
                            <h5 style="font-weight:700;color:var(--primary);margin:0">
                                {{ $fund->approved_amount ? '$' . number_format($fund->approved_amount, 2) : '—' }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                            <p style="font-size:12px;color:var(--text-muted);margin:0 0 4px">Team Production</p>
                            <h5 style="font-weight:700;color:var(--text);margin:0">${{ number_format($fund->team_production, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                            <p style="font-size:12px;color:var(--text-muted);margin:0 0 4px">Target Production</p>
                            <h5 style="font-weight:700;color:var(--text);margin:0">${{ number_format($fund->target_production, 2) }}</h5>
                        </div>
                    </div>
                </div>

                @if($fund->purpose)
                <div class="mt-3">
                    <p style="font-size:12px;color:var(--text-muted);margin:0 0 4px">Purpose</p>
                    <p style="font-size:14px;color:var(--text)">{{ $fund->purpose }}</p>
                </div>
                @endif

                @if($fund->admin_note)
                <div class="mt-3 rounded-3 p-3" style="background:rgba(99,102,241,0.05);border:1px solid rgba(99,102,241,0.1)">
                    <p style="font-size:12px;color:var(--primary);margin:0 0 4px"><i class="fas fa-shield-alt me-1"></i> Admin Note</p>
                    <p style="font-size:13px;color:var(--text);margin:0">{{ $fund->admin_note }}</p>
                </div>
                @endif
            </div>

            {{-- Team Members --}}
            @if(in_array($fund->status, ['approved', 'completed']) && $teamMembers->count() > 0)
            <div class="card-custom">
                <div class="p-3" style="border-bottom:1px solid var(--border)">
                    <h5 class="mb-0" style="font-weight:600;color:var(--text)">
                        <i class="fas fa-users me-1" style="color:var(--primary)"></i> Team Production Breakdown
                    </h5>
                </div>
                <div style="overflow-x:auto">
                    <table class="table table-hover mb-0" style="color:var(--text)">
                        <thead>
                            <tr style="color:var(--text-muted);font-size:12px;text-transform:uppercase">
                                <th>Member</th>
                                <th>Active Investments</th>
                                <th>Total Volume</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalVolume = 0; @endphp
                            @foreach($teamMembers as $member)
                                @php
                                    $investVolume = $member->investments->sum('amount');
                                    $totalVolume += $investVolume;
                                @endphp
                                <tr>
                                    <td style="font-size:13px;font-weight:600">{{ $member->name }}</td>
                                    <td style="font-size:13px">{{ $member->investments->count() }}</td>
                                    <td style="font-size:13px;font-weight:600;color:#10b981">${{ number_format($investVolume, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="border-top:2px solid var(--border)">
                                <td style="font-weight:700">Total</td>
                                <td></td>
                                <td style="font-weight:700;color:var(--primary)">${{ number_format($totalVolume, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar: Withdrawal Rules --}}
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card-custom" style="position:sticky;top:80px">
                <h5 class="mb-3" style="font-weight:600;color:var(--text)">
                    <i class="fas fa-shield-alt me-1" style="color:var(--primary)"></i> Withdrawal Rules
                </h5>

                <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded-3" style="background:rgba(16,185,129,0.08)">
                    <i class="fas fa-check-circle" style="color:#10b981;font-size:20px"></i>
                    <div>
                        <p style="font-size:13px;font-weight:600;color:#10b981;margin:0">Commissions</p>
                        <p style="font-size:12px;color:var(--text-muted);margin:0">Available immediately</p>
                    </div>
                </div>

                @if(in_array($fund->status, ['approved', 'completed']))
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:12px;color:var(--text-muted)">Progress</span>
                        <span style="font-size:12px;font-weight:600;color:var(--text)">{{ $fund->progressPercent() }}%</span>
                    </div>
                    <div style="background:rgba(99,102,241,0.1);border-radius:8px;height:8px;overflow:hidden">
                        <div style="background:linear-gradient(90deg,#6366f1,#a855f7);height:100%;width:{{ $fund->progressPercent() }}%;border-radius:8px"></div>
                    </div>
                    <p style="font-size:11px;color:var(--text-muted);margin:6px 0 0">
                        Remaining: ${{ number_format($fund->remainingProduction(), 2) }}
                    </p>
                </div>

                <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded-3" style="background:rgba({{ $fund->target_met ? '16,185,129' : '245,158,11' }},0.08)">
                    <i class="fas fa-{{ $fund->target_met ? 'check-circle' : 'lock' }}" style="color:{{ $fund->target_met ? '#10b981' : '#f59e0b' }};font-size:20px"></i>
                    <div>
                        <p style="font-size:13px;font-weight:600;color:{{ $fund->target_met ? '#10b981' : '#f59e0b' }};margin:0">Profits</p>
                        <p style="font-size:12px;color:var(--text-muted);margin:0">
                            @if($fund->target_met) Unlocked @else Locked — need 100% @endif
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded-3" style="background:rgba({{ $fund->target_met ? '16,185,129' : '245,158,11' }},0.08)">
                    <i class="fas fa-{{ $fund->target_met ? 'check-circle' : 'lock' }}" style="color:{{ $fund->target_met ? '#10b981' : '#f59e0b' }};font-size:20px"></i>
                    <div>
                        <p style="font-size:13px;font-weight:600;color:{{ $fund->target_met ? '#10b981' : '#f59e0b' }};margin:0">Capital</p>
                        <p style="font-size:12px;color:var(--text-muted);margin:0">
                            @if($fund->target_met) Unlocked @else Locked — need 100% @endif
                        </p>
                    </div>
                </div>
                @else
                <p style="font-size:13px;color:var(--text-muted)">Withdrawal rules apply once your fund is approved and active.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
