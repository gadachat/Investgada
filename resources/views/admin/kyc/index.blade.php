@extends('layouts.admin')

@section('title', 'KYC Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="background: linear-gradient(135deg, #6366f1, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">KYC Management</h2>
            <p class="text-muted mb-0">Review and manage identity verification requests</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            {{-- KYC ON/OFF toggle --}}
            <div class="card border-0 px-3 py-2" style="background: {{ $kycEnabled ? '#22c55e10' : '#ef444410' }};">
                <div class="d-flex align-items-center gap-2">
                    <span class="small fw-semibold {{ $kycEnabled ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-{{ $kycEnabled ? 'toggle-on' : 'toggle-off' }} me-1"></i>KYC {{ $kycEnabled ? 'Enabled' : 'Disabled' }}
                    </span>
                    <div class="form-check form-switch ms-2 mb-0">
                        <input type="checkbox" class="form-check-input" id="kycToggle" {{ $kycEnabled ? 'checked' : '' }} onchange="toggleKyc(this.checked)" style="cursor: pointer;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3" style="width: 48px; height: 48px; border-radius: 12px; background: #6366f115; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-layer-group text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Total</p>
                        <h4 class="fw-bold mb-0">{{ number_format($stats['total']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3" style="width: 48px; height: 48px; border-radius: 12px; background: #f59e0b15; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clock" style="color: #f59e0b;"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Pending</p>
                        <h4 class="fw-bold mb-0">{{ number_format($stats['pending']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3" style="width: 48px; height: 48px; border-radius: 12px; background: #22c55e15; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Verified</p>
                        <h4 class="fw-bold mb-0">{{ number_format($stats['verified']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3" style="width: 48px; height: 48px; border-radius: 12px; background: #ef444415; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-times-circle text-danger"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Rejected</p>
                        <h4 class="fw-bold mb-0">{{ number_format($stats['rejected']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, or document number..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" @if(request('status') === 'pending') selected @endif>Pending Review</option>
                        <option value="verified" @if(request('status') === 'verified') selected @endif>Verified</option>
                        <option value="rejected" @if(request('status') === 'rejected') selected @endif>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                </div>
                <div class="col-md-3 text-end">
                    <a href="{{ route('admin.kyc.index') }}" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- KYC Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th class="ps-4">User</th>
                        <th>Document</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kycs as $kyc)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #7c3aed); display: flex; align-items: center; justify-content: center;">
                                    <span class="text-white fw-bold">{{ strtoupper(substr($kyc->user->name ?? '?', 0, 1)) }}</span>
                                </div>
                                <div>
                                    <p class="fw-semibold mb-0">{{ $kyc->user->name ?? 'N/A' }}</p>
                                    <p class="text-muted small mb-0">{{ $kyc->user->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark text-capitalize">{{ str_replace('_', ' ', $kyc->document_type) }}</span>
                            <br><small class="text-muted">{{ $kyc->document_number }}</small>
                        </td>
                        <td><small class="text-muted">{{ $kyc->created_at->format('M d, Y') }}</small></td>
                        <td>
                            @if($kyc->status === 'pending')
                            <span class="badge bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock me-1"></i>Pending</span>
                            @elseif($kyc->status === 'verified')
                            <span class="badge bg-success bg-opacity-10 text-success"><i class="fas fa-check me-1"></i>Verified</span>
                            @elseif($kyc->status === 'rejected')
                            <span class="badge bg-danger bg-opacity-10 text-danger"><i class="fas fa-times me-1"></i>Rejected</span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('admin.kyc.show', $kyc->id) }}" class="btn btn-sm" style="background: #6366f115; color: #6366f1;">
                                <i class="fas fa-eye me-1"></i>Review
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No KYC submissions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($kycs->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $kycs->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function toggleKyc(enabled) {
    fetch('{{ route("admin.kyc.toggle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ enabled: enabled })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update badge text
            const card = event.target.closest('.card');
            const span = card.querySelector('span');
            span.className = 'small fw-semibold ' + (enabled ? 'text-success' : 'text-danger');
            span.innerHTML = '<i class="fas fa-' + (enabled ? 'toggle-on' : 'toggle-off') + ' me-1"></i>KYC ' + (enabled ? 'Enabled' : 'Disabled');
        }
    })
    .catch(err => console.error(err));
}
</script>
@endsection
