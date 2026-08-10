@extends('layouts.auth')
@section('title', 'Admin Login')

@push('styles')
<style>
    .admin-login-badge {
        background: linear-gradient(135deg, var(--purple-1), var(--purple-2));
        color: white;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
        display: inline-block;
        margin-bottom: 16px;
    }
    .admin-login-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--purple-1), var(--purple-2));
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 24px;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="auth-card" style="max-width: 420px; margin: 0 auto;">
    <div class="text-center mb-4">
        <div class="admin-login-icon"><i class="fas fa-shield-alt"></i></div>
        <span class="admin-login-badge">ADMINISTRATOR ACCESS</span>
        <h4 style="font-weight: 700; margin-bottom: 4px;">Admin Panel Login</h4>
        <p style="color: var(--text-muted); font-size: 13px;">Authorized personnel only</p>
    </div>

    @if(session('error'))
    <div class="alert alert-danger" style="border-radius: 10px; font-size: 13px;">{{ session('error') }}</div>
    @endif
    @if(session('success'))
    <div class="alert alert-success" style="border-radius: 10px; font-size: 13px;">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login.post') }}">
        @csrf
        <div class="mb-3">
            <label style="font-size: 13px; font-weight: 600; margin-bottom: 6px;">Email or Username</label>
            <input type="text" name="login" class="form-control @error('login') is-invalid @enderror" 
                   placeholder="admin@example.com" required autofocus>
            @error('login') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label style="font-size: 13px; font-weight: 600; margin-bottom: 6px;">Password</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                       placeholder="Enter password" required>
                <button type="button" class="btn" style="border: 1px solid var(--border); background: var(--bg-card);" 
                        onclick="togglePassword()">
                    <i class="fas fa-eye" id="pw-icon"></i>
                </button>
            </div>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label for="remember" class="form-check-label" style="font-size: 12px; color: var(--text-muted);">Remember me</label>
            </div>
            <a href="{{ route('password.request') }}" style="font-size: 12px; color: var(--purple-1);">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-gradient w-100" style="padding: 12px;">
            <i class="fas fa-sign-in-alt me-2"></i> Sign In to Admin Panel
        </button>
    </form>

    <div class="text-center mt-3" style="font-size: 12px; color: var(--text-muted);">
        <a href="{{ route('login') }}" style="color: var(--purple-1);">← Back to user login</a>
    </div>
</div>

<script>
function togglePassword() {
    const pw = document.getElementById('password');
    const icon = document.getElementById('pw-icon');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        pw.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection
