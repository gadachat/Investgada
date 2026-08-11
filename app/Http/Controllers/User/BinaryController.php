<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BinaryController extends Controller
{
    /**
     * Binary tree visualization — genealogy view
     */
    public function index()
    {
        $user = auth()->user();

        // Build tree (3 levels deep for visualization)
        $tree = $this->buildTree($user, 3);

        // Leg summary
        $leftLeg  = $this->getLegStats($user, 'left');
        $rightLeg = $this->getLegStats($user, 'right');

        // Matching bonus history
        $matchingHistory = Transaction::where('user_id', $user->id)
            ->where('type', 'matching_bonus')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Matching bonus config
        $matchingBonusRate = (float) PlatformSetting::get('matching_bonus_rate', '0');
        $matchingBonusCap  = (float) PlatformSetting::get('matching_bonus_cap', '0');
        $matchingFrequency = PlatformSetting::get('matching_bonus_frequency', 'weekly');
        $flushOutPeriod    = PlatformSetting::get('flush_out_period', 'monthly');

        // Current cycle info
        $currentCycle = $this->getCurrentCycle();

        // This cycle's matched volume and earned bonus
        $cycleEarnings = $this->getCycleEarnings($user->id, $currentCycle);

        // Carry-forward balances
        $leftCarryForward  = (float) ($user->left_carry_forward ?? 0);
        $rightCarryForward = (float) ($user->right_carry_forward ?? 0);

        // Weak leg / strong leg
        $weakLeg   = $leftLeg['volume'] <= $rightLeg['volume'] ? 'left' : 'right';
        $weakVol   = min($leftLeg['volume'], $rightLeg['volume']);
        $strongVol = max($leftLeg['volume'], $rightLeg['volume']);

        // Potential matching bonus
        $potentialBonus = $weakVol * ($matchingBonusRate / 100);
        $potentialBonus = min($potentialBonus, $matchingBonusCap);

        // Total downline count
        $totalDownline = $leftLeg['count'] + $rightLeg['count'];

        // Rank info
        $rank = $user->rank ?? 'Starter';

        return view('dashboard.binary.index', compact(
            'tree', 'leftLeg', 'rightLeg', 'matchingHistory',
            'matchingBonusRate', 'matchingBonusCap', 'matchingFrequency', 'flushOutPeriod',
            'currentCycle', 'cycleEarnings', 'leftCarryForward', 'rightCarryForward',
            'weakLeg', 'weakVol', 'strongVol', 'potentialBonus', 'totalDownline', 'rank'
        ));
    }

    /**
     * Fetch tree data via AJAX (for expand/collapse)
     */
    public function getNode(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // Security: verify this user is in the downline
        if (!$this->isInDownline(auth()->id(), $userId) && $userId != auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $children = User::where('parent_id', $userId)
            ->orderBy('position')
            ->get()
            ->map(function ($child) {
                return [
                    'id'       => $child->id,
                    'name'     => $child->name,
                    'username' => $child->username,
                    'avatar'   => $child->avatar ?? null,
                    'position' => $child->position,
                    'rank'     => $child->rank ?? 'Starter',
                    'volume'   => (float) ($child->total_invested ?? 0),
                    'joined'   => $child->created_date?->format('M d, Y'),
                    'isActive' => $child->status === 'active',
                    'hasChildren' => User::where('parent_id', $child->id)->exists(),
                    'leftCount'  => $this->getLegCount($child->id, 'left'),
                    'rightCount' => $this->getLegCount($child->id, 'right'),
                ];
            });

        return response()->json([
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'rank'     => $user->rank ?? 'Starter',
                'volume'   => (float) ($user->total_invested ?? 0),
            ],
            'children' => $children,
        ]);
    }

    /**
     * Build a nested tree structure for visualization
     */
    private function buildTree($user, $depth)
    {
        $node = [
            'id'        => $user->id,
            'name'      => $user->name,
            'username'  => $user->username,
            'avatar'    => $user->avatar ?? null,
            'rank'      => $user->rank ?? 'Starter',
            'volume'    => (float) ($user->total_invested ?? 0),
            'position'  => $user->position,
            'isActive'  => $user->status === 'active',
            'joined'    => $user->created_date?->format('M d, Y'),
            'left'      => null,
            'right'     => null,
            'hasLeft'   => false,
            'hasRight'  => false,
        ];

        if ($depth <= 0) return $node;

        $leftChild  = User::where('parent_id', $user->id)->where('position', 'left')->first();
        $rightChild = User::where('parent_id', $user->id)->where('position', 'right')->first();

        if ($leftChild) {
            $node['left']    = $this->buildTree($leftChild, $depth - 1);
            $node['hasLeft']  = true;
        }
        if ($rightChild) {
            $node['right']   = $this->buildTree($rightChild, $depth - 1);
            $node['hasRight'] = true;
        }

        return $node;
    }

    /**
     * Get stats for a leg
     */
    private function getLegStats($user, $position)
    {
        $legRoot = User::where('parent_id', $user->id)->where('position', $position)->first();

        if (!$legRoot) {
            return [
                'count'    => 0,
                'volume'    => 0,
                'active'    => 0,
                'hasRoot'   => false,
                'rootName'  => null,
                'rootRank'  => null,
                'rootId'    => null,
            ];
        }

        $count  = 1 + $this->getDownlineCount($legRoot->id);
        $volume = (float) ($legRoot->total_invested ?? 0) + $this->getDownlineVolume($legRoot->id);
        $active = $legRoot->status === 'active' ? 1 : 0;
        $active += $this->getActiveDownlineCount($legRoot->id);

        return [
            'count'    => $count,
            'volume'   => $volume,
            'active'   => $active,
            'hasRoot'  => true,
            'rootName' => $legRoot->name,
            'rootRank' => $legRoot->rank ?? 'Starter',
            'rootId'   => $legRoot->id,
        ];
    }

    private function getDownlineCount($userId, $maxDepth = 15)
    {
        $count = 0;
        $currentLevel = [$userId];

        for ($i = 0; $i < $maxDepth; $i++) {
            $nextLevel = User::whereIn('parent_id', $currentLevel)->pluck('id')->toArray();
            if (empty($nextLevel)) break;
            $count += count($nextLevel);
            $currentLevel = $nextLevel;
        }

        return $count;
    }

    private function getDownlineVolume($userId, $maxDepth = 15)
    {
        $volume = 0;
        $currentLevel = [$userId];

        for ($i = 0; $i < $maxDepth; $i++) {
            $users = User::whereIn('id', $currentLevel)->get();
            foreach ($users as $u) {
                $volume += (float) ($u->total_invested ?? 0);
            }
            $nextLevel = User::whereIn('parent_id', $currentLevel)->pluck('id')->toArray();
            if (empty($nextLevel)) break;
            $currentLevel = $nextLevel;
        }

        return $volume;
    }

    private function getActiveDownlineCount($userId, $maxDepth = 15)
    {
        $count = 0;
        $currentLevel = [$userId];

        for ($i = 0; $i < $maxDepth; $i++) {
            $nextLevel = User::where('status', 'active')
                ->whereIn('parent_id', $currentLevel)
                ->pluck('id')->toArray();
            if (empty($nextLevel)) break;
            $count += count($nextLevel);
            $currentLevel = User::whereIn('parent_id', $currentLevel)->pluck('id')->toArray();
            if (empty($currentLevel)) break;
        }

        return $count;
    }

    private function getLegCount($userId, $position)
    {
        $legRoot = User::where('parent_id', $userId)->where('position', $position)->first();
        if (!$legRoot) return 0;
        return 1 + $this->getDownlineCount($legRoot->id);
    }

    /**
     * Check if $descendantId is in the downline of $ancestorId
     */
    private function isInDownline($ancestorId, $descendantId, $maxDepth = 20)
    {
        $currentLevel = [$ancestorId];

        for ($i = 0; $i < $maxDepth; $i++) {
            $nextLevel = User::whereIn('parent_id', $currentLevel)->pluck('id')->toArray();
            if (empty($nextLevel)) return false;
            if (in_array($descendantId, $nextLevel)) return true;
            $currentLevel = $nextLevel;
        }

        return false;
    }

    /**
     * Get current bonus cycle
     */
    private function getCurrentCycle()
    {
        $frequency = PlatformSetting::get('matching_bonus_frequency', 'weekly');

        if ($frequency === 'daily') {
            $start = now()->startOfDay();
            $end  = now()->endOfDay();
            $label = now()->format('M d, Y');
        } elseif ($frequency === 'monthly') {
            $start = now()->startOfMonth();
            $end  = now()->endOfMonth();
            $label = now()->format('F Y');
        } else {
            // weekly (default)
            $start = now()->startOfWeek();
            $end  = now()->endOfWeek();
            $label = 'Week of ' . $start->format('M d');
        }

        return [
            'start' => $start,
            'end'   => $end,
            'label' => $label,
        ];
    }

    /**
     * Get earnings for current cycle
     */
    private function getCycleEarnings($userId, $cycle)
    {
        $earnings = Transaction::where('user_id', $userId)
            ->where('type', 'matching_bonus')
            ->whereBetween('created_at', [$cycle['start'], $cycle['end']])
            ->sum('amount');

        $matchedVolume = Transaction::where('user_id', $userId)
            ->where('type', 'matching_bonus')
            ->whereBetween('created_at', [$cycle['start'], $cycle['end']])
            ->sum('metadata->matched_volume');

        return [
            'earnings'      => (float) $earnings,
            'matchedVolume' => (float) $matchedVolume,
        ];
    }
}
