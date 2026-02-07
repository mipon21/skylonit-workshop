/**
 * FCM (Firebase Cloud Messaging) for client push notifications.
 * Loaded only for authenticated client users; config is injected by the layout.
 * Works alongside existing polling and popup system — additive only.
 */
(async function () {
  const config = typeof window.clientFcmConfig !== 'undefined' ? window.clientFcmConfig : null;
  if (!config || !config.firebase || !config.registerUrl) return;

  let getMessaging, getToken, onMessage;
  try {
    const messagingMod = await import('firebase/messaging');
    const appMod = await import('firebase/app');
    getMessaging = messagingMod.getMessaging;
    getToken = messagingMod.getToken;
    onMessage = messagingMod.onMessage;
    if (typeof appMod.initializeApp !== 'function') return;
  } catch (e) {
    return;
  }

  const app = (await import('firebase/app')).initializeApp(config.firebase);
  const messaging = getMessaging(app);

  async function requestPermissionAndRegister() {
    try {
      const permission = await Notification.requestPermission();
      if (permission !== 'granted') return;
      const token = await getToken(messaging, {
        vapidKey: config.vapidKey || undefined,
      });
      if (!token) return;
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      await fetch(config.registerUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrf || '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
          fcm_token: token,
          platform: 'web',
        }),
      });
    } catch (e) {
      console.warn('FCM registration failed', e);
    }
  }

  onMessage(messaging, (payload) => {
    const d = payload?.data || {};
    const n = {
      id: parseInt(d.notification_id, 10) || 0,
      type: d.type || 'normal',
      title: d.title || payload?.notification?.title || 'Notification',
      message: d.message || payload?.notification?.body || '',
      show_url: d.action_url || '',
      project_name: d.project_name || '',
      amount: d.amount !== undefined && d.amount !== '' ? parseFloat(d.amount) : null,
      payment_link: d.payment_link || '',
      payment_status: d.payment_status || '',
      invoice_view_url: d.action_url && d.invoice_id ? d.action_url : null,
    };
    if (typeof window.clientNotificationAddFromPush === 'function') {
      window.clientNotificationAddFromPush(n);
    }
  });

  try {
    await requestPermissionAndRegister();
  } catch (e) {
    console.warn('FCM init failed', e);
  }
})();
