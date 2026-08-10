@extends('layouts.dashboard')

@section('page-title', 'Recovery Codes — Security')

@section('content')
<div class="fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card-custom" style="padding: 32px;">
                <div class="text-center mb-4">
                    <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #f59e0b, #ef4444); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                        <i class="fas fa-key" style="color: white; font-size: 24px;"></i>
                    </div>
                    <h4 style="font-weight: 700; margin-bottom: 6px;">Save Your Recovery Codes</h4>
                    <p style="color: var(--text-muted); font-size: 14px;">Each code can be used once. Store them somewhere safe.</p>
                </div>

                @if(session('success'))
                <div class="alert alert-success" role="alert" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
                @endif

                <div class="alert alert-warning" style="background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); color: #f59e0b; font-size: 13px;">
                    <i class="fas fa-exclamation-triangle"></i> These codes will only be shown once. Write them down or print them now.
                </div>

                <div style="background: var(--bg-input); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <div class="row">
                        @foreach($recoveryCodes as $i => $code)
                        <div class="col-6 mb-2">
                            <div style="font-family: monospace; font-size: 16px; font-weight: 600; color: var(--text-bright); padding: 8px 12px; background: var(--bg-card); border-radius: 8px; border: 1px solid var(--border); text-align: center;">
                                {{ $code }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('dashboard.2fa.manage') }}" class="btn btn-success w-100" style="background: linear-gradient(135deg, #10b981, #059669); border: none; color: white; font-weight: 600;">
                        <i class="fas fa-check"></i> I've Saved Them
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-secondary" style="border-color: var(--border);">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
