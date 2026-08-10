@extends('layouts.dashboard')

@section('page-title', 'Two-Factor Authentication — Security')

@section('content')
<div class="fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card-custom" style="padding: 32px;">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #6366f1, #7c3aed); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-shield-alt" style="color: white; font-size: 22px;"></i>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; margin: 0;">Two-Factor Authentication</h4>
                        <p style="color: var(--text-muted); font-size: 14px; margin: 0;">Manage your 2FA settings</p>
                    </div>
                </div>

                @if(session('success'))
                <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444;">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
                @endif

                @if($user->two_factor_enabled)
                <!-- 2FA is ENABLED -->
                <div style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-check-circle" style="color: #10b981;"></i>
                        <span style="font-weight: 600; color: #10b981;">2FA is Active</span>
                    </div>
                    <p style="font-size: 13px; color: var(--text-muted); margin: 0;">
                        Enabled on {{ $user->two_factor_verified_at?->format('M d, Y') }}. Your account is protected with an authenticator app.
                    </p>
                </div>

                <!-- Regenerate Recovery Codes -->
                <div style="background: var(--bg-input); border-radius: 12px; padding: 20px; margin-bottom: 16px;">
                    <h6 style="font-weight: 600; margin-bottom: 8px;">
                        <i class="fas fa-key" style="color: var(--purple-3);"></i> Regenerate Recovery Codes
                    </h6>
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">Generate a new set of one-time recovery codes. Previous codes will be invalidated.</p>
                    <form method="POST" action="{{ route('dashboard.2fa.regenerate') }}" style="display: flex; gap: 8px;">
                        @csrf
                        <input type="text" name="code" class="form-control" maxlength="6" placeholder="Enter 2FA code" required style="flex: 1;" />
                        <button type="submit" class="btn btn-outline-primary" style="border-color: var(--purple-3); color: var(--purple-3);">
                            <i class="fas fa-sync"></i> Regenerate
                        </button>
                    </form>
                </div>

                <!-- Disable 2FA -->
                <div style="background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.2); border-radius: 12px; padding: 20px;">
                    <h6 style="font-weight: 600; margin-bottom: 8px; color: #ef4444;">
                        <i class="fas fa-times-circle"></i> Disable 2FA
                    </h6>
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">Disabling 2FA makes your account less secure. This action cannot be undone.</p>
                    <form method="POST" action="{{ route('dashboard.2fa.disable') }}">
                        @csrf
                        <div class="form-group mb-2">
                            <input type="password" name="password" class="form-control mb-2" placeholder="Your password" required />
                        </div>
                        <div class="form-group mb-2">
                            <input type="text" name="code" class="form-control" maxlength="6" placeholder="2FA code" required />
                        </div>
                        <button type="submit" class="btn btn-danger w-100" style="background: #ef4444; border: none; color: white;">
                            <i class="fas fa-shield-alt"></i> Disable 2FA
                        </button>
                    </form>
                </div>

                @else
                <!-- 2FA is NOT enabled -->
                <div style="background: rgba(245,158,11,0.05); border: 1px solid rgba(245,158,11,0.2); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i>
                        <span style="font-weight: 600; color: #f59e0b;">2FA is Not Enabled</span>
                    </div>
                    <p style="font-size: 13px; color: var(--text-muted); margin: 0;">
                        Protect your account with an authenticator app. It adds an extra layer of security.
                    </p>
                </div>

                <a href="{{ route('dashboard.2fa.setup') }}" class="btn btn-lg w-100" style="background: linear-gradient(135deg, #6366f1, #7c3aed); border: none; color: white; font-weight: 600; text-decoration: none;">
                    <i class="fas fa-shield-alt"></i> Enable 2FA Now
                </a>
                @endif

                <div class="text-center mt-3">
                    <a href="{{ route('dashboard.index') }}" style="font-size: 13px; color: var(--text-muted);">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
