@extends('layouts.auth')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h3>Reset Password</h3>
            <p>Enter your email to receive a reset link</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                       placeholder="Enter your email" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-paper-plane"></i> Send Reset Link
            </button>
        </form>

        <div class="auth-footer">
            Remembered your password? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </div>
</div>
@endsection
