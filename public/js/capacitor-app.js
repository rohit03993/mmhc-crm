/**
 * MMHC CRM — native WebView helpers (additive; desktop browsers ignore this).
 */
(function () {
    function isNative() {
        return window.Capacitor && typeof window.Capacitor.isNativePlatform === 'function' && window.Capacitor.isNativePlatform();
    }

    if (!isNative()) {
        return;
    }

    document.documentElement.classList.add('capacitor-app');
    document.body.classList.add('capacitor-app');

    var style = document.createElement('style');
    style.textContent = [
        'html.capacitor-app, body.capacitor-app {',
        '  -webkit-tap-highlight-color: transparent;',
        '}',
        'body.capacitor-app {',
        '  padding-top: env(safe-area-inset-top);',
        '  padding-bottom: env(safe-area-inset-bottom);',
        '}',
        'body.capacitor-app button,',
        'body.capacitor-app a,',
        'body.capacitor-app .btn,',
        'body.capacitor-app [role="button"] {',
        '  touch-action: manipulation;',
        '}',
        '@media (max-width: 767px) {',
        '  body.capacitor-app .main-content {',
        '    padding-bottom: calc(80px + env(safe-area-inset-bottom)) !important;',
        '  }',
        '  body.capacitor-app .top-navbar .btn-link {',
        '    min-width: 48px;',
        '    min-height: 48px;',
        '    display: inline-flex;',
        '    align-items: center;',
        '    justify-content: center;',
        '  }',
        '}',
    ].join('\n');
    document.head.appendChild(style);

    console.info('[MMHC] Capacitor WebView', window.Capacitor.getPlatform());
})();
