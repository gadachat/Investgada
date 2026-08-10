<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminProfitShareController extends Controller
{
    /**
     * Profit-sharing overview — admin control panel.
     */
    public function index(Request $request)
    {
        $settings = $this->getSettings();

        // Current cycle info
        $currentCycle = $this->getCurrentCycle($settings['profit_cycle_frequency']);

        // Total pool stats
        $totalDistributed = DB::table('profit_distributions')
            ->where('status', 'completed')
            ->sum('amount');

        $totalCyclesRun = DB::table('profit_distributions')
            ->distinct('cycle_id')
            ->count('cycle_id');

        $lastCycle = DB::table('profit_distributions')
            ->orderBy('created_at', 'desc')
            ->first();

        // Eligible users — have active investments with min capital
        $eligibleInvestments = DB::table('investments')
            ->where('status', 'active')
            ->get();

        $totalActiveCapital = $eligibleInvestments->sum('amount');

        // Weighted capital
        $weightedCapital = 0;
        $eligibleUsers = [];
        foreach ($eligibleInvestments as $inv) {
            $pkg = DB::table('investment_packages')->where('id', $inv->package_id)->first();
            $weight = $pkg->profit_share_weight ?? 1.0;
            $weighted = $inv->amount * $weight;
            $weightedCapital += $weighted;

            if (!isset($eligibleUsers[$inv->user_id])) {
                $eligibleUsers[$inv->user_id] = [
                    'user_id' => $inv->user_id,
                    'raw_capital' => 0,
                    'weighted_capital' => 0,
                    'investments' => 0,
                ];
            }
            $eligibleUsers[$inv->user_id]['raw_capital'] += $inv->amount;
            $eligibleUsers[$inv->user_id]['weighted_capital'] += $weighted;
            $eligibleUsers[$inv->user_id]['investments']++;
        }

        // Add user names
        foreach ($eligibleUsers as $uid => &$data) {
            $u = DB::table('users')->where('id', $uid)->first();
            $data['user_name'] = $u->name ?? 'Unknown';
            $data['user_email'] = $u->email ?? '';
            $data['share_percentage'] = $weightedCapital > 0
                ? ($data['weighted_capital'] / $weightedCapital) * 100
                : 0;
            $data['estimated_payout'] = 0; // computed when pool amount is entered
        }
        unset($data);

        // Recent distributions
        $recentDistributions = DB::table('profit_distributions')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($d) {
                $u = DB::table('users')->where('id', $d->user_id)->first();
                $d->user_name = $u->name ?? 'Unknown';
                return $d;
            });

        // All cycle history
        $cycleHistory = DB::table('profit_distributions')
            ->select('cycle_id', DB::raw('count(*) as recipients'), DB::raw('sum(amount) as total'), DB::raw('min(created_at) as date'))
            ->groupBy('cycle_id')
            ->orderBy('date', 'desc')
            ->limit(20)
            ->get();

        return view('admin.profit-share.index', compact(
            'settings', 'currentCycle', 'totalDistributed', 'totalCyclesRun',
            'lastCycle', 'totalActiveCapital', 'weightedCapital',
            'eligibleUsers', 'recentDistributions', 'cycleHistory'
        ));
    }

    /**
     * Run a profit-sharing cycle manually.
     */
    public function runCycle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pool_amount' => 'required|numeric|min:0.01',
            'cycle_note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $settings = $this->getSettings();

        if (!$settings['profit_share_enabled']) {
            return back()->with('error', 'Profit sharing is disabled. Enable it in settings first.');
        }

        $poolAmount = $request->pool_amount;

        // Check max daily payout
        if ($poolAmount > $settings['max_daily_payout']) {
            return back()->with('error', 'Pool amount exceeds the maximum daily payout of $' . number_format($settings['max_daily_payout']));
        }

        // Get all active investments with their package weights
        $investments = DB::table('investments')
            ->where('status', 'active')
            ->get();

        if ($investments->isEmpty()) {
            return back()->with('error', 'No active investments to distribute profits to.');
        }

        // Compute weighted capital
        $weightedCapital = 0;
        $userCapital = [];
        foreach ($investments as $inv) {
            $pkg = DB::table('investment_packages')->where('id', $inv->package_id)->first();
            $weight = $pkg->profit_share_weight ?? 1.0;
            $weighted = $inv->amount * $weight;
            $weightedCapital += $weighted;

            if (!isset($userCapital[$inv->user_id])) {
                $userCapital[$inv->user_id] = ['weighted' => 0, 'investments' => []];
            }
            $userCapital[$inv->user_id]['weighted'] += $weighted;
            $userCapital[$inv->user_id]['investments'][] = $inv;
        }

        if ($weightedCapital <= 0) {
            return back()->with('error', 'Total weighted capital is zero. Check package weights.');
        }

        // Generate cycle ID
        $cycleId = 'PS-' . now()->format('YmdHis');
        $now = now();
        $distributed = 0;
        $recipientCount = 0;

        DB::transaction(function () use ($userCapital, $weightedCapital, $poolAmount, $cycleId, $now, $settings, &$distributed, &$recipientCount, $request) {
            foreach ($userCapital as $userId => $data) {
                // User's share = (their weighted capital / total weighted capital) * pool amount
                $shareAmount = ($data['weighted'] / $weightedCapital) * $poolAmount;

                if ($shareAmount < 0.01) continue;

                // Distribute proportionally across the user's investments
                $userWeighted = $data['weighted'];
                foreach ($data['investments'] as $inv) {
                    $pkg = DB::table('investment_packages')->where('id', $inv->package_id)->first();
                    $invWeight = $inv->amount * ($pkg->profit_share_weight ?? 1.0);
                    $invShare = $userWeighted > 0 ? ($invWeight / $userWeighted) * $shareAmount : 0;

                    if ($invShare < 0.01) continue;

                    // Create distribution record
                    DB::table('profit_distributions')->insert([
                        'user_id' => $userId,
                        'investment_id' => $inv->id,
                        'cycle_id' => $cycleId,
                        'amount' => round($invShare, 2),
                        'pool_amount' => $poolAmount,
                        'weighted_capital' => $invWeight,
                        'total_weighted_capital' => $weightedCapital,
                        'share_percentage' => $weightedCapital > 0 ? ($invWeight / $weightedCapital) * 100 : 0,
                        'status' => 'completed',
                        'note' => $request->cycle_note,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    // Credit the user's interest wallet
                    DB::table('wallets')
                        ->where('user_id', $userId)
                        ->where('type', 'interest')
                        ->increment('balance', round($invShare, 2));

                    // Create transaction record
                    DB::table('transactions')->insert([
                        'user_id' => $userId,
                        'wallet_id' => DB::table('wallets')->where('user_id', $userId)->where('type', 'interest')->value('id'),
                        'type' => 'profit_share',
                        'amount' => round($invShare, 2),
                        'direction' => 'credit',
                        'reference' => 'PS-' . $cycleId . '-' . $inv->id,
                        'description' => 'Profit share from cycle ' . $cycleId,
                        'status' => 'completed',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    // Update investment earned amount
                    DB::table('investments')
                        ->where('id', $inv->id)
                        ->increment('earned_amount', round($invShare, 2));

                    // Send notification
                    DB::table('notifications')->insert([
                        'user_id' => $userId,
                        'type' => 'profit',
                        'title' => 'Profit Share Received',
                        'message' => 'You received $' . number_format($invShare, 2) . ' from profit-sharing cycle ' . $cycleId,
                        'link' => '/dashboard/profit-share',
                        'is_read' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $distributed += $invShare;
                    $recipientCount++;
                }
            }
        });

        return back()->with('success', "Profit cycle {$cycleId} completed. Distributed $" . number_format($distributed, 2) . " to {$recipientCount} investments.");
    }

    /**
     * Update profit-sharing settings.
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profit_share_enabled' => 'boolean',
            'profit_pool_percentage' => 'nullable|numeric|min:0|max:100',
            'profit_cycle_frequency' => 'in:daily,weekly,biweekly,monthly',
            'min_active_capital' => 'nullable|numeric|min:0',
            'max_daily_payout' => 'nullable|numeric|min:0',
            'weighting_mode' => 'in:package_weight,equal,custom',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $settings = [
            'profit_share_enabled' => $request->has('profit_share_enabled') ? '1' : '0',
            'profit_pool_percentage' => $request->get('profit_pool_percentage', '0'),
            'profit_cycle_frequency' => $request->get('profit_cycle_frequency', 'daily'),
            'min_active_capital' => $request->get('min_active_capital', '0'),
            'max_daily_payout' => $request->get('max_daily_payout', '0'),
            'weighting_mode' => $request->get('weighting_mode', 'package_weight'),
        ];

        foreach ($settings as $key => $value) {
            $this->updateSetting($key, $value, 'profit_share');
        }

        return back()->with('success', 'Profit-sharing settings updated.');
    }

    private function getSettings()
    {
        $raw = DB::table('platform_settings')->where('group', 'profit_share')->pluck('value', 'key')->toArray();
        return [
            'profit_share_enabled' => ($raw['profit_share_enabled'] ?? '1') === '1',
            'profit_pool_percentage' => floatval($raw['profit_pool_percentage'] ?? '30'),
            'profit_cycle_frequency' => $raw['profit_cycle_frequency'] ?? 'daily',
            'min_active_capital' => floatval($raw['min_active_capital'] ?? '100'),
            'max_daily_payout' => floatval($raw['max_daily_payout'] ?? '5000'),
            'weighting_mode' => $raw['weighting_mode'] ?? 'package_weight',
        ];
    }

    private function updateSetting($key, $value, $group)
    {
        $exists = DB::table('platform_settings')->where('key', $key)->exists();
        if ($exists) {
            DB::table('platform_settings')->where('key', $key)->update(['value' => $value, 'updated_at' => now()]);
        } else {
            DB::table('platform_settings')->insert(['key' => $key, 'value' => $value, 'group' => $group, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    private function getCurrentCycle($frequency)
    {
        $last = DB::table('profit_distributions')->orderBy('created_at', 'desc')->first();
        return [
            'id' => $last->cycle_id ?? null,
            'date' => $last->created_at ?? null,
            'next' => match ($frequency) {
                'daily' => now()->addDay(),
                'weekly' => now()->addWeek(),
                'biweekly' => now()->addWeeks(2),
                'monthly' => now()->addMonth(),
                default => now()->addDay(),
            },
        ];
    }
}
