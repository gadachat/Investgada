@extends('layouts.admin')

@section('page-title', 'Feature Manager')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 8px; font-size: 22px;"><i class="fas fa-toggle-on" style="color: var(--purple-3);"></i> Feature ON/OFF Manager</h2>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Enable or disable any module instantly. Changes take effect immediately across the platform.</p>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="row g-3">
        @foreach($features as $feature)
        <div class="col-lg-4 col-md-6">
            <div class="card-custom" style="padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: {{ $feature->is_enabled ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)' }}; color: {{ $feature->is_enabled ? 'var(--green)' : 'var(--red)' }}; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                            <i class="fas fa-{{ $feature->key === 'crypto' ? 'bitcoin-sign' : ($feature->key === 'forex' ? 'dollar-sign' : ($feature->key === 'stocks' ? 'chart-line' : ($feature->key === 'bonds' ? 'landmark' : ($feature->key === 'binary' ? 'layer-group' : ($feature->key === 'kyc' ? 'id-card' : ($feature->key === 'referral' ? 'users' : ($feature->key === 'binary_mlm' ? 'sitemap' : ($feature->key === 'matching_bonus' ? 'gift' : ($feature->key === 'profit_share' ? 'percent' : ($feature->key === 'deposit' ? 'arrow-down' : ($feature->key === 'withdrawal' ? 'arrow-up' : ($feature->key === 'support' ? 'ticket-alt' : ($feature->key === 'auto_trading' ? 'robot' : ($feature->key === 'live_trading' ? 'satellite-dish' : 'cog'))))))))))))) }}"></i>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 700; color: var(--text-bright);">{{ $feature->label }}</div>
                            <div style="font-size: 11px; color: var(--text-dim); margin-top: 2px;">{{ $feature->key }}</div>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" {{ $feature->is_enabled ? 'checked' : '' }} onchange="toggleFeature('{{ $feature->key }}', this)">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                @if($feature->description)
                <p style="font-size: 12px; color: var(--text-muted); margin: 0; line-height: 1.5;">{{ $feature->description }}</p>
                @endif
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(51,65,85,0.3); display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 11px; color: var(--text-dim);">Status:</span>
                    <span id="status_{{ $feature->key }}" style="font-size: 12px; font-weight: 700; color: {{ $feature->is_enabled ? 'var(--green)' : 'var(--red)' }};">
                        {{ $feature->is_enabled ? '● ENABLED' : '● DISABLED' }}
                    </span>
                </div>
                <!-- Configure button -->
                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(51,65,85,0.3);">
                    <button type="button"
                        onclick="openConfigModal({{ $feature->id }}, '{{ $feature->key }}', '{{ addslashes($feature->label) }}')"
                        style="background: transparent; border: 1px solid var(--border); color: var(--text-muted); padding: 6px 14px; border-radius: 8px; font-size: 11px; cursor: pointer; transition: all 0.2s;"
                        onmouseover="this.style.borderColor='var(--purple-1)'; this.style.color='var(--purple-1)'"
                        onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-muted)'">
                        <i class="fas fa-sliders"></i> Configure
                    </button>
                    @if($feature->config)
                    <span style="font-size: 10px; color: var(--green); margin-left: 8px;">● Has config</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Config Editor Modal -->
<div class="modal fade" id="configModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" style="font-weight: 700;">
                    <i class="fas fa-sliders" style="color: var(--purple-3);"></i>
                    Configure: <span id="configFeatureLabel"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="configForm" method="POST" action="">
                @csrf
                <div class="modal-body" style="padding: 24px;">
                    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 16px;">
                        Edit the JSON configuration for this feature. Leave empty to clear the config.
                    </p>
                    <input type="hidden" name="feature_id" id="configFeatureId">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 8px;">Configuration (JSON)</label>
                    <textarea name="config" id="configTextarea" class="form-control"
                        rows="12"
                        style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); font-family: 'Courier New', monospace; font-size: 13px; border-radius: 10px;"
                        placeholder='{ "key": "value" }'></textarea>
                    <small style="color: var(--text-dim); font-size: 11px; margin-top: 6px; display: block;">
                        Enter valid JSON. Example: <code>{ "min_amount": 10, "max_amount": 5000 }</code>
                    </small>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="font-size: 13px; border-radius: 10px;">Cancel</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #6366f1, #7c3aed, #a855f7); color: white; font-size: 13px; border-radius: 10px; padding: 10px 28px; border: none;">
                        <i class="fas fa-save"></i> Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleFeature(key, checkbox) {
    fetch("{{ route('admin.settings.toggle-feature') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ key: key, is_enabled: checkbox.checked })
    })
    .then(r => r.json())
    .then(data => {
        var statusEl = document.getElementById('status_' + key);
        var card = checkbox.closest('.card-custom');
        if (data.is_enabled) {
            statusEl.textContent = '● ENABLED';
            statusEl.style.color = 'var(--green)';
        } else {
            statusEl.textContent = '● DISABLED';
            statusEl.style.color = 'var(--red)';
        }
    })
    .catch(err => {
        checkbox.checked = !checkbox.checked;
        alert('Failed to toggle feature. Please try again.');
    });
}

// Config editor
var configModal = null;
document.addEventListener('DOMContentLoaded', function() {
    configModal = new bootstrap.Modal(document.getElementById('configModal'));
});

function openConfigModal(featureId, featureKey, featureLabel) {
    document.getElementById('configFeatureId').value = featureId;
    document.getElementById('configFeatureLabel').textContent = featureLabel;

    // Set the form action to the correct route URL
    var form = document.getElementById('configForm');
    form.action = "{{ route('admin.settings.update-feature-config', '__ID__') }}".replace('__ID__', featureId);

    // Fetch current config via AJAX and prefill textarea
    fetch('/admin/settings/features/' + featureId + '/config', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('Not found');
        return r.json();
    })
    .then(data => {
        document.getElementById('configTextarea').value = data.config || '';
    })
    .catch(() => {
        // If GET endpoint doesn't exist, just leave empty
        document.getElementById('configTextarea').value = '';
    });

    configModal.show();
}
</script>
@endsection