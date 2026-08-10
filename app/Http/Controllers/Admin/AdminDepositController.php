<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use App\Models\Deposit;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminDepositController extends Controller
{
    public function index(Request $request)
    {
        $query = Deposit::with('user');

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })->orWhere('reference', 'like', "%{$request->search}%");
        }

        $deposits = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total'      => Deposit::sum('amount'),
            'confirmed'  => Deposit::where('status', 'confirmed')->sum('amount'),
            'pending'    => Deposit::where('status', 'pending')->count(),
            'pending_amt'=> Deposit::where('status', 'pending')->sum('amount'),
        ];

        return view('admin.deposits.index', compact('deposits', 'stats'));
    }

    public function approve(Request $request, Deposit $deposit)
    {
        if ($deposit->status !== 'pending') {
            return back()->with('error', 'This deposit has already been processed.');
        }

        $request->validate(['admin_note' => 'nullable|string']);

        $user = $deposit->user;
        $wallet = $user->wallet('deposit');

        if (!$wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id, 'type' => 'deposit',
                'currency' => 'USD', 'balance' => 0,
            ]);
        }

        // Credit wallet
        $wallet->credit($deposit->net_amount > 0 ? $deposit->net_amount : $deposit->amount);

        // Record transaction
        Transaction::create([
            'reference'     => 'TXN-' . strtoupper(Str::random(12)),
            'user_id'       => $user->id,
            'wallet_id'     => $wallet->id,
            'type'          => 'deposit',
            'direction'     => 'credit',
            'amount'        => $deposit->amount,
            'balance_after' => $wallet->fresh()->balance,
            'currency'      => $deposit->currency,
            'description'   => 'Deposit confirmed — ' . $deposit->method . ' (' . $deposit->reference . ')',
            'metadata'      => ['deposit_id' => $deposit->id],
            'status'        => 'completed',
        ]);

        $deposit->update([
            'status'      => 'confirmed',
            'approved_by'  => auth()->id(),
            'approved_at'  => now(),
            'admin_note'   => $request->admin_note,
        ]);

        return back()->with('success', 'Deposit of $' . number_format($deposit->amount, 2) . ' approved for ' . $user->name);
    }

    public function reject(Request $request, Deposit $deposit)
    {
        if ($deposit->status !== 'pending') {
            return back()->with('error', 'This deposit has already been processed.');
        }

        $request->validate(['admin_note' => 'required|string']);

        $deposit->update([
            'status'      => 'rejected',
            'approved_by'  => auth()->id(),
            'approved_at'  => now(),
            'admin_note'   => $request->admin_note,
        ]);

        return back()->with('success', 'Deposit rejected.');
    }
}
