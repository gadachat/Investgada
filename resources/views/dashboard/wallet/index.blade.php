@extends('layouts.dashboard')

@section('page-title', 'My Wallets')

@section('content')
<div class="fade-in">

    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0; font-size: 22px;">
                <i class="fas fa-wallet" style="color: var(--purple-3);"></i> My Wallets
            </h2>
            <p style="color: var(--text-muted); margin: 4px 0 0; font-size: 14px;">Manage your funds and Web3 wallet connections</p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <button onclick="openWeb3Modal()" class="btn-outline-custom" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border: 1px solid var(--border);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex-shrink: 0;">
                    <path d="M12 2L2 7v10l10 5 10-5V7L12 2z" stroke="#6366f1" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M12 7v10M7 9.5v5M17 9.5v5" stroke="#a855f7" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Connect Web3 Wallet
            </button>
            <a href="{{ route('dashboard.wallet.history') }}" class="btn-outline-custom" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fas fa-history"></i> History
            </a>
            <a href="{{ route('dashboard.deposit.create') }}" class="btn-gradient" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fas fa-arrow-down"></i> Deposit
            </a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: var(--green-bg); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--green); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background: var(--red-bg); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--red); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <!-- Total balance banner -->
    <div style="background: var(--gradient-primary); border-radius: 16px; padding: 28px; margin-bottom: 24px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -30px; right: -10px; width:100%;max-width:180px; height: 180px; background: radial-gradient(circle, rgba(255,255,255,0.1), transparent 70%); border-radius: 50%;"></div>
        <div style="position: relative; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="color: rgba(255,255,255,0.8); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Total Available Balance</div>
                <div style="color: white; font-size: 36px; font-weight: 800; margin-top: 4px; font-variant-numeric: tabular-nums;">${{ number_format($totalBalance, 2) }}</div>
                @if($totalLocked > 0)
                <div style="color: rgba(255,255,255,0.7); font-size: 13px; margin-top: 4px;">
                    <i class="fas fa-lock"></i> ${{ number_format($totalLocked, 2) }} locked in pending transactions
                </div>
                @endif
            </div>
            <div style="display: flex; gap: 12px;">
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 14px 18px; text-align: center;">
                    <div style="color: rgba(255,255,255,0.7); font-size: 11px; text-transform: uppercase;">Total Earned</div>
                    <div style="color: white; font-size: 18px; font-weight: 700;">${{ number_format(auth()->user()->total_earned, 2) }}</div>
                </div>
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 14px 18px; text-align: center;">
                    <div style="color: rgba(255,255,255,0.7); font-size: 11px; text-transform: uppercase;">Invested</div>
                    <div style="color: white; font-size: 18px; font-weight: 700;">${{ number_format(auth()->user()->total_invested, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        <!-- LEFT: Wallet cards + Transfer + Web3 -->
        <div class="col-lg-7">

            <!-- Web3 Connected Wallets -->
            <div class="card-custom mb-3" id="web3WalletSection">
                <div class="section-header">
                    <h5>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                            <path d="M12 2L2 7v10l10 5 10-5V7L12 2z" stroke="#6366f1" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M12 7v10M7 9.5v5M17 9.5v5" stroke="#a855f7" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span style="color: var(--text-bright);">Web3 Wallets</span>
                    </h5>
                    <button onclick="openWeb3Modal()" style="font-size: 12px; color: var(--purple-3); background: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fas fa-plus-circle"></i> Connect Wallet
                    </button>
                </div>

                <div id="web3WalletList">
                    <!-- Loaded via JS -->
                    <div style="text-align: center; padding: 30px 0; color: var(--text-dim);">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <p style="margin-top: 8px; font-size: 13px;">Loading connected wallets...</p>
                    </div>
                </div>
            </div>

            <!-- Wallet cards -->
            <div class="row g-3 mb-3">
                @php
                    $walletConfig = [
                        'deposit'   => ['icon' => 'wallet',          'color' => '#3b82f6', 'bg' => 'rgba(59,130,246,0.15)',   'label' => 'Deposit Wallet',    'desc' => 'Main wallet for deposits and investments'],
                        'interest'  => ['icon' => 'piggy-bank',      'color' => '#10b981', 'bg' => 'rgba(16,185,129,0.15)',   'label' => 'Interest Wallet',   'desc' => 'Earnings from investment payouts'],
                        'commission'=>['icon' => 'handshake',       'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.15)',   'label' => 'Commission Wallet','desc' => 'Direct referral commission earnings'],
                        'bonus'     => ['icon' => 'gift',            'color' => '#a855f7', 'bg' => 'rgba(168,85,247,0.15)',   'label' => 'Bonus Wallet',      'desc' => 'Matching bonus and rank rewards'],
                        'withdrawal'=> ['icon' => 'money-bill-wave','color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.15)',    'label' => 'Withdrawal Wallet', 'desc' => 'Funds ready for withdrawal'],
                        'trading'   => ['icon' => 'chart-line',         'color' => '#a855f7', 'bg' => 'rgba(168,85,247,0.15)',   'label' => 'Trading Wallet',    'desc' => 'Funds for manual trading'],
                    ];
                @endphp

                @foreach($wallets as $wallet)
                @php $cfg = $walletConfig[$wallet->type] ?? $walletConfig['deposit']; @endphp
                <div class="col-md-6">
                    <div class="card-custom" style="padding: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px;">
                            <div style="width: 44px; height: 44px; border-radius: 12px; background: {{ $cfg['bg'] }}; color: {{ $cfg['color'] }}; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                <i class="fas fa-{{ $cfg['icon'] }}"></i>
                            </div>
                            @if($wallet->locked_balance > 0)
                            <span style="font-size: 10px; background: var(--yellow-bg); color: var(--yellow); padding: 3px 8px; border-radius: 6px; font-weight: 600;">
                                <i class="fas fa-lock" style="font-size: 9px;"></i> ${{ number_format($wallet->locked_balance, 2) }} locked
                            </span>
                            @endif
                        </div>
                        <div style="font-size: 13px; color: var(--text-bright); font-weight: 600;">{{ $cfg['label'] }}</div>
                        <div style="font-size: 11px; color: var(--text-dim); margin-bottom: 8px;">{{ $cfg['desc'] }}</div>
                        <div style="font-size: 22px; font-weight: 800; color: {{ $cfg['color'] }};">${{ number_format($wallet->balance, 2) }}</div>
                        <div style="font-size: 11px; color: var(--text-dim); margin-top: 2px;">{{ $wallet->currency }}</div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Transfer between wallets -->
            <div class="card-custom">
                <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 16px;">
                    <i class="fas fa-exchange-alt" style="color: var(--purple-3);"></i> Transfer Between Wallets
                </h5>

                @php
                    $fundSummary = \App\Services\FundService::getWithdrawalSummary(auth()->id());
                @endphp
                @if($fundSummary['is_fund_recipient'] && !$fundSummary['target_met'])
                <div style="background: rgba(99,102,241,0.06); border: 1px solid rgba(99,102,241,0.2); border-radius: 10px; padding: 12px; margin-bottom: 14px; font-size: 12px; color: var(--text-muted);">
                    <div style="font-weight: 600; color: var(--purple-3); margin-bottom: 6px;"><i class="fas fa-shield-alt"></i> Special Fund Account — Transfer Restrictions</div>
                    <div style="margin-bottom: 4px;"><i class="fas fa-check" style="color: var(--green);"></i> Commission &rarr; Withdrawal: <strong>Allowed</strong></div>
                    <div style="margin-bottom: 4px;"><i class="fas fa-lock" style="color: var(--yellow);"></i> Deposit (Capital) &rarr; Withdrawal: <strong>Locked</strong> — team at {{ $fundSummary['progress'] }}%</div>
                    <div><i class="fas fa-lock" style="color: var(--yellow);"></i> Interest (Profit) &rarr; Withdrawal: <strong>Locked</strong> — team at {{ $fundSummary['progress'] }}%</div>
                </div>
                @endif

                <form method="POST" action="{{ route('dashboard.wallet.transfer') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 6px;">From</label>
                            <select name="from" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 10px;" required>
                                @foreach($wallets as $w)
                                <option value="{{ $w->type }}" @selected(old('from') === $w->type)>{{ ucfirst($w->type) }} (${{ number_format($w->balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2" style="display: flex; align-items: flex-end; justify-content: center; padding-bottom: 16px;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 6px;">To</label>
                            <select name="to" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 10px;" required>
                                @foreach($wallets as $w)
                                <option value="{{ $w->type }}" @selected(old('to') === $w->type)>{{ ucfirst($w->type) }} (${{ number_format($w->balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 6px;">Amount (USD)</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-dim); font-weight: 600;">$</span>
                                <input type="number" name="amount" min="1" step="0.01" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 10px 10px 10px 30px;" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn-gradient" style="width: 100%; padding: 12px;">
                                <i class="fas fa-paper-plane"></i> Transfer Funds
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT: Recent transactions -->
        <div class="col-lg-5">
            <div class="card-custom">
                <div class="section-header">
                    <h5><i class="fas fa-receipt" style="color: var(--purple-3);"></i> Recent Transactions</h5>
                    <a href="{{ route('dashboard.wallet.history') }}" class="section-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                @if($recentTransactions->count() > 0)
                <div style="max-height: 600px; overflow-y: auto;">
                    @foreach($recentTransactions as $tx)
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid rgba(51,65,85,0.3);">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: {{ $tx->direction === 'credit' ? 'var(--green-bg)' : 'var(--red-bg)' }}; color: {{ $tx->direction === 'credit' ? 'var(--green)' : 'var(--red)' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-{{ $tx->direction === 'credit' ? 'arrow-down' : 'arrow-up' }}"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 13px; font-weight: 600; color: var(--text-bright); text-transform: capitalize;">{{ str_replace('_', ' ', $tx->type) }}</div>
                            <div style="font-size: 11px; color: var(--text-dim);">{{ $tx->created_at->format('M d, Y H:i') }}</div>
                            @if($tx->description)
                            <div style="font-size: 11px; color: var(--text-dim); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $tx->description }}</div>
                            @endif
                        </div>
                        <div style="text-align: right; flex-shrink: 0;">
                            <div style="font-size: 14px; font-weight: 700; color: {{ $tx->direction === 'credit' ? 'var(--green)' : 'var(--red)' }};">
                                {{ $tx->direction === 'credit' ? '+' : '-' }}${{ number_format($tx->amount, 2) }}
                            </div>
                            <span class="badge-custom {{ $tx->status === 'completed' ? 'badge-up' : 'badge-pending' }}" style="font-size: 10px;">{{ $tx->status }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="text-align: center; padding: 40px 0; color: var(--text-dim);">
                    <i class="fas fa-receipt" style="font-size: 36px; color: var(--border); margin-bottom: 12px;"></i>
                    <p style="font-size: 14px;">No transactions yet</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ========== WEB3 WALLET MODAL ========== -->
<div id="web3Modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; z-index:9999; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
    <div style="background: var(--bg-card); border-radius:20px; max-width:100%;max-width:480px; width:90%; max-height:85vh; overflow-y:auto; border:1px solid var(--border); box-shadow:0 20px 60px rgba(0,0,0,0.5);">
        
        <!-- Modal Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; padding:20px 24px; border-bottom:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:10px; background:var(--gradient-primary); display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L2 7v10l10 5 10-5V7L12 2z" stroke="white" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M12 7v10M7 9.5v5M17 9.5v5" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:16px; font-weight:700; color:var(--text-bright);">Connect Web3 Wallet</div>
                    <div style="font-size:11px; color:var(--text-muted);">Connect your crypto wallet to deposit & trade</div>
                </div>
            </div>
            <button onclick="closeWeb3Modal()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:20px; padding:4px 8px; border-radius:8px;">&times;</button>
        </div>

        <!-- Modal Body -->
        <div style="padding:20px 24px;" id="web3ModalBody">

            <!-- Step 1: Wallet Selection -->
            <div id="web3StepSelect">
                <div style="font-size:12px; color:var(--text-muted); margin-bottom:14px;">Choose your preferred wallet provider:</div>
                <div style="display:flex; flex-direction:column; gap:10px;" id="walletProviderList">
                    <!-- Wallet providers rendered by JS -->
                </div>
                
                <div style="margin-top:16px; padding:14px; border-radius:10px; background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.15);">
                    <div style="display:flex; align-items:flex-start; gap:10px;">
                        <i class="fas fa-shield-alt" style="color:var(--purple-3); font-size:14px; margin-top:2px;"></i>
                        <div>
                            <div style="font-size:12px; font-weight:600; color:var(--text-bright);">Secure Connection</div>
                            <div style="font-size:11px; color:var(--text-muted); margin-top:2px; line-height:1.5;">
                                We never store your private keys. Connection is secured via cryptographic signature verification. 
                                You'll be asked to sign a message to prove wallet ownership.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Connecting -->
            <div id="web3StepConnecting" style="display:none; text-align:center; padding:40px 0;">
                <div class="spinner-border" style="width:48px; height:48px; color:var(--purple-3);" role="status"></div>
                <div style="font-size:14px; font-weight:600; color:var(--text-bright); margin-top:16px;" id="connectingText">Connecting to MetaMask...</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;" id="connectingSubtext">Please confirm the connection in your wallet</div>
            </div>

            <!-- Step 3: Signature Request -->
            <div id="web3StepSign" style="display:none; text-align:center; padding:40px 0;">
                <div style="width:60px; height:60px; border-radius:50%; background:rgba(168,85,247,0.15); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                    <i class="fas fa-signature" style="font-size:24px; color:var(--purple-3);"></i>
                </div>
                <div style="font-size:14px; font-weight:600; color:var(--text-bright);">Signature Required</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px; max-width:100%;max-width:320px; margin-left:auto; margin-right:auto; line-height:1.5;">
                    Please sign the message in your wallet to verify ownership. This does not send any transaction or cost gas.
                </div>
                <div style="margin-top:16px; padding:12px; border-radius:10px; background:var(--bg-input); border:1px solid var(--border);">
                    <div style="font-size:11px; color:var(--text-dim); margin-bottom:4px;">Wallet Address:</div>
                    <div style="font-size:12px; color:var(--text-bright); font-family:monospace; word-break:break-all;" id="signAddress">0x...</div>
                </div>
            </div>

            <!-- Step 4: Success -->
            <div id="web3StepSuccess" style="display:none; text-align:center; padding:40px 0;">
                <div style="width:60px; height:60px; border-radius:50%; background:rgba(16,185,129,0.15); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                    <i class="fas fa-check-circle" style="font-size:28px; color:var(--green);"></i>
                </div>
                <div style="font-size:16px; font-weight:700; color:var(--text-bright);">Wallet Connected!</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Your wallet has been successfully linked to your account.</div>
                <div style="margin-top:16px; padding:12px; border-radius:10px; background:var(--bg-input); border:1px solid var(--border);">
                    <div style="font-size:11px; color:var(--text-dim); margin-bottom:4px;">Connected Address:</div>
                    <div style="font-size:13px; color:var(--text-bright); font-family:monospace; word-break:break-all;" id="successAddress">0x...</div>
                </div>
                <button onclick="closeWeb3Modal()" style="margin-top:20px; padding:10px 24px; border-radius:10px; background:var(--gradient-primary); color:white; border:none; font-weight:600; cursor:pointer; font-size:13px;">Done</button>
            </div>

            <!-- Step 5: Error -->
            <div id="web3StepError" style="display:none; text-align:center; padding:40px 0;">
                <div style="width:60px; height:60px; border-radius:50%; background:rgba(239,68,68,0.15); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                    <i class="fas fa-times-circle" style="font-size:28px; color:var(--red);"></i>
                </div>
                <div style="font-size:16px; font-weight:700; color:var(--text-bright);">Connection Failed</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px; max-width:100%;max-width:320px; margin-left:auto; margin-right:auto;" id="errorText">An error occurred while connecting.</div>
                <button onclick="resetWeb3Modal()" style="margin-top:20px; padding:10px 24px; border-radius:10px; background:var(--bg-input); color:var(--text-bright); border:1px solid var(--border); font-weight:600; cursor:pointer; font-size:13px;">Try Again</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ========== WEB3 WALLET CONFIGURATION ==========
let web3Config = null;
let web3Nonce = null;
let connectedWallets = [];

// Wallet provider definitions
const WALLET_PROVIDERS = {
    metamask: {
        label: 'MetaMask',
        icon: '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><path d="M27.5 5.5L17.5 13L19.5 8.5L27.5 5.5z" fill="#E2761B"/><path d="M4.5 5.5L14.5 13L12.5 8.5L4.5 5.5z" fill="#E4761B"/><path d="M24 22L21 19L22.5 27L24 22z" fill="#E4761B"/><path d="M8 22L6.5 27L8 19L8 22z" fill="#E4761B"/><path d="M10 14L8.5 16.5L11 16L10 14z" fill="#E4761B"/><path d="M22 14L21 16L23.5 16.5L22 14z" fill="#E4761B"/><path d="M8 22L10 19L12 19.5L10 22.5L8 22z" fill="#D7C1B3"/><path d="M24 22L22 22.5L20 19.5L22 19L24 22z" fill="#D7C1B3"/><path d="M10 22.5L12.5 30L10 22.5z" fill="#233447"/><path d="M22 22.5L22.5 30L22 22.5z" fill="#233447"/><path d="M10 22.5L12 19.5L10.5 19L10 22.5z" fill="#CD6116"/><path d="M22 22.5L22 19L20.5 19.5L22 22.5z" fill="#CD6116"/><path d="M8 22.5L10 30L6.5 27L8 22.5z" fill="#E4751F"/><path d="M24 22.5L25.5 27L22 30L24 22.5z" fill="#E4751F"/><path d="M19.5 19L20 22L22 22.5L20.5 19.5L19.5 19z" fill="#F6851B"/><path d="M12.5 19L10.5 19.5L10 22.5L12 19.5L12.5 19z" fill="#F6851B"/><path d="M12 19.5L10 22L8 19L10.5 16.5L12 19.5z" fill="#763D16"/><path d="M20 19L22 19L24 19.5L21.5 16.5L20 19z" fill="#763D16"/><path d="M22 19L24 22L22.5 19.5L22 19z" fill="#763D16"/><path d="M8 19L10 22L8 22.5L8 19z" fill="#763D16"/><path d="M10.5 16.5L12 13L10 16L10.5 16.5z" fill="#E2761B"/><path d="M21.5 16.5L22 16L20 13L21.5 16.5z" fill="#E2761B"/><path d="M12 13L10.5 16.5L12 19.5L12 13z" fill="#E2761B"/><path d="M20 13L20 19.5L21.5 16.5L20 13z" fill="#E2761B"/><path d="M12 13L16 16L12 13z" fill="#F6851B"/><path d="M20 13L16 16L20 13z" fill="#F6851B"/><path d="M16 16L12 19.5L16 16z" fill="#F6851B"/><path d="M16 16L20 19.5L16 16z" fill="#F6851B"/><path d="M16 16L12 13L16 16z" fill="#E4751F"/><path d="M16 16L20 13L16 16z" fill="#E4751F"/><path d="M16 16L16 30L12 30L16 16z" fill="#6E2D1B"/><path d="M16 16L16 30L20 30L16 16z" fill="#6E2D1B"/><path d="M12 30L16 16L12 19.5L10 22.5L12 30z" fill="#E4761B"/><path d="M20 30L16 16L20 19.5L22 22.5L20 30z" fill="#E4761B"/><path d="M16 16L12 13L20 13L16 16z" fill="#F6851B"/></svg>',
        color: '#E2761B',
        detect: () => typeof window.ethereum !== 'undefined' && window.ethereum.isMetaMask,
        connect: connectMetaMask,
        detectHint: 'Browser extension required',
    },
    walletconnect: {
        label: 'WalletConnect',
        icon: '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><path d="M16 4C10.5 4 6 8.5 6 14c0 5.5 4.5 10 10 10s10-4.5 10-10c0-5.5-4.5-10-10-10zm-4.5 13.5c0-2.5 2-4.5 4.5-4.5s4.5 2 4.5 4.5" stroke="#3B99FC" stroke-width="2.5" fill="none" stroke-linecap="round"/><path d="M10 11.5c3.3-3.3 8.7-3.3 12 0M12.5 14c1.8-1.8 4.7-1.8 6.5 0" stroke="#3B99FC" stroke-width="2" fill="none" stroke-linecap="round"/><circle cx="16" cy="17.5" r="1.5" fill="#3B99FC"/></svg>',
        color: '#3B99FC',
        detect: () => true, // WalletConnect works without extension
        connect: connectWalletConnect,
        detectHint: 'Scan QR with mobile wallet',
    },
    trust: {
        label: 'Trust Wallet',
        icon: '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><path d="M16 3L5 8v8c0 6.5 4.5 12 11 13 6.5-1 11-6.5 11-13V8L16 3z" fill="#0CB7E5"/><path d="M16 6L8 10v6c0 4.5 3.5 8.5 8 9 4.5-0.5 8-4.5 8-9v-6L16 6z" fill="#1A73E8"/><path d="M16 10L12 12v4c0 2.5 2 4.5 4 5 2-0.5 4-2.5 4-5v-4L16 10z" fill="white"/></svg>',
        color: '#0CB7E5',
        detect: () => typeof window.ethereum !== 'undefined' && (window.ethereum.isTrust || window.ethereum.isTrustWallet),
        connect: connectMetaMask, // Trust injects as ethereum provider
        detectHint: 'Browser extension or mobile app',
    },
    coinbase: {
        label: 'Coinbase Wallet',
        icon: '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="12" fill="#0052FF"/><circle cx="16" cy="16" r="6" fill="white"/><rect x="13" y="13" width="6" height="6" rx="1" fill="#0052FF"/></svg>',
        color: '#0052FF',
        detect: () => typeof window.ethereum !== 'undefined' && window.ethereum.isCoinbaseWallet,
        connect: connectMetaMask, // Coinbase injects as ethereum provider
        detectHint: 'Browser extension or mobile app',
    },
    rabby: {
        label: 'Rabby Wallet',
        icon: '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="12" fill="#8696FF"/><path d="M10 12c2-2 4-2 6 0M16 12c2-2 4-2 6 0M12 18c2 2 6 2 8 0" stroke="white" stroke-width="2" fill="none" stroke-linecap="round"/></svg>',
        color: '#8696FF',
        detect: () => typeof window.ethereum !== 'undefined' && window.ethereum.isRabby,
        connect: connectMetaMask,
        detectHint: 'Browser extension required',
    },
    okx: {
        label: 'OKX Wallet',
        icon: '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><rect x="5" y="5" width="8" height="8" rx="2" fill="#000"/><rect x="19" y="5" width="8" height="8" rx="2" fill="#000"/><rect x="5" y="19" width="8" height="8" rx="2" fill="#000"/><rect x="19" y="19" width="8" height="8" rx="2" fill="#000"/></svg>',
        color: '#000000',
        detect: () => typeof window.okxwallet !== 'undefined',
        connect: connectMetaMask,
        detectHint: 'Browser extension required',
    },
    phantom: {
        label: 'Phantom',
        icon: '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="14" r="10" fill="#AB9FF2"/><path d="M10 20c2 3 4 4 6 4s4-1 6-4" stroke="#AB9FF2" stroke-width="2" fill="none"/><circle cx="13" cy="13" r="2" fill="white"/><circle cx="19" cy="13" r="2" fill="white"/></svg>',
        color: '#AB9FF2',
        detect: () => typeof window.phantom !== 'undefined',
        connect: connectMetaMask,
        detectHint: 'Solana & EVM support',
    },
};

// ========== INIT ==========
document.addEventListener('DOMContentLoaded', function() {
    loadWeb3Config();
    loadConnectedWallets();
});

async function loadWeb3Config() {
    try {
        const res = await fetch('{{ route("dashboard.web3.config") }}', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        if (data.success) {
            web3Config = data;
            web3Nonce = data.nonce;
            renderWalletProviders();
        }
    } catch(e) {
        console.error('Web3 config error:', e);
    }
}

function renderWalletProviders() {
    const list = document.getElementById('walletProviderList');
    if (!list || !web3Config) return;

    const enabledWallets = web3Config.wallets || ['metamask', 'walletconnect', 'trust', 'coinbase'];
    let html = '';

    enabledWallets.forEach(type => {
        const wallet = WALLET_PROVIDERS[type];
        if (!wallet) return;

        const detected = wallet.detect();
        const hasExtension = type === 'walletconnect' || detected;

        html += `
            <div onclick="${hasExtension ? 'connectWallet("' + type + '")' : 'installWallet("' + type + '")'}" 
                 style="display:flex; align-items:center; gap:14px; padding:14px 16px; border-radius:14px; background:var(--bg-input); border:1px solid var(--border); cursor:pointer; transition:all 0.2s;"
                 onmouseover="this.style.borderColor='var(--purple-3)'; this.style.background='rgba(99,102,241,0.08)';"
                 onmouseout="this.style.borderColor='var(--border)'; this.style.background='var(--bg-input)';">
                
                <div style="width:44px; height:44px; border-radius:12px; background:rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    ${wallet.icon}
                </div>
                
                <div style="flex:1;">
                    <div style="font-size:14px; font-weight:600; color:var(--text-bright);">${wallet.label}</div>
                    <div style="font-size:11px; color:var(--text-muted);">${wallet.detectHint}</div>
                </div>
                
                <div style="flex-shrink:0;">
                    ${detected 
                        ? '<span style="font-size:10px; padding:3px 8px; border-radius:6px; background:rgba(16,185,129,0.15); color:#10b981; font-weight:600;">DETECTED</span>'
                        : (type === 'walletconnect' 
                            ? '<i class="fas fa-arrow-right" style="color:var(--text-dim); font-size:14px;"></i>'
                            : '<span style="font-size:10px; padding:3px 8px; border-radius:6px; background:rgba(245,158,11,0.15); color:#f59e0b; font-weight:600;">INSTALL</span>'
                        )
                    }
                </div>
            </div>
        `;
    });

    list.innerHTML = html;
}

// ========== MODAL CONTROL ==========
function openWeb3Modal() {
    const modal = document.getElementById('web3Modal');
    modal.style.display = 'flex';
    resetWeb3Modal();
}

function closeWeb3Modal() {
    document.getElementById('web3Modal').style.display = 'none';
    resetWeb3Modal();
}

function resetWeb3Modal() {
    document.getElementById('web3StepSelect').style.display = 'block';
    document.getElementById('web3StepConnecting').style.display = 'none';
    document.getElementById('web3StepSign').style.display = 'none';
    document.getElementById('web3StepSuccess').style.display = 'none';
    document.getElementById('web3StepError').style.display = 'none';
}

function showStep(stepId) {
    ['web3StepSelect', 'web3StepConnecting', 'web3StepSign', 'web3StepSuccess', 'web3StepError'].forEach(id => {
        document.getElementById(id).style.display = 'none';
    });
    document.getElementById(stepId).style.display = 'block';
}

function showError(msg) {
    document.getElementById('errorText').textContent = msg;
    showStep('web3StepError');
}

// ========== WALLET CONNECTION ==========
async function connectWallet(type) {
    const wallet = WALLET_PROVIDERS[type];
    if (!wallet) return;

    try {
        showStep('web3StepConnecting');
        document.getElementById('connectingText').textContent = 'Connecting to ' + wallet.label + '...';
        document.getElementById('connectingSubtext').textContent = 'Please confirm the connection in your wallet';

        const result = await wallet.connect(type);

        if (result && result.address) {
            // If signature verification is required
            if (web3Config && web3Config.require_sig && web3Nonce) {
                showStep('web3StepSign');
                document.getElementById('signAddress').textContent = result.address;

                let signature = result.signature;
                if (!signature) {
                    // Request signature
                    signature = await requestSignature(result.address, type);
                }

                if (signature) {
                    await saveWalletConnection(type, result, signature);
                } else {
                    // Connect without signature (user rejected)
                    await saveWalletConnection(type, result, null);
                }
            } else {
                await saveWalletConnection(type, result, null);
            }
        }
    } catch(err) {
        console.error('Connect error:', err);
        showError(err.message || 'Failed to connect wallet. Please try again.');
    }
}

// ========== METAMASK / EIP-1193 PROVIDER ==========
async function connectMetaMask(type) {
    if (typeof window.ethereum === 'undefined') {
        // Try specific providers
        const providers = {
            'trust': window.trustwallet,
            'coinbase': window.coinbaseWalletExtension,
            'rabby': window.rabby,
            'okx': window.okxwallet,
        };
        
        if (providers[type]) {
            window.ethereum = providers[type];
        } else {
            throw new Error('No Web3 wallet detected. Please install MetaMask or another Web3 wallet extension.');
        }
    }

    // Request accounts
    const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
    if (!accounts || accounts.length === 0) {
        throw new Error('No accounts returned. Please unlock your wallet and try again.');
    }

    // Get chain ID
    const chainId = await window.ethereum.request({ method: 'eth_chainId' });
    const chainIdNum = parseInt(chainId, 16);
    
    // Find network name from config
    let networkName = 'Unknown';
    if (web3Config && web3Config.networks) {
        const net = web3Config.networks.find(n => n.chain_id === chainIdNum);
        if (net) networkName = net.name;
    }

    return {
        address: accounts[0],
        chain_id: String(chainIdNum),
        network_name: networkName,
    };
}

// ========== WALLETCONNECT ==========
async function connectWalletConnect(type) {
    // WalletConnect v2 — requires projectId
    if (!web3Config || !web3Config.project_id) {
        // Fallback: try injecting via WalletConnect modal
        // Load WalletConnect provider dynamically
        await loadWalletConnectProvider();
    }

    // If we have a WalletConnect provider
    if (window.WalletConnectProvider) {
        const provider = new window.WalletConnectProvider.default({
            infuraId: web3Config?.project_id || 'default',
        });

        await provider.enable();
        const accounts = provider.accounts;
        const chainId = provider.chainId;

        let networkName = 'Unknown';
        if (web3Config && web3Config.networks) {
            const net = web3Config.networks.find(n => n.chain_id === chainId);
            if (net) networkName = net.name;
        }

        return {
            address: accounts[0],
            chain_id: String(chainId),
            network_name: networkName,
        };
    }

    throw new Error('WalletConnect not available. Please use MetaMask or another browser extension wallet.');
}

async function loadWalletConnectProvider() {
    return new Promise((resolve, reject) => {
        if (window.WalletConnectProvider) { resolve(); return; }
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/@walletconnect/ethereum-provider@2.13.0/dist/index.umd.min.js';
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

// ========== SIGNATURE VERIFICATION ==========
async function requestSignature(address, type) {
    try {
        const message = `Welcome to ${window.location.hostname}!\n\nI confirm ownership of wallet ${address}.\n\nNonce: ${web3Nonce}`;

        let signature;
        if (type === 'walletconnect' && window.WalletConnectProvider) {
            // Sign via WalletConnect provider
            const provider = window._wcProvider;
            signature = await provider.request({
                method: 'personal_sign',
                params: [message, address],
            });
        } else {
            // Sign via ethereum provider (MetaMask etc.)
            signature = await window.ethereum.request({
                method: 'personal_sign',
                params: [message, address],
            });
        }

        return signature;
    } catch(err) {
        console.error('Signature error:', err);
        return null;
    }
}

// ========== SAVE CONNECTION TO SERVER ==========
async function saveWalletConnection(type, walletData, signature) {
    showStep('web3StepConnecting');
    document.getElementById('connectingText').textContent = 'Saving connection...';
    document.getElementById('connectingSubtext').textContent = 'Linking wallet to your account';

    try {
        const res = await fetch('{{ route("dashboard.web3.connect") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                wallet_type: type,
                address: walletData.address,
                chain_id: walletData.chain_id,
                network_name: walletData.network_name,
                signature: signature,
                require_sig: web3Config?.require_sig || false,
            }),
        });

        const data = await res.json();

        if (data.success) {
            showStep('web3StepSuccess');
            document.getElementById('successAddress').textContent = walletData.address;
            loadConnectedWallets(); // Refresh wallet list
        } else {
            showError(data.message || 'Failed to save wallet connection.');
        }
    } catch(err) {
        showError('Network error: ' + (err.message || 'Failed to connect to server.'));
    }
}

// ========== LOAD CONNECTED WALLETS ==========
async function loadConnectedWallets() {
    try {
        const res = await fetch('{{ route("dashboard.web3.config") }}', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        
        if (data.success && data.connected) {
            connectedWallets = data.connected;
            renderConnectedWallets();
        }
    } catch(e) {
        console.error('Load wallets error:', e);
    }
}

function renderConnectedWallets() {
    const container = document.getElementById('web3WalletList');
    
    if (!connectedWallets || connectedWallets.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; padding:30px 0;">
                <div style="width:60px; height:60px; border-radius:50%; background:rgba(99,102,241,0.1); display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L2 7v10l10 5 10-5V7L12 2z" stroke="#6366f1" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div style="font-size:14px; font-weight:600; color:var(--text-bright);">No wallets connected</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Connect your Web3 wallet to enable crypto deposits</div>
                <button onclick="openWeb3Modal()" style="margin-top:14px; padding:8px 20px; border-radius:10px; background:var(--gradient-primary); color:white; border:none; font-weight:600; cursor:pointer; font-size:12px;">
                    <i class="fas fa-link"></i> Connect Wallet
                </button>
            </div>
        `;
        return;
    }

    let html = '';
    connectedWallets.forEach(w => {
        const provider = WALLET_PROVIDERS[w.wallet_type] || {};
        const icon = provider.icon || '👛';
        const label = provider.label || w.wallet_type;
        const isPrimary = w.is_primary;
        const verified = w.verified_at !== null;
        const shortAddr = w.address.length > 12 
            ? w.address.substring(0, 6) + '...' + w.address.substring(w.address.length - 4) 
            : w.address;

        html += `
            <div style="display:flex; align-items:center; gap:12px; padding:14px; border-radius:12px; background:var(--bg-input); border:1px solid var(--border); margin-bottom:8px;">
                <div style="width:40px; height:40px; border-radius:10px; background:rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    ${icon}
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                        <span style="font-size:13px; font-weight:600; color:var(--text-bright);">${label}</span>
                        ${isPrimary ? '<span style="font-size:9px; padding:1px 6px; border-radius:4px; background:rgba(168,85,247,0.15); color:#a855f7; font-weight:600;">PRIMARY</span>' : ''}
                        ${verified ? '<span style="font-size:9px; padding:1px 6px; border-radius:4px; background:rgba(16,185,129,0.15); color:#10b981; font-weight:600;"><i class="fas fa-check" style="font-size:8px;"></i> VERIFIED</span>' : '<span style="font-size:9px; padding:1px 6px; border-radius:4px; background:rgba(245,158,11,0.15); color:#f59e0b; font-weight:600;">UNVERIFIED</span>'}
                    </div>
                    <div style="font-size:12px; color:var(--text-muted); font-family:monospace; margin-top:2px;">${shortAddr}</div>
                    ${w.network_name ? '<div style="font-size:10px; color:var(--text-dim); margin-top:2px;"><i class="fas fa-network-wired"></i> ' + w.network_name + '</div>' : ''}
                </div>
                <div style="display:flex; gap:4px; flex-shrink:0;">
                    ${!isPrimary ? '<button onclick="setPrimaryWallet(' + w.id + ')" style="padding:5px 8px; border-radius:8px; background:rgba(168,85,247,0.1); border:1px solid rgba(168,85,247,0.2); color:#a855f7; cursor:pointer; font-size:10px; font-weight:600;" title="Set as primary"><i class="fas fa-star"></i></button>' : ''}
                    <button onclick="disconnectWallet(' + w.id + ')" style="padding:5px 8px; border-radius:8px; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2); color:#ef4444; cursor:pointer; font-size:10px; font-weight:600;" title="Disconnect"><i class="fas fa-unlink"></i></button>
                    <button onclick="copyAddress(\'' + w.address + '\', this)" style="padding:5px 8px; border-radius:8px; background:rgba(99,102,241,0.1); border:1px solid rgba(99,102,241,0.2); color:#6366f1; cursor:pointer; font-size:10px; font-weight:600;" title="Copy address"><i class="fas fa-copy"></i></button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// ========== WALLET ACTIONS ==========
async function disconnectWallet(id) {
    if (!confirm('Disconnect this wallet? You can reconnect it later.')) return;

    try {
        const res = await fetch(`/dashboard/web3/${id}/disconnect`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            loadConnectedWallets();
        } else {
            alert(data.message || 'Failed to disconnect wallet.');
        }
    } catch(e) {
        alert('Error: ' + e.message);
    }
}

async function setPrimaryWallet(id) {
    try {
        const res = await fetch(`/dashboard/web3/${id}/set-primary`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            loadConnectedWallets();
        }
    } catch(e) {
        alert('Error: ' + e.message);
    }
}

function copyAddress(addr, btn) {
    navigator.clipboard.writeText(addr).then(() => {
        const icon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => { btn.innerHTML = icon; }, 1500);
    });
}

function installWallet(type) {
    const urls = {
        'metamask': 'https://metamask.io/download/',
        'trust': 'https://trustwallet.com/download',
        'coinbase': 'https://www.coinbase.com/wallet/downloads',
        'rabby': 'https://rabby.io/',
        'okx': 'https://www.okx.com/web3',
        'phantom': 'https://phantom.app/download',
    };
    
    const url = urls[type] || 'https://ethereum.org/en/wallets/';
    window.open(url, '_blank');
}

// ========== METAMASK EVENT LISTENERS ==========
if (typeof window.ethereum !== 'undefined') {
    window.ethereum.on('accountsChanged', (accounts) => {
        if (accounts.length === 0) {
            // User disconnected
            loadConnectedWallets();
        } else {
            // Account changed — reload
            loadConnectedWallets();
        }
    });

    window.ethereum.on('chainChanged', () => {
        // Chain changed — could reload or update
        loadConnectedWallets();
    });
}
</script>
@endpush
@endsection