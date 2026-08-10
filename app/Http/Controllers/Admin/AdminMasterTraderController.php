<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterTrader;
use App\Models\User;
use App\Models\TradePosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminMasterTraderController extends Controller
{
    public function index(Request $request)
    {
        // Stats summary (always across ALL master traders, not filtered)
        $totalMasters   = MasterTrader::count();
        $activeMasters  = MasterTrader::where('is_active', true)->count();
        $totalFollowers = MasterTrader::sum('followers_count');
        $avgWinRate     = MasterTrader::where('is_active', true)
            ->selectRaw('AVG(CASE WHEN use_manual_win_rate = 1 THEN manual_win_rate ELSE win_rate END) as avg')
            ->value('avg');
        $avgWinRate = $avgWinRate ? round($avgWinRate, 1) : 0;

        // Build filtered query
        $query = MasterTrader::with(['user', 'subscriptions']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('title', 'like', "%{$search}%");
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($request->filled('strategy')) {
            $query->where('strategy_type', $request->strategy);
        }

        $masters = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());

        return view('admin.master-traders.index', compact(
            'masters', 'totalMasters', 'activeMasters', 'totalFollowers', 'avgWinRate'
        ));
    }

    public function create()
    {
        $users = User::where('role', '!=', 'admin')
            ->whereNotIn('id', MasterTrader::pluck('user_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.master-traders.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'title'            => 'required|string|max:100',
            'description'      => 'nullable|string|max:500',
            'max_followers'    => 'nullable|integer|min:0',
            'strategy_type'    => 'nullable|string|max:50',
            'avatar'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'manual_win_rate'  => 'nullable|numeric|min:0|max:100',
            'monthly_return'   => 'nullable|numeric|min:0|max:100',
            'daily_profit_pct' => 'nullable|numeric|min:0|max:100',
            'loss_rate_pct'    => 'nullable|numeric|min:0|max:100',
            'trades_per_day'   => 'nullable|integer|min:1|max:50',
            'profit_variance'  => 'nullable|numeric|min:0|max:100',
        ]);

        $existing = MasterTrader::where('user_id', $request->user_id)->first();
        if ($existing) {
            return back()->with('error', 'This user is already a master trader.')->withInput();
        }

        // Calculate stats from existing trade positions
        $positions = TradePosition::where('user_id', $request->user_id)->where('status', 'closed')->get();
        $totalTrades = $positions->count();
        $winningTrades = $positions->where('pnl', '>', 0)->count();
        $winRate = $totalTrades > 0 ? round(($winningTrades / $totalTrades) * 100, 2) : 0;

        // Handle avatar upload
        $avatarPath = null;
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $avatarPath = $request->file('avatar')->store('master-traders', 'public');
        }

        // Determine if manual win rate should be used
        $useManual = $request->boolean('use_manual_win_rate') && $request->filled('manual_win_rate');

        MasterTrader::create([
            'user_id'              => $request->user_id,
            'title'                => $request->title,
            'description'          => $request->description,
            'avatar'               => $avatarPath,
            'strategy_type'        => $request->strategy_type,
            'max_followers'        => $request->max_followers ?? 0,
            'win_rate'             => $winRate,
            'manual_win_rate'      => $useManual ? $request->manual_win_rate : null,
            'use_manual_win_rate'  => $useManual,
            'monthly_return'       => $request->monthly_return,
            'daily_profit_pct'     => $request->daily_profit_pct ?? 2.50,
            'loss_rate_pct'        => $request->loss_rate_pct ?? 5.00,
            'trades_per_day'       => $request->trades_per_day ?? 6,
            'profit_variance'      => $request->profit_variance ?? 15.00,
            'total_trades'         => $totalTrades,
            'winning_trades'       => $winningTrades,
            'is_active'            => true,
        ]);

        return redirect()->route('admin.master-traders.index')
            ->with('success', 'Master trader designated successfully.');
    }

    /**
     * Show the edit form for a master trader.
     */
    public function edit(MasterTrader $masterTrader)
    {
        $masterTrader->load('user');
        return view('admin.master-traders.edit', compact('masterTrader'));
    }

    /**
     * Update master trader details including avatar and win rate.
     */
    public function update(Request $request, MasterTrader $masterTrader)
    {
        $request->validate([
            'title'               => 'required|string|max:100',
            'description'         => 'nullable|string|max:500',
            'strategy_type'       => 'nullable|string|max:50',
            'avatar'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_avatar'       => 'nullable|boolean',
            'manual_win_rate'     => 'nullable|numeric|min:0|max:100',
            'use_manual_win_rate' => 'nullable|boolean',
            'monthly_return'      => 'nullable|numeric|min:0|max:100',
            'daily_profit_pct'    => 'nullable|numeric|min:0|max:100',
            'loss_rate_pct'       => 'nullable|numeric|min:0|max:100',
            'trades_per_day'      => 'nullable|integer|min:1|max:50',
            'profit_variance'     => 'nullable|numeric|min:0|max:100',
            'max_followers'       => 'nullable|integer|min:0',
            'total_trades'        => 'nullable|integer|min:0',
            'winning_trades'      => 'nullable|integer|min:0',
            'total_profit'        => 'nullable|numeric|min:0',
        ]);

        $data = [
            'title'            => $request->title,
            'description'      => $request->description,
            'strategy_type'    => $request->strategy_type,
            'max_followers'    => $request->max_followers ?? 0,
            'monthly_return'   => $request->monthly_return,
            'total_profit'     => $request->total_profit ?? 0,
            'daily_profit_pct' => $request->daily_profit_pct ?? 2.50,
            'loss_rate_pct'    => $request->loss_rate_pct ?? 5.00,
            'trades_per_day'   => $request->trades_per_day ?? 6,
            'profit_variance'  => $request->profit_variance ?? 15.00,
        ];

        // Handle win rate override
        if ($request->boolean('use_manual_win_rate') && $request->filled('manual_win_rate')) {
            $data['manual_win_rate']     = $request->manual_win_rate;
            $data['use_manual_win_rate'] = true;
        } else {
            $data['use_manual_win_rate'] = false;
        }

        // Allow manual stats override
        if ($request->filled('total_trades')) {
            $data['total_trades'] = $request->total_trades;
        }
        if ($request->filled('winning_trades')) {
            $data['winning_trades'] = $request->winning_trades;
            if ($data['total_trades'] > 0) {
                $data['win_rate'] = round(($request->winning_trades / $data['total_trades']) * 100, 2);
            }
        }

        // Handle avatar upload
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            if ($masterTrader->avatar) {
                Storage::disk('public')->delete($masterTrader->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('master-traders', 'public');
        }

        // Handle avatar removal
        if ($request->boolean('remove_avatar') && $masterTrader->avatar) {
            Storage::disk('public')->delete($masterTrader->avatar);
            $data['avatar'] = null;
        }

        $masterTrader->update($data);

        return redirect()->route('admin.master-traders.index')
            ->with('success', 'Master trader updated successfully.');
    }

    public function destroy(MasterTrader $masterTrader)
    {
        if ($masterTrader->avatar) {
            Storage::disk('public')->delete($masterTrader->avatar);
        }
        $masterTrader->subscriptions()->delete();
        $masterTrader->delete();

        return back()->with('success', 'Master trader removed.');
    }

    public function toggle(MasterTrader $masterTrader)
    {
        $masterTrader->update(['is_active' => !$masterTrader->is_active]);
        return back()->with('success', 'Master trader ' . ($masterTrader->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function updateStats(MasterTrader $masterTrader)
    {
        $positions = TradePosition::where('user_id', $masterTrader->user_id)
            ->where('status', 'closed')->get();
        $total = $positions->count();
        $wins = $positions->where('pnl', '>', 0)->count();
        $winRate = $total > 0 ? round(($wins / $total) * 100, 2) : 0;

        $masterTrader->update([
            'win_rate'        => $winRate,
            'total_trades'    => $total,
            'winning_trades'  => $wins,
            'followers_count' => $masterTrader->subscriptions()->active()->count(),
        ]);

        return back()->with('success', 'Stats synced from trade history.');
    }
}
