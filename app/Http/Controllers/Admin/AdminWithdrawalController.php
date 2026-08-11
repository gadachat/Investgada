<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $query = Withdrawal::with('user');

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })->orWhere('reference', 'like', "%{$request->search}%");
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total'       => Withdrawal::where('status', 'completed')->sum('amount'),
            'pending'     => Withdrawal::where('status', 'pending')->count(),
            'pending_amt' => Withdrawal::where('status', 'pending')->sum('amount'),
            'processing'  => Withdrawal::where('status', 'processing')->count(),
        ];

        return view('admin.withdrawals.index', compact('withdrawals', 'stats'));
    }

    public function approve(Request $request, Withdrawal $withdrawal)
    {
        if (!in_array($withdrawal->status, ['pending'])) {
            return back()->with('error', 'This withdrawal has already been processed.');
        }

        $request->validate(['admin_note' => 'nullable|string']);

        DB::transaction(function () use ($withdrawal, $request) {
            $withdrawal->update([
                'status'     => 'processing',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'admin_note'  => $request->admin_note,
            ]);
        });

        // Send notification
        NotifyService::withdrawalProcessed($withdrawal->user, $withdrawal->amount, $withdrawal->method);

        return redirect()->back()->with('success', 'Withdrawal approved and marked as processing.');
    }

    public function complete(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'processing') {
            return back()->with('error', 'Withdrawal must be in processing state.');
        }

        $withdrawal->update([
            'status'      => 'completed',
            'processed_at' => now(),
            'admin_note'   => $request->admin_note ?? $withdrawal->admin_note,
        ]);

        // Unlock the funds from the withdrawal wallet
        $wallet = $withdrawal->user->wallet('withdrawal');
        if ($wallet) {
            $wallet->decrement('locked_balance', $withdrawal->amount);
        }

        $withdrawal->user->increment('total_withdrawn', $withdrawal->amount);

        return back()->with('success', 'Withdrawal of $' . number_format($withdrawal->amount, 2) . ' marked as completed.');
    }

    public function reject(Request $request, Withdrawal $withdrawal)
    {
        if (!in_array($withdrawal->status, ['pending', 'processing'])) {
            return back()->with('error', 'This withdrawal has already been completed.');
        }

        $request->validate(['admin_note' => 'required|string']);

        // Refund locked amount back to withdrawal wallet
        $wallet = $withdrawal->user->wallet('withdrawal');
        if ($wallet && $wallet->locked_balance >= $withdrawal->amount) {
            $wallet->unlock($withdrawal->amount);
        }

        $withdrawal->update([
            'status'      => 'rejected',
            'admin_note'  => $request->admin_note,
        ]);

        // Send notification
        NotifyService::withdrawalRejected($withdrawal->user, $withdrawal->amount, $withdrawal->reason ?? 'Not specified');

        return redirect()->back()->with('success', 'Withdrawal rejected. Funds returned to user wallet.');
    }
}
