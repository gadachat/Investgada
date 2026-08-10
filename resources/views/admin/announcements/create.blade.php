@extends('layouts.admin')

@section('page-title', 'Create Announcement')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight: 700;"><i class="fas fa-plus-circle" style="color: var(--purple-3);"></i> Create Announcement</h4>
        <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-custom">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-3" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; border-radius: 10px;">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card-custom" style="max-width: 850px; margin: 0 auto;">
        <form action="{{ route('admin.announcements.store') }}" method="POST">
            @csrf

            <!-- Title -->
            <div class="mb-3">
                <label class="form-label" style="font-weight: 600; color: var(--text-bright);">Announcement Title <span style="color: #ef4444;">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. Scheduled System Maintenance" maxlength="200" required>
            </div>

            <!-- Message -->
            <div class="mb-3">
                <label class="form-label" style="font-weight: 600; color: var(--text-bright);">Message Content <span style="color: #ef4444;">*</span></label>
                <textarea name="message" class="form-control" rows="5" placeholder="Enter the announcement message here..." required>{{ old('message') }}</textarea>
            </div>

            <div class="row g-3 mb-3">
                <!-- Type Selector -->
                <div class="col-md-6">
                    <label class="form-label" style="font-weight: 600; color: var(--text-bright);">Notification Type <span style="color: #ef4444;">*</span></label>
                    <select name="type" class="form-control" required>
                        <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>Info (Blue Banner)</option>
                        <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Success (Green Banner)</option>
                        <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Warning (Yellow Banner)</option>
                        <option value="danger" {{ old('type') == 'danger' ? 'selected' : '' }}>Danger (Red Banner)</option>
                        <option value="maintenance" {{ old('type') == 'maintenance' ? 'selected' : '' }}>Maintenance (Purple Banner)</option>
                    </select>
                </div>

                <!-- Target Audience -->
                <div class="col-md-6">
                    <label class="form-label" style="font-weight: 600; color: var(--text-bright);">Target Audience <span style="color: #ef4444;">*</span></label>
                    <select name="target" id="targetSelect" class="form-control" required onchange="toggleSpecificUser()">
                        <option value="all" {{ old('target', 'all') == 'all' ? 'selected' : '' }}>All Users</option>
                        <option value="verified" {{ old('target') == 'verified' ? 'selected' : '' }}>Verified Users (KYC Passed)</option>
                        <option value="investors" {{ old('target') == 'investors' ? 'selected' : '' }}>Investors Only</option>
                        <option value="traders" {{ old('target') == 'traders' ? 'selected' : '' }}>Traders Only</option>
                        <option value="specific" {{ old('target') == 'specific' ? 'selected' : '' }}>Specific User</option>
                    </select>
                </div>
            </div>

            <!-- Specific User Select -->
            <div class="mb-3" id="specificUserContainer" style="display: {{ old('target') == 'specific' ? 'block' : 'none' }};">
                <label class="form-label" style="font-weight: 600; color: var(--text-bright);">Select User <span style="color: #ef4444;">*</span></label>
                <select name="target_user_id" class="form-control">
                    <option value="">-- Choose User --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('target_user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-4">
                <!-- Starts At -->
                <div class="col-md-6">
                    <label class="form-label" style="font-weight: 600; color: var(--text-bright);">Starts At (Optional)</label>
                    <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                    <small style="color: var(--text-dim);">Leave blank to start immediately.</small>
                </div>

                <!-- Ends At -->
                <div class="col-md-6">
                    <label class="form-label" style="font-weight: 600; color: var(--text-bright);">Ends At (Optional)</label>
                    <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at') }}">
                    <small style="color: var(--text-dim);">Leave blank to stay active indefinitely.</small>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <!-- Is Active -->
                <div class="col-md-6">
                    <div class="form-check form-switch d-flex align-items-center gap-3 ps-0">
                        <label class="form-check-label m-0" style="font-weight: 600; color: var(--text-bright);">Active Immediately</label>
                        <input class="form-check-input ms-auto" type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} style="width: 44px; height: 22px;">
                    </div>
                </div>

                <!-- Is Dismissible -->
                <div class="col-md-6">
                    <div class="form-check form-switch d-flex align-items-center gap-3 ps-0">
                        <label class="form-check-label m-0" style="font-weight: 600; color: var(--text-bright);">User Can Dismiss Banner</label>
                        <input class="form-check-input ms-auto" type="checkbox" name="is_dismissible" value="1" {{ old('is_dismissible', '1') ? 'checked' : '' }} style="width: 44px; height: 22px;">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-custom">Cancel</a>
                <button type="submit" class="btn btn-gradient">
                    <i class="fas fa-paper-plane"></i> Publish Announcement
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleSpecificUser() {
    const target = document.getElementById('targetSelect').value;
    const container = document.getElementById('specificUserContainer');
    container.style.display = target === 'specific' ? 'block' : 'none';
}
</script>
@endsection
