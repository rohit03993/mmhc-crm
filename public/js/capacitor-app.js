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

    document.querySelectorAll('.top-navbar .btn-link, .mmhc-sidebar-toggle').forEach(function (el) {
        el.style.touchAction = 'manipulation';
    });

    console.info('[MMHC] Capacitor WebView', window.Capacitor.getPlatform());
})();
