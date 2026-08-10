{{-- Mobile Bottom Navigation Bar — shows on phones only --}}
@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $isActive = function($pattern) use ($currentRoute) {
        return $currentRoute && str_starts_with($currentRoute, $pattern) ? 'active' : '';
    };
@endphp

<nav class="mobile-bottom-nav" id="mobileBottomNav">
    <a href="{{ route('dashboard.index') }}" class="mobile-nav-item {{ $isActive('dashboard.index') }}">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('dashboard.deposit.create') }}" class="mobile-nav-item {{ $isActive('dashboard.deposit') }}">
        <i class="fas fa-wallet"></i>
        <span>Deposit</span>
    </a>
    <a href="{{ route('dashboard.packages.index') }}" class="mobile-nav-item {{ $isActive('dashboard.packages') }} {{ $isActive('dashboard.investments') }}">
        <i class="fas fa-chart-line"></i>
        <span>Invest</span>
    </a>
    <a href="{{ route('dashboard.trade.index') }}" class="mobile-nav-item {{ $isActive('dashboard.trade') }}">
        <i class="fas fa-exchange-alt"></i>
        <span>Trade</span>
    </a>
    <a href="{{ route('dashboard.profile.index') }}" class="mobile-nav-item {{ $isActive('dashboard.profile') }}">
        <i class="fas fa-user"></i>
        <span>Profile</span>
    </a>
</nav>

<style>
    /* Mobile Bottom Navigation */
    .mobile-bottom-nav {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-top: 1px solid rgba(99, 102, 241, 0.2);
        padding: 6px 4px;
        padding-bottom: calc(6px + var(--safe-area-bottom, env(safe-area-inset-bottom, 0px)));
        z-index: 999;
        justify-content: space-around;
        align-items: center;
        transition: transform 0.3s ease;
    }

    .mobile-bottom-nav.hidden {
        transform: translateY(100%);
    }

    .mobile-nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        padding: 6px 10px;
        color: var(--text-muted, #94a3b8);
        text-decoration: none;
        font-size: 10px;
        font-weight: 500;
        border-radius: 12px;
        transition: all 0.2s;
        flex: 1;
        max-width: 80px;
        -webkit-tap-highlight-color: transparent;
    }

    .mobile-nav-item i {
        font-size: 18px;
        transition: all 0.2s;
    }

    .mobile-nav-item.active {
        color: #a855f7;
    }

    .mobile-nav-item.active i {
        transform: scale(1.15);
        filter: drop-shadow(0 0 8px rgba(168, 85, 247, 0.5));
    }

    .mobile-nav-item:active {
        background: rgba(99, 102, 241, 0.1);
    }

    /* Show only on mobile (phones and small tablets) */
    @media (max-width: 768px) {
        .mobile-bottom-nav {
            display: flex;
        }

        /* Add bottom padding to main content so nav doesn't cover content */
        .main-content {
            padding-bottom: calc(70px + var(--safe-area-bottom, env(safe-area-inset-bottom, 0px))) !important;
        }

        .page-content {
            padding-bottom: calc(80px + var(--safe-area-bottom, env(safe-area-inset-bottom, 0px))) !important;
        }
    }

    /* Hide on landscape phones (less space needed) */
    @media (max-width: 768px) and (orientation: landscape) {
        .mobile-bottom-nav {
            padding: 4px 4px;
        }

        .mobile-nav-item {
            font-size: 9px;
            padding: 4px 8px;
        }

        .mobile-nav-item i {
            font-size: 16px;
        }
    }

    /* Light mode adjustments */
    body:not(.dark) .mobile-bottom-nav {
        background: rgba(255, 255, 255, 0.95);
        border-top: 1px solid rgba(99, 102, 241, 0.15);
    }

    body:not(.dark) .mobile-nav-item {
        color: #64748b;
    }

    body:not(.dark) .mobile-nav-item.active {
        color: #7c3aed;
    }
</style>

<script>
    // Show/hide bottom nav on scroll (hide when scrolling down, show when scrolling up)
    (function() {
        let lastScrollY = 0;
        const nav = document.getElementById('mobileBottomNav');
        if (!nav) return;

        let ticking = false;

        function updateNavVisibility() {
            const currentScrollY = window.scrollY;

            if (currentScrollY < 50) {
                // Always show at top
                nav.classList.remove('hidden');
            } else if (currentScrollY > lastScrollY && currentScrollY > 200) {
                // Scrolling down — hide
                nav.classList.add('hidden');
            } else {
                // Scrolling up — show
                nav.classList.remove('hidden');
            }

            lastScrollY = currentScrollY;
            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(updateNavVisibility);
                ticking = true;
            }
        }, { passive: true });
    })();
</script>
