/* MeD Miracle PWA service worker — network-first for pages, cache for shell assets + Web Push */
const CACHE_VERSION = 'mmhc-pwa-v7';
const SHELL_CACHE = `${CACHE_VERSION}-shell`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;

const PRECACHE_URLS = [
    '/offline.html',
    '/css/mobile-crm.css',
    '/css/capacitor-app.css',
    '/css/pwa-install.css',
    '/js/pwa-install.js',
    '/js/pwa-push.js',
    '/js/capacitor-app.js',
    '/js/mobile-crm.js',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL_CACHE).then((cache) =>
            Promise.all(
                PRECACHE_URLS.map((url) =>
                    cache.add(url).catch(() => {
                        /* skip missing/blocked asset so SW still activates */
                    })
                )
            )
        ).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key.startsWith('mmhc-pwa-') && key !== SHELL_CACHE && key !== RUNTIME_CACHE)
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

function isSkippableRequest(request) {
    if (request.method !== 'GET') {
        return true;
    }
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return true;
    }
    if (url.pathname.startsWith('/admin/telescope') || url.pathname.startsWith('/_debugbar')) {
        return true;
    }
    // Never cache auth-mutating or chatty endpoints
    if (
        url.pathname.includes('/logout') ||
        url.pathname.includes('/sanctum/') ||
        url.searchParams.has('nocache')
    ) {
        return true;
    }
    return false;
}

function isStaticAsset(url) {
    return (
        url.pathname.startsWith('/css/') ||
        url.pathname.startsWith('/js/') ||
        url.pathname.startsWith('/images/') ||
        url.pathname.startsWith('/fonts/') ||
        url.pathname === '/favicon.svg' ||
        url.pathname === '/favicon.ico' ||
        url.pathname === '/offline.html'
    );
}

function isIconOrManifest(url) {
    return (
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/pwa-icon/') ||
        url.pathname === '/apple-touch-icon.png' ||
        url.pathname === '/manifest.webmanifest'
    );
}

async function networkFirstAsset(request) {
    try {
        const response = await fetch(request, { cache: 'no-store' });
        if (response && response.ok) {
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (err) {
        return (await caches.match(request)) || Response.error();
    }
}

async function networkFirstNavigation(request) {
    try {
        const response = await fetch(request);
        return response;
    } catch (err) {
        const offline = await caches.match('/offline.html');
        return offline || Response.error();
    }
}

async function staleWhileRevalidate(request) {
    const cache = await caches.open(RUNTIME_CACHE);
    const cached = await cache.match(request);
    const networkPromise = fetch(request)
        .then((response) => {
            if (response && response.ok) {
                cache.put(request, response.clone());
            }
            return response;
        })
        .catch(() => cached);
    return cached || networkPromise;
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (isSkippableRequest(request)) {
        return;
    }

    const url = new URL(request.url);

    if (request.mode === 'navigate') {
        event.respondWith(networkFirstNavigation(request));
        return;
    }

    if (isIconOrManifest(url)) {
        event.respondWith(networkFirstAsset(request));
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(staleWhileRevalidate(request));
    }
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

self.addEventListener('push', (event) => {
    let data = {
        title: 'MeD Miracle',
        body: 'You have a new update.',
        url: '/dashboard',
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
    };

    try {
        if (event.data) {
            const parsed = event.data.json();
            data = Object.assign(data, parsed || {});
        }
    } catch (e) {
        try {
            data.body = event.data ? event.data.text() : data.body;
        } catch (e2) { /* ignore */ }
    }

    event.waitUntil(
        self.registration.showNotification(data.title || 'MeD Miracle', {
            body: data.body || '',
            icon: data.icon || '/icons/icon-192.png',
            badge: data.badge || '/icons/icon-192.png',
            data: { url: data.url || '/dashboard' },
            vibrate: [120, 60, 120],
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = (event.notification.data && event.notification.data.url)
        ? event.notification.data.url
        : '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if ('focus' in client) {
                    client.focus();
                    if ('navigate' in client) {
                        return client.navigate(targetUrl);
                    }
                    return undefined;
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
            return undefined;
        })
    );
});
