<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install APTrades — Setup Wizard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg: #0f1226;
            --bg2: #131630;
            --card: #1a1d3a;
            --card2: #22254a;
            --border: #2a2d52;
            --text: #e2e8f0;
            --text-bright: #f8fafc;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --purple1: #6366f1;
            --purple2: #7c3aed;
            --purple3: #a855f7;
            --green: #10b981;
            --red: #ef4444;
            --amber: #f59e0b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        .installer-wrapper {
            max-width: 880px; margin: 0 auto; padding: 40px 20px 80px;
        }

        /* Header */
        .installer-header { text-align: center; margin-bottom: 40px; }
        .installer-logo {
            width: 64px; height: 64px; border-radius: 16px;
            background: linear-gradient(135deg, var(--purple1), var(--purple2));
            display: inline-flex; align-items: center; justify-content: center;
            color: white; font-size: 28px; margin-bottom: 16px;
        }
        .installer-header h1 { color: var(--text-bright); font-weight: 800; font-size: 28px; }
        .installer-header p { color: var(--text-muted); font-size: 14px; }

        /* Step Progress */
        .step-progress { display: flex; justify-content: center; gap: 0; margin-bottom: 40px; }
        .step-item { display: flex; align-items: center; gap: 8px; }
        .step-circle {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px;
            background: var(--card); border: 2px solid var(--border); color: var(--text-dim);
            transition: all 0.3s;
        }
        .step-item.active .step-circle {
            background: linear-gradient(135deg, var(--purple1), var(--purple2));
            border-color: var(--purple1); color: white;
            box-shadow: 0 0 20px rgba(99,102,241,0.4);
        }
        .step-item.completed .step-circle {
            background: var(--green); border-color: var(--green); color: white;
        }
        .step-item.active .step-label { color: var(--text-bright); font-weight: 600; }
        .step-item.completed .step-label { color: var(--green); }
        .step-label { font-size: 13px; color: var(--text-dim); transition: all 0.3s; }
        .step-connector { width: 40px; height: 2px; background: var(--border); margin: 0 12px; }
        .step-item.completed + .step-connector { background: var(--green); }
        @media (max-width: 600px) { .step-label { display: none; } .step-connector { width: 24px; } }

        /* Card */
        .installer-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 16px; padding: 32px;
        }
        h2, h3 { color: var(--text-bright); font-weight: 700; }
        h2 { font-size: 22px; }
        h3 { font-size: 20px; }
        .text-muted { color: var(--text-muted) !important; }
        .text-primary { color: var(--purple1) !important; }
        .text-success { color: var(--green) !important; }
        .text-danger { color: var(--red) !important; }
        .text-warning { color: var(--amber) !important; }

        /* Form */
        .card {
            background: var(--card); border: 1px solid var(--border); border-radius: 12px;
        }
        .card-header { background: var(--bg2); border-bottom: 1px solid var(--border); padding: 14px 20px; border-radius: 12px 12px 0 0 !important; font-weight: 600; color: var(--text-bright); font-size: 14px; }
        .card-body { padding: 24px 20px; }
        .form-label { color: var(--text); font-size: 13px; }
        .form-control, .form-select {
            background: var(--bg2); border: 1px solid var(--border); color: var(--text-bright);
            padding: 10px 14px; border-radius: 8px; font-size: 14px;
        }
        .form-control:focus, .form-select:focus {
            background: var(--bg2); border-color: var(--purple1); color: var(--text-bright);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }
        .form-control::placeholder { color: var(--text-dim); }
        small { font-size: 12px; }
        code { background: var(--bg2); padding: 2px 6px; border-radius: 4px; font-size: 12px; color: var(--purple3); }

        /* Buttons */
        .btn { border-radius: 10px; font-weight: 600; font-size: 14px; padding: 10px 24px; transition: all 0.2s; }
        .btn-primary { background: linear-gradient(135deg, var(--purple1), var(--purple2)); border: none; color: white; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(99,102,241,0.4); }
        .btn-success { background: linear-gradient(135deg, var(--green), #059669); border: none; }
        .btn-success:hover { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(16,185,129,0.4); }
        .btn-outline-secondary { background: transparent; border: 1px solid var(--border); color: var(--text-muted); }
        .btn-outline-secondary:hover { background: var(--card2); color: var(--text-bright); border-color: var(--purple1); }
        .btn-outline-primary { background: transparent; border: 1px solid var(--purple1); color: var(--purple1); }
        .btn-outline-primary:hover { background: var(--purple1); color: white; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Alerts */
        .alert { border-radius: 10px; border: 1px solid; font-size: 14px; }
        .alert-danger { background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.3); color: #fca5a5; }
        .alert-info { background: rgba(99,102,241,0.08); border-color: rgba(99,102,241,0.2); color: var(--text); }
        .alert-warning { background: rgba(245,158,11,0.08); border-color: rgba(245,158,11,0.2); color: #fcd34d; }
        .alert-success { background: rgba(16,185,129,0.08); border-color: rgba(16,185,129,0.2); color: #6ee7b7; }
        .alert-dismissible .btn-close { filter: invert(1); opacity: 0.5; }
        .border-warning { border-color: rgba(245,158,11,0.3) !important; }

        /* Requirements */
        .req-category-title { color: var(--text-bright); font-size: 14px; font-weight: 700; margin-bottom: 12px; }
        .req-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px; }
        .req-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px; background: var(--bg2); border: 1px solid var(--border); }
        .req-passed { border-color: rgba(16,185,129,0.2); }
        .req-failed { border-color: rgba(239,68,68,0.3); }
        .req-check i { font-size: 18px; }
        .req-passed .req-check i { color: var(--green); }
        .req-failed .req-check i { color: var(--red); }
        .req-label { font-size: 13px; font-weight: 600; color: var(--text-bright); }
        .req-current { font-size: 11px; color: var(--text-dim); }

        /* Status Banner */
        .status-banner { padding: 16px 20px; border-radius: 10px; font-weight: 600; font-size: 14px; display: flex; align-items: center; }
        .status-ok { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #6ee7b7; }
        .status-fail { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; }

        /* Hosting Hints */
        .hosting-hints { display: flex; flex-direction: column; gap: 8px; }
        .hint-card { background: var(--bg2); border: 1px solid var(--border); border-left: 3px solid var(--purple1); border-radius: 8px; padding: 12px 16px; }
        .hint-provider { font-size: 13px; font-weight: 700; color: var(--purple1); margin-bottom: 4px; }
        .hint-text { font-size: 13px; color: var(--text-muted); }

        /* Install Features */
        .install-feature-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: var(--bg2); border-radius: 8px; }
        .install-feature-item i { font-size: 18px; flex-shrink: 0; }
        .install-feature-item .fw-semibold { color: var(--text-bright); font-size: 13px; }

        /* Install Log */
        .install-log { padding: 8px 0; }
        .log-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px 20px; border-bottom: 1px solid var(--border); }
        .log-item:last-child { border-bottom: none; }
        .log-icon { flex-shrink: 0; padding-top: 2px; }
        .log-icon i { font-size: 18px; }
        .log-label { font-weight: 600; color: var(--text-bright); font-size: 14px; }
        .log-detail { color: var(--text-muted); display: block; margin-top: 2px; }
        .log-error { color: var(--red); display: block; margin-top: 2px; }

        /* Result Icon */
        .install-result-icon {
            width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 16px;
            display: flex; align-items: center; justify-content: center; font-size: 36px;
        }
        .install-success { background: rgba(16,185,129,0.1); color: var(--green); }
        .install-fail { background: rgba(239,68,68,0.1); color: var(--red); }

        /* Next Steps */
        .next-steps { padding-left: 20px; }
        .next-steps li { margin-bottom: 16px; color: var(--text-bright); font-size: 14px; }
        .next-steps li .text-muted { font-size: 12px; }
        .next-steps li .btn { margin-top: 6px; }

        /* Input group fix */
        .input-group .btn-outline-secondary { border: 1px solid var(--border); }
        .input-group .btn-outline-secondary:hover { background: var(--card2); }

        @media (max-width: 576px) {
            .installer-card, .card { padding: 16px 12px !important; border-radius: 10px; }
            h2 { font-size: 18px; }
            p { font-size: 12px; }
            .form-control { font-size: 13px; }
            .btn { font-size: 13px; padding: 10px; }
        }
    </style>
@include('partials._pwa')
</head>
<body>
    <div class="installer-wrapper">
        <div class="installer-header">
            <div class="installer-logo"><i class="fas fa-shield-alt"></i></div>
            <h1>APTrades Installer</h1>
            <p>Automated setup wizard for shared hosting deployment</p>
        </div>

        <!-- Step Progress Bar -->
        <div class="step-progress">
            <div class="step-item {{ (int)$step >= 1 ? 'completed' : '' }} {{ $step == 1 ? 'active' : '' }}">
                <div class="step-circle">{{ (int)$step > 1 ? '<i class="fas fa-check"></i>' : '1' }}</div>
                <span class="step-label">Requirements</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-item {{ (int)$step > 2 ? 'completed' : '' }} {{ $step == 2 ? 'active' : '' }}">
                <div class="step-circle">{{ (int)$step > 2 ? '<i class="fas fa-check"></i>' : '2' }}</div>
                <span class="step-label">Database</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-item {{ (int)$step > 3 ? 'completed' : '' }} {{ $step == 3 ? 'active' : '' }}">
                <div class="step-circle">{{ (int)$step > 3 ? '<i class="fas fa-check"></i>' : '3' }}</div>
                <span class="step-label">Admin Account</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-item {{ $step == 4 ? 'active' : '' }}">
                <div class="step-circle">4</div>
                <span class="step-label">Complete</span>
            </div>
        </div>

        <!-- Content -->
        <div class="installer-card">
            @yield('content')
        </div>

        <div class="text-center mt-4">
            <small class="text-muted">APTrades Installer v1.0 · Built for Namecheap & Ultahost shared hosting</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
