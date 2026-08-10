<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\Deposit;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Wallet balances
        $wallets = $user->wallets()->orderBy('type')->get();
        $totalBalance = $wallets->sum('balance');

        // Active investments
        $activeInvestments = Investment::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('package')
            ->orderBy('activated_at', 'desc')
            ->get();

        $totalInvested = $activeInvestments->sum('amount');
        $totalExpectedReturn = $activeInvestments->sum('expected_return');
        $totalEarnedFromInvestments = $activeInvestments->sum('earned_so_far');

        // Recent transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Deposit stats
        $pendingDeposits = Deposit::where('user_id', $user->id)
            ->where('status', 'pending')->count();

        // Withdrawal stats
        $pendingWithdrawals = Withdrawal::where('user_id', $user->id)
            ->where('status', 'pending')->count();
        $totalWithdrawn = $user->total_withdrawn;

        // Referral stats
        $directReferrals = Referral::where('referrer_id', $user->id)->count();
        $referralEarnings = $user->total_referral_earnings;

        // Binary tree stats
        $binaryNode = $user->binaryNode;
        $leftCount = $binaryNode?->left_count ?? 0;
        $rightCount = $binaryNode?->right_count ?? 0;
        $leftVolume = $binaryNode?->left_volume ?? 0;
        $rightVolume = $binaryNode?->right_volume ?? 0;

        // Rank
        $rank = $user->rank;

        // Weekly chart data (earnings over last 7 days)
        $weeklyEarnings = Transaction::where('user_id', $user->id)
            ->where('direction', 'credit')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('D');
            $chartData[] = (float) ($weeklyEarnings->get($date)?->total ?? 0);
        }

        return view('dashboard.index', compact(
            'user', 'wallets', 'totalBalance',
            'activeInvestments', 'totalInvested', 'totalExpectedReturn', 'totalEarnedFromInvestments',
            'recentTransactions', 'pendingDeposits', 'pendingWithdrawals', 'totalWithdrawn',
            'directReferrals', 'referralEarnings',
            'leftCount', 'rightCount', 'leftVolume', 'rightVolume',
            'rank', 'chartLabels', 'chartData'
        ));
    }

    /**
     * Fetch live prices for crypto, forex, and indices.
     */
    public function livePrices(Request $request)
    {
        $category = $request->query('category', 'crypto');

        $prices = $this->getMockPrices($category);

        return response()->json([
            'success'   => true,
            'category'   => $category,
            'prices'     => $prices,
            'updated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get market overview for dashboard widget.
     */
    public function marketOverview()
    {
        $crypto = $this->getMockPrices('crypto');
        $forex = $this->getMockPrices('forex');
        $indices = $this->getMockPrices('indices');

        return response()->json([
            'crypto'   => array_slice($crypto, 0, 5),
            'forex'    => array_slice($forex, 0, 5),
            'indices'  => array_slice($indices, 0, 5),
        ]);
    }

    /**
     * Generate realistic mock price data with slight variation.
     * In production, replace with real API calls (Binance, Alpha Vantage, etc.)
     */
    private function getMockPrices(string $category): array
    {
        $now = now()->timestamp;

        $datasets = [
            'crypto' => [
                ['symbol' => 'BTC', 'name' => 'Bitcoin',    'base' => 0,   'icon' => 'bitcoin',   'color' => '#f7931a'],
                ['symbol' => 'ETH', 'name' => 'Ethereum',   'base' => 0,    'icon' => 'ethereum',  'color' => '#627eea'],
                ['symbol' => 'BNB',  'name' => 'BNB',        'base' => 0,     'icon' => 'bnb',       'color' => '#f3ba2f'],
                ['symbol' => 'SOL',  'name' => 'Solana',     'base' => 0,     'icon' => 'solana',    'color' => '#14f195'],
                ['symbol' => 'XRP',  'name' => 'Ripple',     'base' => 0,    'icon' => 'ripple',    'color' => '#23292f'],
                ['symbol' => 'ADA',  'name' => 'Cardano',    'base' => 0,    'icon' => 'cardano',   'color' => '#0033ad'],
                ['symbol' => 'DOT',  'name' => 'Polkadot',   'base' => 0,    'icon' => 'polkadot',  'color' => '#e6007a'],
                ['symbol' => 'DOGE', 'name' => 'Dogecoin',   'base' => 0,   'icon' => 'doge',      'color' => '#c2a633'],
                ['symbol' => 'AVAX', 'name' => 'Avalanche',  'base' => 0,  'icon' => 'avax',      'color' => '#e84142'],
                ['symbol' => 'MATIC','name' => 'Polygon',   'base' => 0,   'icon' => 'matic',     'color' => '#8247e5'],
                ['symbol' => 'LINK', 'name' => 'Chainlink',  'base' => 0,  'icon' => 'link',      'color' => '#2a5ada'],
                ['symbol' => 'LTC',  'name' => 'Litecoin',   'base' => 0,  'icon' => 'litecoin',  'color' => '#345d9d'],
            ],
            'forex' => [
                ['symbol' => 'EUR/USD', 'name' => 'Euro / US Dollar',       'base' => 0,  'icon' => 'eurusd', 'color' => '#0052b4'],
                ['symbol' => 'GBP/USD', 'name' => 'British Pound / US Dollar', 'base' => 0,  'icon' => 'gbpusd', 'color' => '#c8102e'],
                ['symbol' => 'USD/JPY', 'name' => 'US Dollar / Japanese Yen',  'base' => 0,  'icon' => 'usdjpy', 'color' => '#bc002d'],
                ['symbol' => 'USD/CHF', 'name' => 'US Dollar / Swiss Franc',  'base' => 0,  'icon' => 'usdchf', 'color' => '#d52b1e'],
                ['symbol' => 'AUD/USD', 'name' => 'Australian / US Dollar',  'base' => 0,  'icon' => 'audusd', 'color' => '#012169'],
                ['symbol' => 'USD/CAD', 'name' => 'US Dollar / Canadian',    'base' => 0,  'icon' => 'usdcad', 'color' => '#d80621'],
                ['symbol' => 'NZD/USD', 'name' => 'NZ Dollar / US Dollar',   'base' => 0,  'icon' => 'nzdusd', 'color' => '#00247d'],
                ['symbol' => 'EUR/GBP', 'name' => 'Euro / British Pound',    'base' => 0,  'icon' => 'eurgbp', 'color' => '#003399'],
            ],
            'indices' => [
                ['symbol' => 'SPX',   'name' => 'S&P 500',         'base' => 0,   'icon' => 'sp500',  'color' => '#1a2b49'],
                ['symbol' => 'NDX',   'name' => 'Nasdaq 100',     'base' => 0,  'icon' => 'nasdaq', 'color' => '#00d4aa'],
                ['symbol' => 'DJI',   'name' => 'Dow Jones',      'base' => 0,  'icon' => 'dow',    'color' => '#1a3a5c'],
                ['symbol' => 'DAX',  'name' => 'German DAX',      'base' => 0,  'icon' => 'dax',    'color' => '#1e1e1e'],
                ['symbol' => 'FTSE', 'name' => 'FTSE 100',         'base' => 0,   'icon' => 'ftse',   'color' => '#c8102e'],
                ['symbol' => 'NIKKEI','name' => 'Nikkei 225',      'base' => 0,  'icon' => 'nikkei', 'color' => '#bc002d'],
                ['symbol' => 'HSI',  'name' => 'Hang Seng',         'base' => 0,  'icon' => 'hsi',    'color' => '#e60012'],
                ['symbol' => 'VIX',  'name' => 'Volatility Index',  'base' => 0,  'icon' => 'vix',    'color' => '#8b0000'],
            ],
        ];

        $data = $datasets[$category] ?? $datasets['crypto'];

        return collect($data)->map(function ($item) use ($now) {
            // Simulate realistic price fluctuation
            $volatility = $item['base'] > 1000 ? 0.015 : 0.03; // 1.5% or 3% variance
            $change = (mt_rand(-1000, 1000) / 1000) * $volatility * $item['base'];
            $price = round($item['base'] + $change, $item['base'] < 1 ? 4 : 2);
            $changePercent = round(($change / $item['base']) * 100, 2);

            return [
                'symbol'    => $item['symbol'],
                'name'      => $item['name'],
                'price'     => $price,
                'change'    => round($change, $item['base'] < 1 ? 4 : 2),
                'change_pct'=> $changePercent,
                'trend'     => $changePercent >= 0 ? 'up' : 'down',
                'color'     => $item['color'],
                'icon'      => $item['icon'],
            ];
        })->values()->toArray();
    }
}
