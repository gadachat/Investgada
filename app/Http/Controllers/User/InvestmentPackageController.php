<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use App\Models\InvestmentPackage;
use App\Models\Investment;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Services\CommissionEngine;
use App\Models\FeatureSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvestmentPackageController extends Controller
{
    /**
     * List all active investment packages.
     */
    public function index()
    {
        $packages = InvestmentPackage::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('min_amount')
            ->get()
            ->groupBy('category');

        $user = auth()->user();
        $depositWallet = $user->wallet('deposit');
        $activeInvestments = Investment::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        $maxActive = (int) \App\Models\PlatformSetting::get('max_active_investments', 10);

        return view('dashboard.packages.index', compact(
            'packages', 'depositWallet', 'activeInvestments', 'maxActive'
        ));
    }

    /**
     * Show single package details + invest form.
     */
    public function show($slug)
    {
        $package = InvestmentPackage::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $user = auth()->user();
        $wallet = $user->wallet('deposit');

        return view('dashboard.packages.show', compact('package', 'wallet'));
    }

    /**
     * Process an investment purchase.
     */
    public function invest(Request $request, $slug)
    {
        $package = InvestmentPackage::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $request->validate([
            'amount'    => ['required', 'numeric', 'min:' . $package->min_amount],
            'wallet_pin'=> ['nullable', 'string'],
        ]);

        if ($package->max_amount && $request->amount > $package->max_amount) {
            return back()->with('error', 'Maximum investment amount is $' . number_format($package->max_amount, 2));
        }

        $user = auth()->user();
        $wallet = $user->wallet('deposit');

        if (!$wallet || $wallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance in your deposit wallet. Current balance: $' . number_format($wallet?->balance ?? 0, 2));
        }

        // Check max active investments
        $activeCount = Investment::where('user_id', $user->id)->where('status', 'active')->count();
        $maxActive = (int) \App\Models\PlatformSetting::get('max_active_investments', 10);
        if ($activeCount >= $maxActive) {
            return back()->with('error', 'You have reached the maximum of ' . $maxActive . ' active investments.');
        }

        // Calculate expected return
        $expectedReturn = $package->expectedReturn($request->amount);

        // Create investment
        $investment = $investment = Investment::create([
            'reference'       => 'INV-' . strtoupper(Str::random(12)),
            'user_id'         => $user->id,
            'package_id'       => $package->id,
            'amount'          => $request->amount,
            'expected_return'  => $expectedReturn,
            'earned_so_far'    => 0,
            'status'           => 'active',
            'activated_at'     => now(),
            'matures_at'       => now()->addDays($package->duration_days),
            'last_payout_at'   => now(),
            'next_payout_at'   => now()->addDays($package->cycle_days),
        ]);

        // Debit deposit wallet
        $wallet->debit($request->amount);

        // Record transaction
        Transaction::create([
            'reference'     => 'TXN-' . strtoupper(Str::random(12)),
            'user_id'       => $user->id,
            'wallet_id'     => $wallet->id,
            'type'          => 'investment',
            'direction'     => 'debit',
            'amount'        => $request->amount,
            'balance_after' => $wallet->fresh()->balance,
            'currency'      => 'USD',
            'description'   => 'Investment in ' . $package->name . ' (' . $package->category . ')',
            'metadata'      => ['investment_id' => $investment->id, 'package_id' => $package->id],
            'status'        => 'completed',
        ]);

        // Update binary tree volume
        $this->updateBinaryVolume($user, $request->amount);

        // Process all commissions via the Commission Engine
        $engine = new CommissionEngine();
        $engine->onInvestmentActivated($user->id, $request->amount, $investment->id);

        return redirect()->route('dashboard.investments.index')
            ->with('success', 'Investment of $' . number_format($request->amount, 2) . ' activated successfully!');
    }

    /**
     * List user's investments.
     */
    public function myInvestments()
    {
        $investments = Investment::where('user_id', auth()->id())
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard.investments.index', compact('investments'));
    }

    /**
     * Propagate investment volume up the binary tree.
     */
    private function updateBinaryVolume($user, $amount): void
    {
        $node = $user->binaryNode;
        if (!$node) return;

        $parentId = $node->parent_id;
        $position = $node->position;

        while ($parentId) {
            $parent = \App\Models\BinaryTree::where('user_id', $parentId)->first();
            if (!$parent) break;

            if ($position === 'left') {
                $parent->increment('left_volume', $amount);
            } else {
                $parent->increment('right_volume', $amount);
            }

            $position = $parent->position;
            $parentId = $parent->parent_id;
        }
    }
}
