<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FundApplication;
use App\Models\FundSetting;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FundApplicationController extends Controller
{
    /**
     * Show fund application dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        $applications = FundApplication::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        $activeFund = $user->active_fund_id
            ? FundApplication::find($user->active_fund_id)
            : null;

        $canApply = FundSetting::isEnabled()
            && !$activeFund // can't apply if already has active fund
            && in_array($user->applicant_type, ['marketer', 'leader']);

        $settings = FundSetting::allSettings();

        return view('dashboard.funds.index', compact(
            'applications', 'activeFund', 'canApply', 'settings'
        ));
    }

    /**
     * Show the application form.
     */
    public function create()
    {
        $user = auth()->user();

        if (!FundSetting::isEnabled()) {
            return back()->with('error', 'Fund program is currently disabled.');
        }

        if (!in_array($user->applicant_type, ['marketer', 'leader'])) {
            return back()->with('error', 'Only marketers and leaders can apply for funds.');
        }

        if ($user->active_fund_id) {
            return back()->with('error', 'You already have an active fund. Complete it before applying again.');
        }

        $minAmount = (float) FundSetting::get('min_fund_amount', 100);
        $maxAmount = (float) FundSetting::get('max_fund_amount', 100000);
        $targetPercent = (float) FundSetting::get('team_target_percent', 100);

        return view('dashboard.funds.create', compact('minAmount', 'maxAmount', 'targetPercent'));
    }

    /**
     * Submit a fund application.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!FundSetting::isEnabled()) {
            return back()->with('error', 'Fund program is currently disabled.');
        }

        if (!in_array($user->applicant_type, ['marketer', 'leader'])) {
            return back()->with('error', 'Only marketers and leaders can apply for funds.');
        }

        if ($user->active_fund_id) {
            return back()->with('error', 'You already have an active fund.');
        }

        $minAmount = (float) FundSetting::get('min_fund_amount', 100);
        $maxAmount = (float) FundSetting::get('max_fund_amount', 100000);

        $request->validate([
            'applicant_type' => 'required|in:marketer,leader',
            'amount'         => "required|numeric|min:{$minAmount}|max:{$maxAmount}",
            'purpose'         => 'nullable|string|max:500',
        ]);

        $reference = 'FUND-' . strtoupper(Str::random(12));

        FundApplication::create([
            'reference'        => $reference,
            'user_id'           => $user->id,
            'applicant_type'    => $request->applicant_type,
            'requested_amount'  => $request->amount,
            'purpose'           => $request->purpose,
            'status'            => 'pending',
            'target_production' => 0,
            'team_production'    => 0,
        ]);

        return redirect()->route('dashboard.funds.index')
            ->with('success', 'Fund application submitted! Reference: ' . $reference);
    }

    /**
     * Show fund application details.
     */
    public function show(FundApplication $fund)
    {
        if ($fund->user_id !== auth()->id()) {
            abort(403);
        }

        $fund->load('approver');

        // Get team production breakdown
        $teamMembers = \App\Models\User::where('sponsor_id', auth()->id())
            ->orWhere('parent_id', auth()->id())
            ->with(['investments' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get();

        return view('dashboard.funds.show', compact('fund', 'teamMembers'));
    }
}
