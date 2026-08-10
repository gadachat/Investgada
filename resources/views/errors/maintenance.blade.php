@extends('layouts.auth')

@section('title', 'Maintenance Mode')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card text-center">
        <i class="fas fa-tools" style="font-size: 48px; color: var(--primary); margin-bottom: 20px;"></i>
        <h3>Under Maintenance</h3>
        <p style="color: var(--text-muted); margin-top: 8px;">
            We're performing scheduled maintenance. Please check back shortly.
        </p>
    </div>
</div>
@endsection
