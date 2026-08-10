@extends('layouts.admin')

@section('page-title', 'Platform Settings')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 8px; font-size: 22px;"><i class="fas fa-cog" style="color: var(--purple-3);"></i> Platform Settings</h2>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Configure platform-wide parameters, payment limits, and MLM rules.</p>

    <form method="POST" action="{{ route('admin.settings.platform.update') }}">
        @csrf
        @foreach($settings as $group => $items)
        <div class="card-custom mb-3">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 16px; text-transform: capitalize;">
                <i class="fas fa-{{ $group === 'general' ? 'info-circle' : ($group === 'payment' ? 'credit-card' : ($group === 'investment' ? 'chart-pie' : ($group === 'mlm' ? 'sitemap' : 'envelope'))) }}" style="color: var(--purple-3);"></i>
                {{ $group }} Settings
            </h5>
            <div class="row g-3">
                @foreach($items as $setting)
                <div class="col-md-6">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 6px; display: block;">
                        {{ str_replace('_', ' ', ucfirst($setting->key)) }}
                    </label>
                    <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="form-control" style="font-size: 14px;">
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        <!-- Web3 Wallet Settings -->
        <div class="card-custom mb-3" style="border: 1px solid rgba(99,102,241,0.2);">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 16px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="display:inline-block; vertical-align:middle; margin-right:4px;">
                    <path d="M12 2L2 7v10l10 5 10-5V7L12 2z" stroke="#6366f1" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M12 7v10M7 9.5v5M17 9.5v5" stroke="#a855f7" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Web3 Wallet Settings
            </h5>
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">Configure Web3 wallet connections for crypto deposits and trading.</p>
            
            @php
                $web3Enabled = old('web3_enabled', \App\Models\PlatformSetting::get('web3_enabled', 'true'));
                $web3Networks = old('web3_networks', \App\Models\PlatformSetting::get('web3_networks', '[]'));
                $web3Wallets = old('web3_wallets', \App\Models\PlatformSetting::get('web3_wallets', '[]'));
                $web3ProjectId = old('web3_project_id', \App\Models\PlatformSetting::get('web3_project_id', ''));
                $web3RequireSig = old('web3_require_signature', \App\Models\PlatformSetting::get('web3_require_signature', 'true'));
            @endphp

            <div class="row g-3">
                <div class="col-md-6">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 6px;">Enable Web3 Wallets</label>
                    <select name="settings[web3_enabled]" class="form-control" style="font-size: 14px;">
                        <option value="true" @selected($web3Enabled === 'true')>Enabled</option>
                        <option value="false" @selected($web3Enabled === 'false')>Disabled</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 6px;">Require Signature Verification</label>
                    <select name="settings[web3_require_signature]" class="form-control" style="font-size: 14px;">
                        <option value="true" @selected($web3RequireSig === 'true')>Yes (Recommended)</option>
                        <option value="false" @selected($web3RequireSig === 'false')>No (Less Secure)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 6px;">WalletConnect Project ID</label>
                    <input type="text" name="settings[web3_project_id]" value="{{ $web3ProjectId }}" class="form-control" style="font-size: 14px;" placeholder="Get from cloud.walletconnect.com">
                </div>
                <div class="col-md-6">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 6px;">Supported Wallet Providers</label>
                    <input type="text" name="settings[web3_wallets]" value="{{ $web3Wallets }}" class="form-control" style="font-size: 14px;" placeholder='["metamask","walletconnect","trust","coinbase"]'>
                    <div style="font-size: 10px; color: var(--text-dim); margin-top: 4px;">JSON array: metamask, walletconnect, trust, coinbase, rabby, okx, phantom</div>
                </div>
                <div class="col-12">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 6px;">Supported Networks (JSON)</label>
                    <textarea name="settings[web3_networks]" class="form-control" style="font-size: 13px; min-height: 100px; font-family: monospace;">{{ $web3Networks }}</textarea>
                    <div style="font-size: 10px; color: var(--text-dim); margin-top: 4px;">Array of objects with chain_id, name, symbol, rpc fields</div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-gradient" style="padding: 12px 32px; font-size: 14px;"><i class="fas fa-save"></i> Save All Settings</button>
    </form>
</div>
@endsection
