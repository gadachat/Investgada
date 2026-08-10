@extends('layouts.dashboard')

@section('title', 'Submit KYC — Verification')

@section('content')
<div class="container-fluid" style="max-width:100%;max-width:800px;">
    <div class="mb-4">
        <a href="{{ route('dashboard.kyc.index') }}" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i>Back to KYC</a>
        <h2 class="fw-bold mt-2 mb-1" style="background: linear-gradient(135deg, #6366f1, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Submit KYC Documents</h2>
        <p class="text-muted mb-0">Please provide accurate information and clear document copies.</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>Please correct the errors below.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('dashboard.kyc.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Personal Information --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-user me-2 text-primary"></i>Personal Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control form-control-lg" value="{{ old('first_name', auth()->user()->name) }}" required>
                        @error('first_name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control form-control-lg" value="{{ old('last_name') }}" required>
                        @error('last_name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" class="form-control form-control-lg" value="{{ old('date_of_birth') }}" required>
                        @error('date_of_birth')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nationality <span class="text-danger">*</span></label>
                        <input type="text" name="nationality" class="form-control form-control-lg" value="{{ old('nationality') }}" placeholder="e.g. Nigerian" required>
                        @error('nationality')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone_number" class="form-control form-control-lg" value="{{ old('phone_number') }}" placeholder="+234 800 000 0000" required>
                        @error('phone_number')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Document Information --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-id-card me-2 text-primary"></i>Document Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Document Type <span class="text-danger">*</span></label>
                        <select name="document_type" class="form-select form-select-lg" required>
                            <option value="">Select document type...</option>
                            @foreach($documentTypes as $value => $label)
                            <option value="{{ $value }}" @if(old('document_type') === $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('document_type')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Document Number <span class="text-danger">*</span></label>
                        <input type="text" name="document_number" class="form-control form-control-lg" value="{{ old('document_number') }}" placeholder="ID / Passport number" required>
                        @error('document_number')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Address</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Address Line 1 <span class="text-danger">*</span></label>
                        <input type="text" name="address_line_1" class="form-control form-control-lg" value="{{ old('address_line_1') }}" placeholder="House number, street name" required>
                        @error('address_line_1')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Address Line 2</label>
                        <input type="text" name="address_line_2" class="form-control form-control-lg" value="{{ old('address_line_2') }}" placeholder="Apartment, suite, etc. (optional)">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">City <span class="text-danger">*</span></label>
                        <input type="text" name="city" class="form-control form-control-lg" value="{{ old('city') }}" required>
                        @error('city')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">State / Province <span class="text-danger">*</span></label>
                        <input type="text" name="state" class="form-control form-control-lg" value="{{ old('state') }}" required>
                        @error('state')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Postal Code</label>
                        <input type="text" name="postal_code" class="form-control form-control-lg" value="{{ old('postal_code') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Country <span class="text-danger">*</span></label>
                        <input type="text" name="country" class="form-control form-control-lg" value="{{ old('country') }}" placeholder="e.g. Nigeria" required>
                        @error('country')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Document Uploads --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-upload me-2 text-primary"></i>Document Uploads</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i>Accepted formats: JPG, PNG, PDF — Max {{ Setting::get('kyc_max_file_size', 2048) / 1024 }}MB per file.</p>
                <div class="row g-3">
                    @php
                        $uploads = [
                            ['id_front', 'ID Document — Front', 'fa-id-card', 'required'],
                            ['id_back', 'ID Document — Back', 'fa-id-card', 'required'],
                            ['proof_of_address', 'Proof of Address', 'fa-file-invoice', 'required'],
                            ['selfie', 'Selfie with ID', 'fa-camera', 'required'],
                        ];
                    @endphp
                    @foreach($uploads as $upload)
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">{{ $upload[1] }} <span class="text-danger">*</span></label>
                        <div class="upload-zone border rounded p-3 text-center" style="border-style: dashed !important; cursor: pointer; transition: all 0.2s;" onclick="document.getElementById('file-{{ $upload[0] }}').click()">
                            <input type="file" name="{{ $upload[0] }}" id="file-{{ $upload[0] }}" class="d-none" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect(this, '{{ $upload[0] }}')" required>
                            <div id="preview-{{ $upload[0] }}">
                                <i class="fas {{ $upload[2] }} fa-2x text-muted mb-2"></i>
                                <p class="small text-muted mb-0">Click to upload</p>
                                <p class="small text-muted mb-0" id="filename-{{ $upload[0] }}"></p>
                            </div>
                        </div>
                        @error($upload[0])<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Consent --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="consent" required>
                    <label class="form-check-label small text-muted" for="consent">
                        I confirm that all information provided is accurate and the documents are genuine. I authorize the platform to verify my identity and store these documents securely. <span class="text-danger">*</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 mb-5">
            <a href="{{ route('dashboard.kyc.index') }}" class="btn btn-outline-secondary px-4 py-2">Cancel</a>
            <button type="submit" class="btn text-white px-5 py-2 flex-grow-0" style="background: linear-gradient(135deg, #6366f1, #7c3aed); border: none;">
                <i class="fas fa-paper-plane me-2"></i>Submit for Review
            </button>
        </div>
    </form>
</div>

<script>
function handleFileSelect(input, name) {
    const file = input.files[0];
    if (!file) return;
    const filenameEl = document.getElementById('filename-' + name);
    const previewEl  = document.getElementById('preview-' + name);

    if (filenameEl) {
        filenameEl.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
    }

    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewEl.innerHTML =
                '<img src="' + e.target.result + '" style="max-width: 100%; max-height: 120px; border-radius: 8px;" class="mb-1">' +
                '<p class="small text-muted mb-0">' + file.name + '</p>';
        };
        reader.readAsDataURL(file);
    } else {
        if (filenameEl) filenameEl.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
    }
}

// Style upload zones on hover
document.querySelectorAll('.upload-zone').forEach(function(zone) {
    zone.addEventListener('mouseenter', function() {
        this.style.borderColor = '#6366f1';
        this.style.background = '#6366f108';
    });
    zone.addEventListener('mouseleave', function() {
        this.style.borderColor = '#dee2e6';
        this.style.background = 'transparent';
    });
});
</script>
@endsection
