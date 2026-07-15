/**
 * MMHC PWA — register service worker + smart install prompt.
 * Never shows if already installed (standalone), Capacitor, or dismissed.
 * Shows wait timer during install; only treats success on `appinstalled`.
 */
(function () {
    'use strict';

    var DISMISS_KEY = 'mmhc_pwa_dismiss_until';
    var INSTALLED_KEY = 'mmhc_pwa_installed';
    var IOS_TIP_KEY = 'mmhc_pwa_ios_tip_seen';
    var DISMISS_DAYS = 14;
    var SHOW_DELAY_MS = 2800;
    var INSTALL_WAIT_SEC = 15;
    var INSTALL_TIMEOUT_MS = 20000;

    var deferredPrompt = null;
    var sheetEl = null;
    var showTimer = null;
    var installWaitTimer = null;
    var installTimeout = null;
    var waitingForInstalled = false;

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

    function clearInstalledFlag() {
        try {
            localStorage.removeItem(INSTALLED_KEY);
        } catch (e) { /* ignore */ }
    }

    function markInstalled() {
        try {
            localStorage.setItem(INSTALLED_KEY, '1');
            localStorage.removeItem(DISMISS_KEY);
        } catch (e) { /* ignore */ }
        stopInstallWait();
        waitingForInstalled = false;
        showSuccessState();
    }

    function dismissForDays() {
        try {
            var until = Date.now() + DISMISS_DAYS * 24 * 60 * 60 * 1000;
            localStorage.setItem(DISMISS_KEY, String(until));
        } catch (e) { /* ignore */ }
        stopInstallWait();
        hideSheet(true);
    }

    function shouldSkipPrompt() {
        if (isNativeCapacitor()) return true;
        if (isStandaloneDisplay()) return true;
        if (isMarkedInstalled() && !deferredPrompt) return true;
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

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function setSheetContent(opts) {
        if (!sheetEl) return;
        var body = sheetEl.querySelector('.mmhc-pwa-install__body');
        if (!body) return;

        var actions = '';
        (opts.actions || []).forEach(function (a) {
            var cls = a.primary
                ? 'mmhc-pwa-install__btn mmhc-pwa-install__btn--primary'
                : 'mmhc-pwa-install__btn mmhc-pwa-install__btn--ghost';
            actions +=
                '<button type="button" class="' + cls + '" data-pwa-action="' + a.action + '">' +
                escapeHtml(a.label) +
                '</button>';
        });

        var waitBlock = opts.wait
            ? '<div class="mmhc-pwa-install__wait" aria-live="polite">' +
              '<div class="mmhc-pwa-install__spinner" aria-hidden="true"></div>' +
              '<div class="mmhc-pwa-install__wait-meta">' +
              '<span class="mmhc-pwa-install__wait-label">Installing… please wait</span>' +
              '<span class="mmhc-pwa-install__wait-time" id="mmhcPwaWaitTime">About ' +
              INSTALL_WAIT_SEC +
              's remaining</span>' +
              '</div></div>'
            : '';

        var tip = opts.tip
            ? '<p class="mmhc-pwa-install__tip">' + opts.tip + '</p>'
            : '';

        body.innerHTML =
            '<p class="mmhc-pwa-install__title">' + escapeHtml(opts.title) + '</p>' +
            '<p class="mmhc-pwa-install__text">' + escapeHtml(opts.text) + '</p>' +
            waitBlock +
            tip +
            (opts.iosSteps || '') +
            '<div class="mmhc-pwa-install__actions">' + actions + '</div>';
    }

    function stopInstallWait() {
        if (installWaitTimer) {
            clearInterval(installWaitTimer);
            installWaitTimer = null;
        }
        if (installTimeout) {
            clearTimeout(installTimeout);
            installTimeout = null;
        }
    }

    function startInstallWaitCountdown() {
        stopInstallWait();
        var left = INSTALL_WAIT_SEC;
        var el = document.getElementById('mmhcPwaWaitTime');
        if (el) el.textContent = 'About ' + left + 's remaining';

        installWaitTimer = setInterval(function () {
            left -= 1;
            var node = document.getElementById('mmhcPwaWaitTime');
            if (!node) return;
            if (left <= 0) {
                node.textContent = 'Finishing up… almost done';
            } else {
                node.textContent = 'About ' + left + 's remaining';
            }
        }, 1000);

        installTimeout = setTimeout(function () {
            if (!waitingForInstalled) return;
            waitingForInstalled = false;
            stopInstallWait();
            showHelpState(
                'Install is taking longer than usual',
                'Chrome may still be adding the icon. Check your Home screen for “MeD Miracle”. It will not appear in the Play Store.'
            );
        }, INSTALL_TIMEOUT_MS);
    }

    function showInstallingState() {
        setSheetContent({
            title: 'Installing MeD Miracle',
            text: 'Please keep this page open. This is usually quick — not a heavy download.',
            wait: true,
            tip: 'When done, look on your <strong>Home screen</strong> (not Play Store) for “MeD Miracle”.',
            actions: []
        });
        // tip used escapeHtml path - need raw tip with strong. Fix: pass tipHtml separately
        var tipEl = sheetEl && sheetEl.querySelector('.mmhc-pwa-install__tip');
        if (tipEl) {
            tipEl.innerHTML =
                'When done, look on your <strong>Home screen</strong> (not Play Store) for “MeD Miracle”.';
        }
        startInstallWaitCountdown();
    }

    function showSuccessState() {
        if (!sheetEl) buildSheet('android');
        setSheetContent({
            title: 'Installed ✓',
            text: 'Open MeD Miracle from your Home screen — like any other app.',
            tip: '',
            actions: [
                { label: 'Got it', action: 'close-success', primary: true }
            ]
        });
        var tipEl = sheetEl.querySelector('.mmhc-pwa-install__tip');
        if (tipEl) {
            tipEl.innerHTML =
                'Swipe your Home screens and search apps for <strong>MeD Miracle</strong>. It is not listed in the Play Store.';
        }
        if (sheetEl) sheetEl.classList.add('is-visible');
    }

    function showHelpState(title, text) {
        if (!sheetEl) buildSheet('android');
        setSheetContent({
            title: title,
            text: text,
            actions: [
                { label: 'Try again', action: 'retry', primary: true },
                { label: 'Not now', action: 'dismiss', primary: false }
            ]
        });
        var tipEl = sheetEl.querySelector('.mmhc-pwa-install__tip');
        if (!tipEl && sheetEl) {
            var body = sheetEl.querySelector('.mmhc-pwa-install__body');
            if (body) {
                var p = document.createElement('p');
                p.className = 'mmhc-pwa-install__tip';
                p.innerHTML =
                    'Or open Chrome menu (⋮) → <strong>Install app</strong> / <strong>Add to Home screen</strong>.';
                var actions = body.querySelector('.mmhc-pwa-install__actions');
                body.insertBefore(p, actions);
            }
        }
        if (sheetEl) sheetEl.classList.add('is-visible');
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
        var iosSteps = isIos
            ? '<ol class="mmhc-pwa-install__ios-steps">' +
              '<li>Tap <strong>Share</strong> at the bottom of Safari</li>' +
              '<li>Scroll and choose <strong>Add to Home Screen</strong></li>' +
              '<li>Tap <strong>Add</strong> — open from your Home screen</li>' +
              '</ol>'
            : '';

        root.innerHTML =
            '<div class="mmhc-pwa-install__sheet">' +
            '<div class="mmhc-pwa-install__icon"><img src="' + iconUrl() + '" alt="" width="48" height="48"></div>' +
            '<div class="mmhc-pwa-install__body"></div>' +
            '<button type="button" class="mmhc-pwa-install__close" data-pwa-action="dismiss" aria-label="Close">&times;</button>' +
            '</div>';

        document.body.appendChild(root);
        sheetEl = root;

        if (isIos) {
            setSheetContent({
                title: 'Install MeD Miracle',
                text: 'Add to your Home Screen for a full-screen app experience.',
                iosSteps: iosSteps,
                actions: [
                    { label: 'Got it', action: 'got-it', primary: true },
                    { label: 'Not now', action: 'dismiss', primary: false }
                ]
            });
        } else {
            setSheetContent({
                title: 'Install MeD Miracle',
                text: 'Install on your phone for faster access — like any other app. Light install; usually under 15 seconds.',
                actions: [
                    { label: 'Install', action: 'install', primary: true },
                    { label: 'Not now', action: 'dismiss', primary: false }
                ]
            });
        }

        root.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-pwa-action]');
            if (!btn) return;
            var action = btn.getAttribute('data-pwa-action');
            if (action === 'install' || action === 'retry') {
                triggerInstall();
            } else if (action === 'got-it') {
                markIosTipSeen();
                dismissForDays();
            } else if (action === 'close-success') {
                hideSheet(true);
            } else if (action === 'dismiss') {
                if (isIos) markIosTipSeen();
                dismissForDays();
            }
        });

        return root;
    }

    function showSheet(mode, force) {
        if (!force && shouldSkipPrompt() && !waitingForInstalled) return;
        if (!sheetEl) buildSheet(mode || (isIosSafari() ? 'ios' : 'android'));
        requestAnimationFrame(function () {
            if (sheetEl) sheetEl.classList.add('is-visible');
        });
    }

    /**
     * Manual install from menu / /install page — does not wait for the auto popup.
     */
    function openInstallPrompt() {
        if (isNativeCapacitor()) {
            return false;
        }
        if (isStandaloneDisplay()) {
            try {
                alert('MeD Miracle is already installed. Open it from your Home screen.');
            } catch (e) { /* ignore */ }
            return false;
        }

        // Manual open ignores the 14-day "Not now" dismiss
        if (showTimer) {
            clearTimeout(showTimer);
            showTimer = null;
        }

        var mode = isIosSafari() ? 'ios' : 'android';
        if (sheetEl) {
            sheetEl.remove();
            sheetEl = null;
        }
        buildSheet(mode);
        showSheet(mode, true);

        if (mode === 'android' && !deferredPrompt) {
            showHelpState(
                'Install MeD Miracle',
                'Tap Chrome menu (⋮) → Install app / Add to Home screen. Or wait a moment on this page and tap Install again when Chrome is ready.'
            );
        }

        return true;
    }

    function bindMenuTriggers() {
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('[data-mmhc-pwa-install]');
            if (!trigger) return;
            e.preventDefault();
            openInstallPrompt();
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
            showHelpState(
                'Install not ready yet',
                'Open this site in Chrome, wait a few seconds, then try again. Or use Chrome menu → Install app.'
            );
            return;
        }

        showInstallingState();
        waitingForInstalled = true;

        try {
            deferredPrompt.prompt();
            var choice = await deferredPrompt.userChoice;
            deferredPrompt = null;

            if (!choice || choice.outcome !== 'accepted') {
                waitingForInstalled = false;
                stopInstallWait();
                dismissForDays();
                return;
            }

            // Do NOT mark installed yet — wait for appinstalled (or timeout help).
            var timeEl = document.getElementById('mmhcPwaWaitTime');
            if (timeEl) {
                timeEl.textContent = 'Chrome is adding the icon… usually a few more seconds';
            }
        } catch (e) {
            waitingForInstalled = false;
            stopInstallWait();
            showHelpState(
                'Install didn’t finish',
                'Please try Chrome menu (⋮) → Install app, then check your Home screen for MeD Miracle.'
            );
        }
    }

    function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) return;
        if (isNativeCapacitor()) return;
        if (!window.isSecureContext) return;

        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function (err) {
                console.info('[MMHC PWA] SW register skipped:', err && err.message ? err.message : err);
            });
        });
    }

    function bindInstallEvents() {
        window.addEventListener('beforeinstallprompt', function (e) {
            // Chrome offering install again = previous install did not stick
            clearInstalledFlag();
            e.preventDefault();
            deferredPrompt = e;
            if (isDismissed()) return;
            scheduleShow('android');
        });

        window.addEventListener('appinstalled', function () {
            deferredPrompt = null;
            waitingForInstalled = false;
            markInstalled();
        });

        if (isIosSafari() && !iosTipAlreadySeen() && !shouldSkipPrompt()) {
            scheduleShow('ios');
        }
    }

    function init() {
        markStandaloneBody();

        // If we're truly running as installed app, lock the flag and never prompt
        if (isStandaloneDisplay()) {
            try {
                localStorage.setItem(INSTALLED_KEY, '1');
            } catch (e) { /* ignore */ }
            registerServiceWorker();
            bindMenuTriggers();
            window.mmhcPwa = {
                install: openInstallPrompt,
                isInstalled: function () { return true; },
                canPrompt: function () { return false; }
            };
            return;
        }

        registerServiceWorker();
        bindMenuTriggers();
        window.mmhcPwa = {
            install: openInstallPrompt,
            isInstalled: isStandaloneDisplay,
            canPrompt: function () { return !!deferredPrompt; }
        };
        if (isNativeCapacitor()) return;
        bindInstallEvents();

        // /install page opens the sheet immediately
        if (document.body && document.body.getAttribute('data-mmhc-pwa-autostart') === '1') {
            setTimeout(function () {
                openInstallPrompt();
            }, 600);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
