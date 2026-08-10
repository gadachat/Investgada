@extends('layouts.admin')

@section('page-title', 'User: ' . $user->name)

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.users.index') }}" style="color: var(--text-dim); font-size: 13px; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> All Users
                </a>
            </div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0; font-size: 22px;">
                {{ $user->name }}
                <span style="font-size: 14px; color: var(--text-dim); font-weight: 400;">{{ $user->email }}</span>
            </h2>
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

    <div class="row g-4">
        <!-- Left column: Profile + Controls -->
        <div class="col-lg-4">
            <!-- Profile card -->
            <div class="card-custom" style="padding: 24px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 20px;">
                    <div style="width: 56px; height: 56px; border-radius: 14px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; font-size: 22px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size: 16px; font-weight: 700; color: var(--text-bright);">{{ $user->name }}</div>
                        <div style="font-size: 12px; color: var(--text-dim);">@{{ $user->username }}</div>
                    </div>
                </div>

                <div style="display: grid; gap: 8px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Email</span>
                        <span style="color: var(--text);">{{ $user->email }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Phone</span>
                        <span style="color: var(--text);">{{ $user->phone ?: '—' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Country</span>
                        <span style="color: var(--text);">{{ $user->country ?: '—' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Referral Code</span>
                        <span style="color: #818cf8; font-family: monospace;">{{ $user->referral_code }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Status</span>
                        @php $sc = ['active' => '#10b981', 'inactive' => '#64748b', 'suspended' => '#f59e0b', 'banned' => '#ef4444']; @endphp
                        <span style="color: {{ $sc[$user->status] ?? '#64748b' }}; font-weight: 600; text-transform: capitalize;">{{ $user->status }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Role</span>
                        <span style="color: var(--text); text-transform: capitalize;">{{ str_replace('_', ' ', $user->role) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">KYC</span>
                        <span style="color: {{ $user->kyc_status === 'verified' ? '#10b981' : ($user->kyc_status === 'rejected' ? '#ef4444' : '#f59e0b') }}; text-transform: capitalize;">{{ $user->kyc_status }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Rank</span>
                        <span style="color: {{ $user->rank?->badge_color ?? 'var(--text-dim)' }};">{{ $user->rank?->name ?? 'None' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <span style="color: var(--text-dim);">Sponsor</span>
                        <span style="color: var(--text);">{{ $user->sponsor?->name ?? '—' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                        <span style="color: var(--text-dim);">Joined</span>
                        <span style="color: var(--text);">{{ $user->created_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Send Test Funds -->
            <div class="card-custom" style="padding: 20px; margin-bottom: 16px;">
                <h6 style="color: var(--text-bright); font-weight: 700; margin-bottom: 14px;">
                    <i class="fas fa-paper-plane" style="color: #10b981;"></i> Send Test Funds
                </h6>
                <form method="POST" action="{{ route('admin.users.send-funds', $user) }}">
                    @csrf
                    <div class="mb-2">
                        <label style="font-size: 11px; color: var(--text-muted);">Wallet</label>
                        <select name="wallet_type" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px 12px; font-size: 13px;" required>
                            <option value="deposit">Deposit Wallet</option>
                            <option value="interest">Interest Wallet</option>
                            <option value="commission">Commission Wallet</option>
                            <option value="bonus">Bonus Wallet</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label style="font-size: 11px; color: var(--text-muted);">Amount ($)</label>
                        <input type="number" name="amount" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px 12px; font-size: 13px;" step="0.01" min="0.01" required placeholder="100.00">
                    </div>
                    <div class="mb-2">
                        <label style="font-size: 11px; color: var(--text-muted);">Note (optional)</label>
                        <input type="text" name="note" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px 12px; font-size: 13px;" placeholder="Test funds for demo">
                    </div>
                    <button type="submit" class="btn w-100" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 10px; padding: 10px; font-size: 13px; font-weight: 600;" onclick="return confirm('Send test funds to this user?')">
                        <i class="fas fa-paper-plane"></i> Send Funds
                    </button>
                </form>
            </div>

            <!-- Deduct Funds -->
            <div class="card-custom" style="padding: 20px; margin-bottom: 16px;">
                <h6 style="color: var(--text-bright); font-weight: 700; margin-bottom: 14px;">
                    <i class="fas fa-minus-circle" style="color: #ef4444;"></i> Deduct Funds
                </h6>
                <form method="POST" action="{{ route('admin.users.deduct-funds', $user) }}">
                    @csrf
                    <div class="mb-2">
                        <label style="font-size: 11px; color: var(--text-muted);">Wallet</label>
                        <select name="wallet_type" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px 12px; font-size: 13px;" required>
                            <option value="deposit">Deposit Wallet</option>
                            <option value="interest">Interest Wallet</option>
                            <option value="commission">Commission Wallet</option>
                            <option value="bonus">Bonus Wallet</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label style="font-size: 11px; color: var(--text-muted);">Amount ($)</label>
                        <input type="number" name="amount" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px 12px; font-size: 13px;" step="0.01" min="0.01" required placeholder="50.00">
                    </div>
                    <button type="submit" class="btn w-100" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; border-radius: 10px; padding: 10px; font-size: 13px; font-weight: 600;" onclick="return confirm('Deduct funds from this user?')">
                        <i class="fas fa-minus"></i> Deduct
                    </button>
                </form>
            </div>

            <!-- Account Controls -->
            <div class="card-custom" style="padding: 20px; margin-bottom: 16px;">
                <h6 style="color: var(--text-bright); font-weight: 700; margin-bottom: 14px;">
                    <i class="fas fa-shield-alt" style="color: #f59e0b;"></i> Account Controls
                </h6>

                <!-- Status changer -->
                <form method="POST" action="{{ route('admin.users.update-status', $user) }}" style="margin-bottom: 10px;">
                    @csrf
                    <div class="d-flex gap-2">
                        <select name="status" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px 12px; font-size: 13px;">
                            <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ $user->status === 'suspended' ? 'selected' : '' }}>Suspend</option>
                            <option value="banned" {{ $user->status === 'banned' ? 'selected' : '' }}>Ban</option>
                        </select>
                        <button type="submit" class="btn" style="background: var(--gradient-primary); color: white; border: none; border-radius: 8px; padding: 8px 16px; font-size: 12px; font-weight: 600;">Update</button>
                    </div>
                    <input type="text" name="reason" class="form-control mt-2" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 6px 12px; font-size: 12px;" placeholder="Reason (optional)">
                </form>

                <!-- Role changer -->
                <form method="POST" action="{{ route('admin.users.update-role', $user) }}" style="margin-bottom: 10px;">
                    @csrf
                    <div class="d-flex gap-2">
                        <select name="role" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px 12px; font-size: 13px;">
                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="super_admin" {{ $user->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        </select>
                        <button type="submit" class="btn" style="background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.3); color: #f59e0b; border-radius: 8px; padding: 8px 16px; font-size: 12px; font-weight: 600;">Set Role</button>
                    </div>
                </form>

                <!-- Applicant type (Fund Program) -->
                <div style="margin-bottom: 10px;">
                    <div style="font-size: 11px; color: var(--text-dim); margin-bottom: 4px; font-weight: 600;">FUND PROGRAM TYPE</div>
                    <form method="POST" action="{{ route('admin.users.applicant-type', $user) }}">
                        @csrf
                        <div class="d-flex gap-2">
                            <select name="applicant_type" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px 12px; font-size: 13px;">
                                <option value="user" {{ $user->applicant_type === 'user' ? 'selected' : '' }}>Regular User</option>
                                <option value="marketer" {{ $user->applicant_type === 'marketer' ? 'selected' : '' }}>Marketer</option>
                                <option value="leader" {{ $user->applicant_type === 'leader' ? 'selected' : '' }}>Leader</option>
                            </select>
                            <button type="submit" class="btn" style="background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.3); color: #6366f1; border-radius: 8px; padding: 8px 16px; font-size: 12px; font-weight: 600;">Set Type</button>
                        </div>
                    </form>
                    @if($user->is_fund_recipient)
                    <div style="margin-top: 6px; padding: 6px 10px; background: rgba(99,102,241,0.08); border-radius: 6px; font-size: 11px; color: var(--purple-3);">
                        <i class="fas fa-shield-alt"></i> Active fund recipient
                    </div>
                    @endif
                </div>

                <!-- Password reset -->
                <details style="margin-top: 10px;">
                    <summary style="cursor: pointer; font-size: 12px; color: #ef4444; font-weight: 600; padding: 6px 0;">Reset Password</summary>
                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" style="margin-top: 10px;">
                        @csrf
                        <input type="password" name="password" class="form-control mb-2" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px 12px; font-size: 13px;" placeholder="New password" required>
                        <input type="password" name="password_confirmation" class="form-control mb-2" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 8px; padding: 8px 12px; font-size: 13px;" placeholder="Confirm password" required>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input type="checkbox" name="notify_user" value="1" id="notify_{{ $user->id }}">
                            <label for="notify_{{ $user->id }}" style="font-size: 11px; color: var(--text-dim);">Notify user</label>
                        </div>
                        <button type="submit" class="btn w-100" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; border-radius: 8px; padding: 8px; font-size: 12px; font-weight: 600;" onclick="return confirm('Reset this user password?')">
                            Reset Password
                        </button>
                    </form>
                </details>
            </div>
        </div>

        <!-- Right column: Wallets + Transactions + Activity -->
        <div class="col-lg-8">
            <!-- Wallets -->
            <div class="card-custom" style="padding: 0; overflow: hidden; margin-bottom: 16px;">
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
                    <h5 style="color: var(--text-bright); font-weight: 700; margin: 0;">
                        <i class="fas fa-wallet" style="color: #6366f1;"></i> Wallets
                    </h5>
                </div>
                <div class="row g-0">
                    @php $walletIcons = ['deposit' => 'fa-wallet', 'interest' => 'fa-percentage', 'commission' => 'fa-hand-holding-usd', 'bonus' => 'fa-gift', 'withdrawal' => 'fa-arrow-up']; @endphp
                    @foreach(['deposit', 'interest', 'commission', 'bonus', 'withdrawal'] as $type)
                    @php $wallet = $user->wallets->where('type', $type)->first(); @endphp
                    <div class="col-md-4 col-6" style="padding: 16px; border-right: 1px solid rgba(51,65,85,0.1); border-bottom: 1px solid rgba(51,65,85,0.1);">
                        <div style="font-size: 11px; color: var(--text-dim); text-transform: capitalize; margin-bottom: 4px;">
                            <i class="fas {{ $walletIcons[$type] ?? 'fa-coins' }}"></i> {{ $type }} Wallet
                        </div>
                        <div style="font-size: 18px; font-weight: 700; color: var(--text-bright);">${{ number_format((float) ($wallet?->balance ?? 0), 2) }}</div>
                        @if($wallet && $wallet->locked_balance > 0)
                        <div style="font-size: 10px; color: #f59e0b; margin-top: 2px;">Locked: ${{ number_format((float) $wallet->locked_balance, 2) }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                <div style="padding: 12px 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; font-size: 13px;">
                    <span style="color: var(--text-dim);">Total Balance:</span>
                    @php $totalBalance = $user->wallets->sum(fn($w) => (float) $w->balance); @endphp
                    <span style="color: #10b981; font-weight: 700; font-size: 16px;">${{ number_format($totalBalance, 2) }}</span>
                </div>
            </div>

            <!-- Totals -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-icon purple"><i class="fas fa-chart-line"></i></div>
                        <div class="stat-label">Total Invested</div>
                        <div class="stat-value" style="font-size: 16px;">${{ number_format((float) $user->total_invested, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-icon green"><i class="fas fa-coins"></i></div>
                        <div class="stat-label">Total Earned</div>
                        <div class="stat-value" style="font-size: 16px;">${{ number_format((float) $user->total_earned, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-icon red"><i class="fas fa-arrow-up"></i></div>
                        <div class="stat-label">Withdrawn</div>
                        <div class="stat-value" style="font-size: 16px;">${{ number_format((float) $user->total_withdrawn, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="fas fa-user-plus"></i></div>
                        <div class="stat-label">Direct Referrals</div>
                        <div class="stat-value">{{ $directReferrals->count() }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card-custom" style="padding: 0; overflow: hidden; margin-bottom: 16px;">
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
                    <h5 style="color: var(--text-bright); font-weight: 700; margin: 0;">
                        <i class="fas fa-list" style="color: #818cf8;"></i> Recent Transactions
                    </h5>
                </div>
                @forelse($transactions as $tx)
                <div style="padding: 12px 20px; border-bottom: 1px solid rgba(51,65,85,0.1); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 13px; color: var(--text-bright); font-weight: 600; text-transform: capitalize;">{{ str_replace('_', ' ', $tx->type) }}</div>
                        <div style="font-size: 11px; color: var(--text-dim);">{{ $tx->description }} · {{ $tx->created_at->format('M d, Y H:i') }}</div>
                        @if($tx->type === 'admin_fund')
                        <span style="font-size: 9px; padding: 1px 6px; border-radius: 4px; background: rgba(16,185,129,0.15); color: #10b981; font-weight: 600;">TEST FUND</span>
                        @endif
                    </div>
                    <div style="font-size: 14px; font-weight: 700; color: {{ $tx->direction === 'credit' ? '#10b981' : '#ef4444' }};">
                        {{ $tx->direction === 'credit' ? '+' : '-' }}${{ number_format((float) $tx->amount, 2) }}
                    </div>
                </div>
                @empty
                <div style="padding: 30px; text-align: center; color: var(--text-dim); font-size: 13px;">No transactions yet.</div>
                @endforelse
            </div>

            <!-- Direct Referrals -->
            @if($directReferrals->isNotEmpty())
            <div class="card-custom" style="padding: 0; overflow: hidden;">
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
                    <h5 style="color: var(--text-bright); font-weight: 700; margin: 0;">
                        <i class="fas fa-users" style="color: #6366f1;"></i> Direct Referrals ({{ $directReferrals->count() }})
                    </h5>
                </div>
                @foreach($directReferrals as $ref)
                <div style="padding: 10px 20px; border-bottom: 1px solid rgba(51,65,85,0.1); display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 28px; height: 28px; border-radius: 8px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 11px;">
                            {{ strtoupper(substr($ref->name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="color: var(--text-bright);">{{ $ref->name }}</div>
                            <div style="color: var(--text-dim); font-size: 11px;">{{ $ref->email }}</div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 11px; color: {{ $ref->status === 'active' ? '#10b981' : '#64748b' }}; text-transform: capitalize;">{{ $ref->status }}</span>
                        <div style="font-size: 12px; color: var(--text);">${{ number_format((float) $ref->total_invested, 2) }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
