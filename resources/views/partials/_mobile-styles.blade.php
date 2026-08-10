{{-- Mobile Enhancement CSS — improves UX on iPhone, Android, and all mobile devices --}}

<style>
    /* ===== MOBILE RESPONSIVE ENHANCEMENTS ===== */

    /* Tables: make scrollable and stackable on mobile */
    @media (max-width: 768px) {
        /* Horizontal scroll for wide tables */
        .table {
            min-width: 500px;
        }

        .table-responsive {
            border-radius: 12px;
            border: 1px solid var(--border, rgba(148,163,184,0.1));
        }

        /* Stack mode for small tables */
        .table-mobile-stack tbody tr {
            display: block;
            margin-bottom: 12px;
            border: 1px solid var(--border, rgba(148,163,184,0.1));
            border-radius: 12px;
            padding: 8px;
        }

        .table-mobile-stack tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: none !important;
            padding: 6px 0;
        }

        .table-mobile-stack thead {
            display: none;
        }
    }

    /* Cards: stack on mobile */
    @media (max-width: 576px) {
        .row > [class*="col-"] {
            margin-bottom: 12px;
        }

        /* Make stat cards 2 per row on mobile */
        .stat-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    /* Modals: full screen on mobile */
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 8px;
            max-width: calc(100vw - 16px) !important;
        }

        .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header,
        .modal-body,
        .modal-footer {
            padding: 14px;
        }
    }

    /* Forms: prevent iOS zoom, larger touch targets */
    @media (max-width: 768px) {
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="tel"],
        input[type="url"],
        textarea,
        select {
            font-size: 16px !important; /* Prevents iOS auto-zoom */
            min-height: 44px;
            border-radius: 10px !important;
        }

        label {
            font-size: 14px;
            margin-bottom: 6px;
        }
    }

    /* Charts: responsive height */
    @media (max-width: 768px) {
        [id*="chart"], .chart-container, .apexcharts-canvas {
            min-height: 280px !important;
        }

        canvas {
            max-width: 100%;
        }
    }

    /* Navigation: touch-friendly dropdowns */
    @media (max-width: 1024px) {
        .dropdown-menu {
            min-width: 200px;
            max-width: 280px;
        }

        .dropdown-item {
            padding: 12px 16px;
            min-height: 44px;
            display: flex;
            align-items: center;
        }
    }

    /* Pagination: center and enlarge on mobile */
    @media (max-width: 768px) {
        .pagination {
            justify-content: center;
            flex-wrap: wrap;
        }

        .page-link {
            min-width: 40px;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
    }

    /* Alerts: full width on mobile */
    @media (max-width: 576px) {
        .alert {
            padding: 12px;
            font-size: 13px;
            border-radius: 10px;
        }
    }

    /* Toast notifications: position at bottom on mobile */
    @media (max-width: 768px) {
        .toast-container {
            position: fixed !important;
            bottom: 70px !important;
            left: 16px !important;
            right: 16px !important;
        }
    }

    /* Hide cursor effects on touch devices */
    @media (hover: none) {
        .card-custom:hover,
        .stat-card:hover {
            transform: none !important;
            box-shadow: none !important;
        }
    }

    /* Dark mode auto-detect for new browsers */
    @media (prefers-color-scheme: light) {
        body:not(.dark-mode):not(.dark) {
            /* Allow light mode if the site supports it */
        }
    }

    /* Reduced motion support */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            transition-duration: 0.01ms !important;
        }
    }

    /* Large screen optimization */
    @media (min-width: 1920px) {
        .container, .container-fluid {
            max-width: 1600px;
        }

        .main-content .container-fluid {
            max-width: 1400px;
        }
    }

    /* Landscape phone adjustments */
    @media (max-width: 768px) and (orientation: landscape) {
        .sidebar {
            width: 200px;
        }

        .topbar {
            padding: 8px 16px;
        }

        .page-content {
            padding: 12px;
        }

        .stat-card {
            padding: 14px;
        }
    }

    /* Print support */
    @media print {
        .sidebar, .topbar, .mobile-bottom-nav, .pwa-install-prompt {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }
    }

    /* Pull-to-refresh disabled (prevents accidental refresh) */
    body {
        overscroll-behavior-y: contain;
    }
</style>
