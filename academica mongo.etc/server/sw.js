const CACHE_NAME = 'academica-cache-v1';
const urlsToCache = [
  '/',
  '/index.html',
  '/main.js',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css',
  'https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css',
  'https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/semantic.min.css',
  'https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.8.1/socket.io.js'
];

// Evento de instalación: cachear recursos estáticos
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Service Worker: Cacheando recursos offline');
                return Promise.allSettled(
                    urlsToCache.map(url => {
                        return cache.add(url).catch(err => {
                            console.warn('Fallo al cachear:', url, err);
                        });
                    })
                );
            })
            .then(() => self.skipWaiting())
    );
});

// Evento de activación: limpiar cachés antiguos
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Service Worker: Eliminando caché antiguo:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Interceptar peticiones para servir desde caché si está offline
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    
    // Evitar cachear llamadas a APIs o WebSockets
    if (url.pathname.startsWith('/api') || url.pathname.startsWith('/socket.io')) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then(cachedResponse => {
                if (cachedResponse) {
                    // Retornamos el recurso del caché e intentamos actualizarlo de fondo
                    fetch(event.request).then(networkResponse => {
                        if (networkResponse && networkResponse.status === 200) {
                            caches.open(CACHE_NAME).then(cache => {
                                cache.put(event.request, networkResponse);
                            });
                        }
                    }).catch(() => { /* Silenciar error si está offline */ });
                    
                    return cachedResponse;
                }

                // Si no está en caché, intentar por la red
                return fetch(event.request).then(networkResponse => {
                    if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then(cache => {
                            cache.put(event.request, responseToCache);
                        });
                    }
                    return networkResponse;
                }).catch(err => {
                    console.log('Error al obtener recurso en offline:', err);
                });
            })
    );
});

self.addEventListener('push', event => {
    let data = { title: 'Nuevo Mensaje', body: 'Tienes un nuevo mensaje' };
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = { title: 'Nuevo Mensaje', body: event.data.text() };
        }
    }
    const options = {
        body: data.body,
        icon: 'https://avatars.githubusercontent.com/u/938710?v=4',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: '2'
        }
    };
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow('https://facebook.com')
    );
});
