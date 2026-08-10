<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class LandingController extends Controller
{
    /**
     * Landing page with live content
     */
    public function index()
    {
        // If app is not installed yet, redirect to installer
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            return redirect('/install');
        }

        // Check if users table exists (installation complete)
        try {
            DB::table('users')->limit(1)->exists();
        } catch (\Exception $e) {
            return redirect('/install');
        }

        // Editable content from settings
        try {
            $content = [
                'hero_title'         => Setting::get('hero_title', 'Trade Smarter. Earn Bigger.'),
                'hero_subtitle'      => Setting::get('hero_subtitle', 'The next-generation investment platform for crypto, forex, stocks, and bonds. Powered by AI-driven analytics and a secure multi-asset wallet system.'),
                'hero_badge'         => Setting::get('hero_badge', 'Trusted by 50,000+ investors worldwide'),
                'hero_cta_primary'   => Setting::get('hero_cta_primary', 'Start Investing'),
                'hero_cta_secondary'  => Setting::get('hero_cta_secondary', 'Explore Packages'),

                'stat1_value' => Setting::get('stat1_value', '$250M+'),
                'stat1_label' => Setting::get('stat1_label', 'Total Volume'),
                'stat2_value' => Setting::get('stat2_value', '50,000+'),
                'stat2_label' => Setting::get('stat2_label', 'Active Investors'),
                'stat3_value' => Setting::get('stat3_value', '99.9%'),
                'stat3_label' => Setting::get('stat3_label', 'Uptime'),
                'stat4_value' => Setting::get('stat4_value', '24/7'),
                'stat4_label' => Setting::get('stat4_label', 'Support'),

                'features_title'    => Setting::get('features_title', 'Why Choose APTrades'),
                'features_subtitle' => Setting::get('features_subtitle', 'A complete investment ecosystem built for performance, security, and growth.'),

                'section2_title'    => Setting::get('section2_title', 'Investment Packages for Every Goal'),
                'section2_subtitle' => Setting::get('section2_subtitle', 'From beginner to pro — diversified portfolios managed by experts.'),

                'cta_title'    => Setting::get('cta_title', 'Ready to Start Your Investment Journey?'),
                'cta_subtitle' => Setting::get('cta_subtitle', 'Join thousands of investors earning daily returns. Create your free account in under 2 minutes.'),
                'cta_button'   => Setting::get('cta_button', 'Create Free Account'),

                'footer_about'  => Setting::get('footer_about', 'APTrades is a next-generation investment platform offering secure, diversified portfolios across crypto, forex, stocks, and bonds.'),
                'footer_email'  => Setting::get('footer_email', 'support@aptrades.io'),
                'footer_phone'  => Setting::get('footer_phone', '+234 800 000 0000'),
                'footer_address'=> Setting::get('footer_address', 'Lagos, Nigeria'),

                'testimonial_title' => Setting::get('testimonial_title', 'What Our Investors Say'),
            ];

            // Investment packages for display
            $packages = DB::table('investment_packages')
                ->where('is_active', true)
                ->orderBy('min_amount')
                ->limit(6)
                ->get();

            // Recent transactions for social proof pop-ups
            $recentDeposits = DB::table('deposits')
                ->where('status', 'confirmed')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(function ($d) {
                    $user = DB::table('users')->where('id', $d->user_id)->first();
                    return [
                        'type'   => 'deposit',
                        'name'   => $this->maskName($user->name ?? 'Investor'),
                        'amount' => $d->amount,
                        'method' => $d->method ?? 'Crypto',
                        'time'   => $d->created_at,
                        'flag'   => $this->getFlag($user->country ?? 'Nigeria'),
                    ];
                });

            $recentWithdrawals = DB::table('withdrawals')
                ->where('status', 'completed')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(function ($w) {
                    $user = DB::table('users')->where('id', $w->user_id)->first();
                    return [
                        'type'   => 'withdrawal',
                        'name'   => $this->maskName($user->name ?? 'Investor'),
                        'amount' => $w->amount,
                        'method' => $w->method ?? 'Bank Transfer',
                        'time'   => $w->created_at,
                        'flag'   => $this->getFlag($user->country ?? 'Nigeria'),
                    ];
                });

            $recentActivity = $recentDeposits->merge($recentWithdrawals)->sortByDesc('time')->take(30)->values();

            // If no real data, generate demo data for live feel
            if ($recentActivity->isEmpty()) {
                $recentActivity = $this->generateDemoActivity();
            }

            // Platform stats (live)
            $platformStats = [
                'total_deposits'   => DB::table('deposits')->where('status', 'confirmed')->sum('amount') ?: 0,
                'total_withdrawals' => DB::table('withdrawals')->where('status', 'completed')->sum('amount') ?: 0,
                'total_users'      => DB::table('users')->where('is_admin', false)->count() ?: 0,
                'active_investments' => DB::table('investments')->where('status', 'active')->count() ?: 0,
            ];
        } catch (\Exception $e) {
            // Use defaults if DB tables don't exist yet
            $content = [
                'hero_title'         => 'Trade Smarter. Earn Bigger.',
                'hero_subtitle'      => 'The next-generation investment platform for crypto, forex, stocks, and bonds. Powered by AI-driven analytics and a secure multi-asset wallet system.',
                'hero_badge'         => 'Trusted by 50,000+ investors worldwide',
                'hero_cta_primary'   => 'Start Investing',
                'hero_cta_secondary'  => 'Explore Packages',
                'stat1_value' => '$250M+', 'stat1_label' => 'Total Volume',
                'stat2_value' => '50,000+', 'stat2_label' => 'Active Investors',
                'stat3_value' => '99.9%', 'stat3_label' => 'Uptime',
                'stat4_value' => '24/7', 'stat4_label' => 'Support',
                'features_title'    => 'Why Choose APTrades',
                'features_subtitle' => 'A complete investment ecosystem built for performance, security, and growth.',
                'section2_title'    => 'Investment Packages for Every Goal',
                'section2_subtitle' => 'From beginner to pro — diversified portfolios managed by experts.',
                'cta_title'    => 'Ready to Start Your Investment Journey?',
                'cta_subtitle' => 'Join thousands of investors earning daily returns. Create your free account in under 2 minutes.',
                'cta_button'   => 'Create Free Account',
                'footer_about'  => 'APTrades is a next-generation investment platform offering secure, diversified portfolios across crypto, forex, stocks, and bonds.',
                'footer_email'  => 'support@aptrades.io',
                'footer_phone'  => '+234 800 000 0000',
                'footer_address'=> 'Lagos, Nigeria',
                'testimonial_title' => 'What Our Investors Say',
            ];
            $packages = collect();
            $recentActivity = $this->generateDemoActivity();
            $platformStats = [
                'total_deposits' => 0, 'total_withdrawals' => 0,
                'total_users' => 0, 'active_investments' => 0,
            ];
        }

        // Live market data (static fallback for demo)
        $markets = $this->getMarketData();

        // Testimonials
        $testimonials = [
            ['name' => 'Chinedu O.', 'country' => '🇳🇬 Nigeria', 'text' => 'I started with $500 and now my portfolio is over $8,000. The profit sharing system is incredible.', 'rating' => 5, 'profit' => '+$7,500'],
            ['name' => 'Sarah M.', 'country' => '🇬🇧 UK', 'text' => 'Best investment platform I have used. Withdrawals are fast and the support team is always available.', 'rating' => 5, 'profit' => '+$12,300'],
            ['name' => 'Rajesh K.', 'country' => '🇮🇳 India', 'text' => 'The binary MLM system helped me build a passive income stream. I earn matching bonuses every week.', 'rating' => 5, 'profit' => '+$5,800'],
            ['name' => 'Amara E.', 'country' => '🇬🇭 Ghana', 'text' => 'I was skeptical at first, but after my first withdrawal hit my bank in 24 hours, I knew this was real.', 'rating' => 5, 'profit' => '+$3,200'],
            ['name' => 'Michael B.', 'country' => '🇺🇸 USA', 'text' => 'The crypto trading packages offer the best returns I have seen. Diversified and professionally managed.', 'rating' => 5, 'profit' => '+$25,000'],
            ['name' => 'Fatima A.', 'country' => '🇦🇪 UAE', 'text' => 'Excellent platform with transparent reporting. I can see every transaction and profit share cycle.', 'rating' => 5, 'profit' => '+$9,400'],
        ];

        return view('landing.index', compact(
            'content', 'packages', 'markets', 'recentActivity',
            'testimonials', 'platformStats'
        ));
    }

    /**
     * Get live-ish market data
     */
    private function getMarketData()
    {
        // Try to fetch from stored tickers, otherwise use demo data
        try {
            $tickers = DB::table('crypto_tickers')->orderByDesc('updated_at')->limit(10)->get();

            if ($tickers->isNotEmpty()) {
                return $tickers->map(fn ($t) => [
                    'symbol'    => $t->symbol,
                    'name'      => $t->name,
                    'price'     => $t->price,
                    'change'    => $t->change_24h,
                    'icon'      => $t->icon ?? 'fa-coins',
                    'color'     => $t->color ?? '#f7931a',
                ])->toArray();
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet — use demo data
        }

        // Demo market data
        return [
            ['symbol' => 'BTC',   'name' => 'Bitcoin',    'price' => 0,  'change' => 0, 'icon' => 'fab fa-bitcoin',   'color' => '#f7931a'],
            ['symbol' => 'ETH',   'name' => 'Ethereum',    'price' => 0,   'change' => 0, 'icon' => 'fab fa-ethereum',   'color' => '#627eea'],
            ['symbol' => 'BNB',   'name' => 'BNB',         'price' => 0,    'change' => 0,'icon' => 'fas fa-coins',      'color' => '#f3ba2f'],
            ['symbol' => 'SOL',   'name' => 'Solana',      'price' => 0,    'change' => 0, 'icon' => 'fas fa-bolt',       'color' => '#9945ff'],
            ['symbol' => 'XRP',   'name' => 'Ripple',      'price' => 0,    'change' => 0, 'icon' => 'fas fa-circle-notch','color' => '#23292f'],
            ['symbol' => 'ADA',   'name' => 'Cardano',     'price' => 0,    'change' => 0,'icon' => 'fas fa-circle',      'color' => '#0033ad'],
            ['symbol' => 'DOGE',  'name' => 'Dogecoin',    'price' => 0,    'change' => 0, 'icon' => 'fas fa-dog',        'color' => '#c2a633'],
            ['symbol' => 'AVAX',  'name' => 'Avalanche',   'price' => 0,     'change' => 0, 'icon' => 'fas fa-mountain',    'color' => '#e84142'],
            ['symbol' => 'DOT',   'name' => 'Polkadot',    'price' => 0,     'change' => 0,'icon' => 'fas fa-circle-dot',  'color' => '#e6007a'],
            ['symbol' => 'LINK',  'name' => 'Chainlink',   'price' => 0,     'change' => 0, 'icon' => 'fas fa-link',       'color' => '#2a5ada'],
        ];
    }

    /**
     * Mask user name for privacy: "Chinedu Okafor" → "C***f O***r"
     */
    private function maskName($name)
    {
        $parts = explode(' ', $name);
        $masked = [];
        foreach ($parts as $part) {
            if (strlen($part) <= 2) {
                $masked[] = $part[0] . '*';
            } else {
                $masked[] = $part[0] . str_repeat('*', strlen($part) - 2) . substr($part, -1);
            }
        }
        return implode(' ', $masked);
    }

    /**
     * Get flag emoji from country
     */
    private function getFlag($country)
    {
        $flags = [
            'Nigeria' => '🇳🇬', 'Ghana' => '🇬🇭', 'USA' => '🇺🇸', 'UK' => '🇬🇧',
            'India' => '🇮🇳', 'UAE' => '🇦🇪', 'South Africa' => '🇿🇦', 'Kenya' => '🇰🇪',
            'Canada' => '🇨🇦', 'Australia' => '🇦🇺', 'Germany' => '🇩🇪', 'France' => '🇫🇷',
        ];
        return $flags[$country] ?? '🌍';
    }

    /**
     * Generate demo activity for the live-feel pop-ups
     */
    private function generateDemoActivity()
    {
        $names = ['Chinedu O.', 'Sarah M.', 'Rajesh K.', 'Amara E.', 'Michael B.', 'Fatima A.', 'David L.', 'Aisha B.', 'James W.', 'Maria S.'];
        $countries = ['🇳🇬', '🇬🇧', '🇮🇳', '🇬🇭', '🇺🇸', '🇦🇪', '🇨🇦', '🇿🇦', '🇰🇪', '🇩🇪'];
        $methods = ['Crypto', 'Bank Transfer', 'USDT', 'Bitcoin'];
        $activity = [];

        for ($i = 0; $i < 15; $i++) {
            $activity[] = [
                'type'   => rand(0, 1) ? 'deposit' : 'withdrawal',
                'name'   => $names[array_rand($names)],
                'amount' => rand(100, 50000),
                'method' => $methods[array_rand($methods)],
                'time'   => now()->subMinutes(rand(1, 1440)),
                'flag'   => $countries[array_rand($countries)],
            ];
        }

        return collect($activity)->sortByDesc('time')->values();
    }

    /**
     * API: Market tickers for landing page AJAX
     */
    public function marketTickers()
    {
        return response()->json($this->getMarketData());
    }

    /**
     * API: Recent activity for landing page AJAX
     */
    public function recentActivity()
    {
        try {
            $recentDeposits = DB::table('deposits')
                ->where('status', 'confirmed')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(function ($d) {
                    $user = DB::table('users')->where('id', $d->user_id)->first();
                    return [
                        'type'   => 'deposit',
                        'name'   => $this->maskName($user->name ?? 'Investor'),
                        'amount' => $d->amount,
                        'method' => $d->method ?? 'Crypto',
                        'time'   => $d->created_at,
                        'flag'   => $this->getFlag($user->country ?? 'Nigeria'),
                    ];
                });

            $recentWithdrawals = DB::table('withdrawals')
                ->where('status', 'completed')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(function ($w) {
                    $user = DB::table('users')->where('id', $w->user_id)->first();
                    return [
                        'type'   => 'withdrawal',
                        'name'   => $this->maskName($user->name ?? 'Investor'),
                        'amount' => $w->amount,
                        'method' => $w->method ?? 'Bank Transfer',
                        'time'   => $w->created_at,
                        'flag'   => $this->getFlag($user->country ?? 'Nigeria'),
                    ];
                });

            $activity = $recentDeposits->merge($recentWithdrawals)->sortByDesc('time')->take(30)->values();

            if ($activity->isEmpty()) {
                $activity = $this->generateDemoActivity();
            }

            return response()->json($activity);
        } catch (\Exception $e) {
            return response()->json($this->generateDemoActivity());
        }
    }
}
