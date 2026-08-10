@extends('layouts.dashboard')

@section('page-title', 'Deposit Funds')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 24px; font-size: 22px;"><i class="fas fa-arrow-down" style="color: var(--green);"></i> Deposit Funds</h2>

    @if(session('success'))
    <div style="background: var(--green-bg); border: 1px solid rgba(16,185,129,0.3); color: var(--green); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-7 col-md-8 col-12">
            <div class="card-custom">
                <form method="POST" action="{{ route('dashboard.deposit.store') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <label style="font-size: 13px; color: var(--text-muted); font-weight: 500; margin-bottom: 8px;">Payment Method</label>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <label style="flex: 1; min-width: 120px; cursor: pointer; padding: 16px; border-radius: 12px; border: 2px solid var(--purple-1); background: rgba(99,102,241,0.05); text-align: center; transition: all 0.2s;" onclick="selectMethod('crypto', this)">
                                <input type="radio" name="method" value="crypto" checked style="display:none;">
                                <i class="fab fa-bitcoin" style="font-size: 28px; color: #f7931a; display: block; margin-bottom: 8px;"></i>
                                <span style="font-size: 13px; font-weight: 600; color: var(--text-bright);">Crypto</span>
                            </label>
                            @if($web3Enabled && $web3Wallets->count() > 0)
                            <label style="flex: 1; min-width: 120px; cursor: pointer; padding: 16px; border-radius: 12px; border: 2px solid var(--border); background: var(--bg-input); text-align: center; transition: all 0.2s;" onclick="selectMethod('wallet', this)">
                                <input type="radio" name="method" value="wallet" style="display:none;">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" style="display:block; margin:0 auto 8px;"><path d="M12 2L2 7v10l10 5 10-5V7L12 2z" stroke="#6366f1" stroke-width="2" stroke-linejoin="round"/><path d="M12 7v10M7 9.5v5M17 9.5v5" stroke="#a855f7" stroke-width="2" stroke-linecap="round"/></svg>
                                <span style="font-size: 13px; font-weight: 600; color: var(--text-bright);">Web3 Wallet</span>
                            </label>
                            @endif
                            <label style="flex: 1; min-width: 120px; cursor: pointer; padding: 16px; border-radius: 12px; border: 2px solid var(--border); background: var(--bg-input); text-align: center; transition: all 0.2s;" onclick="selectMethod('bank_transfer', this)">
                                <input type="radio" name="method" value="bank_transfer" style="display:none;">
                                <i class="fas fa-university" style="font-size: 28px; color: var(--blue-1); display: block; margin-bottom: 8px;"></i>
                                <span style="font-size: 13px; font-weight: 600; color: var(--text-bright);">Bank Transfer</span>
                            </label>
                            <label style="flex: 1; min-width: 120px; cursor: pointer; padding: 16px; border-radius: 12px; border: 2px solid var(--border); background: var(--bg-input); text-align: center; transition: all 0.2s;" onclick="selectMethod('manual', this)">
                                <input type="radio" name="method" value="manual" style="display:none;">
                                <i class="fas fa-money-bill-wave" style="font-size: 28px; color: var(--green); display: block; margin-bottom: 8px;"></i>
                                <span style="font-size: 13px; font-weight: 600; color: var(--text-bright);">Manual</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-size: 13px; color: var(--text-muted); font-weight: 500; margin-bottom: 6px;">Amount (USD)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-dim); font-weight: 600; font-size: 18px;">$</span>
                            <input type="number" name="amount" min="{{ $minDeposit }}" max="{{ $maxDeposit }}" step="0.01" class="form-control" style="padding-left: 30px; font-size: 18px; font-weight: 600;" placeholder="0.00" required oninput="updateFee()">
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 6px; font-size: 11px; color: var(--text-dim);">
                            <span>Min: ${{ number_format($minDeposit, 2) }}</span>
                            <span>Max: ${{ number_format($maxDeposit, 2) }}</span>
                        </div>
                    </div>

                    <!-- Crypto fields (manual crypto deposit) -->
                    <div id="cryptoFields" style="display:block;">
                        @if($addresses->count() > 0)
                        <div class="form-group mb-3">
                            <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Select Network</label>
                            <select name="network" class="form-control" id="networkSelect">
                                @foreach($addresses as $network => $addrs)
                                <option value="{{ $network }}">{{ $network }} — {{ $addrs->first()->coin }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="background: var(--bg-input); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">Deposit Address ({{ $addresses->first()->first()->network ?? '' }}):</div>
                            <div style="font-family: monospace; font-size: 13px; color: var(--text-bright); word-break: break-all; padding: 10px; background: var(--bg-dark); border-radius: 8px; border: 1px solid var(--border);" id="depositAddress">{{ $addresses->first()->first()->address ?? 'No address configured' }}</div>
                        </div>
                        @endif
                        <div class="form-group mb-3">
                            <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Transaction Hash (optional)</label>
                            <input type="text" name="tx_hash" class="form-control" placeholder="0x...">
                        </div>
                        <div class="form-group mb-3">
                            <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">From Address (optional)</label>
                            <input type="text" name="from_address" class="form-control" placeholder="Your wallet address">
                        </div>
                    </div>

                    <!-- Web3 Wallet fields -->
                    @if($web3Enabled && $web3Wallets->count() > 0)
                    <div id="web3Fields" style="display:none;">
                        <div style="background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.2); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7v10l10 5 10-5V7L12 2z" stroke="#6366f1" stroke-width="2" stroke-linejoin="round"/><path d="M12 7v10M7 9.5v5M17 9.5v5" stroke="#a855f7" stroke-width="2" stroke-linecap="round"/></svg>
                                <div>
                                    <div style="font-size: 14px; font-weight: 600; color: var(--text-bright);">Deposit from Connected Wallet</div>
                                    <div style="font-size: 11px; color: var(--text-muted);">Send crypto from your linked Web3 wallet</div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Select Connected Wallet</label>
                                <select name="web3_wallet_id" id="web3WalletSelect" class="form-control" onchange="updateWeb3Details()">
                                    @foreach($web3Wallets as $w)
                                    <option value="{{ $w->id }}" data-address="{{ $w->address }}" data-network="{{ $w->network_name ?? '' }}" data-chain="{{ $w->chain_id ?? '' }}" data-type="{{ $w->wallet_type }}">
                                        {{ ucfirst($w->wallet_type) }} — {{ substr($w->address, 0, 6) }}...{{ substr($w->address, -4) }}{{ $w->is_primary ? ' (Primary)' : '' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="background: var(--bg-input); border-radius: 10px; padding: 12px; margin-bottom: 12px;">
                                <div style="font-size: 11px; color: var(--text-dim); margin-bottom: 4px;">From Address:</div>
                                <div style="font-family: monospace; font-size: 12px; color: var(--text-bright); word-break: break-all;" id="web3FromAddress">—</div>
                            </div>

                            <div id="web3NetworkInfo" style="display:none; background: var(--bg-input); border-radius: 10px; padding: 12px; margin-bottom: 12px;">
                                <div style="font-size: 11px; color: var(--text-dim); margin-bottom: 4px;">Network:</div>
                                <div style="font-size: 12px; color: var(--text-bright);" id="web3NetworkName">—</div>
                            </div>
                        </div>

                        <!-- Show deposit address based on selected wallet's network -->
                        @if($addresses->count() > 0)
                        <div class="form-group mb-3">
                            <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Send To Address</label>
                            <select name="web3_network" id="web3NetworkSelect" class="form-control mb-2" onchange="updateWeb3DepositAddress()">
                                @foreach($addresses as $network => $addrs)
                                <option value="{{ $network }}" data-address="{{ $addrs->first()->address }}">{{ $network }} — {{ $addrs->first()->coin }}</option>
                                @endforeach
                            </select>
                            <div style="background: var(--bg-input); border-radius: 12px; padding: 16px;">
                                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">Platform Deposit Address:</div>
                                <div style="font-family: monospace; font-size: 13px; color: var(--text-bright); word-break: break-all; padding: 10px; background: var(--bg-dark); border-radius: 8px; border: 1px solid var(--border);" id="web3DepositAddress">{{ $addresses->first()->first()->address ?? '' }}</div>
                                <button type="button" onclick="copyDepositAddress()" style="margin-top: 8px; padding: 6px 14px; border-radius: 8px; background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.2); color: var(--purple-3); cursor: pointer; font-size: 12px; font-weight: 600;">
                                    <i class="fas fa-copy"></i> Copy Address
                                </button>
                            </div>
                        </div>
                        @endif

                        <div class="form-group mb-3">
                            <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Transaction Hash (optional)</label>
                            <input type="text" name="tx_hash" class="form-control" placeholder="0x... (auto-filled after sending)">
                        </div>

                        <!-- Hidden field to store from_address -->
                        <input type="hidden" name="from_address" id="web3HiddenFromAddress" value="">
                        <input type="hidden" name="network" id="web3HiddenNetwork" value="">
                    </div>
                    @endif

                    <!-- Bank fields -->
                    <div id="bankFields" style="display:none;">
                        <div class="form-group mb-3">
                            <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Bank Reference Number</label>
                            <input type="text" name="bank_reference" class="form-control" placeholder="Transaction reference">
                        </div>
                    </div>

                    <!-- Fee display -->
                    <div style="background: var(--bg-input); border-radius: 12px; padding: 14px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                            <span style="color: var(--text-muted);">Deposit Fee ({{ $feePercent }}%)</span>
                            <span style="color: var(--text-bright); font-weight: 600;" id="feeDisplay">$0.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; padding-top: 8px; border-top: 1px solid var(--border);">
                            <span style="color: var(--text-muted); font-weight: 600;">You Receive</span>
                            <span style="color: var(--green); font-weight: 700;" id="netDisplay">$0.00</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-gradient" style="width: 100%; padding: 14px; font-size: 15px;"><i class="fas fa-paper-plane"></i> Submit Deposit Request</button>
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
                            @if($w->verified_at)<span style="font-size: 9px; padding: 1px 5px; border-radius: 4px; background: rgba(16,185,129,0.15); color: #10b981; font-weight: 600;"><i class="fas fa-check" style="font-size: 8px;"></i></span>@endif
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); font-family: monospace;">{{ substr($w->address, 0, 8) }}...{{ substr($w->address, -6) }}</div>
                    </div>
                </div>
                @endforeach
                <a href="{{ route('dashboard.wallet.index') }}" style="font-size: 12px; color: var(--purple-3); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px;">
                    Manage wallets <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
                </a>
            </div>
            @elseif($web3Enabled)
            <div class="card-custom mb-3" style="border: 1px solid rgba(99,102,241,0.15);">
                <h5 style="color: var(--text-bright); margin-bottom: 14px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="display:inline-block; vertical-align:middle; margin-right:4px;"><path d="M12 2L2 7v10l10 5 10-5V7L12 2z" stroke="#6366f1" stroke-width="2" stroke-linejoin="round"/></svg>
                    Web3 Wallet
                </h5>
                <div style="text-align: center; padding: 16px 0;">
                    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 10px;">Connect your crypto wallet for faster deposits</div>
                    <a href="{{ route('dashboard.wallet.index') }}" class="btn-gradient" style="padding: 8px 20px; font-size: 13px; text-decoration: none; display: inline-block;">
                        <i class="fas fa-link"></i> Connect Wallet
                    </a>
                </div>
            </div>
            @endif

            <div class="card-custom mb-3">
                <h5 style="color: var(--text-bright); margin-bottom: 14px;"><i class="fas fa-info-circle" style="color: var(--blue-1);"></i> Deposit Information</h5>
                <div style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
                    <p style="margin-bottom: 10px;"><i class="fas fa-check" style="color: var(--green);"></i> Minimum deposit: ${{ number_format($minDeposit, 2) }}</p>
                    <p style="margin-bottom: 10px;"><i class="fas fa-check" style="color: var(--green);"></i> Maximum deposit: ${{ number_format($maxDeposit, 2) }}</p>
                    <p style="margin-bottom: 10px;"><i class="fas fa-check" style="color: var(--green);"></i> Deposit fee: {{ $feePercent }}%</p>
                    <p style="margin-bottom: 10px;"><i class="fas fa-clock" style="color: var(--yellow);"></i> Crypto deposits: 1-3 confirmations needed</p>
                    <p><i class="fas fa-clock" style="color: var(--yellow);"></i> Bank deposits: 24-48 hours processing</p>
                </div>
            </div>

            <div class="card-custom">
                <h5 style="color: var(--text-bright); margin-bottom: 14px;"><i class="fas fa-history" style="color: var(--purple-3);"></i> Recent Deposits</h5>
                @if($recentDeposits->count() > 0)
                @foreach($recentDeposits as $dep)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(51,65,85,0.3);">
                    <div><span style="font-size: 13px; font-weight: 600; color: var(--text-bright);">${{ number_format($dep->amount, 2) }}</span><div style="font-size: 11px; color: var(--text-dim);">{{ $dep->created_at->format('M d, H:i') }}</div></div>
                    @if($dep->status === 'confirmed')<span class="badge-custom badge-up">Confirmed</span>@elseif($dep->status === 'pending')<span class="badge-custom badge-pending">Pending</span>@else<span class="badge-custom" style="background: var(--red-bg); color: var(--red);">Rejected</span>@endif
                </div>
                @endforeach
                @else
                <div style="text-align: center; padding: 20px 0; color: var(--text-dim);"><i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 8px; opacity: 0.5;"></i><p style="font-size: 13px;">No deposits yet</p></div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
var feePercent = {{ $feePercent }};
var web3Addresses = {};
@if($web3Enabled && $addresses->count() > 0)
@foreach($addresses as $network => $addrs)
web3Addresses["{{ $network }}"] = "{{ $addrs->first()->address }}";
@endforeach
@endif

function selectMethod(method, el) {
    document.querySelectorAll('input[name="method"]').forEach(r => r.closest('label').style.borderColor = 'var(--border)');
    document.querySelectorAll('input[name="method"]').forEach(r => r.closest('label').style.background = 'var(--bg-input)');
    el.style.borderColor = 'var(--purple-1)';
    el.style.background = 'rgba(99,102,241,0.05)';

    document.getElementById('cryptoFields').style.display = method === 'crypto' ? 'block' : 'none';
    document.getElementById('bankFields').style.display = method === 'bank_transfer' ? 'block' : 'none';
    var web3Fields = document.getElementById('web3Fields');
    if (web3Fields) web3Fields.style.display = method === 'wallet' ? 'block' : 'none';

    // Update network field based on method
    if (method === 'wallet') {
        updateWeb3Details();
    }
}

function updateFee() {
    var amount = parseFloat(document.querySelector('input[name="amount"]').value) || 0;
    var fee = amount * feePercent / 100;
    var net = amount - fee;
    document.getElementById('feeDisplay').textContent = '$' + fee.toFixed(2);
    document.getElementById('netDisplay').textContent = '$' + net.toFixed(2);
}

function updateWeb3Details() {
    var select = document.getElementById('web3WalletSelect');
    if (!select) return;

    var option = select.options[select.selectedIndex];
    var address = option.getAttribute('data-address');
    var network = option.getAttribute('data-network');
    var chain = option.getAttribute('data-chain');

    document.getElementById('web3FromAddress').textContent = address;
    document.getElementById('web3HiddenFromAddress').value = address;

    if (network && network !== '') {
        document.getElementById('web3NetworkInfo').style.display = 'block';
        document.getElementById('web3NetworkName').textContent = network + (chain ? ' (Chain ID: ' + chain + ')' : '');
        document.getElementById('web3HiddenNetwork').value = network;
    } else {
        document.getElementById('web3NetworkInfo').style.display = 'none';
    }
}

function updateWeb3DepositAddress() {
    var select = document.getElementById('web3NetworkSelect');
    if (!select) return;
    var option = select.options[select.selectedIndex];
    var address = option.getAttribute('data-address') || option.value;
    document.getElementById('web3DepositAddress').textContent = address;
    document.getElementById('web3HiddenNetwork').value = option.textContent.split(' — ')[0];
}

function copyDepositAddress() {
    var addr = document.getElementById('web3DepositAddress').textContent;
    navigator.clipboard.writeText(addr).then(function() {
        alert('Address copied to clipboard!');
    });
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    @if($web3Enabled && $web3Wallets->count() > 0)
    updateWeb3Details();
    @endif
});
</script>
@endsection