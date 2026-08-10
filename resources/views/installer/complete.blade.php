@extends('layouts.installer')

@section('step', '4')
@section('step-title', 'Installation Complete')

@section('content')

<div class="text-center mb-4">
    @if(session('install_error'))
    <div class="install-result-icon install-fail">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <h2 class="text-danger">Installation Incomplete</h2>
    <p class="text-muted">{{ session('install_error') }}</p>
    @else
    <div class="install-result-icon install-success">
        <i class="fas fa-check-circle"></i>
    </div>
    <h2 class="text-success">Installation Complete!</h2>
    <p class="text-muted">Your APTrades investment platform has been successfully installed and is ready to use.</p>
    @endif
</div>

<!-- Installation Steps Log -->
<div class="card mb-4">
    <div class="card-header"><i class="fas fa-terminal me-2 text-primary"></i>Installation Log</div>
    <div class="card-body p-0">
        <div class="install-log">
            @foreach($steps as $step)
            <div class="log-item log-{{ $step['status'] }}">
                <div class="log-icon">
                    @if($step['status'] === 'success')
                    <i class="fas fa-check-circle text-success"></i>
                    @elseif($step['status'] === 'warning')
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    @else
                    <i class="fas fa-times-circle text-danger"></i>
                    @endif
                </div>
                <div class="log-content">
                    <div class="log-label">{{ $step['label'] }}</div>
                    @if(isset($step['detail']))<small class="log-detail">{{ $step['detail'] }}</small>@endif
                    @if(isset($step['message']) && $step['status'] !== 'success')<small class="log-error">{{ $step['message'] }}</small>@endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@if(!session('install_error'))

<!-- Next Steps -->
<div class="card mb-4">
    <div class="card-header"><i class="fas fa-list-check me-2 text-primary"></i>Next Steps</div>
    <div class="card-body">
        <ol class="next-steps">
            <li>
                <strong>Log in to your admin panel</strong>
                <p class="text-muted small mb-1">Use the admin email and password you just created.</p>
                <a href="{{ url('/login') }}" class="btn btn-sm btn-primary"><i class="fas fa-sign-in-alt me-1"></i>Go to Login</a>
            </li>
            <li>
                <strong>Configure your deposit addresses</strong>
                <p class="text-muted small mb-1">Replace the default demo crypto addresses with your real wallet addresses in Admin → Settings → Deposit Addresses.</p>
            </li>
            <li>
                <strong>Set up Tawk.to live chat (optional)</strong>
                <p class="text-muted small mb-1">Add your Tawk.to Property ID in Admin → Settings → Tawk.to Chat to enable live support.</p>
            </li>
            <li>
                <strong>Customize your landing page</strong>
                <p class="text-muted small mb-1">Edit hero text, stats, and content in Admin → Edit Landing Page.</p>
            </li>
            <li>
                <strong>Review investment packages</strong>
                <p class="text-muted small mb-1">Adjust return rates, minimum amounts, and durations in Admin → Packages.</p>
            </li>
            <li>
                <strong>Test a deposit & withdrawal</strong>
                <p class="text-muted small mb-1">Run through the user flow to make sure everything works end-to-end.</p>
            </li>
        </ol>
    </div>
</div>

<!-- Security Warning -->
<div class="card border-warning mb-4">
    <div class="card-body">
        <h6 class="text-warning fw-bold"><i class="fas fa-shield-alt me-2"></i>Security Recommendations</h6>
        <ul class="mb-0 small text-muted">
            <li>Delete the <code>/install</code> route or the installer controller after setup to prevent re-installation.</li>
            <li>Set <code>APP_DEBUG=false</code> in your <code>.env</code> file (already set by installer).</li>
            <li>Ensure <code>.env</code> file is not publicly accessible (handled by the auto-generated <code>.htaccess</code>).</li>
            <li>Set up SSL/HTTPS for your domain via your hosting panel.</li>
            <li>Enable 2FA for your admin account once logged in.</li>
        </ul>
    </div>
</div>

<!-- Action Buttons -->
<div class="d-flex gap-3 justify-content-center">
    <a href="{{ url('/login') }}" class="btn btn-primary px-5">
        <i class="fas fa-sign-in-alt me-2"></i>Go to Admin Login
    </a>
    <a href="{{ url('/') }}" class="btn btn-outline-primary px-5">
        <i class="fas fa-home me-2"></i>View Landing Page
    </a>
</div>

@else

<div class="d-flex gap-3 justify-content-center">
    <a href="{{ route('install.admin') }}" class="btn btn-primary px-5">
        <i class="fas fa-redo me-2"></i>Try Again
    </a>
</div>

@endif

@endsection
