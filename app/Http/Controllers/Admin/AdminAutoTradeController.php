<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\AutoTradeSession;
use App\Models\AutoTrade;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminAutoTradeController extends Controller
{
    /**
     * Auto-trade settings page.
     */
    public function index()
    {
        $settings = $this->getSettings();
        $stats = $this->getStats();

        return view('admin.autotrade.index', compact('settings', 'stats'));
    }

    /**
     * Update auto-trade settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'autotrade_enabled'           => 'nullable|boolean',
            'autotrade_daily_profit_pct'  => 'required|numeric|min:0.1|max:50',
            'autotrade_win_rate'           => 'required|numeric|min:1|max:100',
            'autotrade_min_capital'        => 'required|numeric|min:1',
            'autotrade_max_capital'        => 'required|numeric|gt:autotrade_min_capital',
            'autotrade_trades_per_day'    => 'required|integer|min:1|max:100',
            'autotrade_trade_interval_min' => 'required|integer|min:1|max:1440',
            'autotrade_profit_variance'   => 'required|numeric|min:0|max:100',
            'autotrade_stop_loss_pct'     => 'required|numeric|min:0.1|max:50',
            'autotrade_take_profit_pct'   => 'required|numeric|min:0.1|max:50',
            'autotrade_max_concurrent'    => 'required|integer|min:1|max:20',
            'autotrade_auto_compound'    => 'nullable|boolean',
            'pairs_crypto'                => 'nullable|string',
            'pairs_forex'                 => 'nullable|string',
            'pairs_indices'               => 'nullable|string',
        ]);

        // Save scalar settings
        $scalars = [
            'autotrade_enabled', 'autotrade_daily_profit_pct', 'autotrade_win_rate',
            'autotrade_min_capital', 'autotrade_max_capital', 'autotrade_trades_per_day',
            'autotrade_trade_interval_min', 'autotrade_profit_variance',
            'autotrade_stop_loss_pct', 'autotrade_take_profit_pct',
            'autotrade_max_concurrent', 'autotrade_auto_compound',
        ];

        foreach ($scalars as $key) {
            $value = $validated[$key] ?? '0';
            if ($key === 'autotrade_enabled' || $key === 'autotrade_auto_compound') {
                $value = ($request->has($key) && $request->boolean($key)) ? '1' : '0';
            }
            Setting::set($key, (string) $value, 'string', 'autotrade');
        }

        // Parse and save pair lists
        foreach (['crypto', 'forex', 'indices'] as $cat) {
            $input = $validated["pairs_{$cat}"] ?? '';
            $pairs = array_filter(array_map('trim', explode(',', $input)));
            $pairs = array_filter($pairs, fn($p) => !empty($p));
            Setting::set("autotrade_pairs_{$cat}", json_encode(array_values($pairs)), 'string', 'autotrade');
        }

        return back()->with('success', 'Auto-trade settings updated successfully.');
    }

    /**
     * View all active sessions across all users.
     */
    public function sessions()
    {
        $sessions = AutoTradeSession::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.autotrade.sessions', compact('sessions'));
    }

    /**
     * Admin view of all trades.
     */
    public function trades(Request $request)
    {
        $query = AutoTrade::with('user')->orderBy('created_at', 'desc');

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->pair && $request->pair !== 'all') {
            $query->where('pair', $request->pair);
        }

        $trades = $query->paginate(30);

        return view('admin.autotrade.trades', compact('trades'));
    }

    /**
     * Force-stop a user's session (admin action).
     */
    public function forceStop(AutoTradeSession $session)
    {
        if ($session->status !== 'active') {
            return back()->with('error', 'Session is not active.');
        }

        $user = $session->user;
        $wallet = $user->wallet('deposit');

        // Close open trades
        AutoTrade::where('session_id', $session->id)
            ->where('status', 'open')
            ->update(['status' => 'closed', 'closed_at' => now()]);

        // Return balance
        $returnAmount = (float) $session->current_balance;
        $wallet->credit($returnAmount);

        $session->update(['status' => 'stopped', 'stopped_at' => now()]);

        return back()->with('success', "Session force-stopped. $" . number_format($returnAmount, 2) . " returned to user.");
    }

    /**
     * Get current settings with defaults.
     */
    private function getSettings(): array
    {
        return [
            'enabled'           => Setting::get('autotrade_enabled', '0') === '1',
            'daily_profit_pct'  => Setting::get('autotrade_daily_profit_pct', '0'),
            'win_rate'          => Setting::get('autotrade_win_rate', '0'),
            'min_capital'       => Setting::get('autotrade_min_capital', '0'),
            'max_capital'       => Setting::get('autotrade_max_capital', '0'),
            'trades_per_day'    => Setting::get('autotrade_trades_per_day', '0'),
            'trade_interval_min' => Setting::get('autotrade_trade_interval_min', '0'),
            'profit_variance'   => Setting::get('autotrade_profit_variance', '0'),
            'stop_loss_pct'     => Setting::get('autotrade_stop_loss_pct', '0'),
            'take_profit_pct'   => Setting::get('autotrade_take_profit_pct', '0'),
            'max_concurrent'    => Setting::get('autotrade_max_concurrent', '0'),
            'auto_compound'     => Setting::get('autotrade_auto_compound', '0') === '1',
            'pairs_crypto'      => implode(', ', json_decode(Setting::get('autotrade_pairs_crypto', '["BTC/USDT","ETH/USDT"]'), true) ?? []),
            'pairs_forex'       => implode(', ', json_decode(Setting::get('autotrade_pairs_forex', '["EUR/USD","GBP/USD"]'), true) ?? []),
            'pairs_indices'     => implode(', ', json_decode(Setting::get('autotrade_pairs_indices', '["SPX"]'), true) ?? []),
        ];
    }

    /**
     * Get platform-wide auto-trade stats.
     */
    private function getStats(): array
    {
        return [
            'active_sessions'    => AutoTradeSession::where('status', 'active')->count(),
            'total_sessions'     => AutoTradeSession::count(),
            'total_trades'       => AutoTrade::count(),
            'total_profit'       => AutoTrade::sum('profit'),
            'total_volume'       => AutoTrade::sum('amount'),
            'wins'               => AutoTrade::where('is_win', true)->count(),
            'losses'             => AutoTrade::where('is_win', false)->count(),
            'active_capital'     => AutoTradeSession::where('status', 'active')->sum('current_balance'),
        ];
    }
}
