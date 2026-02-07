/* Minimal FCM service worker. Must exist at domain root for getToken() to work.
 * Background notification display is handled by the browser when using the
 * "notification" payload; click_action opens the URL set by the server.
 */
self.addEventListener('install', function () {
  self.skipWaiting();
});
self.addEventListener('activate', function (event) {
  event.waitUntil(self.clients.claim());
});
