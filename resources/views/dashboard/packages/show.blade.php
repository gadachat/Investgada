@extends('layouts.dashboard')

@section('page-title', 'Invest — ' . $package->name)

@section('content')
<div class="fade-in">

    <!-- Breadcrumb -->
    <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-dim);">
        <a href="{{ route('dashboard.packages.index') }}" style="color: var(--purple-3); text-decoration: none;">Packages</a>
        <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
        <span style="color: var(--text-bright);">{{ $package->name }}</span>
    </div>

    <div class="row g-3">
        <!-- LEFT: Package info -->
        <div class="col-lg-7 col-md-8 col-12">
            <div class="card-custom" style="padding: 0; overflow: hidden;">
                @php
                    $catColors = [
                        'crypto' => 'linear-gradient(135deg, #f7931a, #f3ba2f)',
                        'forex'  => 'linear-gradient(135deg, #3b82f6, #6366f1)',
                        'stocks' => 'linear-gradient(135deg, #10b981, #14b8a6)',
                        'bonds'  => 'linear-gradient(135deg, #8b5cf6, #a855f7)',
                        'mixed'  => 'linear-gradient(135deg, #6366f1, #a855f7)',
                    ];
                    $gradient = $catColors[$package->category] ?? $catColors['mixed'];
                @endphp
                <div style="height: 4px; background: {{ $gradient }};"></div>

                <div style="padding: 24px;">
                    <!-- Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="width: 56px; height: 56px; border-radius: 14px; background: {{ $gradient }}; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                                <i class="fas fa-{{ $package->category === 'crypto' ? 'bitcoin-sign' : ($package->category === 'forex' ? 'dollar-sign' : ($package->category === 'stocks' ? 'chart-line' : ($package->category === 'bonds' ? 'landmark' : 'layer-group'))) }}"></i>
                            </div>
                            <div>
                                <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: var(--text-bright);">{{ $package->name }}</h3>
                                <div style="display: flex; gap: 6px; margin-top: 4px;">
                                    <span class="badge-custom badge-purple">{{ strtoupper($package->category) }}</span>
                                    <span class="badge-custom badge-info">{{ ucfirst($package->type) }}</span>
                                    @if($package->featured)
                                    <span class="badge-custom" style="background: var(--gradient-primary); color: white;"><i class="fas fa-star"></i> Popular</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($package->description)
                    <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 24px;">{{ $package->description }}</p>
                    @endif

                    <!-- Return rate highlight -->
                    <div style="background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(168,85,247,0.04)); border: 1px solid rgba(99,102,241,0.15); border-radius: 16px; padding: 24px; margin-bottom: 24px; text-align: center;">
                        <div style="font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Return per {{ $package->return_type }} cycle</div>
                        <div style="font-size: 48px; font-weight: 800; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">{{ $package->return_rate }}%</div>
                        <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Credited every {{ $package->cycle_days }} day(s) for {{ $package->duration_days }} days</div>
                    </div>

                    <!-- Package details grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 24px;">
                        <div style="padding: 14px; background: var(--bg-input); border-radius: 12px;">
                            <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase; margin-bottom: 4px;">Minimum</div>
                            <div style="font-size: 18px; font-weight: 700; color: var(--blue-1);">${{ number_format($package->min_amount, 2) }}</div>
                        </div>
                        <div style="padding: 14px; background: var(--bg-input); border-radius: 12px;">
                            <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase; margin-bottom: 4px;">Maximum</div>
                            <div style="font-size: 18px; font-weight: 700; color: var(--text-bright);">{{ $package->max_amount ? '$' . number_format($package->max_amount, 2) : 'No limit' }}</div>
                        </div>
                        <div style="padding: 14px; background: var(--bg-input); border-radius: 12px;">
                            <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase; margin-bottom: 4px;">Duration</div>
                            <div style="font-size: 18px; font-weight: 700; color: var(--text-bright);">{{ $package->duration_days }}d</div>
                        </div>
                    </div>

                    <!-- Calculation breakdown -->
                    <div style="padding: 18px; background: var(--bg-input); border-radius: 12px; margin-bottom: 24px;">
                        <h6 style="color: var(--text-bright); font-size: 14px; font-weight: 600; margin-bottom: 14px;">
                            <i class="fas fa-calculator" style="color: var(--purple-3);"></i> Investment Calculation
                        </h6>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; justify-content: space-between; font-size: 13px;">
                                <span style="color: var(--text-muted);">Return rate</span>
                                <span style="color: var(--text-bright); font-weight: 600;">{{ $package->return_rate }}% per {{ $package->cycle_days }} days</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 13px;">
                                <span style="color: var(--text-muted);">Total payout cycles</span>
                                <span style="color: var(--text-bright); font-weight: 600;">{{ intdiv($package->duration_days, $package->cycle_days) }} cycles</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 13px;">
                                <span style="color: var(--text-muted);">Principal returned</span>
                                <span style="color: {{ $package->principal_return ? 'var(--green)' : 'var(--red)' }}; font-weight: 600;">{{ $package->principal_return ? 'Yes' : 'No' }}</span>
                            </div>
                            @if($package->total_return_cap > 0)
                            <div style="display: flex; justify-content: space-between; font-size: 13px;">
                                <span style="color: var(--text-muted);">Total return cap</span>
                                <span style="color: var(--text-bright); font-weight: 600;">{{ $package->total_return_cap }}% of principal</span>
                            </div>
                            @endif
                            <div style="display: flex; justify-content: space-between; font-size: 13px;">
                                <span style="color: var(--text-muted);">Compounding</span>
                                <span style="color: {{ $package->compounding ? 'var(--green)' : 'var(--text-dim)' }}; font-weight: 600;">{{ $package->compounding ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Risk disclosure -->
                    <div style="padding: 14px; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 10px; font-size: 12px; color: var(--text-muted); line-height: 1.5;">
                        <i class="fas fa-exclamation-triangle" style="color: var(--yellow);"></i>
                        <strong>Risk Notice:</strong> All investments carry risk. Past performance does not guarantee future returns. Only invest what you can afford to lose.
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Invest form -->
        <div class="col-lg-5">
            <div class="card-custom" style="position: sticky; top: 80px;">
                <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 20px;">
                    <i class="fas fa-rocket" style="color: var(--purple-3);"></i> Start Investing
                </h5>

                <!-- Wallet balance -->
                <div style="padding: 16px; background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(168,85,247,0.04)); border: 1px solid rgba(99,102,241,0.15); border-radius: 12px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase;">Deposit Wallet</div>
                            <div style="font-size: 24px; font-weight: 700; color: var(--text-bright); margin-top: 4px;">${{ number_format($wallet?->balance ?? 0, 2) }}</div>
                        </div>
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    @if($wallet && $wallet->balance < $package->min_amount)
                    <div style="margin-top: 10px; font-size: 12px; color: var(--red);">
                        <i class="fas fa-exclamation-circle"></i> Insufficient balance. You need at least ${{ number_format($package->min_amount, 2) }}.
                        <a href="{{ route('dashboard.deposit.create') }}" style="color: var(--purple-3); text-decoration: none; font-weight: 600;">Deposit now →</a>
                    </div>
                    @endif
                </div>

                <!-- Invest form -->
                <form method="POST" action="{{ route('dashboard.packages.invest', $package->slug) }}">
                    @csrf

                    <!-- Amount input -->
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label style="font-size: 13px; color: var(--text-muted); font-weight: 500; margin-bottom: 6px;">Investment Amount (USD)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-dim); font-size: 16px; font-weight: 600;">$</span>
                            <input type="number" name="amount" id="investAmount"
                                class="form-control" style="padding-left: 28px; font-size: 18px; font-weight: 600;"
                                min="{{ $package->min_amount }}"
                                max="{{ $package->max_amount ?? '' }}"
                                step="0.01"
                                value="{{ old('amount', $package->min_amount) }}"
                                oninput="calculateReturns()"
                                required>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 6px; font-size: 11px; color: var(--text-dim);">
                            <span>Min: ${{ number_format($package->min_amount, 2) }}</span>
                            @if($package->max_amount)
                            <span>Max: ${{ number_format($package->max_amount, 2) }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Quick amount buttons -->
                    <div style="display: flex; gap: 6px; margin-bottom: 20px;">
                        <button type="button" class="quick-amt-btn" onclick="setAmount({{ $package->min_amount }})">${{ number_format($package->min_amount, 0) }}</button>
                        @if($package->min_amount < 500)
                        <button type="button" class="quick-amt-btn" onclick="setAmount(500)">$500</button>
                        @endif
                        @if($package->min_amount < 1000)
                        <button type="button" class="quick-amt-btn" onclick="setAmount(1000)">$1,000</button>
                        @endif
                        @if($package->min_amount < 5000)
                        <button type="button" class="quick-amt-btn" onclick="setAmount(5000)">$5,000</button>
                        @endif
                        @if($package->min_amount < 10000)
                        <button type="button" class="quick-amt-btn" onclick="setAmount(10000)">$10,000</button>
                        @endif
                    </div>

                    <!-- Live calculation -->
                    <div style="background: var(--bg-input); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                        <h6 style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 12px;">Projected Returns</h6>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; justify-content: space-between; font-size: 13px;">
                                <span style="color: var(--text-muted);">Per cycle payout</span>
                                <span style="color: var(--text-bright); font-weight: 600;" id="perCycle">$0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 13px;">
                                <span style="color: var(--text-muted);">Total payout cycles</span>
                                <span style="color: var(--text-bright); font-weight: 600;">{{ intdiv($package->duration_days, $package->cycle_days) }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 13px;">
                                <span style="color: var(--text-muted);">Total expected return</span>
                                <span style="color: var(--green); font-weight: 700; font-size: 16px;" id="totalReturn">$0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 13px; padding-top: 10px; border-top: 1px solid var(--border);">
                                <span style="color: var(--text-muted);">Net profit</span>
                                <span style="color: var(--purple-3); font-weight: 700; font-size: 16px;" id="netProfit">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-gradient" style="width: 100%; padding: 16px; font-size: 15px;">
                        <i class="fas fa-check-circle"></i> Confirm Investment
                    </button>

                    <p style="text-align: center; font-size: 11px; color: var(--text-dim); margin-top: 12px;">
                        By investing, you agree to the platform's Terms of Service.
                        Funds will be deducted from your deposit wallet.
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.quick-amt-btn {
    flex: 1;
    background: var(--bg-input);
    border: 1px solid var(--border);
    color: var(--text-muted);
    padding: 8px 4px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}
.quick-amt-btn:hover {
    border-color: var(--purple-1);
    color: var(--text-bright);
    background: rgba(99,102,241,0.08);
}
</style>

<script>
var returnRate = {{ $package->return_rate }};
var cycleDays = {{ $package->cycle_days }};
var durationDays = {{ $package->duration_days }};
var cycles = Math.floor(durationDays / cycleDays);

function calculateReturns() {
    var amount = parseFloat(document.getElementById('investAmount').value) || 0;
    var perCycle = (amount * returnRate / 100);
    var total = perCycle * cycles;

    document.getElementById('perCycle').textContent = '$' + perCycle.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('totalReturn').textContent = '$' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('netProfit').textContent = '$' + (total - amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function setAmount(val) {
    document.getElementById('investAmount').value = val;
    calculateReturns();
}

// Initial calculation
calculateReturns();
</script>
@endsection
