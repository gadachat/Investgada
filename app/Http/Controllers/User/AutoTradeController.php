<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AutoTradeSession;
use App\Models\AutoTrade;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AutoTradeController extends Controller
{
    /**
     * Auto-trade dashboard — start/stop, active sessions, live trades.
     */
    public function index()
    {
        $user = auth()->user();

        $activeSession = AutoTradeSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        $recentTrades = AutoTrade::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $allSessions = AutoTradeSession::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $depositWallet = $user->wallet('deposit');

        // Admin settings
        $settings = $this->getTradeSettings();

        // Available pairs grouped by category
        $pairs = $this->getAvailablePairs();

        // Stats
        $totalProfit = AutoTrade::where('user_id', $user->id)->sum('profit');
        $totalTrades = AutoTrade::where('user_id', $user->id)->count();
        $wins = AutoTrade::where('user_id', $user->id)->where('is_win', true)->count();
        $winRate = $totalTrades > 0 ? round(($wins / $totalTrades) * 100, 1) : 0;

        return view('dashboard.autotrade.index', compact(
            'activeSession', 'recentTrades', 'allSessions', 'depositWallet',
            'settings', 'pairs', 'totalProfit', 'totalTrades', 'wins', 'winRate'
        ));
    }

    /**
     * Start a new auto-trading session.
     */
    public function start(Request $request)
    {
        $settings = $this->getTradeSettings();

        $request->validate([
            'capital'       => ['required', 'numeric', 'min:' . $settings['min_capital'], 'max:' . $settings['max_capital']],
            'selected_pairs' => ['required', 'array', 'min:1'],
        ]);

        $user = auth()->user();

        // Check if already has an active session
        $existing = AutoTradeSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($existing) {
            return back()->with('error', 'You already have an active auto-trade session. Stop it first.');
        }

        // Check deposit wallet balance
        $wallet = $user->wallet('deposit');
        if (!$wallet || $wallet->balance < $request->capital) {
            return back()->with('error', 'Insufficient deposit wallet balance. Current: $' . number_format($wallet?->balance ?? 0, 2));
        }

        // Validate selected pairs
        $availablePairs = collect($this->getAvailablePairs())->flatten(1)->pluck('symbol')->toArray();
        $validPairs = array_intersect($request->selected_pairs, $availablePairs);

        if (empty($validPairs)) {
            return back()->with('error', 'Please select at least one valid trading pair.');
        }

        // Debit deposit wallet
        $wallet->debit($request->capital);

        // Create session
        $session = AutoTradeSession::create([
            'reference'         => 'ATS-' . strtoupper(Str::random(12)),
            'user_id'            => $user->id,
            'allocated_capital'  => $request->capital,
            'current_balance'    => $request->capital,
            'selected_pairs'     => array_values($validPairs),
            'status'             => 'active',
            'started_at'         => now(),
            'next_trade_at'      => now()->addMinutes(rand(2, $settings['trade_interval_min'])),
        ]);

        // Record transaction
        Transaction::create([
            'reference'     => 'TXN-' . strtoupper(Str::random(12)),
            'user_id'       => $user->id,
            'wallet_id'     => $wallet->id,
            'type'          => 'auto_trade',
            'direction'     => 'debit',
            'amount'        => $request->capital,
            'balance_after' => $wallet->fresh()->balance,
            'currency'      => 'USD',
            'description'   => 'Auto-trade session started — ' . implode(', ', $validPairs),
            'metadata'      => ['session_id' => $session->id],
            'status'        => 'completed',
        ]);

        return redirect()->route('dashboard.autotrade.index')
            ->with('success', 'Auto-trade session started with $' . number_format($request->capital, 2) . ' capital.');
    }

    /**
     * Stop an active auto-trade session and return capital + profits.
     */
    public function stop(Request $request, AutoTradeSession $session)
    {
        if ($session->user_id !== auth()->id() || $session->status !== 'active') {
            return back()->with('error', 'Invalid session.');
        }

        $user = auth()->user();
        $wallet = $user->wallet('deposit');

        // Close any open trades
        AutoTrade::where('session_id', $session->id)
            ->where('status', 'open')
            ->update(['status' => 'closed', 'closed_at' => now()]);

        // Return current balance to deposit wallet
        $returnAmount = (float) $session->current_balance;
        $wallet->credit($returnAmount);

        // Update session
        $session->update([
            'status'     => 'stopped',
            'stopped_at' => now(),
        ]);

        // Record transaction
        Transaction::create([
            'reference'     => 'TXN-' . strtoupper(Str::random(12)),
            'user_id'       => $user->id,
            'wallet_id'     => $wallet->id,
            'type'          => 'auto_trade',
            'direction'     => 'credit',
            'amount'        => $returnAmount,
            'balance_after' => $wallet->fresh()->balance,
            'currency'      => 'USD',
            'description'   => 'Auto-trade session stopped — capital returned',
            'metadata'      => ['session_id' => $session->id],
            'status'        => 'completed',
        ]);

        $netProfit = $session->netProfit();
        $msg = 'Session stopped. $' . number_format($returnAmount, 2) . ' returned to deposit wallet.';
        if ($netProfit > 0) {
            $msg .= ' Net profit: +$' . number_format($netProfit, 2);
        } elseif ($netProfit < 0) {
            $msg .= ' Net loss: -$' . number_format(abs($netProfit), 2);
        }

        return redirect()->route('dashboard.autotrade.index')->with('success', $msg);
    }

    /**
     * Full trade history with filters.
     */
    public function history(Request $request)
    {
        $query = AutoTrade::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc');

        if ($request->pair && $request->pair !== 'all') {
            $query->where('pair', $request->pair);
        }

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->result && $request->result !== 'all') {
            $query->where('is_win', $request->result === 'win');
        }

        $trades = $query->paginate(25);
        $pairs = AutoTrade::where('user_id', auth()->id())->distinct()->pluck('pair');

        return view('dashboard.autotrade.history', compact('trades', 'pairs'));
    }

    /**
     * Live trade feed (AJAX).
     */
    public function liveFeed()
    {
        $user = auth()->user();

        $activeSession = AutoTradeSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $recentTrades = AutoTrade::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'active_session' => $activeSession ? [
                'reference'    => $activeSession->reference,
                'capital'       => (float) $activeSession->allocated_capital,
                'balance'       => (float) $activeSession->current_balance,
                'profit'        => (float) $activeSession->total_profit,
                'loss'          => (float) $activeSession->total_loss,
                'total_trades'  => (int) $activeSession->total_trades,
                'wins'          => (int) $activeSession->winning_trades,
                'win_rate'      => $activeSession->winRate(),
                'next_trade_at' => $activeSession->next_trade_at?->toISOString(),
            ] : null,
            'recent_trades' => $recentTrades->map(fn($t) => [
                'id'        => $t->id,
                'pair'      => $t->pair,
                'direction' => $t->direction,
                'amount'    => (float) $t->amount,
                'profit'    => (float) $t->profit,
                'status'    => $t->status,
                'is_win'    => $t->is_win,
                'opened_at' => $t->opened_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * Get admin-configured trade settings.
     */
    private function getTradeSettings(): array
    {
        return [
            'enabled'            => Setting::get('autotrade_enabled', '0') === '1',
            'daily_profit_pct'   => (float) Setting::get('autotrade_daily_profit_pct', '0'),
            'win_rate'           => (float) Setting::get('autotrade_win_rate', '0'),
            'min_capital'        => (float) Setting::get('autotrade_min_capital', '0'),
            'max_capital'        => (float) Setting::get('autotrade_max_capital', '0'),
            'trades_per_day'     => (int) Setting::get('autotrade_trades_per_day', '0'),
            'trade_interval_min' => (int) Setting::get('autotrade_trade_interval_min', '0'),
            'profit_variance'   => (float) Setting::get('autotrade_profit_variance', '0'),
            'stop_loss_pct'      => (float) Setting::get('autotrade_stop_loss_pct', '0'),
            'take_profit_pct'    => (float) Setting::get('autotrade_take_profit_pct', '0'),
        ];
    }

    /**
     * Get available trading pairs grouped by category.
     */
    private function getAvailablePairs(): array
    {
        $crypto = json_decode(Setting::get('autotrade_pairs_crypto', '["BTC/USDT","ETH/USDT","BNB/USDT","SOL/USDT","XRP/USDT"]'), true);
        $forex = json_decode(Setting::get('autotrade_pairs_forex', '["EUR/USD","GBP/USD","USD/JPY","AUD/USD"]'), true);
        $indices = json_decode(Setting::get('autotrade_pairs_indices', '["SPX","NDX","DJI"]'), true);

        $format = fn($pairs, $cat) => collect($pairs)->map(fn($p) => [
            'symbol'   => $p,
            'category' => $cat,
        ])->toArray();

        return [
            'crypto'   => $format($crypto ?? [], 'crypto'),
            'forex'    => $format($forex ?? [], 'forex'),
            'indices'  => $format($indices ?? [], 'indices'),
        ];
    }
}
