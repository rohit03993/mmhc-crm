/**
 * MMHC PWA — register service worker + smart install prompt.
 * Never shows if already installed, running as PWA, or inside Capacitor.
 */
(function () {
    'use strict';

    var DISMISS_KEY = 'mmhc_pwa_dismiss_until';
    var INSTALLED_KEY = 'mmhc_pwa_installed';
    var IOS_TIP_KEY = 'mmhc_pwa_ios_tip_seen';
    var DISMISS_DAYS = 14;
    var SHOW_DELAY_MS = 2800;

    var deferredPrompt = null;
    var sheetEl = null;
    var showTimer = null;

    function isNativeCapacitor() {
        try {
            return !!(
                window.Capacitor &&
                typeof window.Capacitor.isNativePlatform === 'function' &&
                window.Capacitor.isNativePlatform()
            );
        } catch (e) {
            return false;
        }
    }

    function isStandaloneDisplay() {
        if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
            return true;
        }
        if (window.matchMedia && window.matchMedia('(display-mode: minimal-ui)').matches) {
            return true;
        }
        // iOS Safari "Add to Home Screen"
        if (typeof navigator.standalone === 'boolean' && navigator.standalone) {
            return true;
        }
        if (document.referrer && document.referrer.indexOf('android-app://') === 0) {
            return true;
        }
        return false;
    }

    function markStandaloneBody() {
        if (isStandaloneDisplay() || isNativeCapacitor()) {
            document.documentElement.classList.add('mmhc-pwa-standalone');
            document.body.classList.add('mmhc-pwa-standalone');
        }
    }

    function isDismissed() {
        try {
            var until = parseInt(localStorage.getItem(DISMISS_KEY) || '0', 10);
            return until > Date.now();
        } catch (e) {
            return false;
        }
    }

    function isMarkedInstalled() {
        try {
            return localStorage.getItem(INSTALLED_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    function markInstalled() {
        try {
            localStorage.setItem(INSTALLED_KEY, '1');
            localStorage.removeItem(DISMISS_KEY);
        } catch (e) { /* ignore */ }
        hideSheet(true);
    }

    function dismissForDays() {
        try {
            var until = Date.now() + DISMISS_DAYS * 24 * 60 * 60 * 1000;
            localStorage.setItem(DISMISS_KEY, String(until));
        } catch (e) { /* ignore */ }
        hideSheet(true);
    }

    function shouldSkipPrompt() {
        if (isNativeCapacitor()) return true;
        if (isStandaloneDisplay()) return true;
        if (isMarkedInstalled()) return true;
        if (isDismissed()) return true;
        if (document.body && document.body.classList.contains('capacitor-app')) return true;
        return false;
    }

    function isIosSafari() {
        var ua = navigator.userAgent || '';
        var iOS = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        var webkit = /WebKit/.test(ua);
        var chromeOrCriOS = /CriOS|FxiOS|EdgiOS|OPiOS|Chrome|Android/.test(ua);
        return iOS && webkit && !chromeOrCriOS;
    }

    function iosTipAlreadySeen() {
        try {
            return localStorage.getItem(IOS_TIP_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    function markIosTipSeen() {
        try {
            localStorage.setItem(IOS_TIP_KEY, '1');
        } catch (e) { /* ignore */ }
    }

    function iconUrl() {
        return (document.querySelector('link[rel="apple-touch-icon"]') || {}).href ||
            '/icons/icon-192.png';
    }

    function buildSheet(mode) {
        var existing = document.getElementById('mmhcPwaInstall');
        if (existing) {
            existing.remove();
        }

        var root = document.createElement('div');
        root.id = 'mmhcPwaInstall';
        root.className = 'mmhc-pwa-install';
        root.setAttribute('role', 'dialog');
        root.setAttribute('aria-live', 'polite');
        root.setAttribute('aria-label', 'Install MeD Miracle app');

        var isIos = mode === 'ios';
        var title = 'Install MeD Miracle';
        var text = isIos
            ? 'Add to your Home Screen for a full-screen app experience.'
            : 'Install the app on your phone for faster access — like any other app.';

        var actionsHtml = isIos
            ? '<button type="button" class="mmhc-pwa-install__btn mmhc-pwa-install__btn--primary" data-pwa-action="got-it">Got it</button>' +
              '<button type="button" class="mmhc-pwa-install__btn mmhc-pwa-install__btn--ghost" data-pwa-action="dismiss">Not now</button>'
            : '<button type="button" class="mmhc-pwa-install__btn mmhc-pwa-install__btn--primary" data-pwa-action="install">Install</button>' +
              '<button type="button" class="mmhc-pwa-install__btn mmhc-pwa-install__btn--ghost" data-pwa-action="dismiss">Not now</button>';

        var iosSteps = isIos
            ? '<ol class="mmhc-pwa-install__ios-steps">' +
              '<li>Tap the <strong>Share</strong> button</li>' +
              '<li>Choose <strong>Add to Home Screen</strong></li>' +
              '<li>Tap <strong>Add</strong></li>' +
              '</ol>'
            : '';

        root.innerHTML =
            '<div class="mmhc-pwa-install__sheet">' +
            '<div class="mmhc-pwa-install__icon"><img src="' + iconUrl() + '" alt="" width="48" height="48"></div>' +
            '<div class="mmhc-pwa-install__body">' +
            '<p class="mmhc-pwa-install__title">' + title + '</p>' +
            '<p class="mmhc-pwa-install__text">' + text + '</p>' +
            iosSteps +
            '<div class="mmhc-pwa-install__actions">' + actionsHtml + '</div>' +
            '</div>' +
            '<button type="button" class="mmhc-pwa-install__close" data-pwa-action="dismiss" aria-label="Close">&times;</button>' +
            '</div>';

        document.body.appendChild(root);
        sheetEl = root;

        root.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-pwa-action]');
            if (!btn) return;
            var action = btn.getAttribute('data-pwa-action');
            if (action === 'install') {
                triggerInstall();
            } else if (action === 'got-it') {
                markIosTipSeen();
                dismissForDays();
            } else if (action === 'dismiss') {
                if (isIos) markIosTipSeen();
                dismissForDays();
            }
        });

        return root;
    }

    function showSheet(mode) {
        if (shouldSkipPrompt()) return;
        if (!sheetEl) buildSheet(mode || 'android');
        requestAnimationFrame(function () {
            if (sheetEl) sheetEl.classList.add('is-visible');
        });
    }

    function hideSheet(remove) {
        if (!sheetEl) return;
        sheetEl.classList.remove('is-visible');
        if (remove) {
            var el = sheetEl;
            sheetEl = null;
            setTimeout(function () {
                if (el && el.parentNode) el.parentNode.removeChild(el);
            }, 320);
        }
    }

    function scheduleShow(mode) {
        if (shouldSkipPrompt()) return;
        if (showTimer) clearTimeout(showTimer);
        showTimer = setTimeout(function () {
            if (shouldSkipPrompt()) return;
            showSheet(mode);
        }, SHOW_DELAY_MS);
    }

    async function triggerInstall() {
        if (!deferredPrompt) {
            hideSheet(true);
            return;
        }
        try {
            deferredPrompt.prompt();
            var choice = await deferredPrompt.userChoice;
            deferredPrompt = null;
            if (choice && choice.outcome === 'accepted') {
                markInstalled();
            } else {
                dismissForDays();
            }
        } catch (e) {
            dismissForDays();
        }
    }

    function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) return;
        if (isNativeCapacitor()) return;
        // Only on secure contexts (https or localhost)
        if (!window.isSecureContext) return;

        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function (err) {
                console.info('[MMHC PWA] SW register skipped:', err && err.message ? err.message : err);
            });
        });
    }

    function bindInstallEvents() {
        window.addEventListener('beforeinstallprompt', function (e) {
            if (shouldSkipPrompt()) return;
            e.preventDefault();
            deferredPrompt = e;
            scheduleShow('android');
        });

        window.addEventListener('appinstalled', function () {
            deferredPrompt = null;
            markInstalled();
        });

        // iOS has no beforeinstallprompt — show tip once on mobile Safari
        if (isIosSafari() && !iosTipAlreadySeen() && !shouldSkipPrompt()) {
            scheduleShow('ios');
        }
    }

    function init() {
        markStandaloneBody();
        registerServiceWorker();
        if (shouldSkipPrompt()) return;
        bindInstallEvents();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
