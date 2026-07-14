/**
 * MMHC PWA Web Push — subscribe logged-in browsers (no Capacitor / Play Store).
 * Requires VAPID public key from /push/vapid-public-key and service worker /sw.js
 */
(function () {
    'use strict';

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var rawData = window.atob(base64);
        var outputArray = new Uint8Array(rawData.length);
        for (var i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function canUsePush() {
        return (
            'serviceWorker' in navigator &&
            'PushManager' in window &&
            'Notification' in window &&
            !!document.querySelector('meta[name="csrf-token"]')
        );
    }

    function isLoggedInCrm() {
        return !!(document.body && document.body.classList.contains('mmhc-crm-auth'));
    }

    async function fetchPublicKey() {
        var resp = await fetch('/push/vapid-public-key', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin'
        });
        if (!resp.ok) {
            return null;
        }
        var data = await resp.json();
        if (!data.success || !data.publicKey) {
            return null;
        }
        return data.publicKey;
    }

    async function postSubscription(subscription) {
        var json = subscription.toJSON();
        var resp = await fetch('/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                endpoint: json.endpoint,
                keys: json.keys,
                contentEncoding: (PushManager.supportedContentEncodings && PushManager.supportedContentEncodings[0]) || 'aesgcm'
            })
        });
        return resp.ok;
    }

    async function enablePush() {
        if (!canUsePush() || !isLoggedInCrm()) {
            return false;
        }

        var publicKey = await fetchPublicKey();
        if (!publicKey) {
            return false;
        }

        var permission = Notification.permission;
        if (permission === 'default') {
            permission = await Notification.requestPermission();
        }
        if (permission !== 'granted') {
            return false;
        }

        var registration = await navigator.serviceWorker.ready;
        var existing = await registration.pushManager.getSubscription();
        var subscription = existing;
        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(publicKey)
            });
        }

        return postSubscription(subscription);
    }

    function schedule() {
        // Ask after a short delay so login UI settles; only when logged in.
        window.setTimeout(function () {
            enablePush().catch(function () { /* silent */ });
        }, 4000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', schedule);
    } else {
        schedule();
    }

    window.mmhcEnableWebPush = enablePush;
})();
