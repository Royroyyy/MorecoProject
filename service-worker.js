const CACHE_NAME = "moreco-cache-v1";

const PRECACHE_URLS = [ "./", "index.html", "about.html", "account.html", "announcements.html", "apply.html", "benefits.html", "dashboard.html", "events.html", "login.html", "loans.html", "notifications.html", "orientation.html", "signup.html", "transactions.html", "withdrawals.html", "style.css", "manifest.json", "js/main.js", "js/auth.js", "js/membership.js", "js/transactions.js", "js/notifications.js", "assets/icons/icon-192.png", "assets/icons/icon-512.png" ];

self.addEventListener("install", event => {
  event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE_URLS)).catch(err => console.warn("SW precache failed (non-fatal):", err)));
  self.skipWaiting();
});

self.addEventListener("activate", event => {
  event.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key)))));
  self.clients.claim();
});

self.addEventListener("fetch", event => {
  const url = new URL(event.request.url);
  if (event.request.method !== "GET") return;
  if (url.pathname.includes("/api/")) {
    event.respondWith(fetch(event.request).catch(() => new Response(JSON.stringify({
      success: false,
      message: "You are offline. Please reconnect to continue."
    }), {
      headers: {
        "Content-Type": "application/json"
      }
    })));
    return;
  }
  event.respondWith(caches.match(event.request).then(cachedResponse => {
    const networkFetch = fetch(event.request).then(networkResponse => {
      if (networkResponse && networkResponse.status === 200) {
        const clone = networkResponse.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
      }
      return networkResponse;
    }).catch(() => cachedResponse);
    return cachedResponse || networkFetch;
  }));
});