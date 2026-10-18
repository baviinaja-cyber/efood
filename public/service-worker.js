// EFOOD Service Worker (PWA)
const CACHE_NAME = 'efood-static-v1.0.0';
const STATIC_ASSETS = [
  '/',
  '/assets/css/app.css',
  '/assets/js/app.js',
  '/assets/js/geolocation.js',
  '/manifest.webmanifest',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
  'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
  'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
];

// Install event: Pre-cache aset statis
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS).catch((err) => {
        console.warn('Gagal pre-cache sebagian aset:', err);
      });
    })
  );
  self.skipWaiting();
});

// Activate event: Bersihkan cache versi lama
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch event: Network-first untuk rute dinamis, cache-first hanya untuk aset statis
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  // PENTING: JANGAN PERNAH CACHE halaman Admin, Checkout, Akun, API, atau request POST!
  if (
    event.request.method !== 'GET' ||
    url.pathname.startsWith('/admin') ||
    url.pathname.startsWith('/checkout') ||
    url.pathname.startsWith('/akun') ||
    url.pathname.startsWith('/api') ||
    url.pathname.startsWith('/cart') ||
    url.pathname.startsWith('/login') ||
    url.pathname.startsWith('/register')
  ) {
    return; // Langsung ke network
  }

  // Untuk file CSS, JS, font, dan gambar static: Cache First, fallback Network
  if (
    url.pathname.match(/\.(css|js|woff2|woff|ttf|png|jpg|jpeg|svg|webp|ico)$/) ||
    url.origin.includes('cdn.jsdelivr.net') ||
    url.origin.includes('cdnjs.cloudflare.com') ||
    url.origin.includes('unpkg.com')
  ) {
    event.respondWith(
      caches.match(event.request).then((cachedResponse) => {
        if (cachedResponse) {
          return cachedResponse;
        }
        return fetch(event.request).then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseToCache = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });
          }
          return networkResponse;
        });
      })
    );
    return;
  }

  // Untuk navigasi HTML umum: Network First, fallback Cache
  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request);
    })
  );
});
