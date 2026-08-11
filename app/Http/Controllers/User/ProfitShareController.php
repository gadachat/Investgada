<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitShareController extends Controller
{
    /**
     * Display the user's profit-sharing history and current cycle info.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get all profit distributions for this user
        $distributions = DB::table('profit_distributions')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Summary stats
        $totalEarned = DB::table('profit_distributions')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        $totalCycles = DB::table('profit_distributions')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->distinct('cycle_id')
            ->count('cycle_id');

        $lastDistribution = DB::table('profit_distributions')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Active investments eligible for profit sharing
        $activeInvestments = DB::table('investments')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        $totalActiveCapital = $activeInvestments->sum('amount');
        $weightedCapital = $activeInvestments->sum(function ($inv) {
            $package = DB::table('investment_packages')->where('id', $inv->package_id)->first();
            $weight = $package->profit_share_weight ?? 1.0;
            return $inv->amount * $weight;
        });

        // Get profit-sharing settings
        $settings = $this->getSettings();

        // Next cycle info
        $cycleFrequency = $settings['profit_cycle_frequency'] ?? 'daily';
        $nextCycle = $this->getNextCycleDate($cycleFrequency);

        // Per-package breakdown
        $packageBreakdown = $activeInvestments->map(function ($inv) {
            $pkg = DB::table('investment_packages')->where('id', $inv->package_id)->first();
            return [
                'package_name' => $pkg->name ?? 'Unknown',
                'category' => $pkg->category ?? 'unknown',
                'amount' => $inv->amount,
                'weight' => 1.0,
                'weighted' => $inv->amount * (1.0),
                'start_date' => $inv->activated_at,
            ];
        });

        return view('dashboard.profit-share.index', compact(
            'distributions',
            'totalEarned',
            'totalCycles',
            'lastDistribution',
            'activeInvestments',
            'totalActiveCapital',
            'weightedCapital',
            'settings',
            'nextCycle',
            'packageBreakdown'
        ));
    }

    /**
     * View details of a specific profit distribution.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $distribution = DB::table('profit_distributions')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$distribution) {
            abort(404);
        }

        $investment = DB::table('investments')->where('id', $distribution->investment_id)->first();
        $package = $investment ? DB::table('investment_packages')->where('id', $investment->package_id)->first() : null;

        return view('dashboard.profit-share.show', compact('distribution', 'investment', 'package'));
    }

    private function getSettings()
    {
        $settings = DB::table('platform_settings')->where('group', 'profit_share')->pluck('value', 'key')->toArray();
        return [
            'profit_share_enabled' => ($settings['profit_share_enabled'] ?? '1') === '1',
            'profit_pool_percentage' => floatval($settings['profit_pool_percentage'] ?? '30'),
            'profit_cycle_frequency' => $settings['profit_cycle_frequency'] ?? 'daily',
            'min_active_capital' => floatval($settings['min_active_capital'] ?? '100'),
            'max_daily_payout' => floatval($settings['max_daily_payout'] ?? '5000'),
            'weighting_mode' => $settings['weighting_mode'] ?? 'package_weight',
        ];
    }

    private function getNextCycleDate($frequency)
    {
        $now = now();
        return match ($frequency) {
            'daily' => $now->addDay(),
            'weekly' => $now->addWeek(),
            'biweekly' => $now->addWeeks(2),
            'monthly' => $now->addMonth(),
            default => $now->addDay(),
        };
    }
}
