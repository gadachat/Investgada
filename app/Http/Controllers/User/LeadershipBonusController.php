<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LeadershipBonus;
use App\Models\Rank;
use Illuminate\Http\Request;

class LeadershipBonusController extends Controller
{
    /**
     * Display leadership bonus history and qualification info.
     */
    public function index()
    {
        $user = auth()->user();

        // Leadership bonus history
        $bonuses = LeadershipBonus::where('user_id', $user->id)
            ->with('rank')
            ->orderByDesc('created_at')
            ->paginate(10);

        // Summary stats
        $totalEarned = LeadershipBonus::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('bonus_amount');

        $totalCycles = LeadershipBonus::where('user_id', $user->id)
            ->where('status', 'paid')
            ->distinct('cycle_id')
            ->count('cycle_id');

        $lastBonus = LeadershipBonus::where('user_id', $user->id)
            ->where('status', 'paid')
            ->orderByDesc('paid_at')
            ->first();

        // Current rank and next rank
        $currentRank = $user->rank_id ? Rank::find($user->rank_id) : null;
        $nextRank = null;
        if ($currentRank) {
            $nextRank = Rank::where('sort_order', '>', $currentRank->sort_order)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->first();
        } else {
            $nextRank = Rank::where('is_active', true)->orderBy('sort_order')->first();
        }

        // Qualification progress
        $progress = $this->getQualificationProgress($user, $nextRank);

        return view('dashboard.leadership.index', compact(
            'bonuses', 'totalEarned', 'totalCycles', 'lastBonus',
            'currentRank', 'nextRank', 'progress'
        ));
    }

    private function getQualificationProgress($user, ?Rank $nextRank): array
    {
        if (!$nextRank) {
            return ['complete' => true, 'items' => []];
        }

        $totalInvested = (float) $user->total_invested;
        $directReferrals = \App\Models\User::where('sponsor_id', $user->id)->where('status', 'active')->count();

        // Team volume
        $teamVolume = 0;
        $currentLevel = [$user->id];
        for ($i = 0; $i < 15; $i++) {
            $users = \App\Models\User::whereIn('id', $currentLevel)->get();
            foreach ($users as $u) {
                $teamVolume += (float) ($u->total_invested ?? 0);
            }
            $nextLevel = \App\Models\User::whereIn('parent_id', $currentLevel)->pluck('id')->toArray();
            if (empty($nextLevel)) break;
            $currentLevel = $nextLevel;
        }

        // Binary leg volumes
        $binaryNode = \Illuminate\Support\Facades\DB::table('binary_tree')->where('user_id', $user->id)->first();
        $leftVolume = $binaryNode ? (float) $binaryNode->left_volume : 0;
        $rightVolume = $binaryNode ? (float) $binaryNode->right_volume : 0;

        $items = [
            [
                'label'    => 'Personal Investment',
                'current'  => $totalInvested,
                'required' => (float) $nextRank->min_investment,
                'met'      => $totalInvested >= $nextRank->min_investment,
            ],
            [
                'label'    => 'Direct Referrals',
                'current'  => $directReferrals,
                'required' => $nextRank->min_direct_referrals,
                'met'      => $directReferrals >= $nextRank->min_direct_referrals,
            ],
            [
                'label'    => 'Team Volume',
                'current'  => $teamVolume,
                'required' => (float) $nextRank->min_team_volume,
                'met'      => $teamVolume >= $nextRank->min_team_volume,
            ],
            [
                'label'    => 'Left Leg Volume',
                'current'  => $leftVolume,
                'required' => (float) $nextRank->min_left_volume,
                'met'      => $leftVolume >= $nextRank->min_left_volume,
            ],
            [
                'label'    => 'Right Leg Volume',
                'current'  => $rightVolume,
                'required' => (float) $nextRank->min_right_volume,
                'met'      => $rightVolume >= $nextRank->min_right_volume,
            ],
        ];

        $allMet = collect($items)->every(fn ($i) => $i['met']);

        return ['complete' => $allMet, 'items' => $items];
    }
}
