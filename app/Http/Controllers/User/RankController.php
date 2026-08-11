<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Rank;
use App\Models\User;
use App\Models\Investment;
use App\Models\Transaction;
use Illuminate\Http\Request;

class RankController extends Controller
{
    /**
     * Rank advancement page — shows current rank, progress toward next rank,
     * all ranks with requirements, and rewards.
     */
    public function index()
    {
        $user = auth()->user();

        // All ranks ordered by sort_order
        $ranks = Rank::where('is_active', true)->orderBy('sort_order')->get();

        // Current rank
        $currentRank = $user->rank_id ? Rank::find($user->rank_id) : $ranks->first();
        $currentRankIndex = $ranks->search(fn($r) => $r->id === ($currentRank?->id ?? $ranks->first()->id));
        $nextRank = $ranks->get($currentRankIndex + 1);

        // ── Calculate user's stats for rank qualification ──
        $userStats = $this->calculateUserStats($user);

        // ── Build progress data for each rank ──
        $rankProgress = [];
        foreach ($ranks as $index => $rank) {
            $isCurrent = $rank->id === ($currentRank?->id ?? null);
            $isAchieved = $index <= $currentRankIndex;
            $isNext = $nextRank && $rank->id === $nextRank->id;

            $requirements = $this->checkRequirements($userStats, $rank);

            $rankProgress[] = [
                'rank'           => $rank,
                'is_current'     => $isCurrent,
                'is_achieved'    => $isAchieved,
                'is_next'        => $isNext,
                'requirements'   => $requirements,
                'overall_progress' => $this->calculateOverallProgress($requirements),
            ];
        }

        // ── Rank history (promotions) ──
        $rankHistory = Transaction::where('user_id', $user->id)
            ->where('type', 'rank_promotion')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // ── Next rank specific progress ──
        $nextRankProgress = null;
        if ($nextRank) {
            $reqs = $this->checkRequirements($userStats, $nextRank);
            $nextRankProgress = [
                'rank' => $nextRank,
                'requirements' => $reqs,
                'overall_progress' => $this->calculateOverallProgress($reqs),
            ];
        }

        return view('dashboard.rank.index', compact(
            'ranks', 'currentRank', 'nextRank', 'userStats',
            'rankProgress', 'rankHistory', 'nextRankProgress'
        ));
    }

    /**
     * Calculate the user's current stats for rank qualification.
     */
    private function calculateUserStats(User $user): array
    {
        // Total personal investment
        $personalInvestment = (float) Investment::where('user_id', $user->id)
            ->where('status', 'active')
            ->sum('amount');

        // Direct referrals count
        $directReferrals = User::where('sponsor_id', $user->id)->count();

        // Active direct referrals (with at least one investment)
        $activeDirectReferrals = User::where('sponsor_id', $user->id)
            ->whereHas('investments', function ($q) {
                $q->where('status', 'active');
            })
            ->count();

        // Team volume (total downline investment)
        $teamVolume = $this->calculateTeamVolume($user);

        // Binary leg volumes
        $leftVolume = (float) ($user->left_volume ?? 0);
        $rightVolume = (float) ($user->right_volume ?? 0);

        // Total downline count
        $totalDownline = $this->countDownline($user);

        return [
            'personal_investment'       => $personalInvestment,
            'direct_referrals'           => $directReferrals,
            'active_direct_referrals'    => $activeDirectReferrals,
            'team_volume'                => $teamVolume,
            'left_volume'                => $leftVolume,
            'right_volume'               => $rightVolume,
            'total_downline'             => $totalDownline,
        ];
    }

    /**
     * Check each requirement for a rank.
     */
    private function checkRequirements(array $stats, Rank $rank): array
    {
        $requirements = [];

        // Personal investment
        $requirements[] = [
            'label'    => 'Personal Investment',
            'current'  => $stats['personal_investment'],
            'required' => (float) $rank->min_investment,
            'met'      => $stats['personal_investment'] >= (float) $rank->min_investment,
            'format'   => 'currency',
            'progress' => $this->progressPercent($stats['personal_investment'], (float) $rank->min_investment),
        ];

        // Direct referrals
        $requirements[] = [
            'label'    => 'Direct Referrals',
            'current'  => $stats['direct_referrals'],
            'required' => (int) $rank->min_direct_referrals,
            'met'      => $stats['direct_referrals'] >= (int) $rank->min_direct_referrals,
            'format'   => 'number',
            'progress' => $this->progressPercent($stats['direct_referrals'], (int) $rank->min_direct_referrals),
        ];

        // Team volume
        $requirements[] = [
            'label'    => 'Team Volume',
            'current'  => $stats['team_volume'],
            'required' => (float) $rank->min_team_volume,
            'met'      => $stats['team_volume'] >= (float) $rank->min_team_volume,
            'format'   => 'currency',
            'progress' => $this->progressPercent($stats['team_volume'], (float) $rank->min_team_volume),
        ];

        // Left leg volume
        if ((float) $rank->min_left_volume > 0) {
            $requirements[] = [
                'label'    => 'Left Leg Volume',
                'current'  => $stats['left_volume'],
                'required' => (float) $rank->min_left_volume,
                'met'      => $stats['left_volume'] >= (float) $rank->min_left_volume,
                'format'   => 'currency',
                'progress' => $this->progressPercent($stats['left_volume'], (float) $rank->min_left_volume),
            ];
        }

        // Right leg volume
        if ((float) $rank->min_right_volume > 0) {
            $requirements[] = [
                'label'    => 'Right Leg Volume',
                'current'  => $stats['right_volume'],
                'required' => (float) $rank->min_right_volume,
                'met'      => $stats['right_volume'] >= (float) $rank->min_right_volume,
                'format'   => 'currency',
                'progress' => $this->progressPercent($stats['right_volume'], (float) $rank->min_right_volume),
            ];
        }

        return $requirements;
    }

    /**
     * Calculate overall progress toward a rank (average of all requirements).
     */
    private function calculateOverallProgress(array $requirements): float
    {
        if (empty($requirements)) return 100;
        $total = 0;
        foreach ($requirements as $req) {
            $total += $req['progress'];
        }
        return round($total / count($requirements), 1);
    }

    /**
     * Calculate progress percentage.
     */
    private function progressPercent(float $current, float $required): float
    {
        if ($required <= 0) return 100;
        return min(100, round(($current / $required) * 100, 1));
    }

    /**
     * Calculate total team volume (all downline investments).
     */
    private function calculateTeamVolume(User $user): float
    {
        $total = (float) ($user->total_invested ?? 0);

        // Recursively get downline investment volume
        $directSponsored = User::where('sponsor_id', $user->id)->get();
        foreach ($directSponsored as $downline) {
            $total += $this->calculateTeamVolume($downline);
        }

        return $total;
    }

    /**
     * Count total downline users.
     */
    private function countDownline(User $user): int
    {
        $count = User::where('sponsor_id', $user->id)->count();
        $directSponsored = User::where('sponsor_id', $user->id)->get();
        foreach ($directSponsored as $downline) {
            $count += $this->countDownline($downline);
        }
        return $count;
    }
}
