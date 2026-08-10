<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AdminSiteSettingsController extends Controller
{
    /**
     * Show the site settings page (branding, logo, SEO).
     */
    public function index()
    {
        $settings = $this->getAllSettings();

        return view('admin.settings.site', compact('settings'));
    }

    /**
     * Update branding + logo + SEO settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // Branding
            'platform_name'           => 'required|string|max:100',
            'platform_tagline'        => 'nullable|string|max:200',
            'platform_email'           => 'nullable|email|max:150',
            'platform_phone'           => 'nullable|string|max:30',
            'platform_address'         => 'nullable|string|max:300',

            // Logo & favicon
            'logo'                     => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:1024',
            'logo_dark'                => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:1024',
            'favicon'                  => 'nullable|image|mimes:png,jpg,jpeg,ico,svg,webp|max:512',
            'remove_logo'              => 'nullable|boolean',
            'remove_logo_dark'         => 'nullable|boolean',
            'remove_favicon'           => 'nullable|boolean',

            // Social links
            'social_twitter'           => 'nullable|url|max:200',
            'social_facebook'          => 'nullable|url|max:200',
            'social_telegram'          => 'nullable|url|max:200',
            'social_instagram'         => 'nullable|url|max:200',
            'social_youtube'           => 'nullable|url|max:200',
            'social_linkedin'          => 'nullable|url|max:200',
            'social_discord'           => 'nullable|url|max:200',

            // SEO — General
            'seo_meta_title'           => 'nullable|string|max:200',
            'seo_meta_description'     => 'nullable|string|max:500',
            'seo_meta_keywords'        => 'nullable|string|max:500',
            'seo_og_title'             => 'nullable|string|max:200',
            'seo_og_description'       => 'nullable|string|max:500',
            'seo_twitter_card'         => 'nullable|in:summary,summary_large_image',
            'seo_canonical_url'        => 'nullable|url|max:200',
            'seo_robots_index'         => 'nullable|boolean',
            'seo_robots_follow'        => 'nullable|boolean',

            // SEO — Analytics
            'google_analytics_id'      => 'nullable|string|max:50',
            'google_search_console'    => 'nullable|string|max:100',
            'facebook_pixel_id'        => 'nullable|string|max:50',

            // SEO — Structured data
            'seo_schema_type'          => 'nullable|in:Organization,FinancialService,WebSite',
            'seo_schema_name'          => 'nullable|string|max:200',
            'seo_schema_description'   => 'nullable|string|max:500',
        ]);

        // Handle logo uploads
        $this->handleFileUpload($request, 'logo', 'logos/logo.png', 'remove_logo');
        $this->handleFileUpload($request, 'logo_dark', 'logos/logo-dark.png', 'remove_logo_dark');
        $this->handleFileUpload($request, 'favicon', 'logos/favicon.png', 'remove_favicon');

        // Save all text settings
        $textFields = [
            'platform_name', 'platform_tagline', 'platform_email', 'platform_phone', 'platform_address',
            'social_twitter', 'social_facebook', 'social_telegram', 'social_instagram',
            'social_youtube', 'social_linkedin', 'social_discord',
            'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords',
            'seo_og_title', 'seo_og_description', 'seo_twitter_card',
            'seo_canonical_url', 'seo_robots_index', 'seo_robots_follow',
            'google_analytics_id', 'google_search_console', 'facebook_pixel_id',
            'seo_schema_type', 'seo_schema_name', 'seo_schema_description',
        ];

        foreach ($textFields as $field) {
            if (array_key_exists($field, $validated)) {
                $value = $validated[$field];
                if ($field === 'seo_robots_index' || $field === 'seo_robots_follow') {
                    $value = $value ? '1' : '0';
                }
                Setting::set($field, $value ?? '', 'string', 'site');
            }
        }

        return back()->with('success', 'Site settings saved successfully.');
    }

    /**
     * Handle a file upload (logo, favicon).
     */
    private function handleFileUpload(Request $request, string $field, string $path, string $removeField): void
    {
        if ($request->boolean($removeField)) {
            Storage::disk('public')->delete($path);
            Setting::set($field, '', 'string', 'site');
            return;
        }

        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            $file = $request->file($field);
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $fullPath = preg_replace('/\.\w+$/', '', $path) . '.' . $ext;

            // Delete old file
            Storage::disk('public')->deleteDirectory('logos');

            // Store new file
            $file->storeAs('logos', basename($fullPath), 'public');
            Setting::set($field, 'storage/' . $fullPath, 'string', 'site');
        }
    }

    /**
     * Get all site settings with defaults.
     */
    private function getAllSettings(): array
    {
        $defaults = [
            'platform_name'           => Setting::get('platform_name', 'APTrades'),
            'platform_tagline'        => Setting::get('platform_tagline', 'Trade Smarter. Earn Bigger.'),
            'platform_email'          => Setting::get('platform_email', 'support@aptrades.io'),
            'platform_phone'           => Setting::get('platform_phone', ''),
            'platform_address'         => Setting::get('platform_address', ''),
            'logo'                     => Setting::get('logo', ''),
            'logo_dark'                => Setting::get('logo_dark', ''),
            'favicon'                  => Setting::get('favicon', ''),
            'social_twitter'           => Setting::get('social_twitter', ''),
            'social_facebook'          => Setting::get('social_facebook', ''),
            'social_telegram'          => Setting::get('social_telegram', ''),
            'social_instagram'         => Setting::get('social_instagram', ''),
            'social_youtube'           => Setting::get('social_youtube', ''),
            'social_linkedin'          => Setting::get('social_linkedin', ''),
            'social_discord'           => Setting::get('social_discord', ''),
            'seo_meta_title'           => Setting::get('seo_meta_title', 'APTrades — Crypto, Forex & Investment Platform'),
            'seo_meta_description'     => Setting::get('seo_meta_description', 'Next-generation investment platform for crypto, forex, stocks, and bonds. AI-driven analytics, secure wallets, and daily profit sharing.'),
            'seo_meta_keywords'        => Setting::get('seo_meta_keywords', 'crypto investment, forex trading, bitcoin, ethereum, USDT, investment platform, daily profits, MLM, referral program'),
            'seo_og_title'             => Setting::get('seo_og_title', ''),
            'seo_og_description'       => Setting::get('seo_og_description', ''),
            'seo_twitter_card'         => Setting::get('seo_twitter_card', 'summary_large_image'),
            'seo_canonical_url'        => Setting::get('seo_canonical_url', ''),
            'seo_robots_index'         => Setting::get('seo_robots_index', '1') === '1',
            'seo_robots_follow'        => Setting::get('seo_robots_follow', '1') === '1',
            'google_analytics_id'      => Setting::get('google_analytics_id', ''),
            'google_search_console'    => Setting::get('google_search_console', ''),
            'facebook_pixel_id'        => Setting::get('facebook_pixel_id', ''),
            'seo_schema_type'          => Setting::get('seo_schema_type', 'FinancialService'),
            'seo_schema_name'          => Setting::get('seo_schema_name', 'APTrades'),
            'seo_schema_description'   => Setting::get('seo_schema_description', 'Next-generation crypto and forex investment platform with AI-driven analytics, secure wallets, and daily profit sharing.'),
        ];

        return $defaults;
    }
}
