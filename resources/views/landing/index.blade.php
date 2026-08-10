@extends('layouts.landing')

@section('title', $content['hero_title'])

@section('content')

<!-- ====== LIVE MARKET TICKER BAR ====== -->
<div class="market-ticker-bar" id="marketTicker">
    <div class="ticker-track" id="tickerTrack">
        @foreach($markets as $m)
        <div class="ticker-item">
            <i class="{{ $m['icon'] }}" style="color: {{ $m['color'] }};"></i>
            <span class="ticker-symbol">{{ $m['symbol'] }}</span>
            <span class="ticker-price">${{ number_format($m['price'], $m['price'] < 1 ? 4 : 2) }}</span>
            <span class="ticker-change {{ $m['change'] >= 0 ? 'text-success' : 'text-danger' }}">
                <i class="fas fa-caret-{{ $m['change'] >= 0 ? 'up' : 'down' }}"></i>
                {{ abs(number_format($m['change'], 2)) }}%
            </span>
        </div>
        @endforeach
    </div>
</div>

<!-- ====== HERO SECTION ====== -->
<section class="hero-section" id="hero">
    <div class="hero-bg-animation"></div>
    <div class="hero-glow hero-glow-1"></div>
    <div class="hero-glow hero-glow-2"></div>

    <div class="container position-relative" style="z-index: 10;">
        <div class="row align-items-center min-vh-90">
            <div class="col-lg-6 text-white">
                <div class="hero-badge mb-3">
                    <span class="pulse-dot"></span>
                    {{ $content['hero_badge'] }}
                </div>
                <h1 class="hero-title mb-3">{{ $content['hero_title'] }}</h1>
                <p class="hero-subtitle mb-4">{{ $content['hero_subtitle'] }}</p>
                <div class="d-flex gap-3 flex-wrap mb-5">
                    <a href="{{ route('register') }}" class="btn btn-hero-primary">
                        <i class="fas fa-rocket me-2"></i>{{ $content['hero_cta_primary'] }}
                    </a>
                    <a href="#packages" class="btn btn-hero-secondary">
                        <i class="fas fa-chart-pie me-2"></i>{{ $content['hero_cta_secondary'] }}
                    </a>
                </div>

                <!-- Hero Stats -->
                <div class="row g-3 mt-2">
                    <div class="col-3">
                        <div class="hero-stat">
                            <div class="hero-stat-value">{{ $content['stat1_value'] }}</div>
                            <div class="hero-stat-label">{{ $content['stat1_label'] }}</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="hero-stat">
                            <div class="hero-stat-value">{{ $content['stat2_value'] }}</div>
                            <div class="hero-stat-label">{{ $content['stat2_label'] }}</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="hero-stat">
                            <div class="hero-stat-value">{{ $content['stat3_value'] }}</div>
                            <div class="hero-stat-label">{{ $content['stat3_label'] }}</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="hero-stat">
                            <div class="hero-stat-value">{{ $content['stat4_value'] }}</div>
                            <div class="hero-stat-label">{{ $content['stat4_label'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Live Trading Card -->
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-trading-card">
                    <div class="trading-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-white-50 small">LIVE PORTFOLIO</span>
                                <h3 class="text-white fw-bold mb-0">${{ number_format($platformStats['total_deposits'] / 1000, 1) }}K</h3>
                            </div>
                            <div class="live-indicator">
                                <span class="live-dot"></span> LIVE
                            </div>
                        </div>
                    </div>
                    <div class="trading-card-body">
                        <!-- Mini sparkline chart -->
                        <div id="heroChart" style="height: 120px;"></div>

                        <!-- Asset rows -->
                        @php $top4 = array_slice($markets, 0, 4); @endphp
                        @foreach($top4 as $m)
                        <div class="trading-asset-row">
                            <div class="d-flex align-items-center gap-2">
                                <div class="asset-icon" style="background: {{ $m['color'] }}20;">
                                    <i class="{{ $m['icon'] }}" style="color: {{ $m['color'] }}; font-size: 14px;"></i>
                                </div>
                                <div>
                                    <div class="asset-name">{{ $m['symbol'] }}</div>
                                    <div class="asset-full text-muted small">{{ $m['name'] }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="asset-price">${{ number_format($m['price'], $m['price'] < 1 ? 4 : 2) }}</div>
                                <div class="asset-change {{ $m['change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="fas fa-caret-{{ $m['change'] >= 0 ? 'up' : 'down' }}"></i>
                                    {{ number_format($m['change'], 2) }}%
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wave separator -->
    <div class="hero-wave">
        <svg viewBox="0 0 1440 100" preserveAspectRatio="none"><path d="M0,40 C480,100 960,0 1440,50 L1440,100 L0,100 Z" fill="rgba(15,18,35,0.95)"/></svg>
    </div>
</section>

<!-- ====== PLATFORM STATS BAR ====== -->
<section class="stats-bar-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="stat-bar-card">
                    <i class="fas fa-dollar-sign stat-bar-icon" style="color: #10b981;"></i>
                    <div class="stat-bar-value" data-counter="{{ $platformStats['total_deposits'] }}">$0</div>
                    <div class="stat-bar-label">Total Deposits</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-bar-card">
                    <i class="fas fa-arrow-up stat-bar-icon" style="color: #ef4444;"></i>
                    <div class="stat-bar-value" data-counter="{{ $platformStats['total_withdrawals'] }}">$0</div>
                    <div class="stat-bar-label">Total Withdrawals</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-bar-card">
                    <i class="fas fa-users stat-bar-icon" style="color: #6366f1;"></i>
                    <div class="stat-bar-value" data-counter="{{ $platformStats['total_users'] }}">0</div>
                    <div class="stat-bar-label">Active Investors</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-bar-card">
                    <i class="fas fa-chart-line stat-bar-icon" style="color: #a855f7;"></i>
                    <div class="stat-bar-value" data-counter="{{ $platformStats['active_investments'] }}">0</div>
                    <div class="stat-bar-label">Active Investments</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====== ASSET CATEGORIES SECTION ====== -->
<section class="assets-section" id="assets">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag">MARKETS</span>
            <h2 class="section-title">Diversify Across Multiple Asset Classes</h2>
            <p class="section-subtitle">Access crypto, forex, stocks, and bonds from a single platform — all professionally managed.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="asset-category-card" style="border-top: 4px solid #f7931a;">
                    <div class="asset-category-icon" style="background: rgba(247,147,26,0.1);">
                        <i class="fab fa-bitcoin" style="color: #f7931a; font-size: 32px;"></i>
                    </div>
                    <h5 class="asset-category-title">Cryptocurrency</h5>
                    <p class="asset-category-text">Trade BTC, ETH, BNB, SOL and 50+ digital assets with competitive returns and cold-storage security.</p>
                    <div class="asset-category-stats">
                        <span><i class="fas fa-coins"></i> 50+ coins</span>
                        <span><i class="fas fa-bolt"></i> 24/7 market</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="asset-category-card" style="border-top: 4px solid #3b82f6;">
                    <div class="asset-category-icon" style="background: rgba(59,130,246,0.1);">
                        <i class="fas fa-dollar-sign" style="color: #3b82f6; font-size: 32px;"></i>
                    </div>
                    <h5 class="asset-category-title">Forex Trading</h5>
                    <p class="asset-category-text">Access major, minor, and exotic currency pairs with tight spreads and leverage options.</p>
                    <div class="asset-category-stats">
                        <span><i class="fas fa-exchange-alt"></i> 30+ pairs</span>
                        <span><i class="fas fa-chart-line"></i> Low spreads</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="asset-category-card" style="border-top: 4px solid #a855f7;">
                    <div class="asset-category-icon" style="background: rgba(168,85,247,0.1);">
                        <i class="fas fa-chart-bar" style="color: #a855f7; font-size: 32px;"></i>
                    </div>
                    <h5 class="asset-category-title">Stocks & Indices</h5>
                    <p class="asset-category-text">Invest in top global equities and indices — from tech giants to blue-chip dividend stocks.</p>
                    <div class="asset-category-stats">
                        <span><i class="fas fa-building"></i> 200+ stocks</span>
                        <span><i class="fas fa-globe"></i> Global markets</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="asset-category-card" style="border-top: 4px solid #10b981;">
                    <div class="asset-category-icon" style="background: rgba(16,185,129,0.1);">
                        <i class="fas fa-landmark" style="color: #10b981; font-size: 32px;"></i>
                    </div>
                    <h5 class="asset-category-title">Bonds & Fixed Income</h5>
                    <p class="asset-category-text">Government and corporate bonds with stable, predictable returns for portfolio diversification.</p>
                    <div class="asset-category-stats">
                        <span><i class="fas fa-shield-alt"></i> Low risk</span>
                        <span><i class="fas fa-percentage"></i> Fixed returns</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====== FEATURES SECTION ====== -->
<section class="features-section" id="features">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag">FEATURES</span>
            <h2 class="section-title">{{ $content['features_title'] }}</h2>
            <p class="section-subtitle">{{ $content['features_subtitle'] }}</p>
        </div>
        <div class="row g-4">
            @php
                $features = [
                    ['fa-shield-alt',  '#6366f1', 'Bank-Grade Security', '256-bit SSL encryption, 2FA authentication, and cold storage for all digital assets.'],
                    ['fa-chart-line',  '#a855f7', 'Real-Time Analytics', 'Live market data, portfolio performance tracking, and detailed earnings reports.'],
                    ['fa-wallet',      '#3b82f6', 'Multi-Asset Wallet', 'Deposit, withdraw, and transfer across 5 wallet types — deposit, interest, referral, matching, and main.'],
                    ['fa-coins',        '#10b981', 'Profit Sharing', 'Earn daily profits through our weighted capital distribution system. Higher packages = bigger shares.'],
                    ['fa-sitemap',     '#f59e0b', 'Binary MLM Engine', 'Build passive income with referral commissions, binary matching bonuses, and rank rewards.'],
                    ['fa-headset',      '#06b6d4', '24/7 Support', 'Round-the-clock support via live chat, tickets, and email. Average response time under 5 minutes.'],
                ];
            @endphp
            @foreach($features as $feature)
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background: {{ $feature[1] }}15;">
                        <i class="fas {{ $feature[0] }}" style="color: {{ $feature[1] }};"></i>
                    </div>
                    <h5 class="feature-title">{{ $feature[2] }}</h5>
                    <p class="feature-text">{{ $feature[3] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ====== PACKAGES SECTION ====== -->
<section class="packages-section" id="packages">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag">PACKAGES</span>
            <h2 class="section-title">{{ $content['section2_title'] }}</h2>
            <p class="section-subtitle">{{ $content['section2_subtitle'] }}</p>
        </div>
        <div class="row g-4">
            @forelse($packages as $pkg)
            <div class="col-md-6 col-lg-4">
                <div class="package-card {{ $pkg->is_featured ? 'package-featured' : '' }}">
                    @if($pkg->is_featured)
                    <div class="package-badge">Most Popular</div>
                    @endif
                    <div class="package-icon" style="background: linear-gradient(135deg, {{ $pkg->color ?? '#6366f1' }}22, {{ $pkg->color ?? '#7c3aed' }}11);">
                        <i class="fas {{ $pkg->icon ?? 'fa-chart-line' }}" style="color: {{ $pkg->color ?? '#6366f1' }};"></i>
                    </div>
                    <h5 class="package-name">{{ $pkg->name }}</h5>
                    <span class="package-category">{{ ucfirst($pkg->category) }}</span>
                    <div class="package-return">
                        <span class="package-return-value">{{ $pkg->return_rate }}%</span>
                        <span class="package-return-label">Daily Return</span>
                    </div>
                    <div class="package-details">
                        <div class="package-detail-row"><span>Min Investment</span><span>${{ number_format($pkg->min_amount) }}</span></div>
                        <div class="package-detail-row"><span>Max Investment</span><span>${{ number_format($pkg->max_amount) }}</span></div>
                        <div class="package-detail-row"><span>Duration</span><span>{{ $pkg->duration_days }} days</span></div>
                        <div class="package-detail-row"><span>Profit Share Weight</span><span>{{ number_format($pkg->profit_share_weight ?? 1, 1) }}x</span></div>
                    </div>
                    <a href="{{ route('register') }}" class="btn package-btn">Invest Now</a>
                </div>
            </div>
            @empty
            <!-- Demo packages if none configured -->
            @php
                $demoPackages = [
                    ['Starter',  'crypto', 1.5, 100, 5000, 30, 'fa-seedling', '#10b981', false],
                    ['Silver',   'crypto', 2.5, 5000, 25000, 60, 'fa-medal', '#6366f1', false],
                    ['Gold',     'mixed',  3.5, 25000, 100000, 90, 'fa-trophy', '#a855f7', true],
                ];
            @endphp
            @foreach($demoPackages as $pkg)
            <div class="col-md-6 col-lg-4">
                <div class="package-card {{ $pkg[8] ? 'package-featured' : '' }}">
                    @if($pkg[8])<div class="package-badge">Most Popular</div>@endif
                    <div class="package-icon" style="background: {{ $pkg[7] }}22;">
                        <i class="fas {{ $pkg[6] }}" style="color: {{ $pkg[7] }};"></i>
                    </div>
                    <h5 class="package-name">{{ $pkg[0] }}</h5>
                    <span class="package-category">{{ ucfirst($pkg[1]) }}</span>
                    <div class="package-return"><span class="package-return-value">{{ $pkg[2] }}%</span><span class="package-return-label">Daily Return</span></div>
                    <div class="package-details">
                        <div class="package-detail-row"><span>Min Investment</span><span>${{ number_format($pkg[3]) }}</span></div>
                        <div class="package-detail-row"><span>Max Investment</span><span>${{ number_format($pkg[4]) }}</span></div>
                        <div class="package-detail-row"><span>Duration</span><span>{{ $pkg[5] }} days</span></div>
                        <div class="package-detail-row"><span>Profit Share</span><span>1.0x</span></div>
                    </div>
                    <a href="{{ route('register') }}" class="btn package-btn">Invest Now</a>
                </div>
            </div>
            @endforeach
            @endforelse
        </div>
    </div>
</section>

<!-- ====== HOW IT WORKS ====== -->
<section class="how-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag">GET STARTED</span>
            <h2 class="section-title">Start in 3 Simple Steps</h2>
            <p class="section-subtitle">From sign-up to your first profit in under 5 minutes.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon"><i class="fas fa-user-plus" style="color: #6366f1;"></i></div>
                    <h5>Create Account</h5>
                    <p>Sign up free, verify your email, and complete your profile in under 2 minutes.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon"><i class="fas fa-wallet" style="color: #a855f7;"></i></div>
                    <h5>Deposit Funds</h5>
                    <p>Fund your account via crypto, bank transfer, or any supported payment method.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon"><i class="fas fa-chart-line" style="color: #10b981;"></i></div>
                    <h5>Invest & Earn</h5>
                    <p>Choose a package, invest, and watch your returns grow daily. Withdraw anytime.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====== SECURITY & TRUST SECTION ====== -->
<section class="security-section" id="security">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-tag">SECURITY FIRST</span>
                <h2 class="section-title">Built Like a Vault. Trusted Like a Bank.</h2>
                <p class="section-subtitle text-start">Every dollar you deposit, every trade we execute, every withdrawal you request — protected by multiple layers of enterprise-grade security. We treat your money like it's ours, because trust is the only currency that matters here.</p>
                <div class="security-features">
                    <div class="security-feature-row">
                        <div class="security-feature-icon" style="background: rgba(99,102,241,0.1);">
                            <i class="fas fa-lock" style="color: #6366f1;"></i>
                        </div>
                        <div>
                            <h6>Military-Grade Encryption</h6>
                            <p>Every byte of data — passwords, balances, transactions, personal info — is encrypted in transit with 256-bit AES SSL, the same standard used by major banks and intelligence agencies worldwide. Nothing leaves your device unencrypted.</p>
                        </div>
                    </div>
                    <div class="security-feature-row">
                        <div class="security-feature-icon" style="background: rgba(16,185,129,0.1);">
                            <i class="fas fa-snowflake" style="color: #10b981;"></i>
                        </div>
                        <div>
                            <h6>Deep Cold Storage</h6>
                            <p>98% of all crypto assets sit in air-gapped, offline cold wallets spread across multiple geographic locations. They're never connected to the internet, never exposed to a hack. Only the 2% needed for daily liquidity stays in a hot wallet with strict withdrawal limits.</p>
                        </div>
                    </div>
                    <div class="security-feature-row">
                        <div class="security-feature-icon" style="background: rgba(168,85,247,0.1);">
                            <i class="fas fa-fingerprint" style="color: #a855f7;"></i>
                        </div>
                        <div>
                            <h6>Multi-Layer Authentication</h6>
                            <p>2FA isn't optional — it's mandatory. Every login, every withdrawal, every sensitive action requires both your password and a time-based code from your authenticator app. Even if someone steals your password, they can't touch your funds.</p>
                        </div>
                    </div>
                    <div class="security-feature-row">
                        <div class="security-feature-icon" style="background: rgba(59,130,246,0.1);">
                            <i class="fas fa-id-card" style="color: #3b82f6;"></i>
                        </div>
                        <div>
                            <h6>Full KYC & AML Screening</h6>
                            <p>Every user is identity-verified before they can invest or withdraw. Every withdrawal is screened against global anti-money-laundering databases. This isn't bureaucracy — it's how we keep the platform clean, legal, and safe for legitimate investors.</p>
                        </div>
                    </div>
                    <div class="security-feature-row">
                        <div class="security-feature-icon" style="background: rgba(245,158,11,0.1);">
                            <i class="fas fa-database" style="color: #f59e0b;"></i>
                        </div>
                        <div>
                            <h6>Encrypted at Rest & Audited</h6>
                            <p>Your data isn't just encrypted in transit — it's encrypted at rest too. Sensitive fields like wallet addresses and KYC documents are hashed with AES-256. We run quarterly third-party security audits and penetration tests to find vulnerabilities before anyone else does.</p>
                        </div>
                    </div>
                    <div class="security-feature-row">
                        <div class="security-feature-icon" style="background: rgba(239,68,68,0.1);">
                            <i class="fas fa-eye" style="color: #ef4444;"></i>
                        </div>
                        <div>
                            <h6>Real-Time Fraud Detection</h6>
                            <p>Our monitoring system flags unusual activity instantly — a login from a new country, a withdrawal pattern that doesn't match your history, a sudden balance change. Suspicious transactions are frozen automatically and reviewed by our security team within minutes.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="security-visual">
                    <div class="security-shield">
                        <i class="fas fa-shield-alt" style="font-size: 120px; color: #6366f1; opacity: 0.9;"></i>
                        <div class="security-ring"></div>
                        <div class="security-ring security-ring-2"></div>
                    </div>
                    <div class="security-badges">
                        <div class="security-badge-item">
                            <i class="fas fa-check-circle" style="color: #10b981;"></i> SOC 2 Type II
                        </div>
                        <div class="security-badge-item">
                            <i class="fas fa-check-circle" style="color: #10b981;"></i> AML Compliant
                        </div>
                        <div class="security-badge-item">
                            <i class="fas fa-check-circle" style="color: #10b981;"></i> GDPR Ready
                        </div>
                        <div class="security-badge-item">
                            <i class="fas fa-check-circle" style="color: #10b981;"></i> PCI-DSS
                        </div>
                        <div class="security-badge-item">
                            <i class="fas fa-check-circle" style="color: #10b981;"></i> ISO 27001
                        </div>
                    </div>
                    <p style="color: rgba(255,255,255,0.35); font-size: 12px; margin-top: 20px; text-align: center; max-width: 280px;">
                        Independently audited every 90 days. Zero security breaches since launch. $0 in customer funds lost — ever.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====== TESTIMONIALS ====== -->
<section class="testimonials-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag">TESTIMONIALS</span>
            <h2 class="section-title">{{ $content['testimonial_title'] }}</h2>
        </div>
        <div class="testimonial-slider" id="testimonialSlider">
            @foreach($testimonials as $t)
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    @for($i = 0; $i < $t['rating']; $i++)<i class="fas fa-star"></i>@endfor
                </div>
                <p class="testimonial-text">"{{ $t['text'] }}"</p>
                <div class="testimonial-footer">
                    <div>
                        <div class="testimonial-name">{{ $t['name'] }}</div>
                        <div class="testimonial-country">{{ $t['country'] }}</div>
                    </div>
                    <div class="testimonial-profit">{{ $t['profit'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ====== FAQ SECTION ====== -->
<section class="faq-section" id="faq">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag">FAQ</span>
            <h2 class="section-title">Frequently Asked Questions</h2>
            <p class="section-subtitle">Everything you need to know about investing with APTrades.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="faq-list">
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>How do I get started?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Creating an account takes under 2 minutes. Click "Start Investing," enter your email and password, verify your account, fund your deposit wallet via crypto or bank transfer, and choose an investment package. Your returns start accruing immediately.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>What is the minimum investment amount?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>The minimum investment depends on the package you choose. Our Starter package begins at $100, making it accessible for new investors. Higher-tier packages offer larger daily returns and increased profit-share weights.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>How are profits calculated and distributed?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Profits are calculated daily based on your package's return rate and credited to your interest wallet. You also earn from our profit-sharing pool, where your share is weighted by your package tier and active investment volume. Withdrawals are available anytime with no lock-up periods.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>How long do withdrawals take?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Crypto withdrawals are processed within 1-24 hours after admin approval. Bank transfers typically take 1-3 business days depending on your bank. All withdrawals go through a verification process to ensure security and AML compliance.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>Can I earn without investing?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Yes! Our referral and binary MLM system lets you earn passive income through direct referral commissions, binary matching bonuses, and rank-based rewards. Build a team, qualify for ranks, and earn weekly — even without an active investment.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>Is my money safe?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Absolutely. We use 256-bit SSL encryption, cold storage for 98% of digital assets, mandatory 2FA, and KYC/AML verification on all withdrawals. Our platform undergoes regular security audits and all data is encrypted at rest.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>What payment methods are supported?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>We support crypto deposits (BTC, ETH, USDT on TRC20/ERC20/BEP20, and more), bank transfers, and card payments depending on your region. All methods are available from your deposit page once you log in.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ====== CTA SECTION ====== -->
<section class="cta-section">
    <div class="container">
        <div class="cta-card">
            <div class="cta-glow"></div>
            <h2 class="cta-title">{{ $content['cta_title'] }}</h2>
            <p class="cta-subtitle">{{ $content['cta_subtitle'] }}</p>
            <a href="{{ route('register') }}" class="btn btn-cta">
                <i class="fas fa-rocket me-2"></i>{{ $content['cta_button'] }}
            </a>
            <div class="cta-trust">
                <span><i class="fas fa-shield-alt me-1"></i>Bank-Grade Security</span>
                <span><i class="fas fa-bolt me-1"></i>Instant Withdrawals</span>
                <span><i class="fas fa-headset me-1"></i>24/7 Support</span>
            </div>
        </div>
    </div>
</section>

<!-- ====== FOOTER ====== -->
<footer class="landing-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="footer-brand">
                    <div class="footer-logo"><i class="fas fa-shield-alt"></i></div>
                    <h4>APTrades</h4>
                </div>
                <p class="footer-text">{{ $content['footer_about'] }}</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-telegram"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="footer-heading">Platform</h6>
                <ul class="footer-links">
                    <li><a href="#packages">Investment Packages</a></li>
                    <li><a href="#assets">Asset Markets</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#security">Security</a></li>
                    <li><a href="#faq">FAQ</a></li>
                    <li><a href="{{ route('register') }}">Sign Up</a></li>
                    <li><a href="{{ route('login') }}">Login</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="footer-heading">Company</h6>
                <ul class="footer-links">
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">AML Policy</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="footer-heading">Contact</h6>
                <ul class="footer-contact">
                    <li><i class="fas fa-envelope"></i> {{ $content['footer_email'] }}</li>
                    <li><i class="fas fa-phone"></i> {{ $content['footer_phone'] }}</li>
                    <li><i class="fas fa-map-marker-alt"></i> {{ $content['footer_address'] }}</li>
                </ul>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <p class="footer-copy mb-0">© {{ date('Y') }} APTrades. All rights reserved.</p>
            <div class="footer-payments">
                <i class="fab fa-bitcoin"></i>
                <i class="fab fa-ethereum"></i>
                <i class="fas fa-university"></i>
                <i class="fab fa-cc-visa"></i>
                <i class="fab fa-cc-mastercard"></i>
            </div>
        </div>
    </div>
</footer>

<!-- ====== SOCIAL PROOF POP-UP NOTIFICATIONS ====== -->
<div id="socialProofContainer" class="social-proof-container"></div>

<!-- ====== SCROLL TO TOP ====== -->
<button id="scrollTop" class="scroll-top-btn"><i class="fas fa-arrow-up"></i></button>

<script>
// ===== Market Data for Ticker & Chart =====
const marketData = @json($markets);
const recentActivity = @json($recentActivity);

// ===== Animated Counters =====
function animateCounter(el, target, duration = 2000) {
    const start = 0;
    const startTime = performance.now();
    const isCurrency = el.textContent.startsWith('$');

    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const value = Math.floor(start + (target - start) * easeOut);

        if (isCurrency) {
            el.textContent = '$' + value.toLocaleString();
        } else {
            el.textContent = value.toLocaleString();
        }

        if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
}

// Trigger counters when visible
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const target = parseInt(entry.target.dataset.counter);
            animateCounter(entry.target, target);
            counterObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

document.querySelectorAll('[data-counter]').forEach(el => counterObserver.observe(el));

// ===== Hero Sparkline Chart =====
if (document.getElementById('heroChart')) {
    const sparkData = Array.from({length: 30}, (_, i) => {
        return 50 + Math.sin(i / 3) * 15 + Math.random() * 8 + i * 0.5;
    });

    new ApexCharts(document.getElementById('heroChart'), {
        series: [{ name: 'Portfolio', data: sparkData }],
        chart: { type: 'area', height: 120, sparkline: { enabled: true }, background: 'transparent' },
        colors: ['#6366f1'],
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.05, stops: [0, 100] } },
        tooltip: { theme: 'dark', y: { formatter: v => '$' + v.toFixed(2) + 'K' } },
    }).render();
}

// ===== Social Proof Pop-ups =====
let activityIndex = 0;
const proofContainer = document.getElementById('socialProofContainer');

function showSocialProof() {
    if (activityIndex >= recentActivity.length) activityIndex = 0;
    const activity = recentActivity[activityIndex];
    activityIndex++;

    const isDeposit = activity.type === 'deposit';
    const popup = document.createElement('div');
    popup.className = 'social-proof-popup';
    popup.innerHTML = `
        <div class="proof-icon ${isDeposit ? 'proof-deposit' : 'proof-withdraw'}">
            <i class="fas fa-${isDeposit ? 'arrow-down' : 'arrow-up'}"></i>
        </div>
        <div class="proof-content">
            <div class="proof-header">
                <span class="proof-name">${activity.flag} ${activity.name}</span>
                <span class="proof-time">${typeof activity.time === 'string' ? activity.time : 'just now'}</span>
            </div>
            <div class="proof-body">
                <span class="proof-action">${isDeposit ? 'deposited' : 'withdrew'}</span>
                <span class="proof-amount ${isDeposit ? 'text-success' : 'text-danger'}">
                    $${Number(activity.amount).toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 2})}
                </span>
                <span class="proof-method">via ${activity.method}</span>
            </div>
        </div>
        <button class="proof-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    `;

    proofContainer.appendChild(popup);

    // Auto-remove after 6 seconds
    setTimeout(() => {
        if (popup.parentElement) {
            popup.classList.add('proof-exit');
            setTimeout(() => popup.remove(), 400);
        }
    }, 6000);
}

// Start pop-ups after 3 seconds, then every 8-12 seconds
setTimeout(() => {
    showSocialProof();
    setInterval(() => {
        // Remove old popups if more than 3 visible
        while (proofContainer.children.length >= 3) {
            proofContainer.children[0].remove();
        }
        showSocialProof();
    }, 9000);
}, 3000);

// ===== Market Ticker Animation (infinite scroll) =====
const tickerTrack = document.getElementById('tickerTrack');
if (tickerTrack) {
    // Duplicate content for seamless loop
    tickerTrack.innerHTML += tickerTrack.innerHTML;
    let tickerPos = 0;
    function animateTicker() {
        tickerPos -= 0.3;
        if (Math.abs(tickerPos) >= tickerTrack.scrollWidth / 2) tickerPos = 0;
        tickerTrack.style.transform = `translateX(${tickerPos}px)`;
        requestAnimationFrame(animateTicker);
    }
    animateTicker();
}

// ===== Live Price Updates (simulated) =====
setInterval(() => {
    document.querySelectorAll('.ticker-item').forEach((item, i) => {
        const idx = i % marketData.length;
        const change = (Math.random() - 0.5) * 0.5;
        marketData[idx].price += change;
        const priceEl = item.querySelector('.ticker-price');
        const changeEl = item.querySelector('.ticker-change');
        if (priceEl && marketData[idx].price < 1) {
            priceEl.textContent = '$' + marketData[idx].price.toFixed(4);
        } else if (priceEl) {
            priceEl.textContent = '$' + marketData[idx].price.toFixed(2);
        }
    });
}, 3000);

// ===== FAQ Toggle =====
function toggleFaq(btn) {
    const answer = btn.nextElementSibling;
    const icon = btn.querySelector('.faq-icon');
    const isOpen = answer.style.maxHeight && answer.style.maxHeight !== '0px';

    // Close all others
    document.querySelectorAll('.faq-answer').forEach(a => {
        a.style.maxHeight = '0px';
        a.classList.remove('open');
    });
    document.querySelectorAll('.faq-icon').forEach(i => i.classList.remove('rotated'));

    if (!isOpen) {
        answer.style.maxHeight = answer.scrollHeight + 'px';
        answer.classList.add('open');
        icon.classList.add('rotated');
    }
}

// ===== Scroll to Top =====
const scrollTopBtn = document.getElementById('scrollTop');
window.addEventListener('scroll', () => {
    scrollTopBtn.style.display = window.scrollY > 500 ? 'flex' : 'none';
});
scrollTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// ===== Smooth Scroll for anchor links =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
    });
});
</script>
@endsection
