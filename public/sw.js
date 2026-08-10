// APTrades Service Worker — PWA Offline Support
const CACHE_NAME = 'aptrades-v1.0.0';
const STATIC_CACHE = 'aptrades-static-v1';
const DYNAMIC_CACHE = 'aptrades-dynamic-v1';

// Resources to cache on install
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/apple-touch-icon.png',
    '/favicon.png',
    '/offline'
];

// Install — cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
    );
});

// Activate — clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then(keys => {
                return Promise.all(
                    keys
                        .filter(key => key !== STATIC_CACHE && key !== DYNAMIC_CACHE)
                        .map(key => caches.delete(key))
                );
            })
            .then(() => self.clients.claim())
    );
});

// Fetch — cache-first for assets, network-first for pages
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip API calls and auth routes
    if (url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/login') ||
        url.pathname.startsWith('/admin/') ||
        url.pathname.startsWith('/install') ||
        url.pathname.includes('/deposit/') ||
        url.pathname.includes('/withdrawal/') ||
        url.pathname.includes('/trade')) {
        return; // Always network for sensitive routes
    }

    // Cache-first for static assets (CSS, JS, images, fonts)
    if (request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'font' ||
        request.destination === 'image') {
        event.respondWith(
            caches.match(request)
                .then(cached => {
                    if (cached) return cached;
                    return fetch(request).then(response => {
                        if (response.ok) {
                            const clone = response.clone();
                            caches.open(STATIC_CACHE).then(cache => cache.put(request, clone));
                        }
                        return response;
                    }).catch(() => caches.match('/offline'));
                })
        );
        return;
    }

    // Network-first for navigation (pages)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then(response => {
                    const clone = response.clone();
                    caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, clone));
                    return response;
                })
                .catch(() => {
                    return caches.match(request)
                        .then(cached => cached || caches.match('/'));
                })
        );
        return;
    }

    // Stale-while-revalidate for other requests
    event.respondWith(
        caches.match(request)
            .then(cached => {
                const fetchPromise = fetch(request).then(response => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, clone));
                    }
                    return response;
                }).catch(() => cached);
                return cached || fetchPromise;
            })
    );
});

// Handle messages from main thread
self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Push notifications (for future use)
self.addEventListener('push', (event) => {
    if (!event.data) return;

    const data = event.data.json();
    const options = {
        body: data.body || 'You have a new notification',
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-120.png',
        vibrate: [100, 50, 100],
        data: { url: data.url || '/' },
        actions: data.actions || []
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'APTrades', options)
    );
});

// Notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window' })
            .then(clientList => {
                for (const client of clientList) {
                    if (client.url === url && 'focus' in client) {
                        return client.focus();
                    }
                }
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
    );
});
