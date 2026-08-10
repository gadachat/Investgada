{{-- PWA Install Prompt — shows on iPhone and Android when app can be installed --}}
@php
    $isIOS = stripos(request()->userAgent() ?? '', 'iPhone') !== false || stripos(request()->userAgent() ?? '', 'iPad') !== false;
@endphp

<div id="pwaInstallPrompt" class="pwa-install-prompt" style="display:none;">
    <div class="pwa-install-card">
        <div class="pwa-install-icon">
            <img src="{{ asset('icons/icon-120.png') }}" alt="APTrades">
        </div>
        <div class="pwa-install-text">
            <strong>Add APTrades to Home Screen</strong>
            <p>Install the app for faster access and push notifications.</p>
        </div>
        <div class="pwa-install-actions">
            <button class="pwa-install-btn" onclick="installPWA()">
                <i class="fas fa-download"></i> Install
            </button>
            <button class="pwa-dismiss-btn" onclick="dismissPWA()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @if($isIOS)
    <div class="pwa-ios-hint" id="pwaIosHint" style="display:none;">
        <div class="ios-arrow"><i class="fas fa-arrow-up"></i></div>
        <div class="ios-text">
            <strong>Tap the Share button</strong>
            <p>then "Add to Home Screen" to install APTrades</p>
        </div>
        <div class="ios-share-icon"><i class="fas fa-share"></i></div>
    </div>
    @endif
</div>

<style>
    .pwa-install-prompt {
        position: fixed;
        bottom: 80px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9998;
        width: calc(100% - 24px);
        max-width: 420px;
        animation: pwaSlideUp 0.3s ease;
    }

    @keyframes pwaSlideUp {
        from { transform: translate(-50%, 100px); opacity: 0; }
        to { transform: translate(-50%, 0); opacity: 1; }
    }

    .pwa-install-card {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(99, 102, 241, 0.3);
        border-radius: 16px;
        padding: 14px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), 0 0 20px rgba(99, 102, 241, 0.1);
    }

    .pwa-install-icon img {
        width: 48px;
        height: 48px;
        border-radius: 12px;
    }

    .pwa-install-text {
        flex: 1;
        min-width: 0;
    }

    .pwa-install-text strong {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #f1f5f9;
        margin-bottom: 2px;
    }

    .pwa-install-text p {
        margin: 0;
        font-size: 12px;
        color: #94a3b8;
    }

    .pwa-install-actions {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
    }

    .pwa-install-btn {
        background: linear-gradient(135deg, #6366f1, #7c3aed);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: transform 0.15s;
    }

    .pwa-install-btn:active {
        transform: scale(0.95);
    }

    .pwa-dismiss-btn {
        background: transparent;
        color: #64748b;
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: 10px;
        width: 36px;
        height: 36px;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pwa-ios-hint {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 8px;
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(99, 102, 241, 0.3);
        border-radius: 16px;
        padding: 14px;
        animation: pwaSlideUp 0.3s ease 0.1s both;
    }

    .ios-arrow {
        font-size: 20px;
        color: #6366f1;
        animation: bounce 1s infinite;
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }

    .ios-share-icon {
        font-size: 24px;
        color: #3b82f6;
    }

    .ios-text strong {
        display: block;
        font-size: 13px;
        color: #f1f5f9;
    }

    .ios-text p {
        margin: 2px 0 0;
        font-size: 12px;
        color: #94a3b8;
    }

    .ios-text {
        flex: 1;
    }

    @media (min-width: 769px) {
        .pwa-install-prompt { display: none !important; }
    }
</style>

<script>
    let deferredPrompt = null;

    // Capture the install prompt event
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        showInstallPrompt();
    });

    function showInstallPrompt() {
        // Don't show if already dismissed or installed
        if (localStorage.getItem('pwaDismissed') === '1') return;
        if (window.matchMedia('(display-mode: standalone)').matches) return;

        const prompt = document.getElementById('pwaInstallPrompt');
        if (prompt) {
            prompt.style.display = 'block';
            setTimeout(() => { prompt.style.display = 'none'; }, 15000);
        }
    }

    function installPWA() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choice) => {
                if (choice.outcome === 'accepted') {
                    console.log('PWA installed');
                }
                deferredPrompt = null;
                document.getElementById('pwaInstallPrompt').style.display = 'none';
            });
        } else {
            // iOS — show the hint
            const hint = document.getElementById('pwaIosHint');
            if (hint) {
                hint.style.display = 'flex';
                setTimeout(() => { hint.style.display = 'none'; }, 8000);
            }
        }
    }

    function dismissPWA() {
        localStorage.setItem('pwaDismissed', '1');
        document.getElementById('pwaInstallPrompt').style.display = 'none';
    }

    // Show install prompt after 30 seconds on first visit
    if (!localStorage.getItem('pwaDismissed') && !sessionStorage.getItem('pwaShown')) {
        sessionStorage.setItem('pwaShown', '1');
        setTimeout(showInstallPrompt, 30000);
    }
</script>
