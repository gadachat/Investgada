<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TradePosition;
use App\Models\TradeSetting;
use App\Models\TradingPackage;
use App\Models\TradingSubscription;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Services\FundService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TradeController extends Controller
{
    /**
     * Trading interface — shows packages, scanner, chart, and positions.
     */
    public function index()
    {
        if (!TradeSetting::isEnabled()) {
            return back()->with('error', 'Trading is currently disabled.');
        }

        $user = auth()->user();

        // Get user's active subscription
        $subscription = TradingSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('package')
            ->first();

        // Trading wallet balance
        $tradingWallet = $user->wallet('trading');
        $tradingBalance = $tradingWallet ? (float) $tradingWallet->balance : 0;

        // Available packages
        $packages = TradingPackage::active()->get();

        // Open positions
        $openPositions = TradePosition::where('user_id', $user->id)
            ->where('status', 'open')
            ->orderByDesc('created_at')
            ->get();

        // Recent closed positions
        $closedPositions = TradePosition::where('user_id', $user->id)
            ->whereNot('status', 'open')
            ->orderByDesc('closed_at')
            ->limit(10)
            ->get();

        // All available pairs grouped
        $allPairs = $this->getAllPairs();

        // Determine which pairs the user can trade
        $availablePairs = $this->getAvailablePairs($subscription);

        $fundSummary = FundService::getWithdrawalSummary($user->id);

        return view('dashboard.trade.index', compact(
            'subscription', 'tradingBalance', 'packages',
            'openPositions', 'closedPositions',
            'allPairs', 'availablePairs', 'fundSummary'
        ));
    }

    /**
     * Subscribe to a trading package (transfers from deposit → trading wallet).
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'amount'          => 'required|numeric|min:20',
            'selected_pairs'  => 'required|array|min:1',
            'selected_pairs.*' => 'required|string',
        ]);

        if (!TradeSetting::isEnabled()) {
            return back()->with('error', 'Trading is currently disabled.');
        }

        $user = auth()->user();

        // Find the package for this amount
        $package = TradingPackage::findForAmount((float) $request->amount);

        if (!$package) {
            return back()->with('error', 'No trading package available for this amount. Choose an amount between $20 and $10,000.');
        }

        // Validate number of selected pairs
        $maxPairs = (int) $package->max_pairs;
        if (count($request->selected_pairs) > $maxPairs) {
            return back()->with('error', "The {$package->name} package allows up to {$maxPairs} pair(s). You selected " . count($request->selected_pairs) . '.');
        }

        // Validate scanner access
        $wantsScanner = $request->boolean('scanner_active');
        if ($wantsScanner && !$package->scanner_enabled) {
            return back()->with('error', "The {$package->name} package does not include scanner access. Upgrade to Premium or VIP.");
        }

        // Check deposit wallet balance
        $depositWallet = $user->wallet('deposit');
        if (!$depositWallet || $depositWallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance in deposit wallet. Available: $' . number_format($depositWallet?->balance ?? 0, 2));
        }

        // Transfer from deposit → trading wallet
        $tradingWallet = $user->wallet('trading');
        if (!$tradingWallet) {
            // Create trading wallet if it doesn't exist
            $tradingWallet = Wallet::create([
                'user_id' => $user->id,
                'type'    => 'trading',
                'currency' => 'USD',
                'balance' => 0,
                'locked_balance' => 0,
            ]);
        }

        $depositWallet->debit($request->amount);
        $tradingWallet->credit($request->amount);

        // Create subscription
        $reference = 'SUB-' . strtoupper(Str::random(10));

        $subscription = TradingSubscription::create([
            'reference'         => $reference,
            'user_id'           => $user->id,
            'trading_package_id' => $package->id,
            'amount'            => $request->amount,
            'selected_pairs'    => $request->selected_pairs,
            'scanner_active'    => $wantsScanner,
            'status'            => 'active',
            'expires_at'        => now()->addDays(30),
        ]);

        // Record transactions
        Transaction::create([
            'reference'     => 'TXN-' . strtoupper(Str::random(12)),
            'user_id'       => $user->id,
            'wallet_id'     => $depositWallet->id,
            'type'          => 'trade_subscription',
            'direction'     => 'debit',
            'amount'        => $request->amount,
            'balance_after' => $depositWallet->fresh()->balance,
            'currency'      => 'USD',
            'description'   => "Trading subscription — {$package->name} ({$reference})",
            'metadata'      => json_encode(['subscription_id' => $subscription->id, 'package' => $package->name]),
            'status'        => 'completed',
        ]);

        return redirect()->route('dashboard.trade.index')
            ->with('success', "Subscribed to {$package->name}! $" . number_format($request->amount, 2) . " transferred to your trading wallet. " . count($request->selected_pairs) . " pair(s) activated.");
    }

    /**
     * Open a trade — with realistic win/loss simulation and admin-controlled profit.
     */
    public function open(Request $request)
    {
        if (!TradeSetting::isEnabled()) {
            return back()->with('error', 'Trading is currently disabled.');
        }

        $user = auth()->user();

        // Must have an active subscription
        $subscription = TradingSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('package')
            ->first();

        if (!$subscription) {
            return back()->with('error', 'You need an active trading subscription. Choose a package first.');
        }

        $package = $subscription->package;

        $minAmount = (float) TradeSetting::get('min_trade_amount', 0);
        $maxLeverage = (int) $package->has_short_selling ? TradeSetting::maxLeverage() : 1;

        $request->validate([
            'symbol'      => 'required|string|max:20',
            'market_type' => 'required|in:crypto,forex,indices',
            'direction'   => $package->has_short_selling ? 'required|in:buy,sell' : 'required|in:buy',
            'amount'      => "required|numeric|min:{$minAmount}",
            'take_profit' => 'nullable|numeric|min:0',
            'stop_loss'   => 'nullable|numeric|min:0',
        ]);

        // Validate pair is in their subscription
        if (!$subscription->canTradePair($request->symbol)) {
            return back()->with('error', "You don't have access to trade {$request->symbol}. This pair is not in your selected pairs.");
        }

        // Check trading wallet balance
        $tradingWallet = $user->wallet('trading');
        if (!$tradingWallet || $tradingWallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance in trading wallet. Available: $' . number_format($tradingWallet?->balance ?? 0, 2));
        }

        // Get current price
        $currentPrice = $this->getMarketPrice($request->symbol, $request->market_type);
        if (!$currentPrice) {
            return back()->with('error', 'Unable to fetch market price for ' . $request->symbol);
        }

        $spreadPercent = (float) TradeSetting::get('spread_percent', 0);
        if ($request->direction === 'buy') {
            $entryPrice = $currentPrice * (1 + $spreadPercent / 100);
        } else {
            $entryPrice = $currentPrice * (1 - $spreadPercent / 100);
        }
        $precision = $currentPrice < 1 ? 8 : ($currentPrice < 100 ? 4 : 2);
        $entryPrice = round($entryPrice, $precision);

        // Leverage is always 1x for package-based trading (profit is admin-controlled)
        $leverage = 1;
        $contractValue = (float) $request->amount;
        $fees = round((float) $request->amount * ($spreadPercent / 100), 2);

        // Lock margin from trading wallet
        $tradingWallet->debit($request->amount);

        $reference = 'TRD-' . strtoupper(Str::random(10));

        $position = TradePosition::create([
            'reference'      => $reference,
            'user_id'        => $user->id,
            'symbol'         => $request->symbol,
            'market_type'    => $request->market_type,
            'direction'      => $request->direction,
            'entry_price'    => $entryPrice,
            'volume'         => $contractValue / $entryPrice,
            'amount'         => $request->amount,
            'leverage'       => $leverage,
            'contract_value' => $contractValue,
            'take_profit'    => $request->take_profit,
            'stop_loss'      => $request->stop_loss,
            'current_price'  => $entryPrice,
            'fees'           => $fees,
            'status'         => 'open',
        ]);

        Transaction::create([
            'reference'     => 'TXN-' . strtoupper(Str::random(12)),
            'user_id'       => $user->id,
            'wallet_id'     => $tradingWallet->id,
            'type'          => 'trade_margin',
            'direction'     => 'debit',
            'amount'        => $request->amount,
            'balance_after' => $tradingWallet->fresh()->balance,
            'currency'      => 'USD',
            'description'   => "Trade opened — {$request->direction} {$request->symbol} ({$reference})",
            'metadata'      => json_encode(['position_id' => $position->id, 'subscription_id' => $subscription->id]),
            'status'        => 'completed',
        ]);

        return redirect()->route('dashboard.trade.index')
            ->with('success', "Position opened: {$request->direction} {$request->symbol} — Margin: $" . number_format($request->amount, 2));
    }

    /**
     * Close a position — simulate realistic outcome with admin-controlled profit rate.
     */
    public function close(Request $request, TradePosition $position)
    {
        if ($position->user_id !== auth()->id()) {
            abort(403);
        }

        if ($position->status !== 'open') {
            return back()->with('error', 'This position is already closed.');
        }

        $user = auth()->user();
        $subscription = TradingSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('package')
            ->first();

        if (!$subscription) {
            return back()->with('error', 'No active trading subscription found.');
        }

        $package = $subscription->package;

        // ── Simulate realistic win/loss ──
        // The actual profit is controlled by admin-set daily_profit_percent
        // But the trade shows as win or loss to feel realistic
        $winRate = (float) $package->win_rate_percent;
        $lossRate = (float) $package->loss_rate_percent;
        $dailyProfitPercent = (float) $package->daily_profit_percent;

        // Determine if this trade is a "win" (weighted by admin-set win rate)
        $isWin = (mt_rand(0, 10000) / 100) <= $winRate;

        // Calculate trade duration factor (how long the position was open)
        $durationMinutes = $position->created_at->diffInMinutes(now());
        $durationFactor = min(1, $durationMinutes / 60); // Scale up to 1 hour for full daily rate

        // Calculate P&L based on admin-set profit rate
        $tradeAmount = (float) $position->amount;

        if ($isWin) {
            // Win: earn a portion of the daily profit rate
            $profitRate = ($dailyProfitPercent / 100) * $durationFactor;
            // Add some randomness so it's not always the same
            $variance = (mt_rand(50, 150) / 100); // 0.5x to 1.5x
            $pnl = round($tradeAmount * $profitRate * $variance, 2);
            $closeReason = 'manual';
        } else {
            // Loss: lose a smaller portion (so user still profits over time)
            $lossRateValue = ($lossRate / 100) * $durationFactor * 0.5; // Losses are half the magnitude
            $variance = (mt_rand(50, 150) / 100);
            $pnl = -round($tradeAmount * $lossRateValue * $variance, 2);
            $closeReason = 'manual';
        }

        // Get a realistic close price that matches the P&L
        $entryPrice = (float) $position->entry_price;
        if ($position->direction === 'buy') {
            // Long: price goes up for win, down for loss
            $priceChange = ($pnl / $tradeAmount) * $entryPrice;
            $closePrice = $entryPrice + $priceChange;
        } else {
            // Short: price goes down for win, up for loss
            $priceChange = ($pnl / $tradeAmount) * $entryPrice;
            $closePrice = $entryPrice - $priceChange;
        }

        $precision = $entryPrice < 1 ? 8 : ($entryPrice < 100 ? 4 : 2);
        $closePrice = round(max($closePrice, $entryPrice * 0.01), $precision);

        // Close the position
        $result = $position->close($closePrice, $closeReason);

        // Return margin + P&L to trading wallet
        $tradingWallet = $user->wallet('trading');
        $returnAmount = $result['return_amount'];

        if ($returnAmount > 0) {
            $tradingWallet->credit($returnAmount);
        }

        // Record subscription trade stats
        $subscription->recordTrade($pnl, $isWin);

        // Record close transaction
        Transaction::create([
            'reference'     => 'TXN-' . strtoupper(Str::random(12)),
            'user_id'       => $user->id,
            'wallet_id'     => $tradingWallet->id,
            'type'          => 'trade_close',
            'direction'     => 'credit',
            'amount'        => $returnAmount,
            'balance_after' => $tradingWallet->fresh()->balance,
            'currency'      => 'USD',
            'description'   => "Trade closed — {$position->symbol} " . ($isWin ? 'WIN' : 'LOSS') . " P&L: $" . number_format($pnl, 2) . " ({$position->reference})",
            'metadata'      => json_encode(['position_id' => $position->id, 'pnl' => $pnl, 'is_win' => $isWin]),
            'status'        => 'completed',
        ]);

        $pnlStr = $pnl >= 0 ? '+$' . number_format($pnl, 2) : '-$' . number_format(abs($pnl), 2);
        $resultMsg = $isWin ? "🎉 Win! P&L: {$pnlStr}" : "📉 Loss. P&L: {$pnlStr}";
        $resultMsg .= " · Returned to trading wallet: $" . number_format($returnAmount, 2);

        return back()->with('success', $resultMsg);
    }

    /**
     * Get price for a symbol.
     */
    public function getPrice(Request $request)
    {
        $request->validate([
            'symbol'      => 'required|string',
            'market_type' => 'required|in:crypto,forex,indices',
        ]);

        $price = $this->getMarketPrice($request->symbol, $request->market_type);

        if (!$price) {
            return response()->json(['success' => false, 'error' => 'Symbol not found'], 404);
        }

        return response()->json(['success' => true, 'symbol' => $request->symbol, 'price' => $price]);
    }

    /**
     * Live P&L update for open positions (simulated for realistic feel).
     */
    public function updatePositions(Request $request)
    {
        $user = auth()->user();
        $subscription = TradingSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('package')
            ->first();

        $positions = TradePosition::where('user_id', $user->id)
            ->where('status', 'open')
            ->get();

        $updates = [];
        $totalPnl = 0;

        foreach ($positions as $position) {
            $currentPrice = $this->getMarketPrice($position->symbol, $position->market_type);
            if (!$currentPrice) continue;

            // Simulate small P&L fluctuations
            $entryPrice = (float) $position->entry_price;
            $tradeAmount = (float) $position->amount;

            // Small random fluctuation (feels like live trading)
            $fluctuationPercent = (mt_rand(-200, 200) / 10000); // -2% to +2%
            $simulatedPnl = $tradeAmount * $fluctuationPercent;

            $position->update([
                'current_price' => $currentPrice,
                'pnl'           => round($simulatedPnl, 2),
                'pnl_percent'   => round(($simulatedPnl / $tradeAmount) * 100, 2),
            ]);

            $totalPnl += $simulatedPnl;

            $updates[] = [
                'id'            => $position->id,
                'reference'     => $position->reference,
                'symbol'        => $position->symbol,
                'direction'     => $position->direction,
                'entry_price'   => $entryPrice,
                'current_price' => $currentPrice,
                'pnl'           => round($simulatedPnl, 2),
                'pnl_percent'   => round(($simulatedPnl / $tradeAmount) * 100, 2),
                'amount'        => $tradeAmount,
            ];
        }

        $tradingWallet = $user->wallet('trading');

        return response()->json([
            'success'        => true,
            'positions'       => $updates,
            'total_pnl'      => round($totalPnl, 2),
            'wallet_balance' => (float) ($tradingWallet?->balance ?? 0),
            'subscription'   => $subscription ? [
                'name'        => $subscription->package->name,
                'win_rate'    => (float) $subscription->package->win_rate_percent,
                'daily_rate'  => (float) $subscription->package->daily_profit_percent,
                'total_trades'=> $subscription->total_trades,
                'net_pnl'     => $subscription->netPnl(),
            ] : null,
        ]);
    }

    /**
     * Trading history.
     */
    public function history()
    {
        $positions = TradePosition::where('user_id', auth()->id())
            ->whereNot('status', 'open')
            ->orderByDesc('closed_at')
            ->paginate(20);

        $stats = [
            'total_trades' => TradePosition::where('user_id', auth()->id())->count(),
            'wins'         => TradePosition::where('user_id', auth()->id())->where('close_pnl', '>', 0)->count(),
            'losses'       => TradePosition::where('user_id', auth()->id())->where('close_pnl', '<', 0)->count(),
            'total_pnl'    => (float) TradePosition::where('user_id', auth()->id())->sum('close_pnl'),
        ];

        $subscriptions = TradingSubscription::where('user_id', auth()->id())
            ->with('package')
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard.trade.history', compact('positions', 'stats', 'subscriptions'));
    }

    /**
     * Scanner — shows recommended pairs based on market analysis (simulated).
     * Only available for packages with scanner_enabled.
     */
    public function scanner()
    {
        $user = auth()->user();
        $subscription = TradingSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('package')
            ->first();

        if (!$subscription) {
            return response()->json(['success' => false, 'error' => 'No active subscription'], 403);
        }

        if (!$subscription->package->scanner_enabled) {
            return response()->json(['success' => false, 'error' => 'Scanner requires Premium or VIP package'], 403);
        }

        // Generate scanner signals for all available pairs
        $allPairs = $this->getAllPairs();
        $signals = [];

        foreach ($allPairs as $category => $pairs) {
            foreach ($pairs as $pair) {
                $price = $this->getMarketPrice($pair['symbol'], $category);
                if (!$price) continue;

                // Generate a signal (simulated technical analysis)
                $signalType = ['STRONG BUY', 'BUY', 'NEUTRAL', 'SELL', 'STRONG SELL'][mt_rand(0, 4)];
                $confidence = mt_rand(45, 95);
                $change = mt_rand(-500, 500) / 100; // -5% to +5%

                $signals[] = [
                    'symbol'    => $pair['symbol'],
                    'name'      => $pair['name'],
                    'category'  => $category,
                    'price'     => $price,
                    'signal'    => $signalType,
                    'confidence'=> $confidence,
                    'change'    => $change,
                    'recommendation' => in_array($signalType, ['STRONG BUY', 'BUY']) ? 'buy' : (in_array($signalType, ['STRONG SELL', 'SELL']) ? 'sell' : 'wait'),
                ];
            }
        }

        // Sort by confidence (highest first)
        usort($signals, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return response()->json([
            'success'  => true,
            'signals'  => array_slice($signals, 0, 15), // Top 15 signals
            'scanned_at' => now()->toISOString(),
        ]);
    }

    /**
     * Transfer from trading wallet back to deposit wallet.
     */
    public function withdrawTrading(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);

        $user = auth()->user();
        $tradingWallet = $user->wallet('trading');

        if (!$tradingWallet || $tradingWallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance in trading wallet.');
        }

        // Check for open positions
        $openCount = TradePosition::where('user_id', $user->id)
            ->where('status', 'open')
            ->count();

        if ($openCount > 0) {
            return back()->with('error', "You have {$openCount} open position(s). Close all positions before withdrawing from trading wallet.");
        }

        $depositWallet = $user->wallet('deposit');
        $tradingWallet->debit($request->amount);
        $depositWallet->credit($request->amount);

        Transaction::create([
            'reference'     => 'TXN-' . strtoupper(Str::random(12)),
            'user_id'       => $user->id,
            'wallet_id'     => $tradingWallet->id,
            'type'          => 'trade_withdraw',
            'direction'     => 'debit',
            'amount'        => $request->amount,
            'balance_after' => $tradingWallet->fresh()->balance,
            'currency'      => 'USD',
            'description'   => 'Transfer from trading wallet to deposit wallet',
            'metadata'      => json_encode(['to' => 'deposit']),
            'status'        => 'completed',
        ]);

        return back()->with('success', '$' . number_format($request->amount, 2) . ' transferred from trading wallet to deposit wallet.');
    }

    // ── Helpers ──

    private function getAllPairs(): array
    {
        return [
            'crypto' => [
                ['symbol' => 'BTC', 'name' => 'Bitcoin', 'price' => 0],
                ['symbol' => 'ETH', 'name' => 'Ethereum', 'price' => 0],
                ['symbol' => 'BNB', 'name' => 'BNB', 'price' => 0],
                ['symbol' => 'SOL', 'name' => 'Solana', 'price' => 0],
                ['symbol' => 'XRP', 'name' => 'Ripple', 'price' => 0],
                ['symbol' => 'ADA', 'name' => 'Cardano', 'price' => 0],
                ['symbol' => 'DOT', 'name' => 'Polkadot', 'price' => 0],
                ['symbol' => 'DOGE', 'name' => 'Dogecoin', 'price' => 0],
                ['symbol' => 'AVAX', 'name' => 'Avalanche', 'price' => 0],
                ['symbol' => 'LINK', 'name' => 'Chainlink', 'price' => 0],
                ['symbol' => 'LTC', 'name' => 'Litecoin', 'price' => 0],
                ['symbol' => 'MATIC', 'name' => 'Polygon', 'price' => 0],
            ],
            'forex' => [
                ['symbol' => 'EUR/USD', 'name' => 'Euro / US Dollar', 'price' => 0],
                ['symbol' => 'GBP/USD', 'name' => 'Pound / US Dollar', 'price' => 0],
                ['symbol' => 'USD/JPY', 'name' => 'US Dollar / Yen', 'price' => 0],
                ['symbol' => 'USD/CHF', 'name' => 'US Dollar / Franc', 'price' => 0],
                ['symbol' => 'AUD/USD', 'name' => 'Aussie / US Dollar', 'price' => 0],
                ['symbol' => 'USD/CAD', 'name' => 'US Dollar / Canadian', 'price' => 0],
                ['symbol' => 'NZD/USD', 'name' => 'Kiwi / US Dollar', 'price' => 0],
                ['symbol' => 'EUR/GBP', 'name' => 'Euro / Pound', 'price' => 0],
            ],
            'indices' => [
                ['symbol' => 'SPX', 'name' => 'S&P 500', 'price' => 0],
                ['symbol' => 'NDX', 'name' => 'Nasdaq 100', 'price' => 0],
                ['symbol' => 'DJI', 'name' => 'Dow Jones', 'price' => 0],
                ['symbol' => 'DAX', 'name' => 'German DAX', 'price' => 0],
                ['symbol' => 'FTSE', 'name' => 'FTSE 100', 'price' => 0],
                ['symbol' => 'NIKKEI', 'name' => 'Nikkei 225', 'price' => 0],
            ],
        ];
    }

    private function getAvailablePairs(?TradingSubscription $subscription): array
    {
        if (!$subscription) return [];

        $selected = $subscription->selected_pairs ?? [];
        $all = $this->getAllPairs();

        $result = [];
        foreach ($all as $category => $pairs) {
            $available = array_filter($pairs, fn($p) => in_array($p['symbol'], $selected));
            if (count($available) > 0) {
                $result[$category] = array_values($available);
            }
        }

        return $result;
    }

    private function getMarketPrice(string $symbol, string $marketType): ?float
    {
        $all = $this->getAllPairs();
        $pairs = $all[$marketType] ?? [];

        $base = null;
        foreach ($pairs as $p) {
            if ($p['symbol'] === $symbol) {
                $base = $p['price'];
                break;
            }
        }

        if (!$base) return null;

        $volatility = $base > 1000 ? 0.015 : 0.03;
        $change = (mt_rand(-1000, 1000) / 1000) * $volatility * $base;
        return round($base + $change, $base < 1 ? 6 : ($base < 100 ? 4 : 2));
    }
}
