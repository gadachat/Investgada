<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TradePosition;
use App\Models\TradeSetting;
use App\Models\TradingPackage;
use App\Models\TradingSubscription;
use Illuminate\Http\Request;

class AdminTradeController extends Controller
{
    public function index(Request $request)
    {
        $query = TradePosition::with('user');

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%");
            })->orWhere('reference', 'like', "%{$request->search}%")
              ->orWhere('symbol', 'like', "%{$request->search}%");
        }

        $positions = $query->orderByDesc('created_at')->paginate(20);

        $stats = [
            'open'          => TradePosition::where('status', 'open')->count(),
            'closed'        => TradePosition::whereNot('status', 'open')->count(),
            'total_volume'  => (float) TradePosition::sum('amount'),
            'total_pnl'     => (float) TradePosition::whereNot('status', 'open')->sum('close_pnl'),
            'wins'          => TradePosition::where('close_pnl', '>', 0)->count(),
            'losses'        => TradePosition::where('close_pnl', '<', 0)->count(),
            'active_subs'   => TradingSubscription::where('status', 'active')->count(),
        ];

        $settings = TradeSetting::allSettings();

        return view('admin.trade.index', compact('positions', 'stats', 'settings'));
    }

    public function show(TradePosition $position)
    {
        $position->load('user');
        return view('admin.trade.show', compact('position'));
    }

    public function forceClose(Request $request, TradePosition $position)
    {
        if ($position->status !== 'open') {
            return back()->with('error', 'This position is already closed.');
        }

        $request->validate(['reason' => 'required|string|max:200']);

        // Use the same simulation logic
        $tradeAmount = (float) $position->amount;
        $isWin = (mt_rand(0, 10000) / 100) <= 60;
        $pnl = $isWin ? round($tradeAmount * 0.01 * (mt_rand(50, 150) / 100), 2) : -round($tradeAmount * 0.005 * (mt_rand(50, 150) / 100), 2);

        $entryPrice = (float) $position->entry_price;
        if ($position->direction === 'buy') {
            $closePrice = $entryPrice + ($pnl / $tradeAmount) * $entryPrice;
        } else {
            $closePrice = $entryPrice - ($pnl / $tradeAmount) * $entryPrice;
        }
        $closePrice = round(max($closePrice, $entryPrice * 0.01), $entryPrice < 1 ? 8 : ($entryPrice < 100 ? 4 : 2));

        $result = $position->close($closePrice, 'manual');

        $wallet = $position->user->wallet('trading');
        if (!$wallet) $wallet = $position->user->wallet('deposit');
        if ($result['return_amount'] > 0) {
            $wallet->credit($result['return_amount']);
        }

        \App\Models\Transaction::create([
            'reference'     => 'TXN-' . strtoupper(\Illuminate\Support\Str::random(12)),
            'user_id'       => $position->user_id,
            'wallet_id'     => $wallet->id,
            'type'          => 'admin_force_close',
            'direction'     => 'credit',
            'amount'        => $result['return_amount'],
            'balance_after' => $wallet->fresh()->balance,
            'currency'      => 'USD',
            'description'   => "Admin force close — {$position->symbol} ({$request->reason})",
            'metadata'      => json_encode(['position_id' => $position->id, 'admin_reason' => $request->reason]),
            'status'        => 'completed',
        ]);

        return back()->with('success', "Position force-closed. P&L: $" . number_format($result['net_pnl'], 2));
    }

    public function settings()
    {
        $settings = TradeSetting::allSettings();
        $packages = TradingPackage::orderBy('sort_order')->get();
        $subscriptions = TradingSubscription::with(['user', 'package'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('admin.trade.settings', compact('settings', 'packages', 'subscriptions'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'trading_enabled'        => 'boolean',
            'max_leverage'            => 'required|integer|min:1|max:500',
            'min_trade_amount'       => 'required|numeric|min:0',
            'max_trade_amount'       => 'required|numeric|min:1',
            'spread_percent'         => 'required|numeric|min:0|max:5',
            'overnight_fee_percent'  => 'required|numeric|min:0|max:1',
            'margin_call_percent'    => 'required|numeric|min:0|max:100',
            'liquidation_percent'    => 'required|numeric|min:0|max:100',
            'tp_sl_enabled'          => 'boolean',
            'allow_short_selling'    => 'boolean',
        ]);

        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['trading_enabled', 'tp_sl_enabled', 'allow_short_selling'])) {
                TradeSetting::set($key, (bool) $value);
            } else {
                TradeSetting::set($key, $value);
            }
        }

        return back()->with('success', 'Trading settings updated.');
    }

    // ── Package Management ──

    public function storePackage(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:50',
            'description'          => 'nullable|string|max:500',
            'min_amount'           => 'required|numeric|min:0',
            'max_amount'           => 'required|numeric|min:1|gt:min_amount',
            'max_pairs'            => 'required|integer|min:1',
            'scanner_enabled'      => 'boolean',
            'has_short_selling'    => 'boolean',
            'daily_profit_percent' => 'required|numeric|min:0|max:100',
            'win_rate_percent'     => 'required|numeric|min:0|max:100',
            'loss_rate_percent'    => 'required|numeric|min:0|max:100',
        ]);

        $slug = \Illuminate\Support\Str::slug($request->name);

        TradingPackage::create([
            'name'                 => $request->name,
            'slug'                 => $slug,
            'description'          => $request->description,
            'min_amount'           => $request->min_amount,
            'max_amount'           => $request->max_amount,
            'max_pairs'            => $request->max_pairs,
            'scanner_enabled'      => $request->boolean('scanner_enabled'),
            'has_short_selling'    => $request->boolean('has_short_selling'),
            'daily_profit_percent' => $request->daily_profit_percent,
            'win_rate_percent'     => $request->win_rate_percent,
            'loss_rate_percent'    => $request->loss_rate_percent,
            'is_active'            => true,
            'sort_order'           => TradingPackage::max('sort_order') + 1,
        ]);

        return back()->with('success', "Trading package '{$request->name}' created.");
    }

    public function updatePackage(Request $request, TradingPackage $package)
    {
        $request->validate([
            'name'                 => 'required|string|max:50',
            'description'          => 'nullable|string|max:500',
            'min_amount'           => 'required|numeric|min:0',
            'max_amount'           => 'required|numeric|min:1|gt:min_amount',
            'max_pairs'            => 'required|integer|min:1',
            'scanner_enabled'      => 'boolean',
            'has_short_selling'    => 'boolean',
            'daily_profit_percent' => 'required|numeric|min:0|max:100',
            'win_rate_percent'     => 'required|numeric|min:0|max:100',
            'loss_rate_percent'    => 'required|numeric|min:0|max:100',
            'is_active'            => 'boolean',
        ]);

        $package->update([
            'name'                 => $request->name,
            'description'          => $request->description,
            'min_amount'           => $request->min_amount,
            'max_amount'           => $request->max_amount,
            'max_pairs'            => $request->max_pairs,
            'scanner_enabled'      => $request->boolean('scanner_enabled'),
            'has_short_selling'    => $request->boolean('has_short_selling'),
            'daily_profit_percent' => $request->daily_profit_percent,
            'win_rate_percent'     => $request->win_rate_percent,
            'loss_rate_percent'    => $request->loss_rate_percent,
            'is_active'            => $request->boolean('is_active'),
        ]);

        return back()->with('success', "Package '{$package->name}' updated.");
    }

    public function togglePackage(TradingPackage $package)
    {
        $package->update(['is_active' => !$package->is_active]);
        return back()->with('success', "Package '" . ($package->is_active ? 'activated' : 'deactivated') . "'.");
    }
}
