const CACHE_NAME = 'dependex-pwa-v6.3';
const STATIC_ASSETS = [
  'offline.html',
  'manifest.webmanifest',
  'assets/css/rainbow-neon.css',
  'assets/css/app.css',
  'assets/js/dx-telemetry.js',
  'assets/js/dx-voice-sos.js',
  'assets/js/dx-micro-checkin.js',
  'assets/js/dx-local-reminders.js',
  'data/recensioni_club_italia.json',
  'data/CENSIMENTO_CLUB_CAT_ITALIA_2026.csv',
  'assets/logo.svg',
  'assets/logo.png',
  'widget-club.php'
];

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys.map((k) => {
          if (k !== CACHE_NAME) {
            return caches.delete(k);
          }
        })
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);

  // HTML navigation requests: Network first with offline fallback
  if (req.mode === 'navigate' || req.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(req)
        .then((response) => {
          if (response && response.status === 200) {
            const copy = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(req, copy));
          }
          return response;
        })
        .catch(async () => {
          const cached = await caches.match(req);
          if (cached) return cached;
          return caches.match('offline.html');
        })
    );
    return;
  }

  // API Territoriali & GeoJSON: Stale-While-Revalidate con supporto Offline totale
  if (url.pathname.includes('api-opendata-geojson.php') ||
      url.pathname.includes('api-clubs-italy.php') ||
      url.pathname.includes('api-feed-territorio.php')) {
    event.respondWith(
      caches.match(req).then((cachedResponse) => {
        const fetchPromise = fetch(req)
          .then((networkResponse) => {
            if (networkResponse && networkResponse.status === 200) {
              caches.open(CACHE_NAME).then((cache) => cache.put(req, networkResponse.clone()));
            }
            return networkResponse;
          })
          .catch(() => cachedResponse);
        return cachedResponse || fetchPromise;
      })
    );
    return;
  }

  // Static Assets (CSS, JS, images): Stale-While-Revalidate
  if (url.pathname.includes('/assets/') || url.pathname.endsWith('.svg') || url.pathname.endsWith('.json') || url.pathname.endsWith('.csv')) {
    event.respondWith(
      caches.match(req).then((cachedResponse) => {
        const fetchPromise = fetch(req)
          .then((networkResponse) => {
            if (networkResponse && networkResponse.status === 200) {
              caches.open(CACHE_NAME).then((cache) => cache.put(req, networkResponse.clone()));
            }
            return networkResponse;
          })
          .catch(() => {});
        return cachedResponse || fetchPromise;
      })
    );
  }
});