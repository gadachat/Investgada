@extends('layouts.auth')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h3>Welcome Back</h3>
            <p>Sign in to your account</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label>Email or Username</label>
                <input type="text" name="login" class="form-control" value="{{ old('login') }}"
                       placeholder="Enter your email or username" required autofocus>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="password-wrap">
                    <input type="password" name="password" class="form-control" id="password"
                           placeholder="Enter password" required>
                    <span class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-row-between">
                <div class="form-check">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input">
                    <label for="remember">Remember me</label>
                </div>
                <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="{{ route('register') }}">Sign up</a>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
@endsection
