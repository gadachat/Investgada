@extends('layouts.dashboard')

@section('title', 'Rank Advancement')

@section('content')
<div class="page-content">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Current Rank Banner --}}
    <div class="rounded-3 p-4 mb-4" style="background:linear-gradient(135deg,{{ $currentRank->badge_color }}22,{{ $currentRank->badge_color }}08);border:1px solid {{ $currentRank->badge_color }}44">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div style="width:60px;height:60px;border-radius:50%;background:{{ $currentRank->badge_color }};display:flex;align-items:center;justify-content:center;font-size:24px">
                    <i class="fas fa-medal" style="color:white"></i>
                </div>
                <div>
                    <p style="font-size:12px;color:var(--text-muted);margin:0">Current Rank</p>
                    <h3 style="font-weight:800;color:{{ $currentRank->badge_color }};margin:0">{{ $currentRank->name }}</h3>
                    <div style="font-size:12px;color:var(--text-muted)">
                        @if($currentRank->salary_bonus > 0)
                            <span class="badge me-1" style="background:rgba(245,158,11,0.15);color:#f59e0b">Salary: ${{ number_format((float)$currentRank->salary_bonus) }}/mo</span>
                        @endif
                        <span class="badge" style="background:rgba(99,102,241,0.15);color:#a855f7">Matching: {{ number_format((float)$currentRank->matching_bonus_percent, 1) }}%</span>
                        @if($currentRank->profit_share_percent > 0)
                            <span class="badge" style="background:rgba(16,185,129,0.15);color:#10b981">Profit Share: {{ number_format((float)$currentRank->profit_share_percent, 1) }}%</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-end">
                @if($nextRank)
                <p style="font-size:12px;color:var(--text-muted);margin:0">Next Rank</p>
                <h5 style="font-weight:700;color:var(--text);margin:0">{{ $nextRank->name }}</h5>
                <div style="width:200px;margin-top:6px">
                    <div class="progress" style="height:8px;background:var(--border)">
                        <div class="progress-bar" style="width:{{ $nextRankProgress['overall_progress'] }}%;background:linear-gradient(90deg,{{ $currentRank->badge_color }},{{ $nextRank->badge_color }})"></div>
                    </div>
                    <small style="font-size:11px;color:var(--text-muted)">{{ $nextRankProgress['overall_progress'] }}% qualified</small>
                </div>
                @else
                <p style="font-size:14px;color:#f59e0b;font-weight:600;margin:0"><i class="fas fa-crown me-1"></i> Highest Rank Achieved!</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                <p style="font-size:11px;color:var(--text-muted);margin:0">Personal Investment</p>
                <h5 style="font-weight:700;color:var(--text);margin:0">${{ number_format($userStats['personal_investment'], 2) }}</h5>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                <p style="font-size:11px;color:var(--text-muted);margin:0">Direct Referrals</p>
                <h5 style="font-weight:700;color:var(--text);margin:0">{{ $userStats['direct_referrals'] }}</h5>
                <small style="font-size:11px;color:#10b981">{{ $userStats['active_direct_referrals'] }} active</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                <p style="font-size:11px;color:var(--text-muted);margin:0">Team Volume</p>
                <h5 style="font-weight:700;color:var(--text);margin:0">${{ number_format($userStats['team_volume'], 2) }}</h5>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border)">
                <p style="font-size:11px;color:var(--text-muted);margin:0">Total Downline</p>
                <h5 style="font-weight:700;color:var(--text);margin:0">{{ $userStats['total_downline'] }}</h5>
            </div>
        </div>
    </div>

    {{-- All Ranks Timeline --}}
    <div class="card-custom mb-4">
        <div class="p-3" style="border-bottom:1px solid var(--border)">
            <h5 class="mb-0" style="font-weight:600;color:var(--text)"><i class="fas fa-trophy me-1" style="color:#f59e0b"></i> Rank Progression</h5>
        </div>
        <div style="padding:20px">
            @foreach($rankProgress as $rp)
            @php $rank = $rp['rank']; @endphp
            <div style="display:flex;gap:16px;margin-bottom:20px;position:relative">
                {{-- Timeline dot --}}
                <div style="flex-shrink:0;width:48px;height:48px;border-radius:50%;background:{{ $rp['is_achieved'] ? $rank->badge_color : 'var(--border)' }};display:flex;align-items:center;justify-content:center;position:relative;z-index:2">
                    @if($rp['is_achieved'])
                        <i class="fas fa-check" style="color:white;font-size:18px"></i>
                    @else
                        <i class="fas fa-medal" style="color:var(--text-muted);font-size:18px"></i>
                    @endif
                </div>

                {{-- Connector line --}}
                @if(!$loop->last)
                <div style="position:absolute;left:24px;top:48px;width:2px;height:calc(100% - 24px);background:{{ $rp['is_achieved'] ? $rank->badge_color : 'var(--border)' }};opacity:0.5"></div>
                @endif

                {{-- Rank content --}}
                <div style="flex:1" class="rounded-3 p-3" style="border:1px solid {{ $rp['is_current'] ? $rank->badge_color.'66' : 'var(--border)' }};background:{{ $rp['is_current'] ? $rank->badge_color.'08' : 'var(--bg)' }}">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                        <div>
                            <h6 style="font-weight:700;color:{{ $rp['is_achieved'] ? $rank->badge_color : 'var(--text)' }};margin:0">{{ $rank->name }}</h6>
                            @if($rp['is_current'])
                                <span class="badge" style="background:{{ $rank->badge_color }};color:white;font-size:10px">CURRENT</span>
                            @elseif($rp['is_achieved'])
                                <span class="badge" style="background:rgba(16,185,129,0.15);color:#10b981;font-size:10px">ACHIEVED</span>
                            @elseif($rp['is_next'])
                                <span class="badge" style="background:rgba(245,158,11,0.15);color:#f59e0b;font-size:10px">NEXT GOAL</span>
                            @endif
                        </div>
                        <div style="font-size:12px;color:var(--text-muted)">
                            @if($rank->salary_bonus > 0)<span class="badge me-1" style="background:rgba(245,158,11,0.1);color:#f59e0b">Salary ${{ number_format((float)$rank->salary_bonus) }}</span>@endif
                            <span class="badge" style="background:rgba(99,102,241,0.1);color:#a855f7">Match {{ number_format((float)$rank->matching_bonus_percent, 1) }}%</span>
                            @if($rank->profit_share_percent > 0)<span class="badge ms-1" style="background:rgba(16,185,129,0.1);color:#10b981">Share {{ number_format((float)$rank->profit_share_percent, 1) }}%</span>@endif
                        </div>
                    </div>

                    {{-- Requirements --}}
                    <div class="row g-2 mt-2">
                        @foreach($rp['requirements'] as $req)
                        <div class="col-md-{{ $rp['requirements']->count() <= 3 ? '4' : '6' }} col-12">
                            <div style="padding:8px;border-radius:8px;background:{{ $req['met'] ? 'rgba(16,185,129,0.05)' : 'var(--bg)' }};border:1px solid {{ $req['met'] ? 'rgba(16,185,129,0.15)' : 'var(--border)' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span style="font-size:11px;color:var(--text-muted)">{{ $req['label'] }}</span>
                                    @if($req['met'])
                                    <i class="fas fa-check-circle" style="color:#10b981;font-size:14px"></i>
                                    @else
                                    <i class="fas fa-circle" style="color:var(--border);font-size:10px"></i>
                                    @endif
                                </div>
                                <div style="font-size:13px;font-weight:600;color:var(--text);margin-top:2px">
                                    @if($req['format'] === 'currency')
                                        ${{ number_format($req['current'], 0) }} / ${{ number_format($req['required'], 0) }}
                                    @else
                                        {{ $req['current'] }} / {{ $req['required'] }}
                                    @endif
                                </div>
                                @if(!$req['met'] && $req['required'] > 0)
                                <div class="progress mt-1" style="height:4px;background:var(--border)">
                                    <div class="progress-bar" style="width:{{ $req['progress'] }}%;background:{{ $req['met'] ? '#10b981' : '#f59e0b' }}"></div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @if(!$rp['is_achieved'])
                    <div class="mt-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px;background:var(--border)">
                                <div class="progress-bar" style="width:{{ $rp['overall_progress'] }}%;background:linear-gradient(90deg,#f59e0b,#fbbf24)"></div>
                            </div>
                            <span style="font-size:12px;font-weight:600;color:#f59e0b">{{ $rp['overall_progress'] }}%</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Rank Promotion History --}}
    @if($rankHistory->count() > 0)
    <div class="card-custom">
        <div class="p-3" style="border-bottom:1px solid var(--border)">
            <h5 class="mb-0" style="font-weight:600;color:var(--text)"><i class="fas fa-history me-1" style="color:var(--text-muted)"></i> Rank History</h5>
        </div>
        <div style="overflow-x:auto">
            <table class="table table-hover mb-0" style="color:var(--text)">
                <thead><tr style="color:var(--text-muted);font-size:12px;text-transform:uppercase">
                    <th>Date</th><th>Event</th><th>Bonus</th>
                </tr></thead>
                <tbody>
                    @foreach($rankHistory as $hist)
                    <tr>
                        <td style="font-size:13px">{{ $hist->created_at->format('M d, Y') }}</td>
                        <td style="font-size:13px">{{ $hist->description }}</td>
                        <td style="font-size:13px;font-weight:600;color:#10b981">${{ number_format((float)$hist->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
