@extends('layouts.dashboard')

@section('page-title', 'Enable 2FA — Security')

@section('content')
<div class="fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card-custom" style="padding: 32px;">
                <div class="text-center mb-4">
                    <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #6366f1, #7c3aed); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                        <i class="fas fa-shield-alt" style="color: white; font-size: 24px;"></i>
                    </div>
                    <h4 style="font-weight: 700; margin-bottom: 6px;">Set Up Two-Factor Authentication</h4>
                    <p style="color: var(--text-muted); font-size: 14px;">Scan the QR code with Google Authenticator, Authy, or any TOTP app.</p>
                </div>

                @if(session('error'))
                <div class="alert alert-danger" role="alert" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444;">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
                @endif

                <!-- Step 1: QR Code -->
                <div class="text-center mb-4">
                    <div style="display: inline-block; padding: 16px; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                        {!! $qrTag !!}
                    </div>
                </div>

                <!-- Secret (manual entry fallback) -->
                <div class="mb-4" style="background: var(--bg-input); border-radius: 12px; padding: 16px;">
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 6px;">Can't scan? Enter this key manually:</div>
                    <div style="font-family: monospace; font-size: 14px; word-break: break-all; color: var(--text-bright); background: var(--bg-card); padding: 10px; border-radius: 8px; border: 1px solid var(--border);">
                        {{ $secret }}
                    </div>
                </div>

                <!-- Step 2: Verify -->
                <div style="border-top: 1px solid var(--border); padding-top: 24px;">
                    <h6 style="font-weight: 600; margin-bottom: 12px;">
                        <span style="display: inline-flex; width: 24px; height: 24px; border-radius: 50%; background: var(--gradient-primary); color: white; align-items: center; justify-content: center; font-size: 12px;">2</span>
                        Enter the 6-digit code to confirm
                    </h6>

                    <form method="POST" action="{{ route('dashboard.2fa.enable') }}">
                        @csrf
                        <div class="form-group mb-3">
                            <input type="text" name="code" class="form-control form-control-lg text-center"
                                   maxlength="6" pattern="[0-9]{6}" required autofocus
                                   placeholder="000000" style="font-size: 24px; letter-spacing: 6px; font-weight: 700;" />
                        </div>

                        <button type="submit" class="btn w-100 btn-lg" style="background: linear-gradient(135deg, #6366f1, #7c3aed); border: none; color: white; font-weight: 600;">
                            <i class="fas fa-check-circle"></i> Enable 2FA
                        </button>
                    </form>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('dashboard.index') }}" style="font-size: 13px; color: var(--text-muted);">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
