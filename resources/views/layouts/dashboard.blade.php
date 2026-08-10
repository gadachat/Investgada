<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials._seo-meta')

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

        :root {
            --purple-1: #6366f1;
            --purple-2: #7c3aed;
            --purple-3: #a855f7;
            --blue-1: #3b82f6;
            --blue-2: #2563eb;
            --blue-3: #1d4ed8;
            --bg-dark: #0f172a;
            --bg-sidebar: #111827;
            --bg-card: #1e293b;
            --bg-card-2: #243049;
            --bg-input: #0f172a;
            --text: #e2e8f0;
            --text-bright: #ffffff;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --border: #334155;
            --green: #10b981;
            --green-bg: rgba(16, 185, 129, 0.1);
            --red: #ef4444;
            --red-bg: rgba(239, 68, 68, 0.1);
            --yellow: #f59e0b;
            --yellow-bg: rgba(245, 158, 11, 0.1);
            --gradient-primary: linear-gradient(135deg, #6366f1 0%, #7c3aed 50%, #a855f7 100%);
            --gradient-blue: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            --gradient-purple: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            --gradient-card: linear-gradient(135deg, #1e293b 0%, #243049 100%);
            --gradient-sidebar: linear-gradient(180deg, #111827 0%, #1e1b3a 100%);
            --shadow-glow: 0 0 20px rgba(99, 102, 241, 0.15);
        }

        body {
            background: var(--bg-dark);
            color: var(--text);
            margin: 0;
            min-height: 100vh;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 260px;
            background: var(--gradient-sidebar);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand .logo {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .sidebar-brand h4 {
            margin: 0;
            color: var(--text-bright);
            font-size: 16px;
            font-weight: 700;
        }

        .sidebar-brand p {
            margin: 0;
            font-size: 11px;
            color: var(--text-dim);
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 16px 0;
        }

        .sidebar-section {
            padding: 12px 24px 6px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--text-dim);
        }

        .nav-link-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            margin: 2px 12px;
            border-radius: 10px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            position: relative;
        }

        .nav-link-item:hover {
            background: rgba(99, 102, 241, 0.1);
            color: var(--text-bright);
            transform: translateX(2px);
        }

        .nav-link-item.active {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .nav-link-item i {
            width: 20px;
            text-align: center;
            font-size: 15px;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--red);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 700;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            transition: margin 0.3s ease;
        }

        /* ========== TOPBAR ========== */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 999;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar-left { display: flex; align-items: center; gap: 16px; }
        .topbar-right { display: flex; align-items: center; gap: 16px; }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text);
            font-size: 20px;
            cursor: pointer;
        }

        .topbar-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-bright);
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            background: var(--bg-input);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 10px;
            padding: 8px 12px 8px 36px;
            font-size: 13px;
            width: 240px;
            outline: none;
        }

        .search-box input:focus {
            border-color: var(--purple-1);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-dim);
            font-size: 13px;
        }

        .icon-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--bg-card);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .icon-btn:hover {
            border-color: var(--purple-1);
            color: var(--purple-3);
        }

        .icon-btn .dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 7px;
            height: 7px;
            background: var(--red);
            border-radius: 50%;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .user-menu:hover { background: var(--bg-card); }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
            font-size: 14px;
        }

        .user-info h6 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-bright);
        }

        .user-info p {
            margin: 0;
            font-size: 11px;
            color: var(--text-dim);
        }

        /* ========== PAGE CONTENT ========== */
        .page-content { padding: 24px; }

        /* ========== CARDS ========== */
        .card-custom {
            background: var(--gradient-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            transition: all 0.3s;
        }

        .card-custom:hover {
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: var(--shadow-glow);
        }

        .stat-card {
            background: var(--gradient-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
            opacity: 0.8;
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 14px;
        }

        .stat-card .stat-icon.purple { background: rgba(99, 102, 241, 0.15); color: var(--purple-3); }
        .stat-card .stat-icon.blue   { background: rgba(59, 130, 246, 0.15); color: var(--blue-1); }
        .stat-card .stat-icon.green  { background: var(--green-bg); color: var(--green); }
        .stat-card .stat-icon.yellow { background: var(--yellow-bg); color: var(--yellow); }
        .stat-card .stat-icon.red    { background: var(--red-bg); color: var(--red); }

        .stat-card .stat-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .stat-card .stat-value {
            font-size: 26px;
            font-weight: 700;
            color: var(--text-bright);
            margin-bottom: 6px;
        }

        .stat-card .stat-sub {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-card .stat-sub.up { color: var(--green); }
        .stat-card .stat-sub.down { color: var(--red); }

        /* ========== LIVE PRICE TICKER ========== */
        .price-ticker {
            background: var(--gradient-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }

        .ticker-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
        }

        .ticker-header h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-bright);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--green-bg);
            color: var(--green);
            font-size: 10px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
        }

        .live-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--green);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .ticker-tabs {
            display: flex;
            gap: 2px;
        }

        .ticker-tab {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: none;
        }

        .ticker-tab:hover { color: var(--text-bright); }
        .ticker-tab.active {
            background: var(--gradient-primary);
            color: white;
        }

        .ticker-body { padding: 12px; }

        .price-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-radius: 10px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .price-item:hover { background: rgba(99, 102, 241, 0.08); }

        .price-item-left { display: flex; align-items: center; gap: 12px; }

        .coin-badge {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: white;
        }

        .price-item-info h6 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-bright);
        }

        .price-item-info p {
            margin: 0;
            font-size: 11px;
            color: var(--text-dim);
        }

        .price-item-right { text-align: right; }

        .price-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-bright);
            font-variant-numeric: tabular-nums;
        }

        .price-change {
            font-size: 11px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .price-change.up { color: var(--green); }
        .price-change.down { color: var(--red); }

        .mini-spark { width: 50px; height: 24px; }

        /* ========== SECTION HEADERS ========== */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .section-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-bright);
        }

        .section-header .section-link {
            font-size: 12px;
            color: var(--purple-3);
            text-decoration: none;
            font-weight: 500;
        }

        .section-header .section-link:hover { text-decoration: underline; }

        /* ========== WALLET CARDS ========== */
        .wallet-card {
            background: var(--gradient-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.2s;
        }

        .wallet-card:hover {
            border-color: rgba(99, 102, 241, 0.3);
        }

        .wallet-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
        }

        .wallet-card .wallet-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .wallet-card .wallet-amount {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-bright);
        }

        /* ========== TABLES ========== */
        .table-custom {
            width: 100%;
        }

        .table-custom th {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-dim);
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .table-custom td {
            padding: 12px;
            font-size: 13px;
            color: var(--text);
            border-bottom: 1px solid rgba(51, 65, 85, 0.4);
        }

        .table-custom tr:last-child td { border-bottom: none; }

        .badge-custom {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 6px;
        }

        .badge-up { background: var(--green-bg); color: var(--green); }
        .badge-down { background: var(--red-bg); color: var(--red); }
        .badge-pending { background: var(--yellow-bg); color: var(--yellow); }
        .badge-info { background: rgba(59, 130, 246, 0.15); color: var(--blue-1); }
        .badge-purple { background: rgba(99, 102, 241, 0.15); color: var(--purple-3); }

        /* ========== BINARY TREE WIDGET ========== */
        .binary-widget {
            display: flex;
            gap: 12px;
        }

        .binary-leg {
            flex: 1;
            text-align: center;
            padding: 14px;
            border-radius: 10px;
        }

        .binary-leg.left {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(99, 102, 241, 0.05));
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .binary-leg.right {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(168, 85, 247, 0.05));
            border: 1px solid rgba(124, 58, 237, 0.2);
        }

        .binary-leg .leg-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .binary-leg .leg-count {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-bright);
        }

        .binary-leg .leg-volume {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* ========== BUTTONS ========== */
        .btn-gradient {
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-gradient:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.3);
            color: white;
        }

        .btn-outline-custom {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-outline-custom:hover {
            border-color: var(--purple-1);
            color: var(--purple-3);
        }

        /* ========== SCROLLBAR ========== */
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-260px); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .menu-toggle { display: block; }
        }

        @media (max-width: 768px) {
            .search-box input { width: 160px; }
            .user-info { display: none; }
            .page-content { padding: 16px; }
            .topbar-title { font-size: 15px; }
        }

        @media (max-width: 576px) {
            .search-box { display: none; }
            .stat-card .stat-value { font-size: 20px; }
        }

        .fade-in { animation: fadeIn 0.4s ease; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ========== MOBILE SAFETY NET ========== */
        @media (max-width: 576px) {
            .modal-dialog, .modal-content { max-width: 95vw !important; margin: 8px auto !important; }
            [style*="max-width"]:not([style*="100%"]) { max-width: 100% !important; }
            [style*="width:"]:not([style*="100%"]) { width: 100% !important; }
            img { max-width: 100% !important; height: auto !important; }
            .btn { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .dropdown-menu { min-width: auto; max-width: 280px; }
        }
    </style>

    @stack('styles')
@include('partials._pwa')
@include('partials._mobile-styles')
</head>
<body>

    <!-- ========== SIDEBAR ========== -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
        @php $siteLogo = \App\Models\Setting::get('logo_dark', \App\Models\Setting::get('logo', '')); @endphp
        @if($siteLogo)
            <img src="{{ asset($siteLogo) }}" alt="Logo" style="max-height: 28px; max-width: 36px; border-radius: 6px; object-fit: contain;">
        @endif
            <div class="logo"><i class="fas fa-chart-line"></i></div>
            <div>
                <h4>{{ \App\Models\Setting::get('platform_name', 'APTrades') }}</h4>
                <p>Investment Platform</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">Main</div>
            <a href="{{ route('dashboard.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="{{ route('dashboard.trade.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.trade.*') ? 'active' : '' }}">
                <i class="fas fa-exchange-alt"></i> Markets
                <span class="live-badge">LIVE</span>
            </a>
            <a href="{{ route('dashboard.packages.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.packages.*') ? 'active' : '' }}">
                <i class="fas fa-coins"></i> Invest
            </a>
            <a href="{{ route('dashboard.investments.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.investments.*') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i> My Investments
            </a>
            <a href="{{ route('dashboard.wallet.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.wallet.*') ? 'active' : '' }}">
                <i class="fas fa-wallet"></i> Wallet
            </a>

            <div class="sidebar-section">Transactions</div>
            <a href="{{ route('dashboard.deposit.create') }}" class="nav-link-item {{ request()->routeIs('dashboard.deposit.*') ? 'active' : '' }}">
                <i class="fas fa-arrow-down"></i> Deposit
            </a>
            <a href="{{ route('dashboard.withdrawal.create') }}" class="nav-link-item {{ request()->routeIs('dashboard.withdrawal.*') ? 'active' : '' }}">
                <i class="fas fa-arrow-up"></i> Withdraw
            </a>
            <a href="{{ route('dashboard.history.deposits') }}" class="nav-link-item {{ request()->routeIs('dashboard.history.deposits*') ? 'active' : '' }}">
                <i class="fas fa-arrow-down"></i> Deposit History
            </a>
            <a href="{{ route('dashboard.history.withdrawals') }}" class="nav-link-item {{ request()->routeIs('dashboard.history.withdrawals*') ? 'active' : '' }}">
                <i class="fas fa-arrow-up"></i> Withdrawal History
            </a>
            <a href="{{ route('dashboard.history.commissions') }}" class="nav-link-item {{ request()->routeIs('dashboard.history.commissions*') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-usd"></i> Commission History
            </a>

            <div class="sidebar-section">Network</div>
            <a href="{{ route('dashboard.binary.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.binary.*') ? 'active' : '' }}">
                <i class="fas fa-sitemap"></i> Binary Tree
            </a>
            <a href="{{ route('dashboard.leadership.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.leadership.*') ? 'active' : '' }}">
                <i class="fas fa-crown" style="color: #f59e0b;"></i> Leadership Bonus
            </a>
            <a href="{{ route('dashboard.referral.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.referral.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Referrals
                @if($directReferrals ?? 0 > 0)
                <span class="nav-badge">{{ $directReferrals }}</span>
                @endif
            </a>
                    <!-- Rank Advancement -->
                    <a href="{{ route('dashboard.rank.index') }}" class="sidebar-link {{ request()->routeIs('dashboard.rank.*') ? 'active' : '' }}">
                        <i class="fas fa-trophy"></i>
                        <span>Rank</span>
                    </a>
            <a href="{{ route('dashboard.profit-share.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.profit-share.*') ? 'active' : '' }}">
                <i class="fas fa-gift"></i> Profit Sharing
            </a>
            <a href="{{ route('dashboard.autotrade.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.autotrade.*') ? 'active' : '' }}">
                <i class="fas fa-robot"></i> Auto Trade
            </a>


            <a href="{{ route('dashboard.2fa.manage') }}" class="nav-link-item {{ request()->routeIs('dashboard.2fa.*') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i> 2FA Security
            </a>

            <a href="{{ route('dashboard.signals.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.signals.*') ? 'active' : '' }}">
                <i class="fas fa-broadcast-tower"></i> Signals
            </a>
            <a href="{{ route('dashboard.copy-trade.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.copy-trade.*') ? 'active' : '' }}">
                <i class="fas fa-copy"></i> Copy Trading
            </a>
            <a href="{{ route('dashboard.activity-log') }}" class="nav-link-item {{ request()->routeIs('dashboard.activity-log') ? 'active' : '' }}">
                <i class="fas fa-history"></i> Activity Log
            </a>
            <a href="{{ route('dashboard.reports.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> Reports
            </a>

            <a href="{{ route('dashboard.kyc.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.kyc.*') ? 'active' : '' }}">
                <i class="fas fa-id-card"></i> KYC Verification
                @if(auth()->user()->kyc_verified)
                <i class="fas fa-check-circle text-success ms-1" style="font-size: 10px;"></i>
                @endif
            </a>

            <div class="sidebar-section">Account</div>
            <a href="{{ route('dashboard.notifications.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.notifications.*') ? 'active' : '' }}">
                <i class="fas fa-bell"></i> Notifications
                @php $unread = \Illuminate\Support\Facades\DB::table('notifications')->where('user_id', auth()->id())->where('is_read', false)->count(); @endphp
                @if($unread > 0)
                <span class="nav-badge" style="background: var(--purple-1);">{{ $unread }}</span>
                @endif
            </a>
            <a href="{{ route('dashboard.funds.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.funds.*') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-usd"></i> Fund Applications
            </a>
            <a href="{{ route('dashboard.support.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.support.*') ? 'active' : '' }}">
                <i class="fas fa-ticket-alt"></i> Support
                @php $openTickets = \App\Models\SupportTicket::where('user_id', auth()->id())->whereIn('status', ['open', 'answered', 'pending'])->count(); @endphp
                @if($openTickets > 0)
                <span class="nav-badge" style="background: var(--purple-1);">{{ $openTickets }}</span>
                @endif
            </a>
            <a href="{{ route('dashboard.profile.index') }}" class="nav-link-item {{ request()->routeIs('dashboard.profile.*') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i> Profile & Settings
            </a>
        </nav>

        <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
            <div class="wallet-card" style="margin-bottom: 10px;">
                <div class="wallet-icon" style="background: var(--gradient-primary); color: white;">
                    <i class="fas fa-bolt"></i>
                </div>
                <div>
                    <div class="wallet-label">Total Balance</div>
                    <div class="wallet-amount">${{ number_format($totalBalance ?? 0, 2) }}</div>
                </div>
            </div>
            <a href="{{ route('dashboard.deposit.create') }}" class="btn-gradient" style="display: block; text-align: center; text-decoration: none;">
                <i class="fas fa-plus-circle"></i> Deposit Now
            </a>
        </div>
    </aside>

    <!-- ========== MAIN CONTENT ========== -->
    <div class="main-content">
        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="topbar-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <button class="icon-btn">
                    <i class="fas fa-bell"></i>
                    <span class="dot"></span>
                </button>
                <button class="icon-btn">
                    <i class="fas fa-envelope"></i>
                </button>
                <div class="user-menu">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="user-info">
                        <h6>{{ auth()->user()->name }}</h6>
                        <p>{{ auth()->user()->role === 'user' ? 'Investor' : 'Admin' }}</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <div class="page-content">
            @if(session('success'))
            <div class="alert" style="background: var(--green-bg); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--green); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
            @endif
            @include('dashboard.partials.announcements')
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
    }

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.menu-toggle');
        if (window.innerWidth <= 1024 && sidebar.classList.contains('open')) {
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });
    </script>
    @stack('scripts')
@include("partials._tawk-widget")
@include('partials._mobile-nav')
@include('partials._install-prompt')
</body>
</html>
