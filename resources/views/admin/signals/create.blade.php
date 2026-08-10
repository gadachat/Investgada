@extends('layouts.admin')

@section('page-title', 'Create Signal')

@section('content')
<div class="fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card-custom" style="padding: 32px;">
                <h4 style="font-weight: 700; margin-bottom: 20px;"><i class="fas fa-broadcast-tower" style="color: var(--purple-3);"></i> Publish Trading Signal</h4>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">All active users will receive an in-app notification and email.</p>

                <form method="POST" action="{{ route('admin.signals.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Symbol / Pair</label>
                            <input type="text" name="symbol" class="form-control" placeholder="BTC/USDT" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Direction</label>
                            <select name="direction" class="form-control" required>
                                <option value="buy">BUY (Long)</option>
                                <option value="sell">SELL (Short)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Entry Price</label>
                            <input type="number" name="entry_price" class="form-control" step="0.00000001" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stop Loss</label>
                            <input type="number" name="stop_loss" class="form-control" step="0.00000001" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Take Profit</label>
                            <input type="number" name="take_profit" class="form-control" step="0.00000001" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">TP2 (optional)</label>
                            <input type="number" name="take_profit_2" class="form-control" step="0.00000001" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control" required>
                                <option value="crypto">Crypto</option>
                                <option value="forex">Forex</option>
                                <option value="indices">Indices</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Timeframe</label>
                            <select name="timeframe" class="form-control">
                                <option value="15m">15m</option>
                                <option value="1h" selected>1h</option>
                                <option value="4h">4h</option>
                                <option value="1d">1D</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Confidence Level: <span id="confVal">0</span>%</label>
                            <input type="range" name="confidence" min="0" max="100" value="0" class="form-range" oninput="document.getElementById('confVal').innerText=this.value" />
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Analysis (optional)</label>
                            <textarea name="analysis" class="form-control" rows="4" placeholder="Technical analysis, why this trade makes sense..."></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-lg" style="background: linear-gradient(135deg, #6366f1, #7c3aed); border: none; color: white; font-weight: 600; flex: 1;">
                            <i class="fas fa-paper-plane"></i> Publish & Notify Users
                        </button>
                        <a href="{{ route('admin.signals.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
