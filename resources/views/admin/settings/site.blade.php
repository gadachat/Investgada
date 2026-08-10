@extends('layouts.admin')

@section('page-title', 'Site Settings')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 8px; font-size: 22px;">
        <i class="fas fa-globe" style="color: var(--purple-3);"></i> Site Settings
    </h2>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Manage branding, logo, favicon, social links, and SEO configuration.</p>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.site-settings.update') }}" enctype="multipart/form-data">
        @csrf

        <!-- ===== BRANDING ===== -->
        <div class="card-custom mb-4">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 20px;">
                <i class="fas fa-palette" style="color: var(--purple-3);"></i> Branding
            </h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Platform Name</label>
                    <input type="text" name="platform_name" value="{{ $settings['platform_name'] }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Tagline</label>
                    <input type="text" name="platform_tagline" value="{{ $settings['platform_tagline'] }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Support Email</label>
                    <input type="email" name="platform_email" value="{{ $settings['platform_email'] }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Phone</label>
                    <input type="text" name="platform_phone" value="{{ $settings['platform_phone'] }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Address</label>
                    <input type="text" name="platform_address" value="{{ $settings['platform_address'] }}" class="form-control">
                </div>
            </div>
        </div>

        <!-- ===== LOGO & FAVICON ===== -->
        <div class="card-custom mb-4">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 20px;">
                <i class="fas fa-image" style="color: var(--purple-3);"></i> Logo & Favicon
            </h5>
            <div class="row g-4">
                <!-- Light Logo -->
                <div class="col-md-4">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 8px;">Logo (Light Background)</label>
                    <div style="background: #fff; border-radius: 12px; padding: 20px; text-align: center; min-height: 100px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; border: 1px solid var(--border);">
                        @if($settings['logo'])
                            <img src="{{ asset($settings['logo']) }}" alt="Logo" style="max-height: 60px; max-width: 180px;">
                        @else
                            <span style="color: #999; font-size: 13px;">No logo uploaded</span>
                        @endif
                    </div>
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="form-control form-control-sm" style="font-size: 12px;">
                    <div class="form-check mt-2">
                        <input type="checkbox" name="remove_logo" value="1" class="form-check-input" id="rm-logo">
                        <label for="rm-logo" class="form-check-label" style="font-size: 12px; color: var(--text-muted);">Remove current logo</label>
                    </div>
                </div>

                <!-- Dark Logo -->
                <div class="col-md-4">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 8px;">Logo (Dark Background)</label>
                    <div style="background: #0f1226; border-radius: 12px; padding: 20px; text-align: center; min-height: 100px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; border: 1px solid var(--border);">
                        @if($settings['logo_dark'])
                            <img src="{{ asset($settings['logo_dark']) }}" alt="Logo Dark" style="max-height: 60px; max-width: 180px;">
                        @else
                            <span style="color: #64748b; font-size: 13px;">No dark logo uploaded</span>
                        @endif
                    </div>
                    <input type="file" name="logo_dark" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="form-control form-control-sm" style="font-size: 12px;">
                    <div class="form-check mt-2">
                        <input type="checkbox" name="remove_logo_dark" value="1" class="form-check-input" id="rm-logo-dark">
                        <label for="rm-logo-dark" class="form-check-label" style="font-size: 12px; color: var(--text-muted);">Remove dark logo</label>
                    </div>
                </div>

                <!-- Favicon -->
                <div class="col-md-4">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 8px;">Favicon (32×32 recommended)</label>
                    <div style="background: var(--bg-card); border-radius: 12px; padding: 20px; text-align: center; min-height: 100px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; border: 1px solid var(--border);">
                        @if($settings['favicon'])
                            <img src="{{ asset($settings['favicon']) }}" alt="Favicon" style="max-height: 48px; max-width: 48px;">
                        @else
                            <i class="fas fa-star" style="color: var(--purple-1); font-size: 28px;"></i>
                        @endif
                    </div>
                    <input type="file" name="favicon" accept="image/png,image/jpeg,image/x-icon,image/svg+xml,image/webp" class="form-control form-control-sm" style="font-size: 12px;">
                    <div class="form-check mt-2">
                        <input type="checkbox" name="remove_favicon" value="1" class="form-check-input" id="rm-favicon">
                        <label for="rm-favicon" class="form-check-label" style="font-size: 12px; color: var(--text-muted);">Remove favicon</label>
                    </div>
                </div>
            </div>
            <p style="font-size: 11px; color: var(--text-dim); margin-top: 12px;">
                <i class="fas fa-info-circle"></i> Recommended: PNG or SVG, max 1MB (logo), 512KB (favicon). Transparent background preferred for logos.
            </p>
        </div>

        <!-- ===== SOCIAL LINKS ===== -->
        <div class="card-custom mb-4">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 20px;">
                <i class="fab fa-twitter" style="color: var(--purple-3);"></i> Social Links
            </h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text-muted);"><i class="fab fa-twitter"></i></span>
                        <input type="url" name="social_twitter" value="{{ $settings['social_twitter'] }}" class="form-control" placeholder="https://twitter.com/...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text-muted);"><i class="fab fa-facebook"></i></span>
                        <input type="url" name="social_facebook" value="{{ $settings['social_facebook'] }}" class="form-control" placeholder="https://facebook.com/...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text-muted);"><i class="fab fa-telegram"></i></span>
                        <input type="url" name="social_telegram" value="{{ $settings['social_telegram'] }}" class="form-control" placeholder="https://t.me/...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text-muted);"><i class="fab fa-instagram"></i></span>
                        <input type="url" name="social_instagram" value="{{ $settings['social_instagram'] }}" class="form-control" placeholder="https://instagram.com/...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text-muted);"><i class="fab fa-youtube"></i></span>
                        <input type="url" name="social_youtube" value="{{ $settings['social_youtube'] }}" class="form-control" placeholder="https://youtube.com/...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text-muted);"><i class="fab fa-linkedin"></i></span>
                        <input type="url" name="social_linkedin" value="{{ $settings['social_linkedin'] }}" class="form-control" placeholder="https://linkedin.com/...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text-muted);"><i class="fab fa-discord"></i></span>
                        <input type="url" name="social_discord" value="{{ $settings['social_discord'] }}" class="form-control" placeholder="https://discord.gg/...">
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== SEO META ===== -->
        <div class="card-custom mb-4">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 20px;">
                <i class="fas fa-search" style="color: var(--purple-3);"></i> SEO — Meta Tags
            </h5>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Meta Title <span style="color: var(--text-dim);">(60 chars max)</span></label>
                    <input type="text" name="seo_meta_title" value="{{ $settings['seo_meta_title'] }}" class="form-control" maxlength="200">
                    <small style="color: var(--text-dim); font-size: 11px;">Appears in browser tabs and search results.</small>
                </div>
                <div class="col-12">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Meta Description <span style="color: var(--text-dim);">(160 chars ideal)</span></label>
                    <textarea name="seo_meta_description" class="form-control" rows="3" maxlength="500">{{ $settings['seo_meta_description'] }}</textarea>
                    <small style="color: var(--text-dim); font-size: 11px;">Short summary shown under your title in search results.</small>
                </div>
                <div class="col-12">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Meta Keywords <span style="color: var(--text-dim);">(comma separated)</span></label>
                    <input type="text" name="seo_meta_keywords" value="{{ $settings['seo_meta_keywords'] }}" class="form-control" placeholder="crypto, forex, investment, bitcoin">
                </div>
                <div class="col-md-6">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Canonical URL</label>
                    <input type="url" name="seo_canonical_url" value="{{ $settings['seo_canonical_url'] }}" class="form-control" placeholder="https://yourdomain.com">
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Robots: Index</label>
                    <select name="seo_robots_index" class="form-select">
                        <option value="1" {{ $settings['seo_robots_index'] ? 'selected' : '' }}>Allow indexing</option>
                        <option value="0" {{ !$settings['seo_robots_index'] ? 'selected' : '' }}>No index</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Robots: Follow</label>
                    <select name="seo_robots_follow" class="form-select">
                        <option value="1" {{ $settings['seo_robots_follow'] ? 'selected' : '' }}>Allow following</option>
                        <option value="0" {{ !$settings['seo_robots_follow'] ? 'selected' : '' }}>No follow</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ===== OPEN GRAPH & TWITTER ===== -->
        <div class="card-custom mb-4">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 20px;">
                <i class="fas fa-share-alt" style="color: var(--purple-3);"></i> Open Graph & Social Cards
            </h5>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">OG Title</label>
                    <input type="text" name="seo_og_title" value="{{ $settings['seo_og_title'] }}" class="form-control" placeholder="Defaults to meta title if empty">
                </div>
                <div class="col-12">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">OG Description</label>
                    <textarea name="seo_og_description" class="form-control" rows="2">{{ $settings['seo_og_description'] }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Twitter Card Type</label>
                    <select name="seo_twitter_card" class="form-select">
                        <option value="summary" {{ $settings['seo_twitter_card'] === 'summary' ? 'selected' : '' }}>Summary</option>
                        <option value="summary_large_image" {{ $settings['seo_twitter_card'] === 'summary_large_image' ? 'selected' : '' }}>Summary with Large Image</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ===== ANALYTICS ===== -->
        <div class="card-custom mb-4">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 20px;">
                <i class="fas fa-chart-line" style="color: var(--purple-3);"></i> Analytics & Tracking
            </h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Google Analytics ID</label>
                    <input type="text" name="google_analytics_id" value="{{ $settings['google_analytics_id'] }}" class="form-control" placeholder="G-XXXXXXXXXX">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Google Search Console</label>
                    <input type="text" name="google_search_console" value="{{ $settings['google_search_console'] }}" class="form-control" placeholder="Verification code">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Facebook Pixel ID</label>
                    <input type="text" name="facebook_pixel_id" value="{{ $settings['facebook_pixel_id'] }}" class="form-control" placeholder="XXXXXXXXXXXXXXX">
                </div>
            </div>
        </div>

        <!-- ===== STRUCTURED DATA ===== -->
        <div class="card-custom mb-4">
            <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 20px;">
                <i class="fas fa-code" style="color: var(--purple-3);"></i> Schema.org Structured Data
            </h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Schema Type</label>
                    <select name="seo_schema_type" class="form-select">
                        <option value="FinancialService" {{ $settings['seo_schema_type'] === 'FinancialService' ? 'selected' : '' }}>Financial Service</option>
                        <option value="Organization" {{ $settings['seo_schema_type'] === 'Organization' ? 'selected' : '' }}>Organization</option>
                        <option value="WebSite" {{ $settings['seo_schema_type'] === 'WebSite' ? 'selected' : '' }}>Website</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Schema Name</label>
                    <input type="text" name="seo_schema_name" value="{{ $settings['seo_schema_name'] }}" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Schema Description</label>
                    <textarea name="seo_schema_description" class="form-control" rows="2">{{ $settings['seo_schema_description'] }}</textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-gradient" style="padding: 14px 40px; font-size: 14px;">
            <i class="fas fa-save"></i> Save All Settings
        </button>
    </form>
</div>
@endsection