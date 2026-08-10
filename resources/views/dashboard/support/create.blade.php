@extends('layouts.dashboard')

@section('page-title', 'New Support Ticket')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px; font-size: 22px;">
                <i class="fas fa-plus-circle" style="color: #6366f1;"></i> New Support Ticket
            </h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Tell us what you need help with and we'll get back to you.</p>
        </div>
        <a href="{{ route('dashboard.support.index') }}" class="btn btn-sm" style="background: var(--bg-card); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 8px 16px; font-size: 12px; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Tickets
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card-custom" style="padding: 28px; max-width:100%;max-width:700px;">
        <form method="POST" action="{{ route('dashboard.support.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label style="color: var(--text-bright); font-weight: 600; font-size: 13px; margin-bottom: 6px;">Subject</label>
                <input type="text" name="subject" value="{{ old('subject') }}" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 12px 16px; font-size: 14px;" placeholder="Brief description of your issue" required>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label style="color: var(--text-bright); font-weight: 600; font-size: 13px; margin-bottom: 6px;">Category</label>
                    <select name="category" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 12px 16px; font-size: 14px;" required>
                        <option value="">Select a category</option>
                        @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label style="color: var(--text-bright); font-weight: 600; font-size: 13px; margin-bottom: 6px;">Priority</label>
                    <select name="priority" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 12px 16px; font-size: 14px;" required>
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low — General question</option>
                        <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium — Needs attention</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High — Urgent issue</option>
                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent — Can't access funds</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label style="color: var(--text-bright); font-weight: 600; font-size: 13px; margin-bottom: 6px;">Message</label>
                <textarea name="message" rows="8" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 12px 16px; font-size: 14px; resize: vertical;" placeholder="Describe your issue in detail. Include any relevant transaction IDs, dates, or amounts..." required>{{ old('message') }}</textarea>
                <p style="color: var(--text-dim); font-size: 11px; margin-top: 4px;">Be as detailed as possible — it helps us resolve your issue faster.</p>
            </div>

            <!-- File Attachments -->
            <div class="mb-4">
                <label style="color: var(--text-bright); font-weight: 600; font-size: 13px; margin-bottom: 6px;">Attachments <span style="color: var(--text-dim); font-weight: 400;">(optional, max 5MB each)</span></label>
                <input type="file" name="attachments[]" multiple class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 10px;" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip">
                <p style="color: var(--text-dim); font-size: 11px; margin-top: 4px;">Upload screenshots, documents, or proof. Max 5 files.</p>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn" style="background: linear-gradient(135deg, #6366f1, #7c3aed, #a855f7); color: white; border: none; border-radius: 12px; padding: 12px 32px; font-size: 14px; font-weight: 600;">
                    <i class="fas fa-paper-plane"></i> Submit Ticket
                </button>
                <a href="{{ route('dashboard.support.index') }}" class="btn" style="background: var(--bg-card); border: 1px solid var(--border); color: var(--text); border-radius: 12px; padding: 12px 24px; font-size: 14px; text-decoration: none;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection