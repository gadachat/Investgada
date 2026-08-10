@extends('layouts.admin')

@section('title', 'Fund Program Settings')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="mb-4">
            <a href="{{ route('admin.funds.index') }}" style="color:var(--text-muted);font-size:13px;text-decoration:none">
                <i class="fas fa-arrow-left me-1"></i> Back to Fund Applications
            </a>
            <h2 class="mt-2 mb-1" style="font-weight:700;color:var(--text)">
                <i class="fas fa-cog me-2" style="color:var(--primary)"></i> Fund Program Settings
            </h2>
            <p style="color:var(--text-muted);font-size:14px">Configure the rules for the marketer/leader fund program</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            <div class="col-lg-8 col-md-10 col-12">
                <form method="POST" action="{{ route('admin.funds.settings.update') }}">
                    @csrf

                    {{-- Program Enabled --}}
                    <div class="card-custom mb-3">
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" name="fund_program_enabled" value="1" class="form-check-input" id="programEnabled"
                                {{ ($settings['fund_program_enabled'] ?? 'true') === 'true' ? 'checked' : '' }}>
                            <label for="programEnabled" style="font-weight:600;color:var(--text);font-size:15px">Fund Program Enabled</label>
                        </div>
                        <p style="font-size:13px;color:var(--text-muted);margin:0">Enable or disable the entire fund application system</p>
                    </div>

                    {{-- Amount Limits --}}
                    <div class="card-custom mb-3">
                        <h6 class="mb-3" style="font-weight:600;color:var(--text)"><i class="fas fa-dollar-sign me-1" style="color:var(--primary)"></i> Amount Limits</h6>
                        <div class="row g-3">
                            <div class="col-md-6 col-12">
                                <label style="font-size:13px;color:var(--text-muted)">Minimum Fund Amount ($)</label>
                                <input type="number" name="min_fund_amount" class="form-control" step="0.01" min="0"
                                       value="{{ $settings['min_fund_amount'] ?? 100 }}"
                                       style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                            </div>
                            <div class="col-md-6 col-12">
                                <label style="font-size:13px;color:var(--text-muted)">Maximum Fund Amount ($)</label>
                                <input type="number" name="max_fund_amount" class="form-control" step="0.01" min="1"
                                       value="{{ $settings['max_fund_amount'] ?? 100000 }}"
                                       style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                            </div>
                        </div>
                    </div>

                    {{-- Team Target --}}
                    <div class="card-custom mb-3">
                        <h6 class="mb-3" style="font-weight:600;color:var(--text)"><i class="fas fa-bullseye me-1" style="color:var(--primary)"></i> Team Target</h6>
                        <label style="font-size:13px;color:var(--text-muted)">Team Target Percent (%) — Team must produce X% of funded capital</label>
                        <input type="number" name="team_target_percent" class="form-control" step="1" min="1" max="200"
                               value="{{ $settings['team_target_percent'] ?? 100 }}"
                               style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                        <small style="color:var(--text-muted);font-size:12px">Default: 100%. Set lower to unlock faster, higher for stricter requirements.</small>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="auto_calculate_production" value="1" class="form-check-input" id="autoCalc"
                                {{ ($settings['auto_calculate_production'] ?? 'true') === 'true' ? 'checked' : '' }}>
                            <label for="autoCalc" style="font-size:13px;color:var(--text)">Auto-calculate team production from downline investments</label>
                        </div>
                    </div>

                    {{-- Withdrawal Rules --}}
                    <div class="card-custom mb-3">
                        <h6 class="mb-3" style="font-weight:600;color:var(--text)"><i class="fas fa-shield-alt me-1" style="color:var(--primary)"></i> Withdrawal Rules</h6>
                        <p style="font-size:13px;color:var(--text-muted);margin-bottom:12px">Control what fund recipients can withdraw before reaching the team target:</p>

                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" name="allow_commission_withdrawal" value="1" class="form-check-input" id="allowCommission"
                                {{ ($settings['allow_commission_withdrawal'] ?? 'true') === 'true' ? 'checked' : '' }}>
                            <label for="allowCommission" style="font-size:14px;color:var(--text)">
                                <i class="fas fa-check-circle me-1" style="color:#10b981"></i> Allow commission withdrawals (before target)
                            </label>
                            <p style="font-size:12px;color:var(--text-muted);margin-left:24px">Referral commissions, matching bonuses, leadership bonuses</p>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" name="allow_profit_withdrawal" value="1" class="form-check-input" id="allowProfit"
                                {{ ($settings['allow_profit_withdrawal'] ?? 'false') === 'true' ? 'checked' : '' }}>
                            <label for="allowProfit" style="font-size:14px;color:var(--text)">
                                <i class="fas fa-lock me-1" style="color:#f59e0b"></i> Allow profit withdrawals (before target)
                            </label>
                            <p style="font-size:12px;color:var(--text-muted);margin-left:24px">Investment profits, daily ROI, auto-trade profits</p>
                        </div>

                        <div class="form-check form-switch">
                            <input type="checkbox" name="allow_capital_withdrawal" value="1" class="form-check-input" id="allowCapital"
                                {{ ($settings['allow_capital_withdrawal'] ?? 'false') === 'true' ? 'checked' : '' }}>
                            <label for="allowCapital" style="font-size:14px;color:var(--text)">
                                <i class="fas fa-lock me-1" style="color:#f59e0b"></i> Allow capital withdrawals (before target)
                            </label>
                            <p style="font-size:12px;color:var(--text-muted);margin-left:24px">Withdrawal of the originally funded capital amount</p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="padding:12px 32px;font-weight:600">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                </form>
            </div>

            {{-- Info --}}
            <div class="col-lg-4 col-md-6 col-12">
                <div class="card-custom" style="position:sticky;top:80px">
                    <h6 style="font-weight:600;color:var(--primary)"><i class="fas fa-info-circle me-1"></i> How It Works</h6>
                    <ul style="font-size:13px;color:var(--text-muted);padding-left:18px;line-height:1.8">
                        <li>Marketer or leader applies for funds</li>
                        <li>Admin approves with an amount → wallet credited</li>
                        <li>Team target = funded amount × target%</li>
                        <li>Downline investments auto-track as "team production"</li>
                        <li>Commission withdrawals: available immediately</li>
                        <li>Profit & capital: locked until team hits target</li>
                        <li>Admin can override any rule here at any time</li>
                        <li>Admin can manually update team production</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
