<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\FundService;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    /**
     * Show all wallets with balances.
     */
    public function index()
    {
        $user = auth()->user();
        $wallets = $user->wallets()->orderBy('type')->get();

        $totalBalance = $wallets->sum('balance');
        $totalLocked = $wallets->sum('locked_balance');

        // Recent wallet transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        return view('dashboard.wallet.index', compact(
            'wallets', 'totalBalance', 'totalLocked', 'recentTransactions'
        ));
    }

    /**
     * Transfer funds between own wallets.
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'from'   => ['required', 'in:deposit,interest,commission,bonus,withdrawal,trading'],
            'to'     => ['required', 'in:deposit,interest,commission,bonus,withdrawal,trading'],
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        if ($request->from === $request->to) {
            return back()->with('error', 'Cannot transfer to the same wallet.');
        }

        // ── Fund withdrawal guard ──
        // Check if this transfer is allowed based on fund program rules
        $fundCheck = FundService::checkWithdrawal(auth()->id(), $request->from);
        if (!$fundCheck['allowed']) {
            return back()->with('error', $fundCheck['reason']);
        }

        $user = auth()->user();
        $fromWallet = $user->wallet($request->from);
        $toWallet = $user->wallet($request->to);

        if (!$fromWallet || !$toWallet) {
            return back()->with('error', 'Wallet not found.');
        }

        if ($fromWallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance in ' . ucfirst($request->from) . ' wallet.');
        }

        // Debit source
        $fromWallet->debit($request->amount);

        // Credit destination
        $toWallet->credit($request->amount);

        // Record transactions
        $ref = 'TRF-' . strtoupper(Str::random(10));

        Transaction::create([
            'reference'     => $ref,
            'user_id'       => $user->id,
            'wallet_id'     => $fromWallet->id,
            'type'          => 'transfer_out',
            'direction'     => 'debit',
            'amount'        => $request->amount,
            'balance_after' => $fromWallet->fresh()->balance,
            'currency'      => 'USD',
            'description'   => 'Transfer to ' . ucfirst($request->to) . ' wallet',
            'metadata'      => ['transfer_to' => $request->to],
            'status'        => 'completed',
        ]);

        Transaction::create([
            'reference'     => $ref . '-IN',
            'user_id'       => $user->id,
            'wallet_id'     => $toWallet->id,
            'type'          => 'transfer_in',
            'direction'     => 'credit',
            'amount'        => $request->amount,
            'balance_after' => $toWallet->fresh()->balance,
            'currency'      => 'USD',
            'description'   => 'Transfer from ' . ucfirst($request->from) . ' wallet',
            'metadata'      => ['transfer_from' => $request->from],
            'status'        => 'completed',
        ]);

        return back()->with('success', '$' . number_format($request->amount, 2) . ' transferred from ' . ucfirst($request->from) . ' to ' . ucfirst($request->to) . ' wallet.');
    }

    /**
     * Show transaction history with filters.
     */
    public function history(Request $request)
    {
        $query = Transaction::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc');

        if ($request->type && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->direction && $request->direction !== 'all') {
            $query->where('direction', $request->direction);
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query->paginate(20);

        $types = [
            'all' => 'All Types',
            'deposit' => 'Deposits',
            'withdrawal' => 'Withdrawals',
            'investment' => 'Investments',
            'payout' => 'Payouts',
            'direct_referral' => 'Referral Commissions',
            'matching_bonus' => 'Matching Bonuses',
            'profit_share' => 'Profit Shares',
            'rank_bonus' => 'Rank Bonuses',
            'transfer_in' => 'Transfers In',
            'transfer_out' => 'Transfers Out',
        ];

        return view('dashboard.wallet.history', compact('transactions', 'types'));
    }
}
