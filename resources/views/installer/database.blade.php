@extends('layouts.installer')

@section('step', '2')
@section('step-title', 'Database Configuration')

@section('content')

@if(session('db_error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-database me-2"></i>{{ session('db_error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="mb-4">
    <h3><i class="fas fa-database me-2 text-primary"></i>Database Configuration</h3>
    <p class="text-muted">Enter your MySQL database credentials. Create a database and user in your hosting panel (cPanel/DirectAdmin) before continuing.</p>
</div>

<!-- Hosting Hints -->
@if(!empty($hostingHints))
<div class="hosting-hints mb-4">
    @foreach($hostingHints as $hint)
    <div class="hint-card">
        <div class="hint-provider"><i class="fas fa-info-circle me-2"></i>{{ $hint['provider'] }}</div>
        <div class="hint-text">{{ $hint['tip'] }}</div>
    </div>
    @endforeach
</div>
@endif

<form action="{{ route('install.test-db') }}" method="POST">
    @csrf

    <!-- App Settings -->
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-cog me-2 text-primary"></i>Application Settings</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Application Name</label>
                    <input type="text" name="app_name" class="form-control" value="{{ $current['APP_NAME'] }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">App URL</label>
                    <input type="url" name="app_url" class="form-control" value="{{ $current['APP_URL'] }}" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Database Settings -->
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-database me-2 text-primary"></i>MySQL Database</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Database Host</label>
                    <input type="text" name="db_host" class="form-control" value="{{ $current['DB_HOST'] }}" placeholder="localhost or 127.0.0.1" required>
                    <small class="text-muted">Usually <code>localhost</code> on shared hosting.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Database Port</label>
                    <input type="number" name="db_port" class="form-control" value="{{ $current['DB_PORT'] }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Database Name</label>
                    <input type="text" name="db_name" class="form-control" value="{{ $current['DB_NAME'] }}" placeholder="e.g., username_aptrades" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Database User</label>
                    <input type="text" name="db_user" class="form-control" value="{{ $current['DB_USER'] }}" placeholder="e.g., username_dbuser" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Database Password</label>
                    <div class="input-group">
                        <input type="password" name="db_pass" class="form-control" id="dbPass" value="{{ $current['DB_PASS'] ?? '' }}" placeholder="Enter database password">
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePass('dbPass', this)"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info border-0" style="background: #6366f10a;">
        <i class="fas fa-lightbulb text-primary me-2"></i>
        <span class="text-muted"><strong>Tip:</strong> On Namecheap, go to cPanel → MySQL® Databases. On Ultahost, check your hosting panel for MySQL management. Make sure the database user has <strong>ALL PRIVILEGES</strong> on the database.</span>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('install.index') }}" class="btn btn-outline-secondary px-4"><i class="fas fa-arrow-left me-2"></i>Back</a>
        <button type="submit" class="btn btn-primary px-5">
            Test Connection & Continue <i class="fas fa-arrow-right ms-2"></i>
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
</script>

@endsection
