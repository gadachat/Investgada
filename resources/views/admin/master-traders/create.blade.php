@extends('layouts.admin')
@section('title', 'Add Master Trader')

@push('styles')
<style>
    .avatar-preview {
        width: 120px; height: 120px; border-radius: 50%;
        object-fit: cover; border: 3px solid var(--border);
    }
    .avatar-placeholder {
        width: 120px; height: 120px; border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #7c3aed);
        display: flex; align-items: center; justify-content: center;
        font-size: 36px; font-weight: 700; color: #fff;
        border: 3px solid var(--border);
    }
    .toggle-switch { position: relative; width: 44px; height: 24px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background: #ccc; border-radius: 24px; transition: 0.3s;
    }
    .toggle-slider:before {
        content: ""; position: absolute; height: 18px; width: 18px;
        left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: 0.3s;
    }
    input:checked + .toggle-slider { background: #6366f1; }
    input:checked + .toggle-slider:before { transform: translateX(20px); }
</style>
@endpush

@section('content')
<div class="fade-in">
    <h4 style="font-weight:700; margin-bottom:20px;"><i class="fas fa-user-tie me-2"></i> Designate Master Trader</h4>

    @if(session('error'))
    <div class="alert alert-danger" style="border-radius:10px; font-size:13px;">{{ session('error') }}</div>
    @endif

    <div class="card-custom" style="max-width:700px;">
        <form method="POST" action="{{ route('admin.master-traders.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Select User --}}
            <div class="mb-3">
                <label style="font-size:13px; font-weight:600; margin-bottom:6px;">Select User</label>
                <select name="user_id" class="form-control" required>
                    <option value="">— Choose a trader —</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">Only non-admin users not already designated as master traders are shown.</div>
            </div>

            {{-- Profile Picture --}}
            <div class="mb-4">
                <label style="font-size:13px; font-weight:600; margin-bottom:8px;">Profile Picture</label>
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="avatar-placeholder" id="avatar-placeholder">?</div>
                    <div>
                        <input type="file" name="avatar" id="avatar-input" accept="image/jpeg,image/png,image/jpg,image/webp" style="display:none;">
                        <button type="button" class="btn btn-sm" style="background:var(--bg-card); border:1px solid var(--border); padding:6px 16px;" onclick="document.getElementById('avatar-input').click()">
                            <i class="fas fa-upload"></i> Upload Photo
                        </button>
                        <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">JPG, PNG, or WebP. Max 2MB. Recommended 400×400px.</div>
                    </div>
                </div>
            </div>

            {{-- Title --}}
            <div class="mb-3">
                <label style="font-size:13px; font-weight:600; margin-bottom:6px;">Title / Strategy Name</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Crypto Sniper, Forex Expert" required>
            </div>

            {{-- Strategy Type --}}
            <div class="mb-3">
                <label style="font-size:13px; font-weight:600; margin-bottom:6px;">Strategy Type</label>
                <select name="strategy_type" class="form-control">
                    <option value="">— Select —</option>
                    @foreach(['Scalper','Day Trader','Swing Trader','Position Trader','Crypto Specialist','Forex Expert','Stocks Analyst','Mixed Strategy'] as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Description --}}
            <div class="mb-3">
                <label style="font-size:13px; font-weight:600; margin-bottom:6px;">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this trader's strategy and specialties..."></textarea>
            </div>

            {{-- Win Rate Override --}}
            <div style="border:1px solid var(--border); border-radius:10px; padding:16px; margin-bottom:16px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div style="font-weight:600; font-size:14px;">Win Rate</div>
                        <div style="font-size:11px; color:var(--text-muted);">Auto-calculated from trade history. Override below if needed.</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size:12px; color:var(--text-muted);">Manual Override</span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="use_manual_win_rate" value="1" id="manual-toggle">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                <div id="manual-win-rate-field" style="display:none;">
                    <label style="font-size:12px; color:var(--text-muted); margin-bottom:4px;">Set Custom Win Rate (%)</label>
                    <input type="number" name="manual_win_rate" class="form-control" min="0" max="100" step="0.1" placeholder="e.g. 87.5">
                    <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">When enabled, this value is shown to users instead of the auto-calculated rate.</div>
                </div>
            </div>

            {{-- Monthly Return --}}
            <div class="mb-3">
                <label style="font-size:13px; font-weight:600; margin-bottom:6px;">Monthly Return (%)</label>
                <input type="number" name="monthly_return" class="form-control" min="0" max="100" step="0.1" placeholder="e.g. 15.5">
                <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">Average monthly return shown to users.</div>
            </div>

            {{-- Max Followers --}}
            <div class="mb-3">
                <label style="font-size:13px; font-weight:600; margin-bottom:6px;">Max Followers (0 = unlimited)</label>
                <input type="number" name="max_followers" class="form-control" value="0" min="0">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-gradient" style="padding:10px 28px;"><i class="fas fa-check"></i> Designate as Master Trader</button>
                <a href="{{ route('admin.master-traders.index') }}" class="btn" style="background:var(--bg-card); border:1px solid var(--border); padding:10px 20px;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('manual-toggle').addEventListener('change', function() {
    document.getElementById('manual-win-rate-field').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('avatar-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const placeholder = document.getElementById('avatar-placeholder');
            const img = document.createElement('img');
            img.src = event.target.result;
            img.className = 'avatar-preview';
            img.id = 'avatar-preview';
            placeholder.replaceWith(img);
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endsection
