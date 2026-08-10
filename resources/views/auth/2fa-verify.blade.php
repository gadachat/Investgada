@extends('layouts.auth')

@section('page-title', 'Two-Factor Verification')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="max-width: 420px;">
        <div class="text-center mb-4">
            <div style="width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #6366f1, #7c3aed); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <i class="fas fa-shield-alt" style="color: white; font-size: 28px;"></i>
            </div>
            <h3 style="font-weight: 700; margin-bottom: 8px;">Two-Factor Authentication</h3>
            <p style="color: var(--text-muted); font-size: 14px;">Enter the 6-digit code from your authenticator app.</p>
        </div>

        @if(session('error'))
        <div class="alert alert-danger alert-custom" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
        @endif
        @if(session('info'))
        <div class="alert alert-info alert-custom" role="alert">
            <i class="fas fa-info-circle"></i> {{ session('info') }}
        </div>
        @endif

        <form method="POST" action="{{ route('2fa.verify.post') }}">
            @csrf
            <div class="form-group mb-3">
                <label class="form-label">Authentication Code</label>
                <input type="text" name="code" class="form-control form-control-lg text-center"
                       maxlength="6" pattern="[0-9]{6}" required autofocus
                       placeholder="000000" style="font-size: 28px; letter-spacing: 8px; font-weight: 700;" />
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg w-100 mb-3"
                    style="background: linear-gradient(135deg, #6366f1, #7c3aed); border: none; color: white; font-weight: 600;">
                <i class="fas fa-check-circle"></i> Verify & Login
            </button>
        </form>

        <hr style="border-color: var(--border); margin: 20px 0;" />

        <form method="POST" action="{{ route('2fa.verify.recovery') }}">
            @csrf
            <div class="text-center mb-2">
                <p style="font-size: 13px; color: var(--text-muted);">Lost your device? Use a recovery code:</p>
            </div>
            <div class="form-group mb-2">
                <input type="text" name="recovery_code" class="form-control text-center"
                       placeholder="XXXX-XXXX" style="letter-spacing: 2px; font-family: monospace;" />
            </div>
            <button type="submit" class="btn btn-outline-secondary w-100" style="border-color: var(--border);">
                <i class="fas fa-key"></i> Use Recovery Code
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" style="font-size: 13px; color: var(--text-muted);">
                <i class="fas fa-arrow-left"></i> Back to login
            </a>
        </div>
    </div>
</div>
@endsection
