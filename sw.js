const CACHE_NAME = 'video-cache-v1';
const VIDEO_URLS = [
  '/lbq.mp4'  // 根据实际视频路径修改
];

// 安装阶段：缓存视频文件
self.addEventListener('install', event => {
  console.log('Service Worker 安装中...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('开始缓存视频文件');
        return cache.addAll(VIDEO_URLS);
      })
      .then(() => self.skipWaiting()) // 强制新 SW 立即激活
  );
});

// 激活阶段：清理旧缓存
self.addEventListener('activate', event => {
  console.log('Service Worker 激活中...');
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => key !== CACHE_NAME)
          .map(key => caches.delete(key))
      );
    }).then(() => self.clients.claim()) // 立即接管所有客户端
  );
});

// 拦截网络请求
self.addEventListener('fetch', event => {
  // 仅对视频请求进行缓存策略
  if (event.request.url.includes('/video/')) {
    event.respondWith(
      caches.match(event.request)
        .then(cachedResponse => {
          // 如果有缓存，直接返回；否则回退到网络
          return cachedResponse || fetch(event.request).then(networkResponse => {
            // 可选：将网络响应存入缓存（下次可用）
            const responseClone = networkResponse.clone();
            caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, responseClone);
            });
            return networkResponse;
          });
        })
    );
  } else {
    // 非视频请求走默认网络（不拦截）
    return;
  }
});