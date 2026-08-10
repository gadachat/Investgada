<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MasterTrader;
use App\Models\CopyTradingSubscription;
use App\Models\Wallet;
use Illuminate\Http\Request;

class CopyTradeController extends Controller
{
    public function index()
    {
        $masters = MasterTrader::with(['user', 'subscriptions'])
            ->active()
            ->orderBy('followers_count', 'desc')
            ->paginate(12);

        $subscriptions = CopyTradingSubscription::with('masterTrader.user')
            ->where('user_id', auth()->id())
            ->active()
            ->get();

        $wallet = auth()->user()->wallet('deposit');
        $available = $wallet ? $wallet->balance : 0;

        return view('dashboard.copy-trade.index', compact('masters', 'subscriptions', 'available'));
    }

    public function subscribe(Request $request, MasterTrader $masterTrader)
    {
        $request->validate([
            'allocation_amount'   => 'required|numeric|min:10',
            'allocation_percent'   => 'nullable|numeric|min:1|max:100',
        ]);

        if (!$masterTrader->is_active) {
            return back()->with('error', 'This master trader is not available.');
        }

        // Check max followers
        if ($masterTrader->max_followers > 0 && $masterTrader->followers_count >= $masterTrader->max_followers) {
            return back()->with('error', 'This master trader has reached the maximum number of followers.');
        }

        // Check existing subscription
        $existing = CopyTradingSubscription::where('user_id', auth()->id())
            ->where('master_trader_id', $masterTrader->id)
            ->active()
            ->first();

        if ($existing) {
            return back()->with('error', 'You are already subscribed to this master trader.');
        }

        // Check balance
        $wallet = auth()->user()->wallet('deposit');
        if (!$wallet || $wallet->balance < $request->allocation_amount) {
            return back()->with('error', 'Insufficient deposit wallet balance.');
        }

        // Lock the allocation amount
        $wallet->lock($request->allocation_amount);

        CopyTradingSubscription::create([
            'user_id'           => auth()->id(),
            'master_trader_id'  => $masterTrader->id,
            'allocation_amount' => $request->allocation_amount,
            'allocation_percent'=> $request->allocation_percent ?? 100,
            'is_active'         => true,
            'started_at'        => now(),
        ]);

        $masterTrader->increment('followers_count');

        return back()->with('success', "Subscribed to {$masterTrader->user->name}. Allocation: $" . number_format($request->allocation_amount, 2));
    }

    public function unsubscribe(CopyTradingSubscription $subscription)
    {
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        // Unlock allocation back to wallet
        $wallet = auth()->user()->wallet('deposit');
        if ($wallet) {
            $wallet->unlock($subscription->allocation_amount);
        }

        $subscription->update([
            'is_active'  => false,
            'stopped_at' => now(),
        ]);

        $subscription->masterTrader->decrement('followers_count');

        return back()->with('success', 'Unsubscribed. Your allocation has been returned to your deposit wallet.');
    }

    public function performance()
    {
        $subscriptions = CopyTradingSubscription::with(['masterTrader.user'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        $totalAllocated = $subscriptions->active->sum('allocation_amount');
        $totalPnl = $subscriptions->sum('total_pnl');
        $activeCount = $subscriptions->active->count();
        $totalCopied = $subscriptions->sum('total_copied');

        return view('dashboard.copy-trade.index', compact('subscriptions', 'totalAllocated', 'totalPnl', 'activeCount', 'totalCopied'));
    }
}
