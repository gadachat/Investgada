@extends('layouts.admin')

@section('title', 'Fund Application Review')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="mb-4">
            <a href="{{ route('admin.funds.index') }}" style="color:var(--text-muted);font-size:13px;text-decoration:none">
                <i class="fas fa-arrow-left me-1"></i> Back to Fund Applications
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-3">
            {{-- Main: Details + Actions --}}
            <div class="col-lg-8 col-md-10 col-12">
                <div class="card-custom mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-1" style="font-weight:700;color:var(--text)">{{ $fund->reference }}</h4>
                            <p style="color:var(--text-muted);font-size:13px">
                                <i class="fas fa-user me-1"></i> {{ $fund->user->name }} ({{ $fund->user->email }})
                                · <span class="badge" style="background:rgba(99,102,241,0.15);color:#a855f7">{{ ucfirst($fund->applicant_type) }}</span>
                                · Applied {{ $fund->created_at->format('M d, Y') }}
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

                    <div class="row g-2 mb-3">
                        <div class="col-md-4 col-12">
                            <div class="rounded-3 p-2" style="background:var(--bg);border:1px solid var(--border)">
                                <p style="font-size:11px;color:var(--text-muted);margin:0">Requested</p>
                                <h6 style="font-weight:700;margin:0;color:var(--text)">${{ number_format($fund->requested_amount, 2) }}</h6>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="rounded-3 p-2" style="background:var(--bg);border:1px solid var(--border)">
                                <p style="font-size:11px;color:var(--text-muted);margin:0">Approved</p>
                                <h6 style="font-weight:700;margin:0;color:var(--primary)">{{ $fund->approved_amount ? '$' . number_format($fund->approved_amount, 2) : '—' }}</h6>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="rounded-3 p-2" style="background:var(--bg);border:1px solid var(--border)">
                                <p style="font-size:11px;color:var(--text-muted);margin:0">Team Production</p>
                                <h6 style="font-weight:700;margin:0;color:#10b981">${{ number_format($fund->team_production, 2) }}</h6>
                            </div>
                        </div>
                    </div>

                    @if($fund->purpose)
                    <div class="mb-3">
                        <p style="font-size:12px;color:var(--text-muted);margin:0 0 4px">Purpose / Strategy</p>
                        <div class="rounded-3 p-3" style="background:var(--bg);border:1px solid var(--border);font-size:14px;color:var(--text)">
                            {{ $fund->purpose }}
                        </div>
                    </div>
                    @endif

                    @if($fund->admin_note)
                    <div class="rounded-3 p-3 mb-3" style="background:rgba(99,102,241,0.05);border:1px solid rgba(99,102,241,0.1)">
                        <p style="font-size:12px;color:var(--primary);margin:0 0 4px"><i class="fas fa-shield-alt me-1"></i> Admin Note</p>
                        <p style="font-size:13px;color:var(--text);margin:0">{{ $fund->admin_note }}</p>
                    </div>
                    @endif

                    @if(in_array($fund->status, ['approved', 'completed']))
                    {{-- Progress --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span style="font-size:13px;color:var(--text-muted)">Team Production</span>
                            <span style="font-size:13px;font-weight:600;color:var(--text)">
                                ${{ number_format($fund->team_production, 2) }} / ${{ number_format($fund->target_production, 2) }} ({{ $fund->progressPercent() }}%)
                            </span>
                        </div>
                        <div style="background:rgba(99,102,241,0.1);border-radius:8px;height:10px;overflow:hidden">
                            <div style="background:linear-gradient(90deg,#6366f1,#a855f7);height:100%;width:{{ $fund->progressPercent() }}%;border-radius:8px"></div>
                        </div>
                    </div>

                    {{-- Manual Production Update --}}
                    <form method="POST" action="{{ route('admin.funds.production', $fund) }}" class="d-flex gap-2 align-items-end">
                        @csrf
                        <div>
                            <label style="font-size:12px;color:var(--text-muted)">Update Team Production ($)</label>
                            <input type="number" name="production_amount" class="form-control" style="width:160px;background:var(--bg);border:1px solid var(--border);color:var(--text)"
                                   value="{{ $fund->team_production }}" step="0.01" min="0">
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-sync me-1"></i> Update
                        </button>
                    </form>
                    @endif
                </div>

                {{-- Team Members --}}
                @if(in_array($fund->status, ['approved', 'completed']) && $teamMembers->count() > 0)
                <div class="card-custom">
                    <div class="p-3" style="border-bottom:1px solid var(--border)">
                        <h6 class="mb-0" style="font-weight:600;color:var(--text)"><i class="fas fa-users me-1" style="color:var(--primary)"></i> Team Members</h6>
                    </div>
                    <div style="overflow-x:auto">
                        <table class="table table-hover mb-0" style="color:var(--text)">
                            <thead>
                                <tr style="color:var(--text-muted);font-size:12px;text-transform:uppercase">
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Investments</th>
                                    <th>Volume</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $total = 0; @endphp
                                @foreach($teamMembers as $member)
                                    @php $vol = $member->investments->sum('amount'); $total += $vol; @endphp
                                    <tr>
                                        <td style="font-size:13px;font-weight:600">{{ $member->name }}</td>
                                        <td style="font-size:13px;color:var(--text-muted)">{{ $member->email }}</td>
                                        <td style="font-size:13px">{{ $member->investments->count() }}</td>
                                        <td style="font-size:13px;font-weight:600;color:#10b981">${{ number_format($vol, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="border-top:2px solid var(--border)">
                                    <td colspan="3" style="font-weight:700">Total Team Volume</td>
                                    <td style="font-weight:700;color:var(--primary)">${{ number_format($total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar: Actions --}}
            <div class="col-lg-4 col-md-6 col-12">
                @if($fund->status === 'pending')
                {{-- Approve Form --}}
                <div class="card-custom mb-3" style="border:1px solid rgba(16,185,129,0.2)">
                    <h6 class="mb-3" style="font-weight:600;color:#10b981"><i class="fas fa-check-circle me-1"></i> Approve & Fund</h6>
                    <form method="POST" action="{{ route('admin.funds.approve', $fund) }}">
                        @csrf
                        <div class="mb-2">
                            <label style="font-size:12px;color:var(--text-muted)">Approved Amount ($)</label>
                            <input type="number" name="approved_amount" class="form-control" required step="0.01" min="1"
                                   value="{{ $fund->requested_amount }}"
                                   style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                        </div>
                        <div class="mb-2">
                            <label style="font-size:12px;color:var(--text-muted)">Admin Note (optional)</label>
                            <textarea name="admin_note" rows="2" class="form-control" style="background:var(--bg);border:1px solid var(--border);color:var(--text)" placeholder="Terms, conditions, notes..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check me-1"></i> Approve & Credit Wallet
                        </button>
                    </form>
                </div>

                {{-- Reject Form --}}
                <div class="card-custom" style="border:1px solid rgba(239,68,68,0.2)">
                    <h6 class="mb-3" style="font-weight:600;color:#ef4444"><i class="fas fa-times-circle me-1"></i> Reject</h6>
                    <form method="POST" action="{{ route('admin.funds.reject', $fund) }}">
                        @csrf
                        <div class="mb-2">
                            <label style="font-size:12px;color:var(--text-muted)">Reason (optional)</label>
                            <textarea name="admin_note" rows="2" class="form-control" style="background:var(--bg);border:1px solid var(--border);color:var(--text)" placeholder="Reason for rejection..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-times me-1"></i> Reject Application
                        </button>
                    </form>
                </div>
                @elseif(in_array($fund->status, ['approved', 'completed']))
                {{-- Revoke Form --}}
                <div class="card-custom" style="border:1px solid rgba(239,68,68,0.2)">
                    <h6 class="mb-3" style="font-weight:600;color:#ef4444"><i class="fas fa-ban me-1"></i> Revoke Fund</h6>
                    <form method="POST" action="{{ route('admin.funds.revoke', $fund) }}">
                        @csrf
                        <div class="mb-2">
                            <label style="font-size:12px;color:var(--text-muted)">Reason (required)</label>
                            <textarea name="admin_note" rows="3" class="form-control" required style="background:var(--bg);border:1px solid var(--border);color:var(--text)" placeholder="Reason for revocation..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to revoke this fund?')">
                            <i class="fas fa-ban me-1"></i> Revoke Fund
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
