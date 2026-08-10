<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Referral;
use App\Models\Transaction;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    /**
     * Referral dashboard — overview, link, QR code, direct referrals, earnings, multi-level stats & marketing.
     */
    public function index()
    {
        $user = auth()->user();

        // Ensure user has a referral code
        if (empty($user->referral_code)) {
            $user->referral_code = User::generateUniqueReferralCode();
            $user->save();
        }

        // Direct referrals (by sponsor_id or referred_by_code)
        $directReferrals = User::where(function ($query) use ($user) {
                $query->where('sponsor_id', $user->id)
                      ->orWhere('referred_by_code', $user->referral_code);
            })
            ->with(['investments' => function ($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Referral stats
        $totalReferrals = $directReferrals->count();
        $activeReferrals = $directReferrals->filter(function ($r) {
            return $r->investments->where('status', 'active')->isNotEmpty() || ($r->total_invested ?? 0) > 0;
        })->count();
        $inactiveReferrals = $totalReferrals - $activeReferrals;
        $conversionRate = $totalReferrals > 0 ? round(($activeReferrals / $totalReferrals) * 100, 1) : 0;

        // Referral earnings from transactions
        $referralEarnings = (float) Transaction::where('user_id', $user->id)
            ->whereIn('type', ['referral_commission', 'direct_referral', 'direct_referral_bonus'])
            ->sum('amount');

        $matchingBonus = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'matching_bonus')
            ->sum('amount');

        $totalEarned = $referralEarnings + $matchingBonus;

        // Commission rates from settings or defaults
        $directCommissionRate = (float) PlatformSetting::get('direct_referral_commission', '5.0');
        $level2CommissionRate = (float) PlatformSetting::get('level_2_referral_commission', '2.0');
        $level3CommissionRate = (float) PlatformSetting::get('level_3_referral_commission', '1.0');
        $matchingBonusRate     = (float) PlatformSetting::get('matching_bonus_rate', '10.0');
        $matchingBonusCap      = (float) PlatformSetting::get('matching_bonus_cap', '5000.0');

        // Multi-level breakdown
        $level1Ids = $directReferrals->pluck('id')->toArray();
        $level2Ids = !empty($level1Ids) ? User::whereIn('sponsor_id', $level1Ids)->pluck('id')->toArray() : [];
        $level3Ids = !empty($level2Ids) ? User::whereIn('sponsor_id', $level2Ids)->pluck('id')->toArray() : [];

        $level1Earnings = $referralEarnings;
        $level2Earnings = (float) Transaction::where('user_id', $user->id)->where('type', 'level_2_commission')->sum('amount');
        $level3Earnings = (float) Transaction::where('user_id', $user->id)->where('type', 'level_3_commission')->sum('amount');

        $commissionLevels = [
            [
                'level' => 1,
                'title' => 'Level 1 (Direct)',
                'rate' => $directCommissionRate,
                'count' => $totalReferrals,
                'active' => $activeReferrals,
                'earnings' => $level1Earnings,
            ],
            [
                'level' => 2,
                'title' => 'Level 2 (Tier 2)',
                'rate' => $level2CommissionRate,
                'count' => count($level2Ids),
                'active' => !empty($level2Ids) ? User::whereIn('id', $level2Ids)->where('total_invested', '>', 0)->count() : 0,
                'earnings' => $level2Earnings,
            ],
            [
                'level' => 3,
                'title' => 'Level 3 (Tier 3)',
                'rate' => $level3CommissionRate,
                'count' => count($level3Ids),
                'active' => !empty($level3Ids) ? User::whereIn('id', $level3Ids)->where('total_invested', '>', 0)->count() : 0,
                'earnings' => $level3Earnings,
            ],
        ];

        // Recent commission transactions
        $recentCommissions = Transaction::where('user_id', $user->id)
            ->whereIn('type', ['referral_commission', 'matching_bonus', 'direct_referral_bonus', 'level_2_commission', 'level_3_commission'])
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        // Referral link & QR Code URL
        $referralLink = $user->getReferralLink();
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($referralLink);

        // Binary leg stats
        $totalDownline = $this->getDownlineCount($user->id);
        $leftLegCount  = $this->getLegCount($user->id, 'left');
        $rightLegCount = $this->getLegCount($user->id, 'right');

        $leftLegVolume  = $this->getLegVolume($user->id, 'left');
        $rightLegVolume = $this->getLegVolume($user->id, 'right');

        $leftCarryForward  = (float) ($user->left_carry_forward ?? 0);
        $rightCarryForward = (float) ($user->right_carry_forward ?? 0);

        // Weekly earnings
        $weekStart = now()->startOfWeek();
        $weekEarnings = Transaction::where('user_id', $user->id)
            ->whereIn('type', ['referral_commission', 'matching_bonus', 'direct_referral_bonus', 'level_2_commission', 'level_3_commission'])
            ->where('created_at', '>=', $weekStart)
            ->sum('amount');

        // Promotional text templates
        $marketingPromos = [
            'short' => "🚀 Join me on " . config('app.name', 'our investment platform') . " and start earning daily returns on crypto and forex trading! Sign up using my referral link: " . $referralLink,
            'social' => "📈 High-yield trading & automated investment strategies! Sign up today on " . config('app.name', 'our platform') . " and claim your welcome bonus. Referral Link: " . $referralLink . " (Code: " . $user->referral_code . ")",
            'email' => "Hi there,\n\nI've been using " . config('app.name', 'this investment platform') . " for trading and daily automated profits. It offers secure deposits, instant withdrawals, and transparent commission structures.\n\nCheck it out and register here: " . $referralLink . "\n\nReferral Code: " . $user->referral_code,
        ];

        return view('dashboard.referral.index', compact(
            'directReferrals', 'totalReferrals', 'activeReferrals', 'inactiveReferrals', 'conversionRate',
            'referralEarnings', 'matchingBonus', 'totalEarned', 'directCommissionRate', 'level2CommissionRate',
            'level3CommissionRate', 'matchingBonusRate', 'matchingBonusCap', 'commissionLevels',
            'recentCommissions', 'referralLink', 'qrCodeUrl', 'totalDownline',
            'leftLegCount', 'rightLegCount', 'leftLegVolume', 'rightLegVolume',
            'leftCarryForward', 'rightCarryForward', 'weekEarnings', 'marketingPromos'
        ));
    }

    /**
     * Download marketing materials pack as text file.
     */
    public function downloadMarketing()
    {
        $user = auth()->user();

        if (empty($user->referral_code)) {
            $user->referral_code = User::generateUniqueReferralCode();
            $user->save();
        }

        $appName = config('app.name', 'Investment & Trading Platform');
        $referralLink = $user->getReferralLink();
        $code = $user->referral_code;

        $content = "=====================================================\n";
        $content .= "   " . strtoupper($appName) . " MARKETING & PROMOTIONAL PACK\n";
        $content .= "=====================================================\n\n";

        $content .= "YOUR REFERRAL CREDENTIALS:\n";
        $content .= "-----------------------------------------------------\n";
        $content .= "Referral Code : " . $code . "\n";
        $content .= "Referral Link : " . $referralLink . "\n";
        $content .= "User Name     : " . $user->name . "\n\n";

        $content .= "1. SHORT SOCIAL MEDIA POST (WhatsApp, Telegram, X, Facebook):\n";
        $content .= "-----------------------------------------------------\n";
        $content .= "🚀 Join me on " . $appName . " and start earning passive income through expert trading strategies! Sign up using my referral link: " . $referralLink . "\n\n";

        $content .= "2. DETAILED PROMOTIONAL COPY:\n";
        $content .= "-----------------------------------------------------\n";
        $content .= "Maximize your investment returns with " . $appName . "!\n";
        $content .= "✔ Daily Automated Trading Returns\n";
        $content .= "✔ Transparent Multi-Level Commission Structure\n";
        $content .= "✔ Instant & Secure Withdrawals\n";
        $content .= "✔ 24/7 Dedicated Support\n\n";
        $content .= "Get started in 3 simple steps:\n";
        $content .= "1. Register using my link: " . $referralLink . "\n";
        $content .= "2. Deposit funds to your trading wallet\n";
        $content .= "3. Choose an investment plan and watch your balance grow!\n\n";

        $content .= "3. EMAIL INVITATION TEMPLATE:\n";
        $content .= "-----------------------------------------------------\n";
        $content .= "Subject: Exclusive Invitation to Join " . $appName . "\n\n";
        $content .= "Hello,\n\n";
        $content .= "I am inviting you to join " . $appName . ", an advanced platform for crypto, forex, and automated portfolio growth.\n\n";
        $content .= "You can register directly using my unique invite link below:\n";
        $content .= $referralLink . "\n\n";
        $content .= "If prompted, enter referral code: " . $code . "\n\n";
        $content .= "Best regards,\n" . $user->name . "\n\n";

        $content .= "4. HTML BANNER EMBED SNIPPET (728x90 Leaderboard):\n";
        $content .= "-----------------------------------------------------\n";
        $content .= '<a href="' . $referralLink . '" target="_blank">' . "\n";
        $content .= '  <div style="width:728px; height:90px; background:linear-gradient(135deg,#6366f1,#7c3aed); color:#ffffff; padding:15px; text-align:center; font-family:sans-serif; border-radius:8px; text-decoration:none;">' . "\n";
        $content .= '    <h3 style="margin:0 0 5px;">Invest & Earn Daily Returns on ' . htmlspecialchars($appName) . '</h3>' . "\n";
        $content .= '    <p style="margin:0; font-size:14px;">Click here to sign up with code ' . $code . '</p>' . "\n";
        $content .= '  </div>' . "\n";
        $content .= '</a>' . "\n\n";

        $fileName = 'marketing-materials-' . strtolower($code) . '.txt';

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Generate a new referral link (for custom campaigns)
     */
    public function generateLink(Request $request)
    {
        $user = auth()->user();
        $campaign = $request->input('campaign', 'default');

        if (empty($user->referral_code)) {
            $user->referral_code = User::generateUniqueReferralCode();
            $user->save();
        }

        $link = url('/register?ref=' . $user->referral_code . '&src=' . urlencode($campaign));

        return response()->json([
            'success' => true,
            'link' => $link,
            'qr' => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($link),
        ]);
    }

    /**
     * Count all descendants in binary tree
     */
    private function getDownlineCount($userId, $depth = 10)
    {
        $count = 0;
        $currentLevel = [$userId];

        for ($i = 0; $i < $depth; $i++) {
            $nextLevel = User::whereIn('parent_id', $currentLevel)->pluck('id')->toArray();
            if (empty($nextLevel)) break;
            $count += count($nextLevel);
            $currentLevel = $nextLevel;
        }

        return $count;
    }

    /**
     * Count members in a specific leg
     */
    private function getLegCount($userId, $position)
    {
        $legRoot = User::where('parent_id', $userId)->where('binary_position', $position)->first();
        if (!$legRoot) return 0;

        return 1 + $this->getDownlineCount($legRoot->id);
    }

    /**
     * Sum investment volume in a leg
     */
    private function getLegVolume($userId, $position)
    {
        $legRoot = User::where('parent_id', $userId)->where('binary_position', $position)->first();
        if (!$legRoot) return 0;

        $volume = 0;
        $currentLevel = [$legRoot->id];

        for ($i = 0; $i < 15; $i++) {
            $users = User::whereIn('id', $currentLevel)->get();
            foreach ($users as $u) {
                $volume += $u->total_invested ?? 0;
            }
            $nextLevel = User::whereIn('parent_id', $currentLevel)->pluck('id')->toArray();
            if (empty($nextLevel)) break;
            $currentLevel = $nextLevel;
        }

        return $volume;
    }
}
