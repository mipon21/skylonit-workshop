# Firebase (FCM) Setup Verification

This checklist verifies that the Firebase push notification feature is correctly wired and what to confirm before going live.

## 1. Backend (Laravel)

| Check | Location | Status |
|-------|----------|--------|
| Config file exists | `config/fcm.php` | ✅ Defines v1 (`project_id`, `client_email`, `private_key`), `server_key`, `public`, `vapid_key` |
| FCM service | `app/Services/FcmService.php` | ✅ HTTP v1 (service account) or legacy fallback; no-op if not configured |
| Observer registered | `app/Providers/AppServiceProvider.php` | ✅ `ClientNotification::observe(ClientNotificationObserver::class)` |
| Observer sends push | `app/Observers/ClientNotificationObserver.php` | ✅ On `created`, calls `FcmService::sendToClientDevices` |
| Device model | `app/Models/ClientDevice.php` | ✅ Fillable, casts, `client()` relation |
| Client has devices | `app/Models/Client.php` | ✅ `devices()` HasMany ClientDevice |
| Migration | `database/migrations/*_create_client_devices_table.php` | ✅ Table `client_devices` with unique(client_id, fcm_token) |
| Register route | `routes/web.php` | ✅ `POST /client/devices/register` → `client.devices.register` |
| Unregister route | `routes/web.php` | ✅ `POST /client/devices/unregister` → `client.devices.unregister` |
| Controller | `app/Http/Controllers/ClientDeviceController.php` | ✅ Auth + client-only; updateOrCreate by (client_id, fcm_token) |
| Unread response | `ClientNotificationController::unread` | ✅ Returns `unread_count` for toolbar badge |

**Payload (FcmService):**  
Notification + data include: `notification_id`, `type`, `title`, `message`, `project_id`, `project_name`, `payment_id`, `payment_link`, `payment_status`, `amount`, `invoice_id`, `action_url`. All data values are strings (FCM requirement).

## 2. Frontend (Web client)

| Check | Location | Status |
|-------|----------|--------|
| Vite entry | `vite.config.js` | ✅ `resources/js/client-fcm.js` in input |
| FCM script | `resources/js/client-fcm.js` | ✅ Async init; permission → getToken → POST register; onMessage → clientNotificationAddFromPush |
| Config injection | `resources/views/layouts/app.blade.php` | ✅ Only when `Auth::user()->isClient()` and `config('fcm.public.api_key')`; sets `window.clientFcmConfig` (firebase, registerUrl, vapidKey) |
| Firebase config keys | Layout @php block | ✅ camelCase: apiKey, authDomain, projectId, storageBucket, messagingSenderId, appId |
| Popup hook | Same layout (inline script) | ✅ `window.clientNotificationAddFromPush` adds card + updates badge |
| Service worker | `public/firebase-messaging-sw.js` | ✅ Exists (minimal); required for getToken() |
| Mobile bell | Layout header | ✅ Client-only, `md:hidden`; id `client-notification-badge`; link to dashboard |
| Badge updates | Inline script | ✅ Polling sets badge from `unread_count`; dismiss decrements |

## 3. Environment

| Variable | Purpose |
|----------|---------|
| `FCM_PROJECT_ID`, `FCM_CLIENT_EMAIL`, `FCM_PRIVATE_KEY` | Backend: HTTP v1 (recommended; use when Legacy is disabled) |
| `FCM_SERVER_KEY` | Backend: legacy API fallback only (if still enabled in Firebase) |
| `FIREBASE_API_KEY` | Web app config |
| `FIREBASE_AUTH_DOMAIN` | Web app config |
| `FIREBASE_PROJECT_ID` | Web app config |
| `FIREBASE_STORAGE_BUCKET` | Web app config |
| `FIREBASE_MESSAGING_SENDER_ID` | Web app config |
| `FIREBASE_APP_ID` | Web app config |
| `FIREBASE_VAPID_KEY` | Web Push; recommended for Chrome |

If neither v1 (project_id + client_email + private_key) nor legacy (server_key) is set, push is not sent. If `FIREBASE_API_KEY` is empty, FCM script is not loaded for clients.

## 4. Commands to run

```bash
# Migrations (client_devices table)
php artisan migrate

# Routes (confirm device routes exist)
php artisan route:list --name=client.devices

# Frontend (must include client-fcm.js in build)
npm install
npm run build
```

## 5. Behaviour summary

- **Existing system unchanged:** ClientNotification table, observers that create notifications, popups (normal + payment), polling, dashboard list, read/unread rules.
- **Push is additive:** On `ClientNotification::create`, observer also sends FCM to all devices for that client.
- **Read status:** Push delivery does not mark as read; only popup dismiss, action click, or mark-read API.
- **Foreground:** FCM `onMessage` calls `clientNotificationAddFromPush(n)` → same popup UI + badge.
- **Background:** Browser shows notification; `click_action` / `action_url` opens link.

## 6. Optional checks before production

1. **HTTPS** – Web Push requires HTTPS (or localhost).
2. **Firebase Console** – Web app added with your domain; VAPID key generated and set in `.env`.
3. **FCM Registration API** – For SDK 6.7+, enable in Google Cloud if needed (see main Firebase setup doc).
4. **HTTP v1** – The app uses FCM HTTP v1 by default when `FCM_PROJECT_ID`, `FCM_CLIENT_EMAIL`, and `FCM_PRIVATE_KEY` are set (recommended when “Cloud Messaging API (Legacy)” is disabled).
