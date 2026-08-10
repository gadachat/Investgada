@extends('layouts.dashboard')

@section('title', 'Trade')

@section('content')
<div class="page-content">
    @if(session('success'))
        <div class="alert alert-success" id="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Fund recipient notice --}}
    @if($fundSummary['is_fund_recipient'] && !$fundSummary['target_met'])
    <div class="rounded-3 p-3 mb-3" style="background:linear-gradient(135deg,rgba(99,102,241,0.08),rgba(168,85,247,0.04));border:1px solid rgba(99,102,241,0.2)">
        <div style="display:flex;align-items:center;gap:8px">
            <i class="fas fa-shield-alt" style="color:var(--primary);font-size:16px"></i>
            <span style="font-size:13px;font-weight:600;color:var(--primary)">Special Fund Account</span>
            <span style="font-size:12px;color:var(--text-muted)">— You can trade with funded capital. Team progress: {{ $fundSummary['progress'] }}%</span>
        </div>
    </div>
    @endif

    @if(!$subscription)
    {{-- ═════════════════ NO SUBSCRIPTION — SHOW PACKAGES ═════════════════ --}}
    <div class="text-center mb-4">
        <h2 style="font-weight:700;color:var(--text)"><i class="fas fa-chart-line me-2" style="color:var(--primary)"></i>Trading Packages</h2>
        <p style="color:var(--text-muted);font-size:14px">Choose a package to start trading. Your deposit wallet funds will be transferred to your trading wallet.</p>
    </div>

    <div class="row g-3 mb-4">
        @foreach($packages as $pkg)
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card-custom h-100" style="border:2px solid {{ $pkg->sort_order === 2 ? 'rgba(168,85,247,0.3)' : 'var(--border)' }}">
                @if($pkg->sort_order === 2)
                <div style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#7c3aed,#a855f7);color:white;font-size:10px;padding:2px 12px;border-radius:10px;font-weight:600">POPULAR</div>
                @endif
                <div class="text-center" style="padding:8px 0">
                    <h4 style="font-weight:700;color:var(--text)">{{ $pkg->name }}</h4>
                    <div style="font-size:28px;font-weight:800;color:var(--primary)">
                        ${{ number_format((float)$pkg->min_amount) }}<span style="font-size:14px;color:var(--text-muted)"> – ${{ number_format((float)$pkg->max_amount) }}</span>
                    </div>
                    <p style="font-size:13px;color:var(--text-muted);margin:6px 0">{{ $pkg->description }}</p>
                </div>
                <div style="border-top:1px solid var(--border);padding-top:12px;margin-top:8px">
                    <ul style="list-style:none;padding:0;font-size:13px;color:var(--text)">
                        <li class="mb-2"><i class="fas fa-check-circle me-2" style="color:#10b981"></i> <strong>{{ $pkg->max_pairs == 99 ? 'Unlimited' : $pkg->max_pairs }}</strong> trading pair(s)</li>
                        <li class="mb-2"><i class="fas fa-{{ $pkg->scanner_enabled ? 'check' : 'times' }}-circle me-2" style="color:{{ $pkg->scanner_enabled ? '#10b981' : '#ef4444' }}"></i> Scanner access</li>
                        <li class="mb-2"><i class="fas fa-{{ $pkg->has_short_selling ? 'check' : 'times' }}-circle me-2" style="color:{{ $pkg->has_short_selling ? '#10b981' : '#ef4444' }}"></i> Short selling</li>
                        <li class="mb-2"><i class="fas fa-percentage me-2" style="color:var(--primary)"></i> <strong>{{ number_format((float)$pkg->daily_profit_percent, 2) }}%</strong> daily profit rate</li>
                        <li class="mb-2"><i class="fas fa-trophy me-2" style="color:#f59e0b"></i> <strong>{{ number_format((float)$pkg->win_rate_percent, 1) }}%</strong> win rate</li>
                    </ul>
                </div>
                <button type="button" class="btn w-100 mt-3" style="background:{{ $pkg->sort_order === 2 ? 'linear-gradient(135deg,#7c3aed,#a855f7)' : 'rgba(99,102,241,0.1)' }};color:{{ $pkg->sort_order === 2 ? 'white' : 'var(--primary)' }};font-weight:600"
                        onclick="openSubscribeModal('{{ $pkg->id }}', '{{ $pkg->name }}', '{{ $pkg->min_amount }}', '{{ $pkg->max_amount }}', '{{ $pkg->max_pairs }}', '{{ $pkg->scanner_enabled ? 1 : 0 }}')">
                    <i class="fas fa-rocket me-1"></i> Choose {{ $pkg->name }}
                </button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Subscribe Modal --}}
    <div class="modal fade" id="subscribeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background:var(--bg);border:1px solid var(--border)">
                <div class="modal-header" style="border-bottom:1px solid var(--border)">
                    <h5 class="modal-title" style="color:var(--text);font-weight:700">
                        <i class="fas fa-rocket me-1" style="color:var(--primary)"></i> Subscribe to <span id="subPkgName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('dashboard.trade.subscribe') }}">
                    @csrf
                    <input type="hidden" id="subPkgId" name="package_id">
                    <div class="modal-body">
                        {{-- Amount --}}
                        <div class="mb-3">
                            <label style="font-size:13px;color:var(--text-muted);font-weight:600">Amount (USD)</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:var(--bg);border:1px solid var(--border);color:var(--text-muted)">$</span>
                                <input type="number" name="amount" id="subAmount" class="form-control" required
                                       style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                            </div>
                            <small id="subAmountHint" style="font-size:11px;color:var(--text-muted)"></small>
                        </div>

                        {{-- Pair Selection --}}
                        <div class="mb-3">
                            <label style="font-size:13px;color:var(--text-muted);font-weight:600">Select Trading Pairs (<span id="pairCount">0</span>/<span id="maxPairs">1</span>)</label>
                            <div style="max-height:200px;overflow-y:auto;border:1px solid var(--border);border-radius:8px;padding:8px">
                                @php $allPairs = $allPairs; @endphp
                                @foreach($allPairs as $category => $pairs)
                                <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;padding:4px 0">{{ $category }}</div>
                                @foreach($pairs as $pair)
                                <label class="d-block" style="padding:4px 8px;cursor:pointer;border-radius:6px;font-size:13px">
                                    <input type="checkbox" name="selected_pairs[]" value="{{ $pair['symbol'] }}" class="pair-checkbox"
                                           onchange="updatePairCount()" style="margin-right:8px">
                                    {{ $pair['symbol'] }} — {{ $pair['name'] }}
                                </label>
                                @endforeach
                                @endforeach
                            </div>
                            <small style="font-size:11px;color:var(--text-muted)">Choose up to the maximum pairs for your tier</small>
                        </div>

                        {{-- Scanner --}}
                        <div class="form-check form-switch mb-2" id="scannerToggle" style="display:none">
                            <input type="checkbox" name="scanner_active" value="1" class="form-check-input" id="scannerActive">
                            <label for="scannerActive" style="font-size:13px;color:var(--text)">
                                <i class="fas fa-satellite-dish me-1" style="color:var(--primary)"></i> Enable Market Scanner
                            </label>
                            <small style="font-size:11px;color:var(--text-muted);display:block;margin-left:2.2em">Get AI-powered trade signals and recommendations</small>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid var(--border)">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="font-weight:600">
                            <i class="fas fa-check me-1"></i> Confirm Subscription
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @else
    {{-- ═════════════════ ACTIVE SUBSCRIPTION — TRADING INTERFACE ═════════════════ --}}
    <div class="row g-3 mb-3">
        <div class="col-md-8 col-12">
            <div class="rounded-3 p-3" style="background:linear-gradient(135deg,rgba(99,102,241,0.08),rgba(168,85,247,0.04));border:1px solid rgba(99,102,241,0.2)">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <span class="badge" style="background:linear-gradient(135deg,#6366f1,#a855f7);color:white;font-size:11px">{{ $subscription->package->name }}</span>
                        <span style="font-size:12px;color:var(--text-muted);margin-left:8px">Ref: {{ $subscription->reference }}</span>
                    </div>
                    <div class="d-flex gap-3 flex-wrap">
                        <div>
                            <span style="font-size:11px;color:var(--text-muted)">Trading Wallet</span>
                            <h5 class="mb-0" style="font-weight:700;color:var(--primary)">${{ number_format($tradingBalance, 2) }}</h5>
                        </div>
                        <div>
                            <span style="font-size:11px;color:var(--text-muted)">Total P&L</span>
                            <h5 class="mb-0" style="font-weight:700;color:{{ $subscription->netPnl() >= 0 ? '#10b981' : '#ef4444' }}">
                                {{ $subscription->netPnl() >= 0 ? '+' : '' }}${{ number_format(abs($subscription->netPnl()), 2) }}
                            </h5>
                        </div>
                        <div>
                            <span style="font-size:11px;color:var(--text-muted)">Win Rate</span>
                            <h5 class="mb-0" style="font-weight:700;color:var(--text)">{{ $subscription->winRate() }}%</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            @if($subscription->package->scanner_enabled)
            <button type="button" class="btn w-100" style="background:rgba(99,102,241,0.1);color:var(--primary);font-weight:600" onclick="runScanner()">
                <i class="fas fa-satellite-dish me-1"></i> Run Scanner
            </button>
            @endif
        </div>
    </div>

    {{-- Scanner Results --}}
    <div id="scannerResults" style="display:none" class="mb-3">
        <div class="card-custom">
            <div class="d-flex justify-content-between align-items-center p-3" style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0" style="font-weight:600;color:var(--primary)"><i class="fas fa-satellite-dish me-1"></i> Scanner Signals</h6>
                <small style="font-size:11px;color:var(--text-muted)" id="scanTime"></small>
            </div>
            <div id="scannerBody" style="max-height:300px;overflow-y:auto"></div>
        </div>
    </div>

    <div class="row g-3">
        {{-- LEFT: Chart + Positions --}}
        <div class="col-lg-8 col-md-7 col-12">
            <div class="card-custom mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center p-3" style="border-bottom:1px solid var(--border)">
                    <div class="d-flex align-items-center gap-2">
                        <select id="symbolSelect" class="form-select" style="width:auto;background:var(--bg);border:1px solid var(--border);color:var(--text);font-size:13px">
                            @foreach($availablePairs as $cat => $pairs)
                            <optgroup label="{{ ucfirst($cat) }}">
                                @foreach($pairs as $p)
                                <option value="{{ $p['symbol'] }}" data-market="{{ $cat }}">{{ $p['symbol'] }} — {{ $p['name'] }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <span style="font-size:11px;color:var(--text-muted)">Price</span>
                            <h5 class="mb-0" id="livePrice" style="font-weight:700;color:var(--text)">—</h5>
                        </div>
                    </div>
                </div>
                <div id="tradeIndToolbar" style="margin-bottom:8px;"></div>
                <div id="tradeChart" style="height:350px"></div>
                <div id="tradeIndSubCharts"></div>
            </div>

            {{-- Open Positions --}}
            <div class="card-custom">
                <div class="p-3" style="border-bottom:1px solid var(--border)">
                    <h5 class="mb-0" style="font-weight:600;color:var(--text)">
                        <i class="fas fa-layer-group me-1" style="color:var(--primary)"></i> Open Positions
                        <span class="badge ms-1" id="openCount" style="background:rgba(99,102,241,0.15);color:#a855f7">{{ $openPositions->count() }}</span>
                    </h5>
                </div>
                @if($openPositions->count() > 0)
                <div style="overflow-x:auto">
                    <table class="table table-hover mb-0" style="color:var(--text)">
                        <thead><tr style="color:var(--text-muted);font-size:12px;text-transform:uppercase">
                            <th>Symbol</th><th>Dir</th><th>Entry</th><th>Current</th><th>Margin</th><th>P&L</th><th></th>
                        </tr></thead>
                        <tbody id="openPositionsBody">
                            @foreach($openPositions as $pos)
                            <tr id="pos-row-{{ $pos->id }}">
                                <td style="font-weight:600;font-size:13px">{{ $pos->symbol }}</td>
                                <td>
                                    @if($pos->direction === 'buy')
                                    <span class="badge" style="background:rgba(16,185,129,0.15);color:#10b981"><i class="fas fa-arrow-up"></i> Long</span>
                                    @else
                                    <span class="badge" style="background:rgba(239,68,68,0.15);color:#ef4444"><i class="fas fa-arrow-down"></i> Short</span>
                                    @endif
                                </td>
                                <td style="font-size:13px">{{ number_format((float)$pos->entry_price, (float)$pos->entry_price < 1 ? 4 : 2) }}</td>
                                <td style="font-size:13px" id="current-{{ $pos->id }}">{{ number_format((float)$pos->current_price, (float)$pos->current_price < 1 ? 4 : 2) }}</td>
                                <td style="font-size:13px">${{ number_format((float)$pos->amount, 2) }}</td>
                                <td style="font-size:13px;font-weight:600" id="pnl-{{ $pos->id }}">
                                    @php $pnl = (float)$pos->pnl; @endphp
                                    <span style="color:{{ $pnl >= 0 ? '#10b981' : '#ef4444' }}">{{ $pnl >= 0 ? '+' : '' }}${{ number_format(abs($pnl), 2) }}</span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('dashboard.trade.close', $pos) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="background:rgba(99,102,241,0.1);color:var(--primary);font-size:11px;padding:4px 12px" onclick="return confirm('Close this position?')">
                                            <i class="fas fa-times me-1"></i> Close
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-line" style="font-size:36px;color:var(--text-muted);opacity:0.3"></i>
                    <p style="color:var(--text-muted);font-size:13px;margin-top:8px">No open positions. Place your first trade below!</p>
                </div>
                @endif
            </div>

            {{-- Recent Closed --}}
            @if($closedPositions->count() > 0)
            <div class="card-custom mt-3">
                <div class="p-3" style="border-bottom:1px solid var(--border)">
                    <h5 class="mb-0" style="font-weight:600;color:var(--text);font-size:14px">
                        <i class="fas fa-history me-1" style="color:var(--text-muted)"></i> Recent Trades
                    </h5>
                </div>
                <div style="overflow-x:auto">
                    <table class="table table-hover mb-0" style="color:var(--text)">
                        <thead><tr style="color:var(--text-muted);font-size:12px;text-transform:uppercase">
                            <th>Symbol</th><th>Dir</th><th>Entry</th><th>Close</th><th>P&L</th><th>Status</th><th>Time</th>
                        </tr></thead>
                        <tbody>
                            @foreach($closedPositions as $pos)
                            <tr>
                                <td style="font-size:13px;font-weight:600">{{ $pos->symbol }}</td>
                                <td style="font-size:12px">@if($pos->direction === 'buy')<span style="color:#10b981">Long</span>@else<span style="color:#ef4444">Short</span>@endif</td>
                                <td style="font-size:13px">{{ number_format((float)$pos->entry_price, (float)$pos->entry_price < 1 ? 4 : 2) }}</td>
                                <td style="font-size:13px">{{ number_format((float)$pos->close_price, (float)$pos->close_price < 1 ? 4 : 2) }}</td>
                                <td style="font-size:13px;font-weight:600">
                                    @php $cp = (float)$pos->close_pnl; @endphp
                                    <span style="color:{{ $cp >= 0 ? '#10b981' : '#ef4444' }}">{{ $cp >= 0 ? '+' : '' }}${{ number_format(abs($cp), 2) }}</span>
                                </td>
                                <td>
                                    @php $sc = ['closed'=>['#6366f1','CLOSED'],'tp_hit'=>['#10b981','TP HIT'],'sl_hit'=>['#ef4444','SL HIT'],'liquidated'=>['#ef4444','LIQUIDATED']][$pos->status] ?? ['#6366f1','CLOSED']; @endphp
                                    <span class="badge" style="background:rgba({{ $sc[0] }},0.15);color:{{ $sc[0] }};font-size:11px">{{ $sc[1] }}</span>
                                </td>
                                <td style="font-size:12px;color:var(--text-muted)">{{ $pos->closed_at?->format('M d, H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-2 text-end">
                    <a href="{{ route('dashboard.trade.history') }}" style="font-size:12px;color:var(--primary);text-decoration:none">View all trades →</a>
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT: Order Panel --}}
        <div class="col-lg-4 col-md-5 col-12">
            <div class="card-custom" style="position:sticky;top:80px">
                <h5 class="mb-3" style="font-weight:600;color:var(--text)">
                    <i class="fas fa-paper-plane me-1" style="color:var(--primary)"></i> Place Trade
                </h5>

                <div class="rounded-3 p-2 mb-3" style="background:var(--bg);border:1px solid var(--border)">
                    <div class="d-flex justify-content-between">
                        <span style="font-size:12px;color:var(--text-muted)">Trading Wallet</span>
                        <span style="font-weight:700;color:var(--primary)">${{ number_format($tradingBalance, 2) }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('dashboard.trade.open') }}" id="orderForm">
                    @csrf
                    <input type="hidden" name="symbol" id="orderSymbol" value="{{ $availablePairs ? collect($availablePairs)->flatten(1)->first()['symbol'] ?? 'BTC' : 'BTC' }}">
                    <input type="hidden" name="market_type" id="orderMarket" value="crypto">

                    @if($subscription->package->has_short_selling)
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="d-block">
                                <input type="radio" name="direction" value="buy" id="dirBuy" checked style="display:none">
                                <div class="text-center py-2 rounded-3" id="buyBtn" style="border:2px solid #10b981;background:rgba(16,185,129,0.1);cursor:pointer;font-weight:600;color:#10b981">
                                    <i class="fas fa-arrow-up"></i> BUY / LONG
                                </div>
                            </label>
                        </div>
                        <div class="col-6">
                            <label class="d-block">
                                <input type="radio" name="direction" value="sell" id="dirSell" style="display:none">
                                <div class="text-center py-2 rounded-3" id="sellBtn" style="border:2px solid var(--border);background:var(--bg);cursor:pointer;font-weight:600;color:var(--text-muted)">
                                    <i class="fas fa-arrow-down"></i> SELL / SHORT
                                </div>
                            </label>
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="direction" value="buy">
                    <div class="rounded-3 p-2 mb-3 text-center" style="background:rgba(16,185,129,0.05);border:1px solid rgba(16,185,129,0.15)">
                        <span style="font-size:13px;color:#10b981;font-weight:600"><i class="fas fa-arrow-up me-1"></i> Buy / Long Only</span>
                        <small style="font-size:11px;color:var(--text-muted);display:block">Upgrade to Premium for short selling</small>
                    </div>
                    @endif

                    <div class="mb-2">
                        <label style="font-size:12px;color:var(--text-muted)">Amount (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:var(--bg);border:1px solid var(--border);color:var(--text-muted)">$</span>
                            <input type="number" name="amount" class="form-control" min="1" step="0.01" value="10" required
                                   style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                        </div>
                        <small style="font-size:11px;color:var(--text-muted)">Deducted from your trading wallet</small>
                    </div>

                    <div class="mb-2">
                        <label style="font-size:12px;color:var(--text-muted)">Take Profit (optional)</label>
                        <input type="number" name="take_profit" class="form-control" step="0.0001" placeholder="Auto"
                               style="background:var(--bg);border:1px solid var(--border);color:var(--text);font-size:13px">
                    </div>
                    <div class="mb-3">
                        <label style="font-size:12px;color:var(--text-muted)">Stop Loss (optional)</label>
                        <input type="number" name="stop_loss" class="form-control" step="0.0001" placeholder="Auto"
                               style="background:var(--bg);border:1px solid var(--border);color:var(--text);font-size:13px">
                    </div>

                    <button type="submit" class="btn w-100" id="submitBtn" style="background:linear-gradient(135deg,#10b981,#059669);color:white;font-weight:600;padding:12px">
                        <i class="fas fa-bolt me-1"></i> Open {{ $subscription->package->has_short_selling ? 'Long' : 'Buy' }} Position
                    </button>
                </form>

                {{-- Withdraw from trading wallet --}}
                @if($openPositions->count() === 0 && $tradingBalance > 0)
                <hr style="border-color:var(--border);margin:16px 0">
                <form method="POST" action="{{ route('dashboard.trade.withdraw') }}">
                    @csrf
                    <label style="font-size:12px;color:var(--text-muted)">Withdraw from Trading Wallet</label>
                    <div class="input-group mb-2">
                        <span class="input-group-text" style="background:var(--bg);border:1px solid var(--border);color:var(--text-muted)">$</span>
                        <input type="number" name="amount" class="form-control" min="1" max="{{ $tradingBalance }}" step="0.01" required
                               style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                    </div>
                    <button type="submit" class="btn btn-outline-secondary w-100" style="font-size:12px">
                        <i class="fas fa-arrow-left me-1"></i> Transfer to Deposit Wallet
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

<script>
// ── Subscribe Modal ──
function openSubscribeModal(id, name, min, max, maxPairs, scanner) {
    document.getElementById('subPkgName').textContent = name;
    document.getElementById('subPkgId').value = id;
    document.getElementById('subAmount').min = min;
    document.getElementById('subAmount').max = max;
    document.getElementById('subAmount').value = min;
    document.getElementById('subAmountHint').textContent = `Range: $${min} – $${max}`;
    document.getElementById('maxPairs').textContent = maxPairs;
    document.getElementById('pairCount').textContent = '0';

    // Reset checkboxes
    document.querySelectorAll('.pair-checkbox').forEach(cb => { cb.checked = false; cb.disabled = false; });

    // Show/hide scanner
    document.getElementById('scannerToggle').style.display = scanner === '1' ? 'block' : 'none';

    new bootstrap.Modal(document.getElementById('subscribeModal')).show();
}

function updatePairCount() {
    const checked = document.querySelectorAll('.pair-checkbox:checked');
    const max = parseInt(document.getElementById('maxPairs').textContent);
    document.getElementById('pairCount').textContent = checked.length;

    if (checked.length >= max) {
        document.querySelectorAll('.pair-checkbox:not(:checked)').forEach(cb => cb.disabled = true);
    } else {
        document.querySelectorAll('.pair-checkbox').forEach(cb => cb.disabled = false);
    }
}

// ── Symbol selection ──
@if($subscription)
document.getElementById('symbolSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    document.getElementById('orderSymbol').value = this.value;
    document.getElementById('orderMarket').value = selected.dataset.market || 'crypto';
    updateChart();
});

// ── Direction toggle ──
@if($subscription->package->has_short_selling)
const buyBtn = document.getElementById('buyBtn');
const sellBtn = document.getElementById('sellBtn');
const submitBtn = document.getElementById('submitBtn');
function setDirection(dir) {
    if (dir === 'buy') {
        document.getElementById('dirBuy').checked = true;
        buyBtn.style.border = '2px solid #10b981'; buyBtn.style.background = 'rgba(16,185,129,0.1)'; buyBtn.style.color = '#10b981';
        sellBtn.style.border = '2px solid var(--border)'; sellBtn.style.background = 'var(--bg)'; sellBtn.style.color = 'var(--text-muted)';
        submitBtn.style.background = 'linear-gradient(135deg,#10b981,#059669)';
        submitBtn.innerHTML = '<i class="fas fa-bolt me-1"></i> Open Long Position';
    } else {
        document.getElementById('dirSell').checked = true;
        sellBtn.style.border = '2px solid #ef4444'; sellBtn.style.background = 'rgba(239,68,68,0.1)'; sellBtn.style.color = '#ef4444';
        buyBtn.style.border = '2px solid var(--border)'; buyBtn.style.background = 'var(--bg)'; buyBtn.style.color = 'var(--text-muted)';
        submitBtn.style.background = 'linear-gradient(135deg,#ef4444,#dc2626)';
        submitBtn.innerHTML = '<i class="fas fa-bolt me-1"></i> Open Short Position';
    }
}
buyBtn.addEventListener('click', () => setDirection('buy'));
sellBtn.addEventListener('click', () => setDirection('sell'));
@endif

// ── Chart ──
let chart;
function updateChart() {
    const symbol = document.getElementById('symbolSelect').value;
    fetch(`{{ route('dashboard.crypto-feed') }}?symbol=${encodeURIComponent(symbol)}&points=60`)
        .then(r => r.json())
        .then(data => {
            if (data.candles) {
                renderChart(data.candles, symbol);
                const last = data.candles[data.candles.length - 1];
                const price = last.y[3];
                document.getElementById('livePrice').textContent = '$' + price.toFixed(price < 1 ? 4 : 2);
            }
        }).catch(() => {});
}
// Include indicators engine
@include('dashboard.partials._indicators')
IndicatorManager.buildToolbar('tradeIndToolbar');
// Override sub-chart container ID
document.getElementById('indicatorSubCharts') && document.getElementById('indicatorSubCharts').remove();

var _tradeCandles = [];
function renderChart(candles, symbol) {
    _tradeCandles = candles.map(c => ({ x: new Date(c.x), y: c.y }));
    const options = {
        chart: { type: 'candlestick', height: 350, background: 'transparent', toolbar: { show: false }, animations: { enabled: false } },
        series: [{ data: _tradeCandles }],
        xaxis: { type: 'datetime', labels: { style: { colors: '#94a3b8' } } },
        yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: v => '$' + v.toFixed(v < 1 ? 4 : 2) } },
        grid: { borderColor: 'rgba(148,163,184,0.1)' },
        plotOptions: { candlestick: { colors: { upward: '#10b981', downward: '#ef4444' } } },
    };
    if (chart) { chart.updateOptions(options); } else { chart = new ApexCharts(document.getElementById('tradeChart'), options); chart.render(); }

    // Apply indicator overlays after render
    setTimeout(function() {
        if (typeof IndicatorManager !== 'undefined' && _tradeCandles.length > 0) {
            var overlaySeries = IndicatorManager.getOverlaySeries(_tradeCandles);
            if (overlaySeries.length > 0 && chart) {
                overlaySeries.forEach(function(s) { chart.appendSeries(s); });
            }
            IndicatorManager.resetInfoStrip();
            IndicatorManager.renderSubCharts(_tradeCandles);
        }
    }, 100);
}

function onIndicatorToggle() {
    if (_tradeCandles.length > 0) {
        renderChart(_tradeCandles, '');
    }
}

// ── Live P&L ──
function updatePositions() {
    fetch('{{ route("dashboard.trade.update") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    }).then(r => r.json()).then(data => {
        if (data.success && data.positions) {
            data.positions.forEach(p => {
                const pnlEl = document.getElementById('pnl-' + p.id);
                const currEl = document.getElementById('current-' + p.id);
                if (pnlEl) { const c = p.pnl >= 0 ? '#10b981' : '#ef4444'; const s = p.pnl >= 0 ? '+' : ''; pnlEl.innerHTML = `<span style="color:${c}">${s}$${Math.abs(p.pnl).toFixed(2)}</span>`; }
                if (currEl) { currEl.textContent = p.current_price.toFixed(p.current_price < 1 ? 4 : 2); }
            });
        }
    }).catch(() => {});
}

// ── Scanner ──
function runScanner() {
    const btn = event.target.closest('button');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Scanning...';
    btn.disabled = true;

    fetch('{{ route("dashboard.trade.scanner") }}')
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-satellite-dish me-1"></i> Run Scanner';

            if (data.success && data.signals) {
                const body = document.getElementById('scannerBody');
                const html = data.signals.map(s => {
                    const colors = { 'STRONG BUY': ['#10b981','rgba(16,185,129,0.1)'], 'BUY': ['#10b981','rgba(16,185,129,0.05)'], 'NEUTRAL': ['#94a3b8','rgba(148,163,184,0.05)'], 'SELL': ['#ef4444','rgba(239,68,68,0.05)'], 'STRONG SELL': ['#ef4444','rgba(239,68,68,0.1)'] };
                    const [c, bg] = colors[s.signal] || colors.NEUTRAL;
                    return `<div class="d-flex justify-content-between align-items-center p-2" style="border-bottom:1px solid var(--border);background:${bg}">
                        <div>
                            <span style="font-weight:600;color:var(--text);font-size:13px">${s.symbol}</span>
                            <span style="font-size:11px;color:var(--text-muted);margin-left:6px">${s.name}</span>
                        </div>
                        <div class="text-end">
                            <span class="badge" style="background:rgba(${c},0.15);color:${c};font-size:11px;font-weight:600">${s.signal}</span>
                            <span style="font-size:11px;color:var(--text-muted);margin-left:8px">${s.confidence}% confidence</span>
                        </div>
                    </div>`;
                }).join('');
                body.innerHTML = html;
                document.getElementById('scanTime').textContent = 'Scanned at ' + new Date().toLocaleTimeString();
                document.getElementById('scannerResults').style.display = 'block';
            }
        }).catch(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-satellite-dish me-1"></i> Run Scanner'; });
}

// Init
updateChart();
setInterval(updatePositions, 5000);
setInterval(updateChart, 30000);
@endif
setTimeout(() => { const a = document.getElementById('alert-success'); if (a) a.style.display = 'none'; }, 5000);
</script>
@endsection
