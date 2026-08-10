<?php

namespace Database\Seeders;

use App\Models\Rank;
use App\Models\FeatureSetting;
use App\Models\PlatformSetting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RanksSeeder::class,
            FeatureSettingsSeeder::class,
            PlatformSettingsSeeder::class,
        ]);
    }
}

class RanksSeeder extends Seeder
{
    public function run(): void
    {
        $ranks = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'badge_color' => '#94a3b8',
                'min_investment' => 0,
                'min_direct_referrals' => 0,
                'matching_bonus_percent' => 0,
                'direct_referral_percent' => 0,
                'profit_share_percent' => 0,
                'salary_bonus' => 0,
                'sort_order' => 1,
            ],
            [
                'name' => 'Silver',
                'slug' => 'silver',
                'badge_color' => '#94a3b8',
                'min_investment' => 0,
                'min_direct_referrals' => 0,
                'min_team_volume' => 0,
                'matching_bonus_percent' => 0,
                'direct_referral_percent' => 0,
                'profit_share_percent' => 0,
                'salary_bonus' => 0,
                'sort_order' => 2,
            ],
            [
                'name' => 'Gold',
                'slug' => 'gold',
                'badge_color' => '#fbbf24',
                'min_investment' => 0,
                'min_direct_referrals' => 0,
                'min_team_volume' => 0,
                'matching_bonus_percent' => 0,
                'direct_referral_percent' => 0,
                'profit_share_percent' => 0,
                'salary_bonus' => 0,
                'sort_order' => 3,
            ],
            [
                'name' => 'Platinum',
                'slug' => 'platinum',
                'badge_color' => '#c0c0c0',
                'min_investment' => 0,
                'min_direct_referrals' => 0,
                'min_team_volume' => 0,
                'matching_bonus_percent' => 0,
                'direct_referral_percent' => 0,
                'profit_share_percent' => 0,
                'salary_bonus' => 0,
                'sort_order' => 4,
            ],
            [
                'name' => 'Diamond',
                'slug' => 'diamond',
                'badge_color' => '#60a5fa',
                'min_investment' => 0,
                'min_direct_referrals' => 0,
                'min_team_volume' => 0,
                'matching_bonus_percent' => 0,
                'direct_referral_percent' => 0,
                'profit_share_percent' => 0,
                'salary_bonus' => 0,
                'sort_order' => 5,
            ],
        ];

        foreach ($ranks as $rank) {
            Rank::firstOrCreate(['slug' => $rank['slug']], $rank);
        }
    }
}

class FeatureSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            ['key' => 'crypto', 'label' => 'Crypto Trading Module', 'is_enabled' => true, 'description' => 'Enable/disable crypto investment packages'],
            ['key' => 'forex', 'label' => 'Forex Trading Module', 'is_enabled' => true, 'description' => 'Enable/disable forex investment packages'],
            ['key' => 'stocks', 'label' => 'Stocks Module', 'is_enabled' => false, 'description' => 'Enable/disable stock investment packages'],
            ['key' => 'bonds', 'label' => 'Bonds Module', 'is_enabled' => false, 'description' => 'Enable/disable bond investment packages'],
            ['key' => 'binary', 'label' => 'Binary Trading Module', 'is_enabled' => false, 'description' => 'Enable/disable binary options trading'],
            ['key' => 'kyc', 'label' => 'KYC Verification', 'is_enabled' => false, 'description' => 'Require KYC before withdrawals'],
            ['key' => 'referral', 'label' => 'Referral System', 'is_enabled' => true, 'description' => 'Enable direct referral commissions'],
            ['key' => 'binary_mlm', 'label' => 'Binary MLM', 'is_enabled' => true, 'description' => 'Binary tree and matching bonuses'],
            ['key' => 'matching_bonus', 'label' => 'Matching Bonus', 'is_enabled' => true, 'description' => 'Binary matching bonus payouts'],
            ['key' => 'profit_share', 'label' => 'Profit Sharing', 'is_enabled' => true, 'description' => 'Distribute profit to eligible rank holders'],
            ['key' => 'deposit', 'label' => 'Deposits', 'is_enabled' => true, 'description' => 'Allow users to deposit funds'],
            ['key' => 'withdrawal', 'label' => 'Withdrawals', 'is_enabled' => true, 'description' => 'Allow users to withdraw funds'],
            ['key' => 'support', 'label' => 'Support Tickets', 'is_enabled' => true, 'description' => 'Support ticket system'],
            ['key' => 'live_trading', 'label' => 'Live Exchange Trading', 'is_enabled' => false, 'description' => 'Connect to live exchange APIs (Binance, Bybit, MT5)'],
            ['key' => 'auto_trading', 'label' => 'Auto Trading Bot', 'is_enabled' => true, 'description' => 'Automated trading bot with configurable risk levels'],
            ['key' => 'trading_signals', 'label' => 'Trading Signals', 'is_enabled' => true, 'description' => 'Admin-published trading signals for users'],
            ['key' => 'copy_trading', 'label' => 'Copy Trading', 'is_enabled' => true, 'description' => 'Users can copy trades from master traders'],
            ['key' => 'announcements', 'label' => 'Announcements', 'is_enabled' => true, 'description' => 'Admin broadcast announcements to users'],
            ['key' => 'activity_log', 'label' => 'Activity Log', 'is_enabled' => true, 'description' => 'User activity tracking and audit trail'],
            ['key' => 'two_factor', 'label' => '2FA Security', 'is_enabled' => true, 'description' => 'Two-factor authentication with Google Authenticator'],
            ['key' => 'pdf_invoices', 'label' => 'PDF Invoices', 'is_enabled' => true, 'description' => 'Downloadable PDF receipts and account statements'],
        ];

        foreach ($features as $feature) {
            FeatureSetting::firstOrCreate(['key' => $feature['key']], $feature);
        }
    }
}

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'platform_name', 'value' => 'APTrades Investment', 'group' => 'general'],
            ['key' => 'platform_tagline', 'value' => 'Invest. Grow. Earn.', 'group' => 'general'],
            ['key' => 'support_email', 'value' => 'support@aptrades.com', 'group' => 'general'],
            ['key' => 'default_currency', 'value' => 'USD', 'group' => 'general'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'general'],
            ['key' => 'registration_open', 'value' => 'true', 'group' => 'general'],

            // Payment
            ['key' => 'min_deposit', 'value' => '50', 'group' => 'payment'],
            ['key' => 'max_deposit', 'value' => '100000', 'group' => 'payment'],
            ['key' => 'min_withdrawal', 'value' => '20', 'group' => 'payment'],
            ['key' => 'max_withdrawal', 'value' => '50000', 'group' => 'payment'],
            ['key' => 'deposit_fee_percent', 'value' => '0', 'group' => 'payment'],
            ['key' => 'withdrawal_fee_percent', 'value' => '0', 'group' => 'payment'],
            ['key' => 'withdrawal_processing_hours', 'value' => '48', 'group' => 'payment'],
            ['key' => 'withdrawal_schedule', 'value' => 'daily', 'group' => 'payment'],

            // Investment
            ['key' => 'max_active_investments', 'value' => '10', 'group' => 'investment'],
            ['key' => 'auto_activate_deposit', 'value' => 'false', 'group' => 'investment'],
            ['key' => 'compounding_enabled', 'value' => 'true', 'group' => 'investment'],

            // MLM
            ['key' => 'matching_bonus_cap_percent', 'value' => '15', 'group' => 'mlm'],
            ['key' => 'matching_flush_mode', 'value' => 'carry_forward', 'group' => 'mlm'],
            ['key' => 'binary_daily_limit', 'value' => '5000', 'group' => 'mlm'],
            ['key' => 'direct_referral_levels', 'value' => '0', 'group' => 'mlm'],
        ];

        foreach ($settings as $setting) {
            PlatformSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
