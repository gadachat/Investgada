<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TradingSignal;
use Illuminate\Http\Request;

class SignalController extends Controller
{
    public function index()
    {
        $activeSignals = TradingSignal::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $closedSignals = TradingSignal::where('status', 'closed')
            ->orderBy('closed_at', 'desc')
            ->limit(10)
            ->get();

        $winRate = 0;
        $totalClosed = TradingSignal::where('status', 'closed')->count();
        $totalWins = TradingSignal::where('status', 'closed')->where('result', 'win')->count();
        if ($totalClosed > 0) {
            $winRate = round(($totalWins / $totalClosed) * 100, 1);
        }

        return view('dashboard.signals.index', compact('activeSignals', 'closedSignals', 'winRate', 'totalClosed', 'totalWins'));
    }
}
