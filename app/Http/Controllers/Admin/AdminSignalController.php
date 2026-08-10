<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TradingSignal;
use App\Services\NotifyService;
use App\Models\User;
use Illuminate\Http\Request;

class AdminSignalController extends Controller
{
    public function index()
    {
        $signals = TradingSignal::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $activeCount = TradingSignal::where('status', 'active')->count();
        $totalSignals = TradingSignal::count();
        $winCount = TradingSignal::where('result', 'win')->count();
        $lossCount = TradingSignal::where('result', 'loss')->count();

        return view('admin.signals.index', compact('signals', 'activeCount', 'totalSignals', 'winCount', 'lossCount'));
    }

    public function create()
    {
        return view('admin.signals.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'symbol'       => 'required|string|max:30',
            'direction'    => 'required|in:buy,sell',
            'entry_price'  => 'required|numeric|min:0',
            'stop_loss'    => 'required|numeric|min:0',
            'take_profit'  => 'required|numeric|min:0',
            'take_profit_2'=> 'nullable|numeric|min:0',
            'category'     => 'required|in:crypto,forex,indices',
            'timeframe'    => 'nullable|string|max:10',
            'confidence'   => 'nullable|integer|min:0|max:100',
            'analysis'     => 'nullable|string|max:2000',
        ]);

        $signal = TradingSignal::create([
            'symbol'        => $request->symbol,
            'direction'     => $request->direction,
            'entry_price'   => $request->entry_price,
            'stop_loss'      => $request->stop_loss,
            'take_profit'    => $request->take_profit,
            'take_profit_2'  => $request->take_profit_2,
            'category'      => $request->category,
            'timeframe'      => $request->timeframe ?? '1h',
            'confidence'     => $request->confidence ?? 0,
            'analysis'       => $request->analysis,
            'status'         => 'active',
            'created_by'     => auth()->id(),
        ]);

        // Notify all active users
        $users = User::where('status', 'active')->where('is_admin', false)->get();
        foreach ($users as $user) {
            NotifyService::signalReceived(
                $user,
                $signal->symbol,
                $signal->direction,
                $signal->entry_price,
                $signal->stop_loss,
                $signal->take_profit
            );
        }

        return redirect()->route('admin.signals.index')
            ->with('success', 'Signal published and ' . $users->count() . ' users notified.');
    }

    public function close(Request $request, TradingSignal $signal)
    {
        $request->validate([
            'result'      => 'required|in:win,loss,breakeven',
            'close_price' => 'nullable|numeric|min:0',
        ]);

        $signal->update([
            'status'      => 'closed',
            'result'      => $request->result,
            'close_price' => $request->close_price,
            'closed_at'   => now(),
        ]);

        return redirect()->back()->with('success', 'Signal closed as ' . $request->result . '.');
    }

    public function destroy(TradingSignal $signal)
    {
        $signal->delete();
        return redirect()->back()->with('success', 'Signal deleted.');
    }
}
