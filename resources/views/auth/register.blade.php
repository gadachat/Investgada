@extends('layouts.auth')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h3>Create Account</h3>
            <p>Join the investment platform</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($sponsor ?? null)
            <div class="alert alert-info">
                <i class="fas fa-user-tag"></i>
                Referred by: <strong>{{ $sponsor->name }}</strong> ({{ $sponsor->referral_code }})
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            @if($referralCode)
                <input type="hidden" name="referral_code" value="{{ $referralCode }}">
            @endif

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                       placeholder="John Doe" required>
            </div>

            <div class="form-row">
                <div class="form-group col">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username') }}"
                           placeholder="johndoe" required>
                </div>
                <div class="form-group col">
                    <label>Phone (optional)</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}"
                           placeholder="+1 234 567 8900">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                           placeholder="john@example.com" required>
                </div>
                <div class="form-group col">
                    <label>Country</label>
                    <select name="country" class="form-control">
                        <option value="">Select country</option>
                        @foreach(config('countries', []) as $code => $name)
                            <option value="{{ $name }}" @selected(old('country') === $name)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control"
                           placeholder="Min 8 characters" required>
                </div>
                <div class="form-group col">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control"
                           placeholder="Re-enter password" required>
                </div>
            </div>

            @if($sponsor ?? null)
            <div class="form-group">
                <label>Placement Position (Binary Tree)</label>
                <select name="binary_position" class="form-control">
                    <option value="left" @selected(old('binary_position') === 'left')>Left</option>
                    <option value="right" @selected(old('binary_position') === 'right')>Right</option>
                </select>
                <small class="text-muted">Choose where you'll be placed in your sponsor's binary tree</small>
            </div>
            @endif

            <div class="form-check">
                <input type="checkbox" name="terms" id="terms" class="form-check-input" required>
                <label for="terms">
                    I agree to the <a href="#" target="_blank">Terms of Service</a> and
                    <a href="#" target="_blank">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </div>
</div>
@endsection
