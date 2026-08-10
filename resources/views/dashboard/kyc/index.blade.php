@extends('layouts.dashboard')

@section('title', 'KYC Verification')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="background: linear-gradient(135deg, #6366f1, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">KYC Verification</h2>
            <p class="text-muted mb-0">Verify your identity to unlock withdrawals and full platform access</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(!$kycEnabled)
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #6366f11a, #7c3aed1a); border: 1px solid #6366f133 !important;">
        <div class="card-body text-center py-5">
            <i class="fas fa-shield-alt fa-3x mb-3" style="color: #6366f1;"></i>
            <h4 class="fw-bold">KYC Verification Disabled</h4>
            <p class="text-muted mb-0">Identity verification is currently not required. You may continue using the platform without KYC.</p>
        </div>
    </div>
    @else

    {{-- Status Banner --}}
    @php
        $statusConfig = [
            'verified' => ['text' => 'Verified',   'class' => 'success', 'icon' => 'check-circle',     'bg' => '#22c55e'],
            'pending'  => ['text' => 'Under Review','class' => 'warning', 'icon' => 'clock',           'bg' => '#f59e0b'],
            'rejected' => ['text' => 'Rejected',   'class' => 'danger',  'icon' => 'times-circle',     'bg' => '#ef4444'],
        ];
        $status = $kyc ? ($statusConfig[$kyc->status] ?? null) : null;
    @endphp

    @if($status)
    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid {{ $status['bg'] }} !important;">
        <div class="card-body d-flex align-items-center py-3">
            <div class="me-3" style="width: 50px; height: 50px; border-radius: 12px; background: {{ $status['bg'] }}20; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-{{ $status['icon'] }} fa-lg" style="color: {{ $status['bg'] }};"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="fw-bold mb-0">KYC Status: <span class="text-{{ $status['class'] }}">{{ $status['text'] }}</span></h5>
                @if($kyc->status === 'verified')
                <p class="text-muted small mb-0">Verified on {{ $kyc->verified_at ? $kyc->verified_at->format('M d, Y \a\t H:i') : 'N/A' }}</p>
                @elseif($kyc->status === 'pending')
                <p class="text-muted small mb-0">Submitted on {{ $kyc->submitted_at ? $kyc->submitted_at->format('M d, Y') : $kyc->created_at->format('M d, Y') }} — Please allow 24-48 hours for review.</p>
                @elseif($kyc->status === 'rejected')
                <p class="text-muted small mb-0">Rejected on {{ $kyc->rejected_at ? $kyc->rejected_at->format('M d, Y') : 'N/A' }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($rejectionReason)
    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #ef4444 !important;">
        <div class="card-body">
            <h6 class="fw-bold text-danger mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Rejection Reason</h6>
            <p class="mb-0 text-muted">{{ $rejectionReason }}</p>
        </div>
    </div>
    @endif

    <div class="row g-4">
        {{-- Left Column: Status Timeline + Requirements --}}
        <div class="col-lg-5 col-md-6 col-12">
            @if($kyc && count($timeline) > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-stream me-2 text-primary"></i>Verification Progress</h6>
                </div>
                <div class="card-body">
                    @foreach($timeline as $item)
                    <div class="d-flex align-items-start mb-3">
                        <div class="me-3 mt-1" style="width: 28px; height: 28px; border-radius: 50%; background: {{ $item['done'] ? '#6366f1' : '#e5e7eb' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            @if($item['done'])
                            <i class="fas fa-check text-white" style="font-size: 11px;"></i>
                            @else
                            <div style="width: 8px; height: 8px; border-radius: 50%; background: #9ca3af;"></div>
                            @endif
                        </div>
                        <div>
                            <p class="fw-semibold mb-0 {{ $item['done'] ? '' : 'text-muted' }}">{{ $item['step'] }}</p>
                            @if($item['date'])
                            <p class="text-muted small mb-0">{{ $item['date']->format('M d, Y \a\t H:i') }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Requirements --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-list-check me-2 text-primary"></i>Requirements</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @foreach($requirements as $req)
                        <li class="d-flex align-items-start mb-3">
                            <i class="fas fa-circle-check text-success me-3 mt-1" style="font-size: 14px;"></i>
                            <span class="text-muted" style="font-size: 0.9rem;">{{ $req }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <hr>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-image text-primary me-2"></i>
                        <small class="text-muted">Accepted formats: JPG, PNG, PDF — Max {{ Setting::get('kyc_max_file_size', 2048) / 1024 }}MB per file</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Action Card or Document View --}}
        <div class="col-lg-7">
            @if(!$kyc || $kyc->status === 'rejected')
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #6366f120, #7c3aed20); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                        <i class="fas fa-id-card fa-2x" style="color: #6366f1;"></i>
                    </div>
                    <h4 class="fw-bold mb-2">@if($kyc && $kyc->status === 'rejected') Resubmit KYC @else Complete Your KYC @endif</h4>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                        @if($kyc && $kyc->status === 'rejected')
                            Your previous submission was rejected. Please correct the issues mentioned above and resubmit.
                        @else
                            Verify your identity to enable withdrawals, increase transaction limits, and access all platform features.
                        @endif
                    </p>
                    <a href="{{ route('dashboard.kyc.create') }}" class="btn text-white px-4 py-2" style="background: linear-gradient(135deg, #6366f1, #7c3aed); border: none;">
                        <i class="fas fa-upload me-2"></i>@if($kyc && $kyc->status === 'rejected') Resubmit Now @else Start Verification @endif
                    </a>
                </div>
            </div>
            @elseif($kyc->status === 'verified')
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #22c55e !important;">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-check-circle text-success me-2"></i>Verification Details</h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Document Type</small>
                            <p class="fw-semibold mb-0 text-capitalize">{{ str_replace('_', ' ', $kyc->document_type) }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Document Number</small>
                            <p class="fw-semibold mb-0">{{ $kyc->document_number }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Full Name</small>
                            <p class="fw-semibold mb-0">{{ $kyc->first_name }} {{ $kyc->last_name }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Nationality</small>
                            <p class="fw-semibold mb-0">{{ $kyc->nationality }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Date of Birth</small>
                            <p class="fw-semibold mb-0">{{ $kyc->date_of_birth ? \Carbon\Carbon::parse($kyc->date_of_birth)->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Country</small>
                            <p class="fw-semibold mb-0">{{ $kyc->country }}</p>
                        </div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-3 mt-3">Submitted Documents</h6>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <a href="{{ route('dashboard.kyc.download', [$kyc->id, 'id_front']) }}" class="d-flex align-items-center p-2 rounded text-decoration-none border">
                                <i class="fas fa-file-alt text-primary me-2"></i>
                                <span class="small">ID Front</span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="{{ route('dashboard.kyc.download', [$kyc->id, 'id_back']) }}" class="d-flex align-items-center p-2 rounded text-decoration-none border">
                                <i class="fas fa-file-alt text-primary me-2"></i>
                                <span class="small">ID Back</span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="{{ route('dashboard.kyc.download', [$kyc->id, 'proof_of_address']) }}" class="d-flex align-items-center p-2 rounded text-decoration-none border">
                                <i class="fas fa-file-alt text-primary me-2"></i>
                                <span class="small">Proof of Address</span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="{{ route('dashboard.kyc.download', [$kyc->id, 'selfie']) }}" class="d-flex align-items-center p-2 rounded text-decoration-none border">
                                <i class="fas fa-camera text-primary me-2"></i>
                                <span class="small">Selfie</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @elseif($kyc->status === 'pending')
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body text-center py-5">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: #f59e0b15; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                        <i class="fas fa-hourglass-half fa-2x" style="color: #f59e0b;"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Under Review</h4>
                    <p class="text-muted mb-0 mx-auto" style="max-width: 400px;">
                        Your documents are being reviewed by our compliance team. This process typically takes 24-48 hours. You will be notified once the review is complete.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
