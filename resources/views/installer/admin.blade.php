@extends('layouts.installer')

@section('step', '3')
@section('step-title', 'Admin Account Setup')

@section('content')

<div class="mb-4">
    <h3><i class="fas fa-user-shield me-2 text-primary"></i>Create Admin Account</h3>
    <p class="text-muted">Set up the super admin account. You will use these credentials to log in and manage the platform.</p>
</div>

<div class="alert alert-warning border-0" style="background: #f59e0b0a;">
    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
    <span class="text-muted"><strong>Important:</strong> Use a strong password (8+ characters, mix of letters, numbers, symbols). This account has full administrative access.</span>
</div>

<form action="{{ route('install.run') }}" method="POST">
    @csrf

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-user-cog me-2 text-primary"></i>Administrator Details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input type="text" name="admin_name" class="form-control @error('admin_name') is-invalid @enderror" value="{{ old('admin_name') }}" placeholder="John Doe" required>
                    @error('admin_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror" value="{{ old('admin_email') }}" placeholder="admin@aptrades.io" required>
                    @error('admin_email')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <input type="password" name="admin_password" class="form-control @error('admin_password') is-invalid @enderror" id="adminPass" placeholder="Min 8 characters" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePass('adminPass', this)"><i class="fas fa-eye"></i></button>
                    </div>
                    @error('admin_password')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirm Password</label>
                    <input type="password" name="admin_password_confirmation" class="form-control" placeholder="Re-enter password" required>
                </div>
            </div>
        </div>
    </div>

    <!-- What will be installed -->
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-boxes me-2 text-primary"></i>What Will Be Installed</div>
        <div class="card-body">
            <div class="row g-2">
                @php
                    $features = [
                        ['fa-database', 'Database tables', '25+ tables with all indexes'],
                        ['fa-key', 'Application key', 'Generated and stored securely'],
                        ['fa-folder', 'Storage symlink', 'Public storage linked'],
                        ['fa-shield-alt', 'Admin account', 'Full access with 5 wallet types'],
                        ['fa-cog', 'Platform settings', 'All features enabled by default'],
                        ['fa-chart-pie', '4 investment packages', 'Starter, Silver, Gold, Platinum'],
                        ['fa-wallet', '4 deposit addresses', 'BTC, ETH, USDT (TRC-20 & ERC-20)'],
                        ['fa-file-code', 'Shared hosting .htaccess', 'Auto-routing for Namecheap/Ultahost'],
                    ];
                @endphp
                @foreach($features as $f)
                <div class="col-md-6">
                    <div class="install-feature-item">
                        <i class="fas {{ $f[0] }} text-primary"></i>
                        <div>
                            <div class="fw-semibold">{{ $f[1] }}</div>
                            <small class="text-muted">{{ $f[2] }}</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('install.database') }}" class="btn btn-outline-secondary px-4"><i class="fas fa-arrow-left me-2"></i>Back</a>
        <button type="submit" class="btn btn-success px-5" id="installBtn">
            <i class="fas fa-rocket me-2"></i>Run Installation
        </button>
    </div>
</form>

<script>
function togglePass(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

// Show loading state on install
document.getElementById('installBtn').addEventListener('click', function(e) {
    setTimeout(() => {
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Installing...';
        this.disabled = true;
    }, 100);
});
</script>

@endsection
