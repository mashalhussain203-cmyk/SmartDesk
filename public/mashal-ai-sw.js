const CACHE_NAME = 'mashal-ai-shell-v1';

const STATIC_ASSETS = [
    '/manifest.webmanifest',
    '/icons/mashal-ai-192.png',
    '/icons/mashal-ai-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (
        request.method !== 'GET'
        || request.url.includes('/ai-chat/workspace/')
        || request.url.includes('/ai-chat/message')
        || request.url.includes('/ai-chat/voice/')
    ) {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    event.respondWith(
        fetch(request)
            .then((response) => {
                if (
                    response.ok
                    && (
                        request.destination === 'style'
                        || request.destination === 'script'
                        || request.destination === 'image'
                        || request.destination === 'font'
                    )
                ) {
                    const copy = response.clone();

                    caches.open(CACHE_NAME)
                        .then((cache) => cache.put(request, copy));
                }

                return response;
            })
            .catch(() => caches.match(request))
    );
});
