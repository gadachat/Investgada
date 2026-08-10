@php
use App\Models\Setting;
$seoTitle = Setting::get('seo_meta_title', config('app.name', 'APTrades') . ' — Investment Platform');
$seoDesc = Setting::get('seo_meta_description', 'Next-generation investment platform for crypto, forex, stocks, and bonds.');
$seoKeywords = Setting::get('seo_meta_keywords', '');
$ogTitle = Setting::get('seo_og_title', $seoTitle);
$ogDesc = Setting::get('seo_og_description', $seoDesc);
$twitterCard = Setting::get('seo_twitter_card', 'summary_large_image');
$canonical = Setting::get('seo_canonical_url', request()->url());
$robotsIndex = Setting::get('seo_robots_index', '1') === '1';
$robotsFollow = Setting::get('seo_robots_follow', '1') === '1';
$logo = Setting::get('logo', '');
$ogImage = $logo ? asset($logo) : '';
$analyticsId = Setting::get('google_analytics_id', '');
$gscCode = Setting::get('google_search_console', '');
$fbPixel = Setting::get('facebook_pixel_id', '');
$favicon = Setting::get('favicon', '');
$schemaType = Setting::get('seo_schema_type', 'FinancialService');
$schemaName = Setting::get('seo_schema_name', Setting::get('platform_name', 'APTrades'));
$schemaDesc = Setting::get('seo_schema_description', '');
$platformEmail = Setting::get('platform_email', '');
$socialTwitter = Setting::get('social_twitter', '');
$socialFacebook = Setting::get('social_facebook', '');
$socialTelegram = Setting::get('social_telegram', '');
$socialInstagram = Setting::get('social_instagram', '');
$socialYoutube = Setting::get('social_youtube', '');
$socialLinkedin = Setting::get('social_linkedin', '');
$socialDiscord = Setting::get('social_discord', '');
@endphp

<!-- Favicon -->
@if($favicon)
<link rel="icon" type="image/x-icon" href="{{ asset($favicon) }}">
<link rel="apple-touch-icon" href="{{ asset($favicon) }}">
@endif

<!-- SEO Meta Tags -->
<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDesc }}">
@if($seoKeywords)
<meta name="keywords" content="{{ $seoKeywords }}">
@endif
<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="{{ $robotsIndex ? 'index' : 'noindex' }}, {{ $robotsFollow ? 'follow' : 'nofollow' }}">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDesc }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:site_name" content="{{ $schemaName }}">
@if($ogImage)
<meta property="og:image" content="{{ $ogImage }}">
@endif

<!-- Twitter Card -->
<meta name="twitter:card" content="{{ $twitterCard }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDesc }}">
@if($ogImage)
<meta name="twitter:image" content="{{ $ogImage }}">
@endif

<!-- Google Search Console Verification -->
@if($gscCode)
<meta name="google-site-verification" content="{{ $gscCode }}">
@endif

<!-- Schema.org Structured Data -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "{{ $schemaType }}",
    "name": "{{ $schemaName }}",
    "description": "{{ $schemaDesc }}",
    "url": "{{ $canonical }}",
    @if($platformEmail)"email": "{{ $platformEmail }}",@endif
    @if($socialTwitter)"sameAs": ["{{ $socialTwitter }}", @if($socialFacebook)"{{ $socialFacebook }}", @endif @if($socialTelegram)"{{ $socialTelegram }}", @endif @if($socialInstagram)"{{ $socialInstagram }}", @endif @if($socialYoutube)"{{ $socialYoutube }}", @endif @if($socialLinkedin)"{{ $socialLinkedin }}", @endif @if($socialDiscord)"{{ $socialDiscord }}" @endif],@endif
    "foundingDate": "2024"
}
</script>

<!-- Google Analytics -->
@if($analyticsId)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $analyticsId }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ $analyticsId }}');
</script>
@endif

<!-- Facebook Pixel -->
@if($fbPixel)
<script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $fbPixel }}');
    fbq('track', 'PageView');
</script>
@endif
