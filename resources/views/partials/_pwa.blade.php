{{-- PWA Meta Tags & Device Support — shared across all layouts --}}

{{-- PWA Manifest --}}
<link rel="manifest" href="{{ asset('manifest.json') }}">

{{-- Theme Color (browser UI on mobile) --}}
<meta name="theme-color" content="#6366f1">
<meta name="msapplication-TileColor" content="#7c3aed">

{{-- Apple iOS / iPadOS Support --}}
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="APTrades">
<link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
<link rel="apple-touch-icon" sizes="76x76" href="{{ asset('icons/icon-76.png') }}">
<link rel="apple-touch-icon" sizes="120x120" href="{{ asset('icons/icon-120.png') }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('icons/icon-152.png') }}">
<link rel="apple-touch-icon" sizes="167x167" href="{{ asset('icons/icon-167.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">

{{-- Android Chrome Support --}}
<meta name="application-name" content="APTrades">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16.png') }}">
<link rel="icon" type="image/svg+xml" href="{{ asset('icons/logo.svg') }}">

{{-- Windows Tile --}}
<meta name="msapplication-config" content="none">
<meta name="msapplication-TileImage" content="{{ asset('icons/icon-152.png') }}">

{{-- Mobile Optimizations --}}
<meta name="format-detection" content="telephone=yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="HandheldFriendly" content="True">
<meta name="MobileOptimized" content="320">

{{-- Safe Area Support (iPhone notch / Dynamic Island) --}}
<style>
    :root {
        --safe-area-top: env(safe-area-inset-top, 0px);
        --safe-area-bottom: env(safe-area-inset-bottom, 0px);
        --safe-area-left: env(safe-area-inset-left, 0px);
        --safe-area-right: env(safe-area-inset-right, 0px);
    }

    /* Apply safe area padding to topbars and footers */
    .topbar {
        padding-top: calc(14px + var(--safe-area-top)) !important;
    }

    .main-content {
        padding-bottom: calc(24px + var(--safe-area-bottom)) !important;
    }

    /* Touch-friendly tap targets on mobile */
    @media (max-width: 1024px) {
        .sidebar-link, .nav-link, button.btn-sm, .dropdown-item {
            min-height: 44px;
            display: flex;
            align-items: center;
        }

        /* Larger touch targets for important actions */
        .btn {
            min-height: 44px;
        }

        .form-control, .form-select {
            min-height: 44px;
            font-size: 16px; /* Prevents iOS zoom on focus */
        }

        .icon-btn {
            min-width: 44px;
            min-height: 44px;
        }
    }

    /* Prevent text size adjustment on orientation change */
    html {
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
        text-size-adjust: 100%;
    }

    /* Smooth scrolling on iOS */
    body {
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-y: contain;
    }

    /* Disable tap highlight */
    * {
        -webkit-tap-highlight-color: transparent;
    }

    /* Better mobile table scrolling */
    .table-responsive {
        -webkit-overflow-scrolling: touch;
        touch-action: pan-x;
    }

    /* Fix 100vh on mobile browsers */
    .min-vh-90, .min-vh-100 {
        min-height: 100dvh;
    }
</style>

{{-- Service Worker Registration --}}
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => {
                console.log('PWA: Service Worker registered', reg.scope);
            })
            .catch(err => console.log('PWA: SW registration failed', err));
    });
}

// Detect device and add body class
document.addEventListener('DOMContentLoaded', () => {
    const ua = navigator.userAgent;
    const body = document.body;

    if (/iPhone|iPad|iPod/.test(ua)) {
        body.classList.add('device-ios');
    } else if (/Android/.test(ua)) {
        body.classList.add('device-android');
    } else if (/Windows/.test(ua)) {
        body.classList.add('device-windows');
    } else if (/Mac/.test(ua)) {
        body.classList.add('device-mac');
    } else if (/Linux/.test(ua)) {
        body.classList.add('device-linux');
    }

    // Detect if installed as PWA
    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
        body.classList.add('pwa-installed');
    }
});
</script>
