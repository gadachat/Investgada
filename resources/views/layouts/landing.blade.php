<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials._seo-meta')

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2"></script>

    <style>
        :root {
            --bg: #0f1226;
            --bg-2: #131630;
            --card: #1a1d3a;
            --card-2: #22254a;
            --border: #2a2d52;
            --text: #e2e8f0;
            --text-bright: #f8fafc;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --purple-1: #6366f1;
            --purple-2: #7c3aed;
            --purple-3: #a855f7;
            --blue-1: #3b82f6;
            --blue-2: #2563eb;
            --green: #10b981;
            --red: #ef4444;
            --amber: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            overflow-x: hidden;
        }

        a { text-decoration: none; }
        ::selection { background: var(--purple-1); color: white; }

        /* ===== NAVBAR ===== */
        .landing-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            padding: 16px 0; transition: all 0.3s;
            background: rgba(15, 18, 38, 0.85); backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(99, 102, 241, 0.1);
        }
        .landing-nav.scrolled { padding: 10px 0; background: rgba(15, 18, 38, 0.95); }
        .nav-brand { display: flex; align-items: center; gap: 10px; }
        .nav-logo {
            width: 40px; height: 40px; border-radius: 10px;
            background: linear-gradient(135deg, var(--purple-1), var(--purple-2));
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 18px;
        }
        .nav-brand h4 { color: var(--text-bright); font-weight: 700; margin: 0; font-size: 20px; }
        .nav-links { display: flex; gap: 28px; align-items: center; }
        .nav-links a { color: var(--text-muted); font-size: 14px; font-weight: 500; transition: color 0.2s; }
        .nav-links a:hover { color: var(--text-bright); }
        .nav-actions { display: flex; gap: 12px; align-items: center; }
        .btn-login { color: var(--text-bright); padding: 8px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; border: 1px solid var(--border); transition: all 0.2s; }
        .btn-login:hover { background: var(--card); }
        .btn-signup {
            padding: 8px 24px; border-radius: 8px; font-size: 14px; font-weight: 600;
            background: linear-gradient(135deg, var(--purple-1), var(--purple-2));
            color: white; border: none; transition: transform 0.2s;
        }
        .btn-signup:hover { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4); }

        /* ===== MARKET TICKER ===== */
        .market-ticker-bar {
            position: fixed; top: 73px; left: 0; right: 0; z-index: 999;
            background: var(--bg-2); border-bottom: 1px solid var(--border);
            overflow: hidden; padding: 8px 0;
        }
        .ticker-track { display: flex; gap: 40px; white-space: nowrap; will-change: transform; }
        .ticker-item { display: flex; align-items: center; gap: 6px; font-size: 13px; flex-shrink: 0; }
        .ticker-symbol { font-weight: 700; color: var(--text-bright); }
        .ticker-price { color: var(--text); font-weight: 500; }
        .ticker-change { font-size: 12px; font-weight: 600; }
        .text-success { color: var(--green) !important; }
        .text-danger { color: var(--red) !important; }

        /* ===== HERO ===== */
        .hero-section {
            position: relative; padding: 140px 0 100px; overflow: hidden;
            background: linear-gradient(180deg, var(--bg) 0%, var(--bg-2) 100%);
        }
        .hero-bg-animation {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background-image: radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.1) 0%, transparent 50%),
                              radial-gradient(circle at 80% 30%, rgba(168, 85, 247, 0.08) 0%, transparent 50%);
        }
        .hero-glow {
            position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3;
            animation: floatGlow 8s ease-in-out infinite;
        }
        .hero-glow-1 { top: 10%; left: -5%; width: 300px; height: 300px; background: var(--purple-1); }
        .hero-glow-2 { bottom: 20%; right: -5%; width: 400px; height: 400px; background: var(--purple-3); animation-delay: 4s; }
        @keyframes floatGlow { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(20px, -20px); } }

        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 16px; border-radius: 50px;
            background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2);
            font-size: 13px; color: var(--text-bright); font-weight: 500;
        }
        .pulse-dot {
            width: 8px; height: 8px; border-radius: 50%; background: var(--green);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
            70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .hero-title {
            font-size: 48px; font-weight: 800; line-height: 1.1;
            background: linear-gradient(135deg, #f8fafc 0%, #c7d2fe 50%, #a855f7 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero-subtitle { font-size: 18px; color: var(--text-muted); line-height: 1.6; max-width: 520px; }
        .btn-hero-primary {
            padding: 14px 36px; border-radius: 12px; font-size: 16px; font-weight: 600;
            background: linear-gradient(135deg, var(--purple-1), var(--purple-2)); color: white;
            border: none; transition: all 0.3s;
        }
        .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(99, 102, 241, 0.5); color: white; }
        .btn-hero-secondary {
            padding: 14px 36px; border-radius: 12px; font-size: 16px; font-weight: 600;
            background: transparent; color: var(--text-bright); border: 1px solid var(--border);
            transition: all 0.3s;
        }
        .btn-hero-secondary:hover { background: var(--card); border-color: var(--purple-1); color: white; }

        .hero-stat { text-align: left; }
        .hero-stat-value { font-size: 24px; font-weight: 800; color: var(--text-bright); }
        .hero-stat-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

        /* Hero Trading Card */
        .hero-trading-card {
            background: var(--card); border: 1px solid var(--border); border-radius: 20px;
            overflow: hidden; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        }
        .trading-card-header { padding: 20px; border-bottom: 1px solid var(--border); }
        .live-indicator { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--green); font-weight: 600; }
        .live-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--green); animation: pulse 1.5s infinite; }
        .trading-card-body { padding: 16px 20px; }
        .trading-asset-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid rgba(42, 45, 82, 0.5); }
        .trading-asset-row:last-child { border-bottom: none; }
        .asset-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .asset-name { color: var(--text-bright); font-weight: 700; font-size: 14px; }
        .asset-full { font-size: 11px; }
        .asset-price { color: var(--text-bright); font-weight: 600; font-size: 14px; }
        .asset-change { font-size: 12px; font-weight: 600; }

        .hero-wave { position: absolute; bottom: -1px; left: 0; right: 0; line-height: 0; }
        .hero-wave svg { width: 100%; height: 80px; }
        .min-vh-90 { min-height: 90vh; }

        /* ===== STATS BAR ===== */
        .stats-bar-section { padding: 40px 0; background: var(--bg-2); }
        .stat-bar-card { text-align: center; }
        .stat-bar-icon { font-size: 28px; margin-bottom: 8px; }
        .stat-bar-value { font-size: 28px; font-weight: 800; color: var(--text-bright); }
        .stat-bar-label { font-size: 13px; color: var(--text-muted); }

        /* ===== SECTIONS ===== */
        .section-tag {
            display: inline-block; padding: 6px 16px; border-radius: 50px;
            background: rgba(99, 102, 241, 0.1); color: var(--purple-1);
            font-size: 12px; font-weight: 700; letter-spacing: 1px; margin-bottom: 16px;
        }
        .section-title { font-size: 36px; font-weight: 800; color: var(--text-bright); margin-bottom: 12px; }
        .section-subtitle { font-size: 16px; color: var(--text-muted); max-width: 600px; margin: 0 auto; }

        /* Features */
        .features-section { padding: 80px 0; }
        .feature-card {
            background: var(--card); border: 1px solid var(--border); border-radius: 16px;
            padding: 28px; transition: all 0.3s; height: 100%;
        }
        .feature-card:hover { transform: translateY(-4px); border-color: var(--purple-1); box-shadow: 0 12px 40px rgba(99, 102, 241, 0.2); }
        .feature-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
        .feature-icon i { font-size: 24px; }
        .feature-title { color: var(--text-bright); font-weight: 700; margin-bottom: 8px; }
        .feature-text { color: var(--text-muted); font-size: 14px; line-height: 1.6; }

        /* Packages */
        .packages-section { padding: 80px 0; background: var(--bg-2); }
        .package-card {
            background: var(--card); border: 1px solid var(--border); border-radius: 20px;
            padding: 32px; text-align: center; position: relative; transition: all 0.3s; height: 100%;
        }
        .package-card:hover { transform: translateY(-6px); border-color: var(--purple-1); }
        .package-featured { border-color: var(--purple-1); box-shadow: 0 8px 40px rgba(99, 102, 241, 0.2); }
        .package-badge {
            position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
            background: linear-gradient(135deg, var(--purple-1), var(--purple-2)); color: white;
            font-size: 11px; font-weight: 700; padding: 4px 16px; border-radius: 50px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .package-icon { width: 64px; height: 64px; border-radius: 16px; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; }
        .package-icon i { font-size: 28px; }
        .package-name { color: var(--text-bright); font-weight: 700; margin-bottom: 4px; }
        .package-category { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .package-return { margin: 20px 0; padding: 16px; border-radius: 12px; background: var(--bg-2); }
        .package-return-value { display: block; font-size: 32px; font-weight: 800; color: var(--green); }
        .package-return-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; }
        .package-details { text-align: left; margin-bottom: 24px; }
        .package-detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(42, 45, 82, 0.5); font-size: 14px; }
        .package-detail-row span:first-child { color: var(--text-muted); }
        .package-detail-row span:last-child { color: var(--text-bright); font-weight: 600; }
        .package-btn {
            width: 100%; padding: 12px; border-radius: 10px; font-weight: 600;
            background: linear-gradient(135deg, var(--purple-1), var(--purple-2)); color: white; border: none; transition: all 0.2s;
        }
        .package-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4); }

        /* How It Works */
        .how-section { padding: 80px 0; }
        .step-card { text-align: center; position: relative; padding: 32px 20px; }
        .step-number {
            position: absolute; top: 0; right: 20px; font-size: 60px; font-weight: 900;
            color: rgba(99, 102, 241, 0.08); line-height: 1;
        }
        .step-icon { width: 72px; height: 72px; border-radius: 18px; margin: 0 auto 20px; background: var(--card); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; }
        .step-icon i { font-size: 28px; }
        .step-card h5 { color: var(--text-bright); font-weight: 700; margin-bottom: 8px; }
        .step-card p { color: var(--text-muted); font-size: 14px; }

        /* Testimonials */
        .testimonials-section { padding: 80px 0; background: var(--bg-2); overflow: hidden; }
        .testimonial-slider { display: flex; gap: 24px; overflow-x: auto; scroll-snap-type: x mandatory; padding-bottom: 16px; }
        .testimonial-slider::-webkit-scrollbar { display: none; }
        .testimonial-card {
            flex: 0 0 380px; background: var(--card); border: 1px solid var(--border); border-radius: 16px;
            padding: 28px; scroll-snap-align: start;
        }
        .testimonial-stars { color: var(--amber); margin-bottom: 12px; font-size: 14px; }
        .testimonial-text { color: var(--text); font-size: 15px; line-height: 1.6; margin-bottom: 20px; }
        .testimonial-footer { display: flex; justify-content: space-between; align-items: center; }
        .testimonial-name { color: var(--text-bright); font-weight: 700; }
        .testimonial-country { color: var(--text-muted); font-size: 12px; }
        .testimonial-profit { color: var(--green); font-weight: 700; font-size: 16px; }

        /* CTA */
        .cta-section { padding: 80px 0; }
        .cta-card {
            background: var(--card); border: 1px solid var(--border); border-radius: 24px;
            padding: 60px 40px; text-align: center; position: relative; overflow: hidden;
        }
        .cta-glow {
            position: absolute; top: -50%; left: 50%; transform: translateX(-50%);
            width: 600px; height: 300px; border-radius: 50%; filter: blur(80px);
            background: var(--purple-1); opacity: 0.15;
        }
        .cta-title { color: var(--text-bright); font-weight: 800; font-size: 36px; margin-bottom: 12px; position: relative; }
        .cta-subtitle { color: var(--text-muted); font-size: 16px; margin-bottom: 32px; max-width: 500px; margin-left: auto; margin-right: auto; position: relative; }
        .btn-cta {
            padding: 16px 48px; border-radius: 12px; font-size: 18px; font-weight: 700;
            background: linear-gradient(135deg, var(--purple-1), var(--purple-2)); color: white; border: none;
            transition: all 0.3s; position: relative;
        }
        .btn-cta:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(99, 102, 241, 0.5); color: white; }
        .cta-trust { display: flex; justify-content: center; gap: 32px; margin-top: 32px; flex-wrap: wrap; position: relative; }
        .cta-trust span { color: var(--text-muted); font-size: 14px; }
        .cta-trust i { color: var(--purple-1); }

        /* Footer */
        .landing-footer { background: var(--bg-2); padding: 60px 0 30px; border-top: 1px solid var(--border); }
        .footer-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .footer-logo { width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--purple-1), var(--purple-2)); display: flex; align-items: center; justify-content: center; color: white; }
        .footer-brand h4 { color: var(--text-bright); font-weight: 700; margin: 0; }
        .footer-text { color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 20px; }
        .footer-social { display: flex; gap: 12px; }
        .footer-social a { width: 36px; height: 36px; border-radius: 10px; background: var(--card); display: flex; align-items: center; justify-content: center; color: var(--text-muted); transition: all 0.2s; }
        .footer-social a:hover { background: var(--purple-1); color: white; }
        .footer-heading { color: var(--text-bright); font-weight: 700; margin-bottom: 16px; font-size: 15px; }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: var(--text-muted); font-size: 14px; transition: color 0.2s; }
        .footer-links a:hover { color: var(--purple-1); }
        .footer-contact { list-style: none; }
        .footer-contact li { color: var(--text-muted); font-size: 14px; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; }
        .footer-contact i { color: var(--purple-1); width: 18px; }
        .footer-divider { border-color: var(--border); margin: 32px 0; }
        .footer-copy { color: var(--text-dim); font-size: 13px; }
        .footer-payments { display: flex; gap: 16px; }
        .footer-payments i { color: var(--text-dim); font-size: 24px; }

        /* ===== SOCIAL PROOF POP-UPS ===== */
        .social-proof-container {
            position: fixed; bottom: 24px; left: 24px; z-index: 9999;
            display: flex; flex-direction: column; gap: 12px; pointer-events: none;
        }
        .social-proof-popup {
            display: flex; align-items: center; gap: 12px;
            background: var(--card); border: 1px solid var(--border); border-radius: 14px;
            padding: 14px 16px; min-width: 320px; max-width: 380px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            animation: proofSlideIn 0.4s ease-out; pointer-events: auto;
        }
        @keyframes proofSlideIn { from { transform: translateX(-120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .proof-exit { animation: proofSlideOut 0.4s ease-in forwards; }
        @keyframes proofSlideOut { to { transform: translateX(-120%); opacity: 0; } }
        .proof-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .proof-deposit { background: rgba(16, 185, 129, 0.15); color: var(--green); }
        .proof-withdraw { background: rgba(239, 68, 68, 0.15); color: var(--red); }
        .proof-content { flex: 1; min-width: 0; }
        .proof-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px; }
        .proof-name { font-weight: 700; color: var(--text-bright); font-size: 13px; }
        .proof-time { font-size: 11px; color: var(--text-dim); }
        .proof-body { font-size: 13px; color: var(--text-muted); display: flex; gap: 4px; flex-wrap: wrap; align-items: center; }
        .proof-action { color: var(--text-muted); }
        .proof-amount { font-weight: 700; }
        .proof-method { font-size: 12px; color: var(--text-dim); }
        .proof-close { background: none; border: none; color: var(--text-dim); cursor: pointer; font-size: 14px; padding: 4px; }
        .proof-close:hover { color: var(--text-bright); }

        /* Scroll to top */
        .scroll-top-btn {
            position: fixed; bottom: 24px; right: 24px; z-index: 999;
            width: 44px; height: 44px; border-radius: 12px;
            background: linear-gradient(135deg, var(--purple-1), var(--purple-2)); color: white;
            border: none; cursor: pointer; font-size: 16px; display: none; align-items: center; justify-content: center;
            transition: transform 0.2s;
        }
        .scroll-top-btn:hover { transform: translateY(-3px); }

        /* Mobile */
        @media (max-width: 768px) {
            .hero-title { font-size: 32px; }

        @media (max-width: 576px) {
            .hero-title { font-size: 26px; }
            .hero-subtitle { font-size: 13px; }
            .section-title { font-size: 22px; }
            .section-subtitle { font-size: 12px; }
            .asset-category-card { padding: 18px 14px; }
            .asset-category-title { font-size: 15px; }
            .cta-trust { gap: 12px; font-size: 12px; }
            .social-proof-popup { min-width: 240px; max-width: 280px; font-size: 12px; }
            .footer-links { flex-direction: column; gap: 10px; }
        }
            .hero-subtitle { font-size: 15px; }
            .section-title { font-size: 28px; }
            .nav-links { display: none; }
            .market-ticker-bar { top: 65px; }
            .social-proof-popup { min-width: 280px; max-width: 320px; }
            .cta-trust { gap: 16px; }
        }

    /* ====== ASSET CATEGORIES ====== */
    .assets-section { padding: 80px 0; background: rgba(15,18,35,0.95); }

    .asset-category-card {
        background: rgba(30,35,55,0.6);
        border: 1px solid rgba(99,102,241,0.15);
        border-radius: 16px;
        padding: 28px 24px;
        text-align: center;
        transition: all 0.3s ease;
        height: 100%;
    }
    .asset-category-card:hover {
        transform: translateY(-6px);
        border-color: rgba(99,102,241,0.4);
        background: rgba(30,35,55,0.9);
        box-shadow: 0 12px 40px rgba(99,102,241,0.15);
    }
    .asset-category-icon {
        width: 72px; height: 72px;
        border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 18px;
    }
    .asset-category-title {
        color: #fff; font-weight: 700; font-size: 18px; margin-bottom: 10px;
    }
    .asset-category-text {
        color: rgba(255,255,255,0.6); font-size: 13px; line-height: 1.6; margin-bottom: 16px;
    }
    .asset-category-stats {
        display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;
    }
    .asset-category-stats span {
        font-size: 11px; color: rgba(255,255,255,0.4);
        display: flex; align-items: center; gap: 4px;
    }
    .asset-category-stats i { font-size: 10px; }

    /* ====== SECURITY SECTION ====== */
    .security-section { padding: 80px 0; background: rgba(10,13,28,0.98); }

    .security-features { margin-top: 24px; }
    .security-feature-row {
        display: flex; align-items: flex-start; gap: 16px;
        margin-bottom: 20px;
    }
    .security-feature-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        font-size: 18px;
    }
    .security-feature-row h6 { color: #fff; font-weight: 600; font-size: 15px; margin-bottom: 4px; }
    .security-feature-row p { color: rgba(255,255,255,0.5); font-size: 13px; line-height: 1.5; margin: 0; }

    .security-visual {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        position: relative;
        min-height: 400px;
    }
    .security-shield {
        position: relative;
        display: flex; align-items: center; justify-content: center;
        width: 200px; height: 200px;
    }
    .security-ring {
        position: absolute;
        width: 180px; height: 180px;
        border: 2px solid rgba(99,102,241,0.2);
        border-radius: 50%;
        animation: security-rotate 8s linear infinite;
    }
    .security-ring-2 {
        width: 240px; height: 240px;
        border: 1px solid rgba(168,85,247,0.15);
        animation: security-rotate 12s linear infinite reverse;
    }
    @keyframes security-rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .security-badges {
        display: flex; flex-wrap: wrap; gap: 12px; margin-top: 30px; justify-content: center;
    }
    .security-badge-item {
        background: rgba(30,35,55,0.8);
        border: 1px solid rgba(16,185,129,0.2);
        border-radius: 20px;
        padding: 8px 16px;
        font-size: 12px;
        color: rgba(255,255,255,0.8);
        display: flex; align-items: center; gap: 6px;
    }

    /* ====== FAQ SECTION ====== */
    .faq-section { padding: 80px 0; background: rgba(15,18,35,0.95); }
    .faq-list { max-width: 800px; margin: 0 auto; }
    .faq-item {
        background: rgba(30,35,55,0.5);
        border: 1px solid rgba(99,102,241,0.12);
        border-radius: 12px;
        margin-bottom: 12px;
        overflow: hidden;
        transition: border-color 0.2s;
    }
    .faq-item:hover { border-color: rgba(99,102,241,0.3); }
    .faq-question {
        width: 100%;
        background: none;
        border: none;
        padding: 18px 24px;
        text-align: left;
        color: #fff;
        font-size: 15px;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: color 0.2s;
    }
    .faq-question:hover { color: #818cf8; }
    .faq-question span { padding-right: 16px; }
    .faq-icon { transition: transform 0.3s; font-size: 13px; color: rgba(255,255,255,0.4); }
    .faq-icon.rotated { transform: rotate(180deg); color: #818cf8; }
    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease;
        padding: 0 24px;
    }
    .faq-answer.open { padding: 0 24px 18px; }
    .faq-answer p {
        color: rgba(255,255,255,0.55);
        font-size: 14px;
        line-height: 1.7;
        margin: 0;
    }

    </style>
@include('partials._pwa')
@include('partials._mobile-styles')
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="landing-nav" id="landingNav">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="nav-brand">
                <div class="nav-logo"><i class="fas fa-shield-alt"></i></div>
                <h4>APTrades</h4>
            </div>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#packages">Packages</a>
                <a href="#">How It Works</a>
                <a href="#">Testimonials</a>
            </div>
            <div class="nav-actions">
                <a href="{{ route('login') }}" class="btn-login">Login</a>
                <a href="{{ route('register') }}" class="btn-signup">Sign Up</a>
            </div>
        </div>
    </div>
</nav>

<!-- Spacer for fixed nav + ticker -->
<div style="height: 110px;"></div>

@yield('content')

<!-- Tawk.to Widget -->
@include('partials._tawk-widget')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Nav scroll effect
window.addEventListener('scroll', () => {
    document.getElementById('landingNav').classList.toggle('scrolled', window.scrollY > 50);
});
</script>
@include('partials._install-prompt')
</body>
</html>
