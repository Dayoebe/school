const CACHE_VERSION = 'elites-pwa-v5';
const CORE_CACHE = `${CACHE_VERSION}-core`;
const PAGE_CACHE = `${CACHE_VERSION}-pages`;
const ASSET_CACHE = `${CACHE_VERSION}-assets`;
const OFFLINE_URL = './offline.html';
const MANIFEST_URL = './build/manifest.json';
const CORE_ASSETS = [
  OFFLINE_URL,
  './',
  './icons/pwa-192x192.png',
  './icons/pwa-512x512.png',
  './icons/pwa-512x512-maskable.png',
];

const STATIC_ASSET_PATTERN = /\.(?:css|js|mjs|png|jpe?g|svg|webp|ico|woff2?|ttf)$/i;
const EXCLUDED_PATH_PREFIXES = [
  '/livewire/',
  '/logout',
  '/sanctum/',
  '/broadcasting/',
  '/telescope/',
];
const EXCLUDED_DOCUMENT_PREFIXES = [
  '/cbt/exam/',
];

function isSameOrigin(url) {
  return url.origin === self.location.origin;
}

function pathStartsWith(pathname, prefixes) {
  return prefixes.some((prefix) => pathname.startsWith(prefix));
}

function isExcludedRequest(url) {
  return pathStartsWith(url.pathname, EXCLUDED_PATH_PREFIXES);
}

function isExcludedDocument(url) {
  return pathStartsWith(url.pathname, EXCLUDED_DOCUMENT_PREFIXES);
}

function isDocumentRequest(request) {
  const accept = request.headers.get('accept') || '';

  return request.mode === 'navigate' || accept.includes('text/html');
}

function shouldCacheStaticAsset(url) {
  return (
    isSameOrigin(url) &&
    (
      url.pathname.includes('/build/') ||
      url.pathname.includes('/images/') ||
      url.pathname.includes('/img/') ||
      url.pathname.includes('/icons/') ||
      STATIC_ASSET_PATTERN.test(url.pathname)
    )
  );
}

async function loadManifestAssets() {
  try {
    const response = await fetch(MANIFEST_URL, { cache: 'no-store' });

    if (!response.ok) {
      return [];
    }

    const manifest = await response.json();

    return Object.values(manifest)
      .map((entry) => entry && entry.file ? `./build/${entry.file}` : null)
      .filter(Boolean);
  } catch (error) {
    return [];
  }
}

async function installCoreAssets() {
  const manifestAssets = await loadManifestAssets();
  const assetsToCache = [...new Set([...CORE_ASSETS, ...manifestAssets])];
  const cache = await caches.open(CORE_CACHE);

  await Promise.allSettled(
    assetsToCache.map(async (asset) => {
      try {
        const request = new Request(asset, { cache: 'reload' });
        const response = await fetch(request);

        if (!response || !response.ok) {
          throw new Error(`Unexpected response: ${response ? response.status : 'no response'}`);
        }

        await cache.put(request, response.clone());
      } catch (error) {
        console.warn('[service-worker] Failed to precache asset:', asset, error);
      }
    })
  );
}

async function cleanupOldCaches() {
  const expectedCaches = [CORE_CACHE, PAGE_CACHE, ASSET_CACHE];
  const cacheKeys = await caches.keys();

  await Promise.all(
    cacheKeys
      .filter((key) => !expectedCaches.includes(key))
      .map((key) => caches.delete(key))
  );
}

async function handleDocumentRequest(request) {
  const pageCache = await caches.open(PAGE_CACHE);

  try {
    const response = await fetch(request);

    if (response && response.ok && response.type === 'basic') {
      pageCache.put(request, response.clone()).catch(() => undefined);
    }

    return response;
  } catch (error) {
    const cachedResponse = await pageCache.match(request);

    if (cachedResponse) {
      return cachedResponse;
    }

    return caches.match(OFFLINE_URL, { ignoreSearch: true });
  }
}

async function handleStaticAssetRequest(request) {
  const assetCache = await caches.open(ASSET_CACHE);
  const cachedResponse = await assetCache.match(request);

  const networkRequest = fetch(request)
    .then((response) => {
      if (response && response.ok && response.type === 'basic') {
        assetCache.put(request, response.clone()).catch(() => undefined);
      }

      return response;
    })
    .catch(() => cachedResponse);

  return cachedResponse || networkRequest;
}

self.addEventListener('install', (event) => {
  event.waitUntil(
    installCoreAssets().then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    cleanupOldCaches().then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;

  if (request.method !== 'GET') {
    return;
  }

  const requestUrl = new URL(request.url);

  if (!isSameOrigin(requestUrl) || isExcludedRequest(requestUrl)) {
    return;
  }

  if (isDocumentRequest(request)) {
    if (isExcludedDocument(requestUrl)) {
      return;
    }

    event.respondWith(handleDocumentRequest(request));
    return;
  }

  if (shouldCacheStaticAsset(requestUrl)) {
    event.respondWith(handleStaticAssetRequest(request));
  }
});
