<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CryptoChartController extends Controller
{
    /**
     * Generate realistic OHLC candlestick data for a given crypto symbol.
     * In production, replace with Binance/CoinGecko API calls.
     */
    public function feed(Request $request)
    {
        $symbol = $request->query('symbol', 'BTC');
        $points = min((int) $request->query('points', 60), 200);

        $symbols = [
            'BTC'  => ['name' => 'Bitcoin',    'base' => 0,   'vol' => 0.012, 'color' => '#f7931a'],
            'ETH'  => ['name' => 'Ethereum',   'base' => 0,    'vol' => 0.018, 'color' => '#627eea'],
            'BNB'  => ['name' => 'BNB',         'base' => 0,     'vol' => 0.015, 'color' => '#f3ba2f'],
            'SOL'  => ['name' => 'Solana',     'base' => 0,     'vol' => 0.025, 'color' => '#14f195'],
            'XRP'  => ['name' => 'Ripple',     'base' => 0,    'vol' => 0.020, 'color' => '#23292f'],
            'ADA'  => ['name' => 'Cardano',    'base' => 0,    'vol' => 0.022, 'color' => '#0033ad'],
            'AVAX' => ['name' => 'Avalanche',  'base' => 0,   'vol' => 0.028, 'color' => '#e84142'],
            'DOT'  => ['name' => 'Polkadot',   'base' => 0,    'vol' => 0.024, 'color' => '#e6007a'],
            'LINK' => ['name' => 'Chainlink',  'base' => 0,   'vol' => 0.026, 'color' => '#2a5ada'],
            'DOGE' => ['name' => 'Dogecoin',   'base' => 0,    'vol' => 0.030, 'color' => '#c2a633'],
        ];

        $meta = $symbols[$symbol] ?? $symbols['BTC'];
        $base = $meta['base'];
        $volatility = $meta['vol'];
        $precision = $base < 1 ? 4 : 2;

        // Generate OHLC candle data — each candle = 1 "interval"
        $candles = [];
        $currentPrice = $base;

        $now = now();
        for ($i = $points; $i > 0; $i--) {
            $timestamp = $now->copy()->subMinutes($i * 5)->timestamp * 1000;

            // Random walk with mean reversion to base
            $drift = ($base - $currentPrice) * 0.05;
            $shock = (mt_rand(-1000, 1000) / 1000) * $volatility * $base;
            $change = $drift + $shock;

            $open = $currentPrice;
            $close = round($open + $change, $precision);
            $high = round(max($open, $close) + abs($shock) * 0.6, $precision);
            $low = round(min($open, $close) - abs($shock) * 0.6, $precision);

            $candles[] = [
                'x' => $timestamp,
                'y' => [$open, $high, $low, $close],
            ];

            $currentPrice = $close;
        }

        // Calculate summary stats
        $firstPrice = $candles[0]['y'][0];
        $lastPrice = $candles[count($candles) - 1]['y'][3];
        $totalChange = round($lastPrice - $firstPrice, $precision);
        $totalChangePct = round(($totalChange / $firstPrice) * 100, 2);

        $high24 = max(array_map(fn($c) => $c['y'][1], $candles));
        $low24 = min(array_map(fn($c) => $c['y'][2], $candles));
        $volume = round(mt_rand(500, 5000) / 10, 1); // mock volume in millions

        // Generate a mini sparkline series for the coin selector buttons
        $sparkline = array_map(fn($c) => $c['y'][3], $candles);

        return response()->json([
            'success'  => true,
            'symbol'   => $symbol,
            'name'     => $meta['name'],
            'color'    => $meta['color'],
            'candles'  => $candles,
            'current'  => $lastPrice,
            'change'   => $totalChange,
            'change_pct' => $totalChangePct,
            'high'     => $high24,
            'low'      => $low24,
            'volume'   => $volume,
            'precision'=> $precision,
            'sparkline'=> array_slice($sparkline, -20),
        ]);
    }

    /**
     * Get the latest tick (for live price updates without full candle refresh).
     */
    public function tick(Request $request)
    {
        $symbol = $request->query('symbol', 'BTC');

        $symbols = [
            'BTC' => ['base' => 0, 'vol' => 0.012],
            'ETH' => ['base' => 0,  'vol' => 0.018],
            'BNB' => ['base' => 0,   'vol' => 0.015],
            'SOL' => ['base' => 0,   'vol' => 0.025],
            'XRP' => ['base' => 0,  'vol' => 0.020],
            'ADA' => ['base' => 0,  'vol' => 0.022],
            'AVAX'=> ['base' => 0, 'vol' => 0.028],
            'DOT' => ['base' => 0,  'vol' => 0.024],
            'LINK'=> ['base' => 0, 'vol' => 0.026],
            'DOGE'=> ['base' => 0,  'vol' => 0.030],
        ];

        $meta = $symbols[$symbol] ?? $symbols['BTC'];
        $base = $meta['base'];
        $volatility = $meta['vol'];
        $precision = $base < 1 ? 4 : 2;

        // Simulate price tick
        $change = (mt_rand(-1000, 1000) / 1000) * $volatility * $base;
        $price = round($base + $change, $precision);
        $changePct = round(($change / $base) * 100, 2);

        return response()->json([
            'symbol' => $symbol,
            'price'  => $price,
            'change' => round($change, $precision),
            'change_pct' => $changePct,
            'trend'  => $changePct >= 0 ? 'up' : 'down',
            'time'   => now()->timestamp * 1000,
        ]);
    }
}
