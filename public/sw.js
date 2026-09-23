// Service worker KARSA -- CLAUDE.md §14.
// Naikkan versi ini setiap kali strategi cache berubah agar client lama diperbarui.
const CACHE_VERSION = 'karsa-v2';
const SHELL_CACHE = `${CACHE_VERSION}-shell`;
const API_CACHE = `${CACHE_VERSION}-api`;
const TILE_CACHE = `${CACHE_VERSION}-tiles`;
const TILE_CACHE_MAX_ENTRIES = 300;

const SHELL_ROUTES = ['/offline', '/menu'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        (async () => {
            const cache = await caches.open(SHELL_CACHE);

            // Precache shell dasar (halaman fallback + menu).
            await cache.addAll(SHELL_ROUTES).catch(() => {});

            // Precache aset hasil build Vite (CSS/JS/font) dari manifest.
            try {
                const manifestRes = await fetch('/build/manifest.json');
                const manifest = await manifestRes.json();
                const assetUrls = Object.values(manifest)
                    .flatMap((entry) => [entry.file, ...(entry.css || [])])
                    .filter(Boolean)
                    .map((f) => `/build/${f}`);
                await cache.addAll(assetUrls);
            } catch (e) {
                // Build manifest belum tersedia (mis. saat dev) -- lewati precache aset.
            }

            self.skipWaiting();
        })()
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const keys = await caches.keys();
            await Promise.all(
                keys
                    .filter((key) => key.startsWith('karsa-') && !key.startsWith(CACHE_VERSION))
                    .map((key) => caches.delete(key))
            );
            await self.clients.claim();
        })()
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET') {
        return;
    }

    // Tile peta (OSM / CartoDB): cache-first, dibatasi jumlah entri.
    if (url.hostname.endsWith('tile.openstreetmap.org') || url.hostname.endsWith('basemaps.cartocdn.com')) {
        event.respondWith(cacheFirstWithLimit(request, TILE_CACHE, TILE_CACHE_MAX_ENTRIES));

        return;
    }

    // API internal: stale-while-revalidate + catat waktu pembaruan terakhir.
    if (url.pathname.startsWith('/api/v1/')) {
        event.respondWith(staleWhileRevalidate(request));

        return;
    }

    // Navigasi halaman: network-first, fallback cache, lalu /offline.
    if (request.mode === 'navigate') {
        event.respondWith(networkFirstNavigation(request));

        return;
    }

    // Aset build (CSS/JS/font): cache-first karena nama file sudah di-hash.
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(request).then((cached) => cached || fetch(request))
        );
    }
});

async function cacheFirstWithLimit(request, cacheName, maxEntries) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);
        await cache.put(request, response.clone());
        trimCache(cacheName, maxEntries);

        return response;
    } catch (e) {
        return cached || Response.error();
    }
}

async function trimCache(cacheName, maxEntries) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();

    if (keys.length > maxEntries) {
        await cache.delete(keys[0]);
        trimCache(cacheName, maxEntries);
    }
}

async function staleWhileRevalidate(request) {
    const cache = await caches.open(API_CACHE);
    const cached = await cache.match(request);

    const networkFetch = fetch(request)
        .then((response) => {
            cache.put(request, response.clone());

            return response;
        })
        .catch(() => null);

    return cached || (await networkFetch) || Response.json({ error: 'offline', message: 'Tidak ada koneksi & belum ada data tersimpan.' }, { status: 503 });
}

async function networkFirstNavigation(request) {
    try {
        const response = await fetch(request);
        const cache = await caches.open(SHELL_CACHE);
        cache.put(request, response.clone());

        return response;
    } catch (e) {
        const cache = await caches.open(SHELL_CACHE);
        const cached = await cache.match(request);

        return cached || cache.match('/offline');
    }
}

// Background Sync: kirim ulang antrean laporan offline saat koneksi kembali.
// Data antrean disimpan di IndexedDB oleh halaman /lapor (idb-keyval, store "karsa-reports-queue").
self.addEventListener('sync', (event) => {
    if (event.tag === 'karsa-sync-reports') {
        event.waitUntil(flushReportQueue());
    }
});

async function flushReportQueue() {
    const clientsList = await self.clients.matchAll();
    clientsList.forEach((client) => client.postMessage({ type: 'flush-report-queue' }));
}
