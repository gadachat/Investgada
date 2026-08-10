<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials._seo-meta')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
        :root {
            --purple-1: #6366f1; --purple-2: #7c3aed; --purple-3: #a855f7;
            --blue-1: #3b82f6; --blue-2: #2563eb;
            --bg-dark: #0f172a; --bg-sidebar: #111827; --bg-card: #1e293b;
            --bg-input: #0f172a;
            --text: #e2e8f0; --text-bright: #fff; --text-muted: #94a3b8; --text-dim: #64748b;
            --border: #334155;
            --green: #10b981; --green-bg: rgba(16,185,129,0.1);
            --red: #ef4444; --red-bg: rgba(239,68,68,0.1);
            --yellow: #f59e0b; --yellow-bg: rgba(245,158,11,0.1);
            --gradient-primary: linear-gradient(135deg, #6366f1 0%, #7c3aed 50%, #a855f7 100%);
            --gradient-sidebar: linear-gradient(180deg, #111827 0%, #1e1b3a 100%);
            --shadow-glow: 0 0 20px rgba(99,102,241,0.15);
        }
        body { background: var(--bg-dark); color: var(--text); margin: 0; min-height: 100vh; }

        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 260px; background: var(--gradient-sidebar); border-right: 1px solid var(--border); display: flex; flex-direction: column; z-index: 1000; transition: transform 0.3s; }
        .sidebar-brand { padding: 24px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; }
        .sidebar-brand .logo { width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: white; }
        .sidebar-brand h4 { margin: 0; color: var(--text-bright); font-size: 15px; font-weight: 700; }
        .sidebar-brand p { margin: 0; font-size: 10px; color: var(--text-dim); }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 16px 0; }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
        .sidebar-section { padding: 12px 24px 6px; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.2px; color: var(--text-dim); }
        .nav-link-item { display: flex; align-items: center; gap: 12px; padding: 10px 20px; margin: 2px 12px; border-radius: 10px; color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.2s; }
        .nav-link-item:hover { background: rgba(99,102,241,0.1); color: var(--text-bright); transform: translateX(2px); }
        .nav-link-item.active { background: var(--gradient-primary); color: white; box-shadow: 0 4px 12px rgba(99,102,241,0.4); }
        .nav-link-item i { width: 20px; text-align: center; font-size: 15px; }
        .nav-badge { margin-left: auto; background: var(--red); color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; font-weight: 700; }
        .nav-badge.yellow { background: var(--yellow); }

        .main-content { margin-left: 260px; min-height: 100vh; }
        .topbar { position: sticky; top: 0; z-index: 999; background: rgba(15,23,42,0.85); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
        .topbar-left, .topbar-right { display: flex; align-items: center; gap: 16px; }
        .menu-toggle { display: none; background: none; border: none; color: var(--text); font-size: 20px; cursor: pointer; }
        .topbar-title { font-size: 18px; font-weight: 600; color: var(--text-bright); }
        .icon-btn { width: 38px; height: 38px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-muted); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; position: relative; }
        .icon-btn:hover { border-color: var(--purple-1); color: var(--purple-3); }
        .icon-btn .dot { position: absolute; top: 8px; right: 8px; width: 7px; height: 7px; background: var(--red); border-radius: 50%; }
        .user-menu { display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 4px 8px; border-radius: 10px; }
        .user-menu:hover { background: var(--bg-card); }
        .user-avatar { width: 36px; height: 36px; border-radius: 10px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 14px; }
        .user-info h6 { margin: 0; font-size: 13px; font-weight: 600; color: var(--text-bright); }
        .user-info p { margin: 0; font-size: 11px; color: var(--text-dim); }
        .page-content { padding: 24px; }

        .card-custom { background: linear-gradient(135deg, #1e293b 0%, #243049 100%); border: 1px solid var(--border); border-radius: 16px; padding: 20px; transition: all 0.3s; }
        .card-custom:hover { border-color: rgba(99,102,241,0.3); box-shadow: var(--shadow-glow); }
        .stat-card { background: linear-gradient(135deg, #1e293b 0%, #243049 100%); border: 1px solid var(--border); border-radius: 16px; padding: 22px; position: relative; overflow: hidden; transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.3); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--gradient-primary); opacity: 0.8; }
        .stat-card .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 14px; }
        .stat-icon.purple { background: rgba(99,102,241,0.15); color: var(--purple-3); }
        .stat-icon.blue { background: rgba(59,130,246,0.15); color: var(--blue-1); }
        .stat-icon.green { background: var(--green-bg); color: var(--green); }
        .stat-icon.yellow { background: var(--yellow-bg); color: var(--yellow); }
        .stat-icon.red { background: var(--red-bg); color: var(--red); }
        .stat-card .stat-label { font-size: 12px; font-weight: 500; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .stat-card .stat-value { font-size: 26px; font-weight: 700; color: var(--text-bright); margin-bottom: 6px; }
        .stat-card .stat-sub { font-size: 12px; }
        .stat-card .stat-sub.up { color: var(--green); }
        .stat-card .stat-sub.down { color: var(--red); }

        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .section-header h5 { margin: 0; font-size: 16px; font-weight: 600; color: var(--text-bright); }
        .badge-custom { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 6px; }
        .badge-up { background: var(--green-bg); color: var(--green); }
        .badge-down { background: var(--red-bg); color: var(--red); }
        .badge-pending { background: var(--yellow-bg); color: var(--yellow); }
        .badge-info { background: rgba(59,130,246,0.15); color: var(--blue-1); }
        .badge-purple { background: rgba(99,102,241,0.15); color: var(--purple-3); }

        .btn-gradient { background: var(--gradient-primary); color: white; border: none; border-radius: 10px; padding: 10px 20px; font-size: 13px; font-weight: 600; transition: all 0.2s; }
        .btn-gradient:hover { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,0.3); color: white; }
        .btn-outline-custom { background: transparent; border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 10px 20px; font-size: 13px; font-weight: 500; transition: all 0.2s; }
        .btn-outline-custom:hover { border-color: var(--purple-1); color: var(--purple-3); }

        .table-custom { width: 100%; }
        .table-custom th { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim); padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--border); }
        .table-custom td { padding: 12px; font-size: 13px; color: var(--text); border-bottom: 1px solid rgba(51,65,85,0.4); }
        .table-custom tr:last-child td { border-bottom: none; }

        .form-control { background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 10px 14px; font-size: 14px; }
        .form-control:focus { background: var(--bg-input); border-color: var(--purple-1); color: var(--text); box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }

        .toggle-switch { position: relative; width: 44px; height: 24px; display: inline-block; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: var(--border); border-radius: 24px; transition: 0.3s; }
        .toggle-slider::before { position: absolute; content: ''; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s; }
        .toggle-switch input:checked + .toggle-slider { background: var(--gradient-primary); }
        .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }

        .fade-in { animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-260px); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .menu-toggle { display: block; }
        }

        @media (max-width: 768px) {
            .page-content, .main-content { padding: 14px; }
            .stat-card .stat-value { font-size: 18px; }
            .stat-card { padding: 12px; }
        }

        @media (max-width: 576px) {
            .stat-card .stat-value { font-size: 16px; }
            .stat-card .stat-label { font-size: 10px; }
            .page-content, .main-content { padding: 10px; }
            .card-custom { padding: 14px 12px !important; border-radius: 10px; }
            table { font-size: 12px; }
            .topbar-title { font-size: 14px; }
            .search-box { display: none; }
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

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="logo">
                    @php $siteLogo = \App\Models\Setting::get('logo', ''); @endphp
                    @if($siteLogo)
                        <img src="{{ asset($siteLogo) }}" alt="Logo" style="max-height: 28px; max-width: 100%; object-fit: contain;">
                    @else
                        <i class="fas fa-shield-alt"></i>
                    @endif
                </div>
            <div>
                <h4>Admin Panel</h4>
                <p>APTrades Platform</p>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-link-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> User Management
            </a>
            <div class="sidebar-section">Financial</div>
            <a href="{{ route('admin.deposits.index') }}" class="nav-link-item {{ request()->routeIs('admin.deposits.*') ? 'active' : '' }}">
                <i class="fas fa-arrow-down"></i> Deposits
                @if($pendingDeposits ?? 0 > 0)<span class="nav-badge">{{ $pendingDeposits }}</span>@endif
            </a>
            <a href="{{ route('admin.withdrawals.index') }}" class="nav-link-item {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
                <i class="fas fa-arrow-up"></i> Withdrawals
                @if($pendingWithdrawals ?? 0 > 0)<span class="nav-badge">{{ $pendingWithdrawals }}</span>@endif
            </a>
            <a href="{{ route('admin.packages.index') }}" class="nav-link-item {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i> Packages
            </a>

            <a href="{{ route('admin.kyc.index') }}" class="nav-link-item {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}">
                <i class="fas fa-id-card"></i> KYC Review
                @if($pendingKyc ?? 0 > 0)<span class="nav-badge">{{ $pendingKyc }}</span>@endif
            </a>

            <div class="sidebar-section">System</div>

            <a href="{{ route('admin.signals.index') }}" class="nav-link-item {{ request()->routeIs('admin.signals.*') ? 'active' : '' }}">
                <i class="fas fa-broadcast-tower"></i> Signals
            </a>
            <a href="{{ route('admin.reports.index') }}" class="nav-link-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> Reports & Analytics
            </a>
            <a href="{{ route('admin.notifications.index') }}" class="nav-link-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                <i class="fas fa-bullhorn"></i> Notifications
            </a>
            <a href="{{ route('admin.profit-share.index') }}" class="nav-link-item {{ request()->routeIs('admin.profit-share.*') ? 'active' : '' }}">
                <i class="fas fa-coins"></i> Profit Sharing
            </a>
            <a href="{{ route('admin.leadership.index') }}" class="nav-link-item {{ request()->routeIs('admin.leadership.*') ? 'active' : '' }}">
                <i class="fas fa-crown" style="color: #f59e0b;"></i> Leadership Bonus
            </a>
            <a href="{{ route('admin.announcements.index') }}" class="nav-link-item {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>
            <a href="{{ route('admin.master-traders.index') }}" class="nav-link-item {{ request()->routeIs('admin.master-traders.*') ? 'active' : '' }}">
                <i class="fas fa-user-tie"></i> Master Traders
            </a>
            <a href="{{ route('admin.audit-logs.index') }}" class="nav-link-item {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                <i class="fas fa-history"></i> Audit Logs
            </a>

            <div class="sidebar-section">Settings</div>
            <a href="{{ route('admin.settings.features') }}" class="nav-link-item {{ request()->routeIs('admin.settings.features') ? 'active' : '' }}">
                <i class="fas fa-toggle-on"></i> Feature Manager
            </a>
            <a href="{{ route('admin.settings.platform') }}" class="nav-link-item {{ request()->routeIs('admin.settings.platform') ? 'active' : '' }}">
                <i class="fas fa-cog"></i> Platform Settings
            </a>
            <a href="{{ route('admin.settings.ranks') }}" class="nav-link-item {{ request()->routeIs('admin.settings.ranks') ? 'active' : '' }}">
                <i class="fas fa-medal"></i> Ranks
            </a>
            <a href="{{ route('admin.settings.addresses') }}" class="nav-link-item {{ request()->routeIs('admin.settings.addresses') ? 'active' : '' }}">
                <i class="fas fa-qrcode"></i> Deposit Addresses
            </a>
            <a href="{{ route('admin.settings.site') }}" class="nav-link-item {{ request()->routeIs('admin.settings.site') ? 'active' : '' }}">
                <i class="fas fa-globe"></i> Site Settings
            </a>
            <a href="{{ route('admin.chat-widget.index') }}" class="nav-link-item {{ request()->routeIs('admin.chat-widget.*') ? 'active' : '' }}">
                <i class="fas fa-comments"></i> Chat Widget
            </a>
            <a href="{{ route('admin.landing.edit') }}" class="nav-link-item {{ request()->routeIs('admin.landing.*') ? 'active' : '' }}">
                <i class="fas fa-pen"></i> Landing Page
            </a>
            <a href="{{ route('admin.cron') }}" class="nav-link-item {{ request()->routeIs('admin.cron') ? 'active' : '' }}">
                <i class="fas fa-clock"></i> Cron Jobs
            </a>
            <a href="{{ route('admin.trading.index') }}" class="nav-link-item {{ request()->routeIs('admin.trading.*') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> Trading
            </a>
            <a href="{{ route('admin.autotrade.index') }}" class="nav-link-item {{ request()->routeIs('admin.autotrade.*') ? 'active' : '' }}">
                <i class="fas fa-robot"></i> Auto Trading
            </a>

            <div class="sidebar-section">Security & Support</div>
            <a href="{{ route('admin.security.index') }}" class="nav-link-item {{ request()->routeIs('admin.security.index') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i> Security Dashboard
            </a>
            <a href="{{ route('admin.security.audit-trail') }}" class="nav-link-item {{ request()->routeIs('admin.security.audit-trail') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i> Audit Trail
            </a>
            <a href="{{ route('admin.security.ip-management') }}" class="nav-link-item {{ request()->routeIs('admin.security.ip-management') ? 'active' : '' }}">
                <i class="fas fa-ban"></i> IP Management
            </a>
            <a href="{{ route('admin.security.sessions') }}" class="nav-link-item {{ request()->routeIs('admin.security.sessions') ? 'active' : '' }}">
                <i class="fas fa-laptop"></i> Active Sessions
            </a>
            <a href="{{ route('admin.security.settings') }}" class="nav-link-item {{ request()->routeIs('admin.security.settings') ? 'active' : '' }}">
                <i class="fas fa-lock"></i> Security Settings
            </a>

            <div class="sidebar-section">Support</div>
            <a href="{{ route('admin.support.index') }}" class="nav-link-item {{ request()->routeIs('admin.support.*') ? 'active' : '' }}">

                    <!-- Fund Management -->
                    <a href="{{ route('admin.funds.index') }}" class="sidebar-link {{ request()->routeIs('admin.funds.*') ? 'active' : '' }}">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Fund Management</span>
                    </a>
                <i class="fas fa-headset"></i> Ticket Inbox
                @php $openTickets = \App\Models\SupportTicket::whereIn('status', ['open', 'pending', 'answered'])->count(); @endphp
                @if($openTickets > 0)
                <span class="nav-badge">{{ $openTickets }}</span>
                @endif
            </a>
        </nav>
        <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
            <a href="{{ route('dashboard.index') }}" class="btn-outline-custom" style="display: block; text-align: center; text-decoration: none; font-size: 12px;">
                <i class="fas fa-arrow-left"></i> Back to User Panel
            </a>
        </div>
    </aside>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fas fa-bars"></i></button>
                <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="topbar-right">
                <button class="icon-btn"><i class="fas fa-bell"></i><span class="dot"></span></button>
                <div class="user-menu">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="user-info">
                        <h6>{{ auth()->user()->name }}</h6>
                        <p>Administrator</p>
                    </div>
                </div>
            </div>
        </header>
        <div class="page-content">
            @if(session('success'))
            <div style="background: var(--green-bg); border: 1px solid rgba(16,185,129,0.3); color: var(--green); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div style="background: var(--red-bg); border: 1px solid rgba(239,68,68,0.3); color: var(--red); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
            @endif
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
@include("partials._tawk-widget")
    <script>
    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.menu-toggle');
        if (window.innerWidth <= 1024 && sidebar && sidebar.classList.contains('open')) {
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });
    </script>
</body>
</html>
