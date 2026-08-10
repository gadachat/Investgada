<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use App\Models\FundApplication;
use App\Models\FundSetting;
use App\Models\Notification;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminFundController extends Controller
{
    /**
     * List all fund applications.
     */
    public function index(Request $request)
    {
        $query = FundApplication::with('user');

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->type && $request->type !== 'all') {
            $query->where('applicant_type', $request->type);
        }

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })->orWhere('reference', 'like', "%{$request->search}%");
        }

        $applications = $query->orderByDesc('created_at')->paginate(15);

        $stats = [
            'pending'   => FundApplication::where('status', 'pending')->count(),
            'approved'   => FundApplication::where('status', 'approved')->count(),
            'completed'  => FundApplication::where('status', 'completed')->count(),
            'rejected'   => FundApplication::where('status', 'rejected')->count(),
            'total_funded' => FundApplication::where('status', 'approved')->sum('approved_amount'),
        ];

        return view('admin.funds.index', compact('applications', 'stats'));
    }

    /**
     * Show a single application.
     */
    public function show(FundApplication $fund)
    {
        $fund->load('user', 'approver');

        $teamMembers = \App\Models\User::where('sponsor_id', $fund->user_id)
            ->orWhere('parent_id', $fund->user_id)
            ->with(['investments' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get();

        return view('admin.funds.show', compact('fund', 'teamMembers'));
    }

    /**
     * Approve and fund an application.
     */
    public function approve(Request $request, FundApplication $fund)
    {
        if ($fund->status !== 'pending') {
            return back()->with('error', 'This application has already been processed.');
        }

        $request->validate([
            'approved_amount' => 'required|numeric|min:1',
            'admin_note'        => 'nullable|string|max:500',
        ]);

        $targetPercent = (float) FundSetting::get('team_target_percent', 100);
        $approvedAmount = (float) $request->approved_amount;
        $targetProduction = $approvedAmount * ($targetPercent / 100);

        $fund->update([
            'approved_amount'   => $approvedAmount,
            'target_production' => $targetProduction,
            'team_production'    => 0,
            'production_percent' => 0,
            'target_met'         => false,
            'status'             => 'approved',
            'approved_by'        => auth()->id(),
            'approved_at'        => now(),
            'funded_at'           => now(),
            'admin_note'         => $request->admin_note,
        ]);

        // Mark user as fund recipient
        $fund->user->update([
            'is_fund_recipient' => true,
            'active_fund_id'    => $fund->id,
        ]);

        // Credit the user's deposit wallet with the funded amount
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $fund->user_id, 'type' => 'deposit'],
            ['balance' => 0, 'currency' => 'USD']
        );
        $wallet->credit($approvedAmount);

        // Record transaction
        Transaction::create([
            'reference'     => 'TXN-' . strtoupper(Str::random(12)),
            'user_id'       => $fund->user_id,
            'wallet_id'     => $wallet->id,
            'type'          => 'fund_allocation',
            'direction'     => 'credit',
            'amount'        => $approvedAmount,
            'balance_after' => $wallet->fresh()->balance,
            'currency'      => 'USD',
            'description'   => 'Fund allocation approved — ' . $fund->reference,
            'metadata'      => json_encode(['fund_id' => $fund->id, 'applicant_type' => $fund->applicant_type]),
            'status'        => 'completed',
        ]);

        // Notify user
        Notification::create([
            'user_id'  => $fund->user_id,
            'type'     => 'fund',
            'title'    => 'Fund Application Approved!',
            'message'  => "Your fund application for $" . number_format($approvedAmount, 2) . " has been approved. Team target: 100% of capital ($" . number_format($targetProduction, 2) . "). You can withdraw commissions immediately. Capital and profits unlock when your team reaches the target.",
            'data'     => json_encode(['fund_id' => $fund->id, 'amount' => $approvedAmount]),
        ]);

        return back()->with('success', 'Fund approved for ' . $fund->user->name . '. $' . number_format($approvedAmount, 2) . ' credited.');
    }

    /**
     * Reject an application.
     */
    public function reject(Request $request, FundApplication $fund)
    {
        if ($fund->status !== 'pending') {
            return back()->with('error', 'This application has already been processed.');
        }

        $request->validate(['admin_note' => 'nullable|string|max:500']);

        $fund->update([
            'status'     => 'rejected',
            'admin_note' => $request->admin_note,
        ]);

        Notification::create([
            'user_id'  => $fund->user_id,
            'type'     => 'fund',
            'title'    => 'Fund Application Update',
            'message'  => 'Your fund application (' . $fund->reference . ') has been rejected.' . ($request->admin_note ? ' Note: ' . $request->admin_note : ''),
            'data'     => json_encode(['fund_id' => $fund->id]),
        ]);

        return back()->with('success', 'Application rejected.');
    }

    /**
     * Revoke an active fund (admin override).
     */
    public function revoke(Request $request, FundApplication $fund)
    {
        if (!in_array($fund->status, ['approved', 'completed'])) {
            return back()->with('error', 'Only active funds can be revoked.');
        }

        $request->validate(['admin_note' => 'required|string|max:500']);

        $fund->update([
            'status'     => 'revoked',
            'admin_note' => $request->admin_note,
        ]);

        $fund->user->update([
            'is_fund_recipient' => false,
            'active_fund_id'    => null,
        ]);

        Notification::create([
            'user_id'  => $fund->user_id,
            'type'     => 'fund',
            'title'    => 'Fund Revoked',
            'message'  => 'Your active fund has been revoked by admin. Reason: ' . $request->admin_note,
            'data'     => json_encode(['fund_id' => $fund->id]),
        ]);

        return back()->with('success', 'Fund revoked.');
    }

    /**
     * Manually update team production (admin override).
     */
    public function updateProduction(Request $request, FundApplication $fund)
    {
        $request->validate([
            'production_amount' => 'required|numeric|min:0',
        ]);

        $fund->update([
            'team_production' => $request->production_amount,
        ]);
        $fund->recalculateProduction();

        return back()->with('success', 'Team production updated to $' . number_format($request->production_amount, 2));
    }

    /**
     * Fund program settings.
     */
    public function settings()
    {
        $settings = FundSetting::allSettings();
        return view('admin.funds.settings', compact('settings'));
    }

    /**
     * Update fund program settings.
     */
    public function updateSettings(Request $request)
    {
        $rules = [
            'fund_program_enabled'       => 'boolean',
            'min_fund_amount'             => 'required|numeric|min:0',
            'max_fund_amount'              => 'required|numeric|min:1',
            'team_target_percent'          => 'required|numeric|min:1|max:200',
            'allow_commission_withdrawal'  => 'boolean',
            'allow_profit_withdrawal'      => 'boolean',
            'allow_capital_withdrawal'     => 'boolean',
            'auto_calculate_production'    => 'boolean',
        ];

        $data = $request->validate($rules);

        foreach ($data as $key => $value) {
            FundSetting::set($key, is_bool($value) ? $value : $value);
        }

        return back()->with('success', 'Fund program settings updated.');
    }
}
