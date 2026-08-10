@extends('layouts.admin')

@section('title', 'Edit Landing Page')

@section('content')
<div class="container-fluid" style="max-width:100%;max-width:900px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="background: linear-gradient(135deg, #6366f1, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Edit Landing Page</h2>
            <p class="text-muted mb-0">Customize all content shown on your public landing page</p>
        </div>
        <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-external-link-alt me-1"></i>Preview</a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('admin.landing.update') }}" method="POST">
        @csrf

        @foreach($sections as $sectionName => $fields)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                <i class="fas fa-pen-to-square text-primary me-2"></i>
                <h6 class="fw-bold mb-0">{{ $sectionName }}</h6>
            </div>
            <div class="card-body">
                @foreach($fields as $field)
                @php
                    $isLong = in_array($field, ['hero_subtitle', 'footer_about', 'cta_subtitle', 'features_subtitle', 'section2_subtitle']);
                    $label = ucwords(str_replace('_', ' ', $field));
                @endphp
                <div class="mb-3">
                    <label class="form-label fw-semibold small">{{ $label }}</label>
                    @if($isLong)
                    <textarea name="{{ $field }}" class="form-control" rows="2">{{ $content[$field] }}</textarea>
                    @else
                    <input type="text" name="{{ $field }}" class="form-control" value="{{ $content[$field] }}">
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="d-flex gap-3 mb-5">
            <button type="submit" class="btn text-white px-5 py-2" style="background: linear-gradient(135deg, #6366f1, #7c3aed); border: none;">
                <i class="fas fa-save me-2"></i>Save All Changes
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary px-4 py-2">Cancel</a>
        </div>
    </form>
</div>
@endsection
