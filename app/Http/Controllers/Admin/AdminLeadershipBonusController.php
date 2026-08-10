<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadershipBonus;
use App\Models\Rank;
use App\Models\User;
use App\Services\CommissionEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminLeadershipBonusController extends Controller
{
    /**
     * Leadership bonus overview — admin dashboard.
     */
    public function index()
    {
        // Stats
        $totalDistributed = LeadershipBonus::where('status', 'paid')->sum('bonus_amount');
        $totalCycles = LeadershipBonus::where('status', 'paid')->distinct('cycle_id')->count('cycle_id');
        $lastCycle = LeadershipBonus::where('status', 'paid')->orderByDesc('paid_at')->first();

        // Eligible users (those with active ranks)
        $eligibleUsers = User::whereNotNull('rank_id')
            ->where('status', 'active')
            ->with('rank')
            ->orderBy('rank_id')
            ->get()
            ->map(function ($user) {
                $rank = $user->rank;
                $totalEarned = LeadershipBonus::where('user_id', $user->id)->where('status', 'paid')->sum('bonus_amount');
                $lastBonus = LeadershipBonus::where('user_id', $user->id)->where('status', 'paid')->orderByDesc('paid_at')->first();
                return [
                    'user'          => $user,
                    'rank'          => $rank,
                    'total_earned'  => $totalEarned,
                    'last_bonus'    => $lastBonus,
                ];
            });

        // Cycle history
        $cycleHistory = LeadershipBonus::select('cycle_id', DB::raw('count(*) as recipients'), DB::raw('sum(bonus_amount) as total'), DB::raw('min(paid_at) as date'))
            ->where('status', 'paid')
            ->groupBy('cycle_id')
            ->orderByDesc('date')
            ->limit(20)
            ->get();

        // Recent distributions
        $recentDistributions = LeadershipBonus::with(['user', 'rank'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Available ranks for qualification threshold
        $ranks = Rank::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.leadership.index', compact(
            'totalDistributed', 'totalCycles', 'lastCycle',
            'eligibleUsers', 'cycleHistory', 'recentDistributions', 'ranks'
        ));
    }

    /**
     * Run a leadership bonus distribution cycle.
     */
    public function runCycle(Request $request, CommissionEngine $engine)
    {
        $request->validate([
            'pool_amount'    => ['required', 'numeric', 'min:0.01'],
            'min_rank_slug'  => ['required', 'string'],
            'note'           => ['nullable', 'string', 'max:500'],
        ]);

        $result = $engine->distributeLeadershipBonus(
            poolAmount: $request->pool_amount,
            minRankSlug: $request->min_rank_slug,
            note: $request->note
        );

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', "Leadership bonus cycle {$result['cycle_id']} completed. Distributed $" . number_format($result['distributed'], 2) . " to {$result['recipients']} users.");
    }
}
