@extends('layouts.auth')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h3>Set New Password</h3>
            <p>Choose a new password for your account</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ $email ?? old('email') }}"
                       readonly>
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Min 8 characters" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control"
                       placeholder="Re-enter password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-key"></i> Reset Password
            </button>
        </form>

        <div class="auth-footer">
            <a href="{{ route('login') }}">Back to login</a>
        </div>
    </div>
</div>
@endsection
