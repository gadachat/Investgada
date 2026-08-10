@extends('layouts.auth')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h3>Account Suspended</h3>
            <p>Your account has been suspended</p>
        </div>

        <div class="alert alert-danger">
            Your account has been {{ session('error_type', 'suspended') }}.
            Please contact our support team for assistance.
        </div>

        <p style="color: var(--text-muted); font-size: 14px; text-align: center;">
            Email: {{ config('app.support_email', 'support@example.com') }}
        </p>

        <div class="auth-footer">
            <a href="{{ route('login') }}">Back to login</a>
        </div>
    </div>
</div>
@endsection
