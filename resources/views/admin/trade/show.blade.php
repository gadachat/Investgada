@extends('layouts.admin')

@section('title', 'Trade Details')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="mb-4">
            <a href="{{ route('admin.trading.index') }}" style="color:var(--text-muted);font-size:13px;text-decoration:none">
                <i class="fas fa-arrow-left me-1"></i> Back to Trading
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-lg-8 col-md-10 col-12">
                <div class="card-custom mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-1" style="font-weight:700;color:var(--text)">{{ $position->reference }}</h4>
                            <p style="color:var(--text-muted);font-size:13px">
                                <i class="fas fa-user me-1"></i> {{ $position->user->name }} ({{ $position->user->email }})
                                · {{ $position->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                        @php
                            $sc = [
                                'open'       => ['bg' => 'rgba(59,130,246,0.15)', 'color' => '#3b82f6', 'label' => 'OPEN'],
                                'closed'     => ['bg' => 'rgba(99,102,241,0.15)', 'color' => '#6366f1', 'label' => 'CLOSED'],
                                'tp_hit'     => ['bg' => 'rgba(16,185,129,0.15)', 'color' => '#10b981', 'label' => 'TP HIT'],
                                'sl_hit'     => ['bg' => 'rgba(239,68,68,0.15)', 'color' => '#ef4444', 'label' => 'SL HIT'],
                                'liquidated' => ['bg' => 'rgba(239,68,68,0.2)', 'color' => '#ef4444', 'label' => 'LIQUIDATED'],
                            ][$position->status] ?? ['bg' => 'rgba(99,102,241,0.15)', 'color' => '#6366f1', 'label' => 'CLOSED'];
                        @endphp
                        <span class="badge px-3 py-2" style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};font-size:13px;font-weight:700">
                            {{ $sc['label'] }}
                        </span>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-3 col-6">
                            <div class="rounded-3 p-2" style="background:var(--bg);border:1px solid var(--border)">
                                <p style="font-size:11px;color:var(--text-muted);margin:0">Symbol</p>
                                <h6 style="font-weight:700;margin:0;color:var(--text)">{{ $position->symbol }}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="rounded-3 p-2" style="background:var(--bg);border:1px solid var(--border)">
                                <p style="font-size:11px;color:var(--text-muted);margin:0">Direction</p>
                                <h6 style="font-weight:700;margin:0;color:{{ $position->direction === 'buy' ? '#10b981' : '#ef4444' }}">
                                    {{ $position->direction === 'buy' ? 'LONG' : 'SHORT' }}
                                </h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="rounded-3 p-2" style="background:var(--bg);border:1px solid var(--border)">
                                <p style="font-size:11px;color:var(--text-muted);margin:0">Leverage</p>
                                <h6 style="font-weight:700;margin:0;color:var(--text)">{{ $position->leverage }}x</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="rounded-3 p-2" style="background:var(--bg);border:1px solid var(--border)">
                                <p style="font-size:11px;color:var(--text-muted);margin:0">Market Type</p>
                                <h6 style="font-weight:700;margin:0;color:var(--text)">{{ ucfirst($position->market_type) }}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="rounded-3 p-2" style="background:var(--bg);border:1px solid var(--border)">
                                <p style="font-size:11px;color:var(--text-muted);margin:0">Entry Price</p>
                                <h6 style="font-weight:700;margin:0;color:var(--text)">{{ number_format((float)$position->entry_price, (float)$position->entry_price < 1 ? 4 : 2) }}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="rounded-3 p-2" style="background:var(--bg);border:1px solid var(--border)">
                                <p style="font-size:11px;color:var(--text-muted);margin:0">Close Price</p>
                                <h6 style="font-weight:700;margin:0;color:var(--text)">{{ $position->close_price ? number_format((float)$position->close_price, (float)$position->close_price < 1 ? 4 : 2) : '—' }}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="rounded-3 p-2" style="background:var(--bg);border:1px solid var(--border)">
                                <p style="font-size:11px;color:var(--text-muted);margin:0">Margin</p>
                                <h6 style="font-weight:700;margin:0;color:var(--text)">${{ number_format((float)$position->amount, 2) }}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="rounded-3 p-2" style="background:var(--bg);border:1px solid var(--border)">
                                <p style="font-size:11px;color:var(--text-muted);margin:0">Contract Value</p>
                                <h6 style="font-weight:700;margin:0;color:var(--text)">${{ number_format((float)$position->contract_value, 2) }}</h6>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mt-2">
                        <div class="col-md-4 col-6">
                            <div class="rounded-3 p-2" style="background:rgba(16,185,129,0.05);border:1px solid rgba(16,185,129,0.15)">
                                <p style="font-size:11px;color:#10b981;margin:0">Take Profit</p>
                                <h6 style="font-weight:700;margin:0;color:#10b981">{{ $position->take_profit ? number_format((float)$position->take_profit, 4) : '—' }}</h6>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="rounded-3 p-2" style="background:rgba(239,68,68,0.05);border:1px solid rgba(239,68,68,0.15)">
                                <p style="font-size:11px;color:#ef4444;margin:0">Stop Loss</p>
                                <h6 style="font-weight:700;margin:0;color:#ef4444">{{ $position->stop_loss ? number_format((float)$position->stop_loss, 4) : '—' }}</h6>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="rounded-3 p-2" style="background:rgba(99,102,241,0.05);border:1px solid rgba(99,102,241,0.15)">
                                <p style="font-size:11px;color:var(--primary);margin:0">Fees</p>
                                <h6 style="font-weight:700;margin:0;color:var(--primary)">${{ number_format((float)$position->fees, 2) }}</h6>
                            </div>
                        </div>
                    </div>

                    @if($position->status !== 'open')
                    <div class="rounded-3 p-3 mt-2" style="background:rgba(99,102,241,0.05);border:1px solid rgba(99,102,241,0.15)">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p style="font-size:12px;color:var(--text-muted);margin:0">Close P&L</p>
                                <h4 style="font-weight:800;margin:0;color:{{ (float)$position->close_pnl >= 0 ? '#10b981' : '#ef4444' }}">
                                    {{ (float)$position->close_pnl >= 0 ? '+' : '' }}${{ number_format(abs((float)$position->close_pnl), 2) }}
                                </h4>
                            </div>
                            <div class="text-end">
                                <p style="font-size:12px;color:var(--text-muted);margin:0">Closed At</p>
                                <p style="font-size:13px;color:var(--text);margin:0">{{ $position->closed_at?->format('M d, Y H:i') }}</p>
                                <p style="font-size:11px;color:var(--text-muted);margin:0">Reason: {{ $position->close_reason }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4 col-md-6 col-12">
                @if($position->status === 'open')
                <div class="card-custom" style="border:1px solid rgba(239,68,68,0.2)">
                    <h6 class="mb-3" style="font-weight:600;color:#ef4444"><i class="fas fa-exclamation-triangle me-1"></i> Force Close</h6>
                    <form method="POST" action="{{ route('admin.trading.force-close', $position) }}">
                        @csrf
                        <div class="mb-2">
                            <label style="font-size:12px;color:var(--text-muted)">Reason</label>
                            <textarea name="reason" rows="3" class="form-control" required style="background:var(--bg);border:1px solid var(--border);color:var(--text)" placeholder="Reason for force close..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Force close this position?')">
                            <i class="fas fa-times me-1"></i> Force Close Position
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
