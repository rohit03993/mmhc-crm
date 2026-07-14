/* MeD Miracle PWA service worker — network-first for pages, cache for shell assets */
const CACHE_VERSION = 'mmhc-pwa-v3';
const SHELL_CACHE = `${CACHE_VERSION}-shell`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;

const PRECACHE_URLS = [
    '/offline.html',
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon.svg',
    '/favicon.svg',
    '/css/mobile-crm.css',
    '/css/capacitor-app.css',
    '/css/pwa-install.css',
    '/js/pwa-install.js',
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
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/images/') ||
        url.pathname.startsWith('/fonts/') ||
        url.pathname === '/favicon.svg' ||
        url.pathname === '/favicon.ico' ||
        url.pathname === '/manifest.webmanifest' ||
        url.pathname === '/offline.html'
    );
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

    if (isStaticAsset(url)) {
        event.respondWith(staleWhileRevalidate(request));
    }
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
