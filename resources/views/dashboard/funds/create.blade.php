@extends('layouts.dashboard')

@section('title', 'Apply for Funds')

@section('content')
<div class="page-content" style="max-width:100%;margin:0 auto">
    <div class="mb-4">
        <a href="{{ route('dashboard.funds.index') }}" style="color:var(--text-muted);font-size:13px;text-decoration:none">
            <i class="fas fa-arrow-left me-1"></i> Back to Funds
        </a>
        <h2 class="mt-2 mb-1" style="font-weight:700;color:var(--text)">
            <i class="fas fa-hand-holding-usd me-2" style="color:var(--primary)"></i> Apply for Trading Capital
        </h2>
        <p style="color:var(--text-muted);font-size:14px">Submit your application for admin review</p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-8 col-md-10 col-12">
            <div class="card-custom">
                <form method="POST" action="{{ route('dashboard.funds.store') }}">
                    @csrf

                    <div class="form-group mb-3">
                        <label style="font-size:13px;font-weight:600;color:var(--text-muted)">Applicant Type</label>
                        <div class="d-flex gap-3 mt-2">
                            <div class="form-check">
                                <input type="radio" name="applicant_type" id="marketer" value="marketer" checked class="form-check-input">
                                <label for="marketer" style="font-size:14px;color:var(--text);cursor:pointer">
                                    <i class="fas fa-bullhorn me-1" style="color:#6366f1"></i> Marketer
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="applicant_type" id="leader" value="leader" class="form-check-input">
                                <label for="leader" style="font-size:14px;color:var(--text);cursor:pointer">
                                    <i class="fas fa-crown me-1" style="color:#a855f7"></i> Leader
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-size:13px;font-weight:600;color:var(--text-muted)">Requested Amount (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:var(--bg);border:1px solid var(--border);color:var(--text-muted)">$</span>
                            <input type="number" name="amount" class="form-control" min="{{ $minAmount }}" max="{{ $maxAmount }}"
                                   step="0.01" value="{{ old('amount') }}" placeholder="Enter amount" required
                                   style="background:var(--bg);border:1px solid var(--border);color:var(--text)">
                        </div>
                        <small style="color:var(--text-muted);font-size:12px">Min: ${{ number_format($minAmount, 2) }} · Max: ${{ number_format($maxAmount, 2) }}</small>
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-size:13px;font-weight:600;color:var(--text-muted)">Purpose / Strategy (optional)</label>
                        <textarea name="purpose" class="form-control" rows="4" placeholder="Describe your trading/marketing strategy..."
                                  style="background:var(--bg);border:1px solid var(--border);color:var(--text)">{{ old('purpose') }}</textarea>
                    </div>

                    {{-- Info Box --}}
                    <div class="rounded-3 p-3 mb-3" style="background:rgba(99,102,241,0.05);border:1px solid rgba(99,102,241,0.15)">
                        <h6 style="font-weight:600;color:var(--primary);font-size:14px" class="mb-2">
                            <i class="fas fa-info-circle me-1"></i> How It Works
                        </h6>
                        <ul style="font-size:13px;color:var(--text-muted);padding-left:18px;margin:0">
                            <li class="mb-1">Admin reviews and approves your application with a funded amount</li>
                            <li class="mb-1">Your team must produce <strong style="color:var(--primary)">{{ $targetPercent }}% of the funded capital</strong> in total volume</li>
                            <li class="mb-1"><span style="color:#10b981"><i class="fas fa-check"></i> Commission withdrawals</span> — available immediately</li>
                            <li class="mb-1"><span style="color:#f59e0b"><i class="fas fa-lock"></i> Profit & capital withdrawals</span> — locked until team reaches target</li>
                            <li>Admin can change these rules at any time</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" style="padding:12px;font-weight:600">
                        <i class="fas fa-paper-plane me-1"></i> Submit Application
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
