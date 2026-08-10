@extends('layouts.dashboard')

@section('page-title', 'Withdraw Funds')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 24px; font-size: 22px;"><i class="fas fa-arrow-up" style="color: var(--red);"></i> Withdraw Funds</h2>

    @if(session('success'))
    <div style="background: var(--green-bg); border: 1px solid rgba(16,185,129,0.3); color: var(--green); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div style="background: var(--red-bg); border: 1px solid rgba(239,68,68,0.3); color: var(--red); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    @php
        $fundSummary = \App\Services\FundService::getWithdrawalSummary(auth()->id());
    @endphp
    @if($fundSummary['is_fund_recipient'])
    <div style="background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(168,85,247,0.05)); border: 1px solid rgba(99,102,241,0.25); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 16px;">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div>
                <div style="font-size: 14px; font-weight: 700; color: var(--text-bright);">Special Fund Account</div>
                <div style="font-size: 12px; color: var(--text-muted);">Reference: {{ $fundSummary['fund_reference'] }}</div>
            </div>
        </div>

        @if($fundSummary['target_met'])
            <div style="font-size: 13px; color: var(--green); padding: 8px 12px; background: rgba(16,185,129,0.08); border-radius: 8px;">
                <i class="fas fa-trophy"></i> Team target reached! All withdrawals are unlocked.
            </div>
        @else
            <div style="margin-bottom: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="font-size: 12px; color: var(--text-muted);">Team Production Progress</span>
                    <span style="font-size: 12px; font-weight: 600; color: var(--text-bright);">{{ $fundSummary['progress'] }}%</span>
                </div>
                <div style="background: rgba(99,102,241,0.1); border-radius: 8px; height: 8px; overflow: hidden;">
                    <div style="background: var(--gradient-primary); height: 100%; width: {{ $fundSummary['progress'] }}%; border-radius: 8px;"></div>
                </div>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                <div style="flex: 1; min-width: 140px; padding: 10px; background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.15); border-radius: 8px;">
                    <div style="font-size: 11px; color: var(--green); font-weight: 600;"><i class="fas fa-check-circle"></i> Commission Available</div>
                    <div style="font-size: 16px; font-weight: 700; color: var(--text-bright); margin-top: 2px;">${{ number_format($fundSummary['commission_available'], 2) }}</div>
                </div>
                <div style="flex: 1; min-width: 140px; padding: 10px; background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.15); border-radius: 8px;">
                    <div style="font-size: 11px; color: var(--yellow); font-weight: 600;"><i class="fas fa-lock"></i> Profit & Capital</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Locked until 100% target</div>
                </div>
            </div>
        @endif
    </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-7 col-md-8 col-12">
            <div class="card-custom">
                <!-- Available balance -->
                <div style="background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(168,85,247,0.04)); border: 1px solid rgba(99,102,241,0.15); border-radius: 12px; padding: 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div><div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase;">Available in Withdrawal Wallet</div><div style="font-size: 26px; font-weight: 700; color: var(--text-bright); margin-top: 4px;">${{ number_format($available, 2) }}</div></div>
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;"><i class="fas fa-money-bill-wave"></i></div>
                </div>

                @if($otherWallets->count() > 0 && $otherWallets->sum('balance') > 0)
                <div style="margin-bottom: 16px; padding: 12px; background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.2); border-radius: 10px; font-size: 12px; color: var(--text-muted);">
                    <i class="fas fa-info-circle" style="color: var(--yellow);"></i> You have funds in other wallets. <a href="{{ route('dashboard.wallet.index') }}" style="color: var(--purple-3); text-decoration: none;">Transfer to withdrawal wallet →</a>
                </div>
                @endif

                <form method="POST" action="{{ route('dashboard.withdrawal.store') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Withdrawal Method</label>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            @if($web3Enabled && $web3Wallets->count() > 0)
                            <label style="flex: 1; min-width: 120px; cursor: pointer; padding: 16px; border-radius: 12px; border: 2px solid var(--purple-1); background: rgba(99,102,241,0.08); text-align: center;" onclick="selectMethod('wallet', this)">
                                <input type="radio" name="method" value="wallet" checked style="display:none;">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" style="display:block; margin:0 auto 8px;"><path d="M12 2L2 7v10l10 5 10-5V7L12 2z" stroke="#6366f1" stroke-width="2" stroke-linejoin="round"/><path d="M12 7v10M7 9.5v5M17 9.5v5" stroke="#a855f7" stroke-width="2" stroke-linecap="round"/></svg>
                                <span style="font-size: 13px; font-weight: 600; color: var(--text-bright);">Web3 Wallet</span>
                            </label>
                            @endif
                            <label style="flex: 1; min-width: 120px; cursor: pointer; padding: 16px; border-radius: 12px; border: 2px solid @if($web3Enabled && $web3Wallets->count() > 0) var(--border) @else var(--purple-1) @endif; background: @if($web3Enabled && $web3Wallets->count() > 0) var(--bg-input) @else rgba(99,102,241,0.08) @endif; text-align: center;" onclick="selectMethod('crypto', this)">
                                <input type="radio" name="method" value="crypto" @if(!$web3Enabled || $web3Wallets->count() === 0) checked @endif style="display:none;">
                                <i class="fab fa-bitcoin" style="font-size: 28px; color: #f7931a; display: block; margin-bottom: 8px;"></i>
                                <span style="font-size: 13px; font-weight: 600; color: var(--text-bright);">Crypto</span>
                            </label>
                            <label style="flex: 1; min-width: 120px; cursor: pointer; padding: 16px; border-radius: 12px; border: 2px solid var(--border); background: var(--bg-input); text-align: center;" onclick="selectMethod('bank_transfer', this)">
                                <input type="radio" name="method" value="bank_transfer" style="display:none;">
                                <i class="fas fa-university" style="font-size: 28px; color: var(--blue-1); display: block; margin-bottom: 8px;"></i>
                                <span style="font-size: 13px; font-weight: 600; color: var(--text-bright);">Bank Transfer</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Amount (USD)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-dim); font-weight: 600; font-size: 18px;">$</span>
                            <input type="number" name="amount" min="{{ $minWithdrawal }}" max="{{ $maxWithdrawal }}" step="0.01" class="form-control" style="padding-left: 30px; font-size: 18px; font-weight: 600;" placeholder="0.00" required oninput="updateWithdrawalFee()">
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 6px; font-size: 11px; color: var(--text-dim);">
                            <span>Min: ${{ number_format($minWithdrawal, 2) }}</span>
                            <span>Max: ${{ number_format($maxWithdrawal, 2) }}</span>
                        </div>
                    </div>

                    <!-- Web3 Wallet withdrawal fields -->
                    @if($web3Enabled && $web3Wallets->count() > 0)
                    <div id="web3Fields" style="display:@if($web3Wallets->count() > 0) block @else none @endif;">
                        <div style="background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.2); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7v10l10 5 10-5V7L12 2z" stroke="#6366f1" stroke-width="2" stroke-linejoin="round"/><path d="M12 7v10M7 9.5v5M17 9.5v5" stroke="#a855f7" stroke-width="2" stroke-linecap="round"/></svg>
                                <div>
                                    <div style="font-size: 14px; font-weight: 600; color: var(--text-bright);">Withdraw to Connected Wallet</div>
                                    <div style="font-size: 11px; color: var(--text-muted);">Funds will be sent to your linked wallet address</div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Select Destination Wallet</label>
                                <select name="web3_wallet_id" id="web3WalletSelect" class="form-control" onchange="updateWeb3WithdrawalDetails()">
                                    @foreach($web3Wallets as $w)
                                    <option value="{{ $w->id }}" data-address="{{ $w->address }}" data-network="{{ $w->network_name ?? '' }}">{{ ucfirst($w->wallet_type) }} — {{ substr($w->address, 0, 6) }}...{{ substr($w->address, -4) }}{{ $w->is_primary ? ' (Primary)' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="background: var(--bg-input); border-radius: 10px; padding: 12px;">
                                <div style="font-size: 11px; color: var(--text-dim); margin-bottom: 4px;">Destination Address:</div>
                                <div style="font-family: monospace; font-size: 12px; color: var(--text-bright); word-break: break-all;" id="web3DestAddress">—</div>
                                <div style="font-size: 11px; color: var(--purple-3); margin-top: 4px;" id="web3DestNetwork"></div>
                            </div>
                            <!-- Hidden fields to store wallet details -->
                            <input type="hidden" name="wallet_address" id="web3HiddenAddress" value="">
                            <input type="hidden" name="network" id="web3HiddenNetwork" value="">
                        </div>
                    </div>
                    @endif

                    <!-- Crypto fields (manual) -->
                    <div id="cryptoFields" style="display:@if($web3Enabled && $web3Wallets->count() > 0) none @else block @endif;">
                        <div class="form-group mb-3">
                            <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Network</label>
                            <select name="network" class="form-control" id="cryptoNetworkSelect"><option>TRC20</option><option>ERC20</option><option>BEP20</option><option>BTC</option></select>
                        </div>
                        <div class="form-group mb-3">
                            <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Your Wallet Address</label>
                            <input type="text" name="wallet_address" class="form-control" placeholder="Enter your wallet address" id="cryptoWalletAddress">
                        </div>
                    </div>

                    <!-- Bank fields -->
                    <div id="bankFields" style="display:none;">
                        <div class="form-group mb-3"><label style="font-size: 13px; color: var(--text-muted);">Account Name</label><input type="text" name="bank_account_name" class="form-control" placeholder="John Doe" required></div>
                        <div class="form-group mb-3"><label style="font-size: 13px; color: var(--text-muted);">Account Number</label><input type="text" name="bank_account_number" class="form-control" placeholder="0000000000" required></div>
                        <div class="form-group mb-3"><label style="font-size: 13px; color: var(--text-muted);">Bank Name</label><input type="text" name="bank_name" class="form-control" placeholder="Bank of America" required></div>
                        <div class="form-group mb-3"><label style="font-size: 13px; color: var(--text-muted);">Country</label><input type="text" name="bank_country" class="form-control" placeholder="United States"></div>
                    </div>

                    <!-- Fee breakdown -->
                    <div style="background: var(--bg-input); border-radius: 12px; padding: 14px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;"><span style="color: var(--text-muted);">Withdrawal Fee ({{ $feePercent }}%)</span><span style="color: var(--red); font-weight: 600;" id="feeDisplay">$0.00</span></div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; padding-top: 8px; border-top: 1px solid var(--border);"><span style="color: var(--text-muted); font-weight: 600;">You Receive</span><span style="color: var(--green); font-weight: 700;" id="netDisplay">$0.00</span></div>
                    </div>

                    <button type="submit" class="btn-gradient" style="width: 100%; padding: 14px; font-size: 15px; background: linear-gradient(135deg, #ef4444, #dc2626);" onclick="return confirm('Confirm withdrawal request? Funds will be locked until processed.')"><i class="fas fa-paper-plane"></i> Submit Withdrawal Request</button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            @if($web3Enabled && $web3Wallets->count() > 0)
            <div class="card-custom mb-3" style="border: 1px solid rgba(99,102,241,0.15);">
                <h5 style="color: var(--text-bright); margin-bottom: 14px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="display:inline-block; vertical-align:middle; margin-right:4px;"><path d="M12 2L2 7v10l10 5 10-5V7L12 2z" stroke="#6366f1" stroke-width="2" stroke-linejoin="round"/></svg>
                    Connected Wallets
                </h5>
                @foreach($web3Wallets as $w)
                <div style="display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 10px; background: var(--bg-input); margin-bottom: 6px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(99,102,241,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        @php
                            $icons = ['metamask' => '🦊', 'walletconnect' => '🔗', 'trust' => '🛡️', 'coinbase' => '🅒', 'rabby' => '🐰', 'okx' => '⬛', 'phantom' => '👻'];
                        @endphp
                        <span style="font-size: 16px;">{{ $icons[$w->wallet_type] ?? '👛' }}</span>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <span style="font-size: 12px; font-weight: 600; color: var(--text-bright);">{{ ucfirst($w->wallet_type) }}</span>
                            @if($w->is_primary)<span style="font-size: 9px; padding: 1px 5px; border-radius: 4px; background: rgba(168,85,247,0.15); color: #a855f7; font-weight: 600;">PRIMARY</span>@endif
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); font-family: monospace;">{{ substr($w->address, 0, 8) }}...{{ substr($w->address, -6) }}</div>
                    </div>
                </div>
                @endforeach
                <a href="{{ route('dashboard.wallet.index') }}" style="font-size: 12px; color: var(--purple-3); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px;">
                    Manage wallets <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
                </a>
            </div>
            @endif

            <div class="card-custom mb-3">
                <h5 style="color: var(--text-bright); margin-bottom: 14px;"><i class="fas fa-info-circle" style="color: var(--blue-1);"></i> Withdrawal Info</h5>
                <div style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
                    <p style="margin-bottom: 10px;"><i class="fas fa-check" style="color: var(--green);"></i> Min withdrawal: ${{ number_format($minWithdrawal, 2) }}</p>
                    <p style="margin-bottom: 10px;"><i class="fas fa-check" style="color: var(--green);"></i> Max withdrawal: ${{ number_format($maxWithdrawal, 2) }}</p>
                    <p style="margin-bottom: 10px;"><i class="fas fa-check" style="color: var(--green);"></i> Fee: {{ $feePercent }}%</p>
                    <p style="margin-bottom: 10px;"><i class="fas fa-clock" style="color: var(--yellow);"></i> Processing time: ~{{ $processingHours }} hours</p>
                    <p><i class="fas fa-shield-alt" style="color: var(--purple-3);"></i> KYC verification required</p>
                </div>
            </div>

            <div class="card-custom">
                <h5 style="color: var(--text-bright); margin-bottom: 14px;"><i class="fas fa-history" style="color: var(--purple-3);"></i> Recent Withdrawals</h5>
                @if($recentWithdrawals->count() > 0)
                @foreach($recentWithdrawals as $wdr)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(51,65,85,0.3);">
                    <div><span style="font-size: 13px; font-weight: 600; color: var(--text-bright);">${{ number_format($wdr->amount, 2) }}</span><div style="font-size: 11px; color: var(--text-dim);">{{ $wdr->created_at->format('M d, H:i') }}</div></div>
                    @if($wdr->status === 'completed')<span class="badge-custom badge-up">Completed</span>@elseif($wdr->status === 'pending')<span class="badge-custom badge-pending">Pending</span>@elseif($wdr->status === 'processing')<span class="badge-custom" style="background: rgba(59,130,246,0.15); color: var(--blue-1);">Processing</span>@else<span class="badge-custom" style="background: var(--red-bg); color: var(--red);">Rejected</span>@endif
                </div>
                @endforeach
                @else
                <div style="text-align: center; padding: 20px 0; color: var(--text-dim);"><i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 8px; opacity: 0.5;"></i><p style="font-size: 13px;">No withdrawals yet</p></div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
var feePercent = {{ $feePercent }};

function selectMethod(method, el) {
    document.querySelectorAll('input[name="method"]').forEach(r => {
        r.closest('label').style.borderColor = 'var(--border)';
        r.closest('label').style.background = 'var(--bg-input)';
    });
    el.style.borderColor = 'var(--purple-1)';
    el.style.background = 'rgba(99,102,241,0.08)';

    document.getElementById('cryptoFields').style.display = method === 'crypto' ? 'block' : 'none';
    document.getElementById('bankFields').style.display = method === 'bank_transfer' ? 'block' : 'none';
    var web3Fields = document.getElementById('web3Fields');
    if (web3Fields) web3Fields.style.display = method === 'wallet' ? 'block' : 'none';

    // Toggle required attributes
    var cryptoAddress = document.getElementById('cryptoWalletAddress');
    var cryptoNetwork = document.getElementById('cryptoNetworkSelect');
    if (cryptoAddress) cryptoAddress.required = (method === 'crypto');
    if (cryptoNetwork) cryptoNetwork.required = (method === 'crypto');

    if (method === 'wallet') {
        updateWeb3WithdrawalDetails();
    }
}

function updateWithdrawalFee() {
    var amount = parseFloat(document.querySelector('input[name="amount"]').value) || 0;
    var fee = amount * feePercent / 100;
    var net = amount - fee;
    document.getElementById('feeDisplay').textContent = '$' + fee.toFixed(2);
    document.getElementById('netDisplay').textContent = '$' + net.toFixed(2);
}

function updateWeb3WithdrawalDetails() {
    var select = document.getElementById('web3WalletSelect');
    if (!select) return;

    var option = select.options[select.selectedIndex];
    var address = option.getAttribute('data-address');
    var network = option.getAttribute('data-network');

    document.getElementById('web3DestAddress').textContent = address;
    document.getElementById('web3HiddenAddress').value = address;

    if (network && network !== '') {
        document.getElementById('web3DestNetwork').textContent = 'Network: ' + network;
        document.getElementById('web3HiddenNetwork').value = network;
    } else {
        document.getElementById('web3DestNetwork').textContent = '';
        document.getElementById('web3HiddenNetwork').value = 'TRC20';
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    @if($web3Enabled && $web3Wallets->count() > 0)
    updateWeb3WithdrawalDetails();
    @endif
});
</script>
@endsection