@extends('layouts.installer')

@section('step', '1')
@section('step-title', 'Welcome & System Check')

@section('content')

@if(session('error'))
<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
@endif

<div class="text-center mb-5">
    <div class="installer-logo mb-3">
        <i class="fas fa-shield-alt"></i>
    </div>
    <h2>Welcome to APTrades Installer</h2>
    <p class="text-muted">This wizard will set up your investment platform in a few simple steps.<br>No SSH or terminal access required — everything runs from your browser.</p>
</div>

<!-- Hosting Hints -->
@if(!empty($hostingHints))
<div class="hosting-hints mb-4">
    @foreach($hostingHints as $hint)
    <div class="hint-card">
        <div class="hint-provider"><i class="fas fa-server me-2"></i>{{ $hint['provider'] }}</div>
        <div class="hint-text">{{ $hint['tip'] }}</div>
    </div>
    @endforeach
</div>
@endif

<!-- Requirements -->
<div class="requirements-section">
    <h4 class="mb-3"><i class="fas fa-list-check me-2"></i>System Requirements</h4>

    @php
        $categories = [
            'PHP & Extensions' => array_filter($requirements, fn($r) => str_contains($r['label'], 'PHP') || str_contains($r['label'], 'Extension')),
            'Folder Permissions' => array_filter($requirements, fn($r) => str_contains($r['label'], 'Writable')),
            'Server Functions' => array_filter($requirements, fn($r) => str_contains($r['label'], 'Function')),
        ];
    @endphp

    @foreach($categories as $catName => $catReqs)
    <div class="req-category mb-3">
        <h6 class="req-category-title">{{ $catName }}</h6>
        <div class="req-grid">
            @foreach($catReqs as $req)
            <div class="req-item {{ $req['passed'] ? 'req-passed' : 'req-failed' }}">
                <div class="req-check">
                    <i class="fas fa-{{ $req['passed'] ? 'check-circle' : 'times-circle' }}"></i>
                </div>
                <div class="req-info">
                    <div class="req-label">{{ $req['label'] }}</div>
                    <div class="req-current">{{ $req['current'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

<!-- Status Banner -->
<div class="status-banner {{ $allPassed ? 'status-ok' : 'status-fail' }} mb-4">
    @if($allPassed)
    <i class="fas fa-check-circle me-2"></i>
    <span>All requirements met! Your server is ready for installation.</span>
    @else
    <i class="fas fa-exclamation-triangle me-2"></i>
    <span>Some requirements are not met. Please contact your hosting provider to enable the missing extensions or fix permissions.</span>
    @endif
</div>

<!-- Navigation -->
<div class="d-flex justify-content-between">
    <a href="/" class="btn btn-outline-secondary px-4">Cancel</a>
    <a href="{{ route('install.database') }}" class="btn btn-primary px-5 {{ !$allPassed ? 'disabled' : '' }}">
        Continue <i class="fas fa-arrow-right ms-2"></i>
    </a>
</div>

@endsection
