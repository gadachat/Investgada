@extends('layouts.admin')

@section('title', 'Tawk.to Chat Widget')

@section('content')
<div class="container-fluid" style="max-width:100%;max-width:800px;">
    <div class="mb-4">
        <h2 class="fw-bold mb-1" style="background: linear-gradient(135deg, #6366f1, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Tawk.to Live Chat</h2>
        <p class="text-muted mb-0">Configure the Tawk.to widget for user support chat</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="fw-bold mb-0"><i class="fab fa-tawk me-2 text-primary"></i>Widget Configuration</h6>
        </div>
        <div class="card-body">
            <div class="alert alert-info border-0" style="background: #6366f10a;">
                <i class="fas fa-info-circle me-2 text-primary"></i>
                <span class="small text-muted">
                    Get your Property ID and Widget ID from your <a href="https://dashboard.tawk.to" target="_blank" class="text-primary">Tawk.to Dashboard</a>.
                    Go to <strong>Administration → Channels → Chat Widget → Direct Chat Link</strong> — the Property ID is in the script snippet (e.g. <code>var Tawk_API=Tawk_API||{}; var Tawk_LoadStart=new Date(); (function(){ var s1=document.createElement('script'),s0=document.getElementsByTagName('script')[0]; s1.async=true; s1.src='https://embed.tawk.to/<strong>PROPERTY_ID</strong>/<strong>WIDGET_ID</strong>'; ...</code>).
                </span>
            </div>

            <form action="{{ route('admin.chat-widget.update') }}" method="POST">
                @csrf

                {{-- Enable Toggle --}}
                <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-4">
                    <div>
                        <p class="fw-bold mb-0">Enable Chat Widget</p>
                        <p class="text-muted small mb-0">Show the Tawk.to chat bubble on user-facing pages</p>
                    </div>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="tawk_enabled" id="tawk_enabled" {{ $settings['tawk_enabled'] ? 'checked' : '' }} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                    </div>
                </div>

                {{-- Show on Admin --}}
                <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-4">
                    <div>
                        <p class="fw-bold mb-0">Show on Admin Panel</p>
                        <p class="text-muted small mb-0">Also display the chat widget in the admin dashboard (for admin-side support)</p>
                    </div>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="tawk_show_on_admin" id="tawk_show_on_admin" {{ $settings['tawk_show_on_admin'] ? 'checked' : '' }} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                    </div>
                </div>

                {{-- Property ID --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Tawk.to Property ID <span class="text-danger">*</span></label>
                    <input type="text" name="tawk_property_id" class="form-control form-control-lg" value="{{ $settings['tawk_property_id'] }}" placeholder="e.g. 1234567890abcdef1234567890" required>
                    <small class="text-muted">The long alphanumeric string in your Tawk.to embed script URL.</small>
                </div>

                {{-- Widget ID --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Widget ID</label>
                    <input type="text" name="tawk_widget_id" class="form-control form-control-lg" value="{{ $settings['tawk_widget_id'] }}" placeholder="default">
                    <small class="text-muted">Usually <code>default</code> unless you have multiple widgets configured.</small>
                </div>

                {{-- Preview --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold small">Preview Embed Code</label>
                    <pre class="bg-dark text-white p-3 rounded" style="font-size: 0.75rem; overflow-x: auto;"><code>&lt;script type="text/javascript"&gt;
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement('script'),s0=document.getElementsByTagName('script')[0];
s1.async=true;
s1.src='https://embed.tawk.to/<span style="color: #a855f7;">{{ $settings['tawk_property_id'] ?: 'YOUR_PROPERTY_ID' }}</span>/<span style="color: #3b82f6;">{{ $settings['tawk_widget_id'] ?: 'default' }}</span>';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
&lt;/script&gt;</code></pre>
                </div>

                {{-- How to get IDs --}}
                <div class="card border-0 bg-light mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2"><i class="fas fa-question-circle text-primary me-2"></i>How to find your IDs</h6>
                        <ol class="small text-muted ps-3 mb-0">
                            <li>Log in to your <a href="https://dashboard.tawk.to" target="_blank" class="text-primary">Tawk.to Dashboard</a></li>
                            <li>Navigate to <strong>Administration → Property Settings</strong></li>
                            <li>Go to <strong>Chat Widget</strong> tab</li>
                            <li>Copy the embed code from the <strong>"Direct Chat Link"</strong> or <strong>"Widget Code"</strong> section</li>
                            <li>The URL in the script <code>src</code> looks like: <code>https://embed.tawk.to/PROPERTY_ID/WIDGET_ID</code></li>
                            <li>Copy both values and paste them above</li>
                        </ol>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn text-white px-4 py-2" style="background: linear-gradient(135deg, #6366f1, #7c3aed); border: none;">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                    <a href="{{ route('admin.settings.features') }}" class="btn btn-outline-secondary px-4 py-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Quick Toggle --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-toggle-on me-2 text-primary"></i>Quick Toggle</h6>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="fw-bold mb-0">Chat Widget Status</p>
                    <p class="small mb-0 {{ $settings['tawk_enabled'] ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-{{ $settings['tawk_enabled'] ? 'check-circle' : 'times-circle' }} me-1"></i>
                        {{ $settings['tawk_enabled'] ? 'Active — showing on user pages' : 'Inactive — not visible to users' }}
                    </p>
                </div>
                <button class="btn {{ $settings['tawk_enabled'] ? 'btn-outline-danger' : 'btn-success' }}" onclick="quickToggle()">
                    <i class="fas fa-{{ $settings['tawk_enabled'] ? 'toggle-off' : 'toggle-on' }} me-1"></i>{{ $settings['tawk_enabled'] ? 'Disable' : 'Enable' }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function quickToggle() {
    fetch('{{ route("admin.chat-widget.toggle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ enabled: {{ $settings['tawk_enabled'] ? 'false' : 'true' }} })
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); })
    .catch(err => console.error(err));
}
</script>
@endsection
