@extends('layouts.admin')

@section('title', 'KYC Review')

@section('content')
<div class="container-fluid" style="max-width:100%;max-width:1000px;">
    <div class="mb-4">
        <a href="{{ route('admin.kyc.index') }}" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i>Back to KYC List</a>
        <div class="d-flex justify-content-between align-items-center mt-2">
            <div>
                <h2 class="fw-bold mb-1" style="background: linear-gradient(135deg, #6366f1, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">KYC Review</h2>
                <p class="text-muted mb-0">{{ $kyc->user->name }} — {{ $kyc->user->email }}</p>
            </div>
            @if($kyc->status === 'pending')
            <div>
                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2"><i class="fas fa-clock me-1"></i>Pending Review</span>
            </div>
            @elseif($kyc->status === 'verified')
            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2"><i class="fas fa-check me-1"></i>Verified</span>
            @elseif($kyc->status === 'rejected')
            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2"><i class="fas fa-times me-1"></i>Rejected</span>
            @endif
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        {{-- Left: Personal + Document Info --}}
        <div class="col-lg-7 col-md-8 col-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-user me-2 text-primary"></i>Personal Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @php
                            $fields = [
                                ['First Name', $kyc->first_name],
                                ['Last Name', $kyc->last_name],
                                ['Date of Birth', $kyc->date_of_birth ? \Carbon\Carbon::parse($kyc->date_of_birth)->format('M d, Y') : 'N/A'],
                                ['Nationality', $kyc->nationality],
                                ['Phone', $kyc->phone_number],
                                ['Document Type', $documentTypes[$kyc->document_type] ?? ucfirst(str_replace('_', ' ', $kyc->document_type))],
                                ['Document Number', $kyc->document_number],
                            ];
                        @endphp
                        @foreach($fields as $field)
                        <div class="col-sm-6">
                            <small class="text-muted d-block">{{ $field[0] }}</small>
                            <p class="fw-semibold mb-0">{{ $field[1] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Address</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1">{{ $kyc->address_line_1 }}</p>
                    @if($kyc->address_line_2)<p class="mb-1 text-muted">{{ $kyc->address_line_2 }}</p>@endif
                    <p class="mb-0 text-muted">{{ $kyc->city }}, {{ $kyc->state }}@if($kyc->postal_code), {{ $kyc->postal_code }}@endif</p>
                    <p class="mb-0 text-muted">{{ $kyc->country }}</p>
                </div>
            </div>
        </div>

        {{-- Right: Document Previews + Actions --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Submitted Documents</h6>
                </div>
                <div class="card-body">
                    @php
                        $documents = [
                            ['id_front', 'ID Front', 'fa-id-card'],
                            ['id_back', 'ID Back', 'fa-id-card'],
                            ['proof_of_address', 'Proof of Address', 'fa-file-invoice'],
                            ['selfie', 'Selfie with ID', 'fa-camera'],
                        ];
                    @endphp
                    <div class="row g-3">
                        @foreach($documents as $doc)
                        <div class="col-6">
                            <div class="border rounded p-3 text-center h-100" style="transition: all 0.2s;">
                                <a href="{{ route('admin.kyc.download', [$kyc->id, $doc[0]]) }}" class="text-decoration-none" target="_blank">
                                    <div style="width: 48px; height: 48px; border-radius: 10px; background: #6366f115; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px;">
                                        <i class="fas {{ $doc[2] }} text-primary"></i>
                                    </div>
                                    <p class="small fw-semibold mb-0 text-dark">{{ $doc[1] }}</p>
                                    <p class="small text-muted mb-0"><i class="fas fa-download me-1"></i>View / Download</p>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Admin Actions --}}
            @if($kyc->status === 'pending')
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-gavel me-2 text-primary"></i>Review Decision</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.kyc.approve', $kyc->id) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 py-2" onclick="return confirm('Approve this KYC? User will be marked as verified.')">
                            <i class="fas fa-check-circle me-2"></i>Approve KYC
                        </button>
                    </form>
                    <form action="{{ route('admin.kyc.reject', $kyc->id) }}" method="POST">
                        @csrf
                        <label class="form-label small fw-semibold text-danger">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control mb-2" rows="3" placeholder="Explain why this KYC is being rejected..." required></textarea>
                        @error('rejection_reason')<small class="text-danger">{{ $message }}</small>@enderror
                        <button type="submit" class="btn btn-danger w-100 py-2" onclick="return confirm('Reject this KYC? User will be notified with the reason.')">
                            <i class="fas fa-times-circle me-2"></i>Reject KYC
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    @if($kyc->status === 'verified')
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="fw-bold text-success mb-0">KYC Approved</p>
                        <p class="small text-muted">Verified on {{ $kyc->verified_at ? $kyc->verified_at->format('M d, Y \a\t H:i') : 'N/A' }}</p>
                    </div>
                    @elseif($kyc->status === 'rejected')
                    <div class="text-center py-3">
                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                        <p class="fw-bold text-danger mb-0">KYC Rejected</p>
                        <p class="small text-muted">Rejected on {{ $kyc->rejected_at ? $kyc->rejected_at->format('M d, Y') : 'N/A' }}</p>
                        <div class="alert alert-danger mt-2 mb-0 small">{{ $kyc->rejection_reason }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
