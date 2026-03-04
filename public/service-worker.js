const CACHE_VERSION = 'elites-pwa-v1';
const OFFLINE_URL = './offline.html';
const CORE_ASSETS = [
  OFFLINE_URL,
  './icons/pwa-192x192.png',
  './icons/pwa-512x512.png',
  './icons/pwa-512x512-maskable.png',
];

const STATIC_ASSET_PATTERN = /\.(?:css|js|png|jpe?g|svg|webp|ico|woff2?)$/i;

function shouldCacheAsset(url) {
  return (
    url.origin === self.location.origin &&
    (
      url.pathname.includes('/build/') ||
      url.pathname.includes('/images/') ||
      url.pathname.includes('/img/') ||
      url.pathname.includes('/icons/') ||
      STATIC_ASSET_PATTERN.test(url.pathname)
    )
  );
}

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches
      .open(CACHE_VERSION)
      .then((cache) => cache.addAll(CORE_ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) =>
        Promise.all(
          keys
            .filter((key) => key !== CACHE_VERSION)
            .map((key) => caches.delete(key))
        )
      )
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method !== 'GET') {
    return;
  }

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() => caches.match(OFFLINE_URL, { ignoreSearch: true }))
    );
    return;
  }

  const requestUrl = new URL(request.url);
  if (!shouldCacheAsset(requestUrl)) {
    return;
  }

  event.respondWith(
    caches.match(request).then((cachedResponse) => {
      const networkRequest = fetch(request)
        .then((response) => {
          if (response && response.ok) {
            const responseClone = response.clone();
            caches
              .open(CACHE_VERSION)
              .then((cache) => cache.put(request, responseClone))
              .catch(() => undefined);
          }
          return response;
        })
        .catch(() => cachedResponse);

      return cachedResponse || networkRequest;
    })
  );
});
