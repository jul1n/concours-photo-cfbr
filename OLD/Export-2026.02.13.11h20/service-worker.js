const CACHE_NAME = 'cfbr-jury -v1';
const ASSETS_TO_CACHE = [
    'assets/favicon.png',
    // Add other static assets if known, e.g. CSS frameworks if local
    // For CDNs, we rely on browser cache or can attempt to cache opaque responses
];

// Install Event
self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(ASSETS_TO_CACHE))
    );
});

// Activate Event
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.map(key => {
                if (key !== CACHE_NAME) return caches.delete(key);
            })
        ))
    );
});

// Fetch Event (Caching Strategy)
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Strategy 1: Stale-While-Revalidate for Photos (Cache first, then update)
    if (url.pathname.includes('/photos/')) {
        event.respondWith(
            caches.open(CACHE_NAME).then(cache => {
                return cache.match(event.request).then(cachedResponse => {
                    const fetchPromise = fetch(event.request).then(networkResponse => {
                        cache.put(event.request, networkResponse.clone());
                        return networkResponse;
                    });
                    return cachedResponse || fetchPromise;
                });
            })
        );
        return;
    }

    // Strategy 2: Network First for Pages (PHP) to ensure fresh logic
    if (url.pathname.endsWith('.php') || url.pathname.endsWith('/')) {
        event.respondWith(
            fetch(event.request).catch(() => {
                // Return offline fallback if available
                return caches.match('offline.html');
            })
        );
        return;
    }

    // Default: Cache First for assets, Network for others
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});
