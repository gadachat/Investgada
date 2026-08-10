<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class AdminLandingPageController extends Controller
{
    /**
     * Show landing page content editor
     */
    public function edit()
    {
        $fields = [
            'hero_title', 'hero_subtitle', 'hero_badge', 'hero_cta_primary', 'hero_cta_secondary',
            'stat1_value', 'stat1_label', 'stat2_value', 'stat2_label', 'stat3_value', 'stat3_label', 'stat4_value', 'stat4_label',
            'features_title', 'features_subtitle',
            'section2_title', 'section2_subtitle',
            'cta_title', 'cta_subtitle', 'cta_button',
            'footer_about', 'footer_email', 'footer_phone', 'footer_address',
            'testimonial_title',
        ];

        $content = [];
        foreach ($fields as $f) {
            $content[$f] = Setting::get($f, $this->defaults($f));
        }

        $sections = [
            'Hero Section'      => ['hero_title', 'hero_subtitle', 'hero_badge', 'hero_cta_primary', 'hero_cta_secondary'],
            'Hero Stats'        => ['stat1_value', 'stat1_label', 'stat2_value', 'stat2_label', 'stat3_value', 'stat3_label', 'stat4_value', 'stat4_label'],
            'Features Section'  => ['features_title', 'features_subtitle'],
            'Packages Section'  => ['section2_title', 'section2_subtitle'],
            'CTA Section'       => ['cta_title', 'cta_subtitle', 'cta_button'],
            'Testimonials'      => ['testimonial_title'],
            'Footer'            => ['footer_about', 'footer_email', 'footer_phone', 'footer_address'],
        ];

        return view('admin.landing.edit', compact('content', 'sections'));
    }

    /**
     * Update landing page content
     */
    public function update(Request $request)
    {
        $fields = [
            'hero_title', 'hero_subtitle', 'hero_badge', 'hero_cta_primary', 'hero_cta_secondary',
            'stat1_value', 'stat1_label', 'stat2_value', 'stat2_label', 'stat3_value', 'stat3_label', 'stat4_value', 'stat4_label',
            'features_title', 'features_subtitle',
            'section2_title', 'section2_subtitle',
            'cta_title', 'cta_subtitle', 'cta_button',
            'footer_about', 'footer_email', 'footer_phone', 'footer_address',
            'testimonial_title',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->input($field));
            }
        }

        return back()->with('success', 'Landing page content updated successfully.');
    }

    private function defaults($field)
    {
        $defaults = [
            'hero_title'        => 'Trade Smarter. Earn Bigger.',
            'hero_subtitle'     => 'The next-generation investment platform for crypto, forex, stocks, and bonds.',
            'hero_badge'        => 'Trusted by 50,000+ investors worldwide',
            'hero_cta_primary'  => 'Start Investing',
            'hero_cta_secondary' => 'Explore Packages',
            'stat1_value'       => '$250M+',
            'stat1_label'       => 'Total Volume',
            'stat2_value'       => '50,000+',
            'stat2_label'       => 'Active Investors',
            'stat3_value'       => '99.9%',
            'stat3_label'       => 'Uptime',
            'stat4_value'       => '24/7',
            'stat4_label'       => 'Support',
            'features_title'    => 'Why Choose APTrades',
            'features_subtitle' => 'A complete investment ecosystem built for performance, security, and growth.',
            'section2_title'    => 'Investment Packages for Every Goal',
            'section2_subtitle' => 'From beginner to pro — diversified portfolios managed by experts.',
            'cta_title'          => 'Ready to Start Your Investment Journey?',
            'cta_subtitle'       => 'Join thousands of investors earning daily returns.',
            'cta_button'         => 'Create Free Account',
            'footer_about'       => 'APTrades is a next-generation investment platform.',
            'footer_email'       => 'support@aptrades.io',
            'footer_phone'       => '+234 800 000 0000',
            'footer_address'     => 'Lagos, Nigeria',
            'testimonial_title'  => 'What Our Investors Say',
        ];

        return $defaults[$field] ?? '';
    }
}
