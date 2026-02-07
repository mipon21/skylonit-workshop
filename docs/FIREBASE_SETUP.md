# Firebase Cloud Messaging (FCM) Setup

Push notifications are an **additional delivery channel** for client notifications. The existing notification system (ClientNotification, popups, polling, dashboard list) remains unchanged.

## What you need

- A [Firebase project](https://console.firebase.google.com/)
- HTTPS for your app (required for Web Push)

## 1. Create / use a Firebase project

1. Go to [Firebase Console](https://console.firebase.google.com/) and create or select a project.
2. Enable **Cloud Messaging**: the API is usually enabled by default.

## 2. Backend (Laravel) — sending push

Used to send push from the server when a `ClientNotification` is created. The app uses **FCM HTTP v1 API** when a service account is configured (recommended; works with “Cloud Messaging API (Legacy) Disabled”). Legacy API is only used as fallback if you set `FCM_SERVER_KEY` and do not set the service account keys.

### Option A: HTTP v1 (recommended — use when Legacy is disabled)

1. **Service account**  
   - Firebase Console → **Project Settings** → **Service accounts**.  
   - Click **Generate new private key** (or use an existing key).  
   - From the JSON file, you need: `project_id`, `client_email`, `private_key`.

2. Add to `.env`:
   ```env
   FCM_PROJECT_ID=your-project-id
   FCM_CLIENT_EMAIL=firebase-adminsdk-xxxxx@your-project.iam.gserviceaccount.com
   FCM_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n"
   ```
   For `FCM_PRIVATE_KEY`, paste the full `private_key` value from the JSON (including `\n` for newlines, or the raw multi-line string in quotes).

### Option B: Legacy API (only if still enabled in your project)

If your Firebase project still has “Cloud Messaging API (Legacy)” enabled, you can instead set:
```env
FCM_SERVER_KEY=your_legacy_server_key
```
The app will use v1 if `FCM_PROJECT_ID`, `FCM_CLIENT_EMAIL`, and `FCM_PRIVATE_KEY` are set; otherwise it uses the legacy key.

## 3. Web client — receiving push in the browser

Used so the client portal can request permission, get an FCM token, and receive messages.

1. **Register a Web app**  
   - Firebase Console → **Project Settings** → **General** → **Your apps** → **Add app** → **Web** (</>).  
   - Register the app; you get a `firebaseConfig` object.

2. **Web Push certificate (VAPID)**  
   - **Project Settings** → **Cloud Messaging** → **Web configuration** → **Web Push certificates**.  
   - Click **Generate key pair** and copy the **Key pair** (public key).

3. Add to `.env`:
   ```env
   FIREBASE_API_KEY=...
   FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
   FIREBASE_PROJECT_ID=your-project-id
   FIREBASE_STORAGE_BUCKET=your-project.appspot.com
   FIREBASE_MESSAGING_SENDER_ID=...
   FIREBASE_APP_ID=...
   FIREBASE_VAPID_KEY=your_generated_vapid_public_key
   ```

## 4. Service worker

The file `public/firebase-messaging-sw.js` must exist at your site root so FCM can register the token. It is included in the repo. Ensure it is deployed and served over HTTPS.

## 5. Build frontend

```bash
npm install
npm run build
```

## 6. Enable FCM Registration API (if needed)

For FCM Web SDK 6.7.0+, enable [FCM Registration API](https://console.cloud.google.com/apis/library/fcmregistrations.googleapis.com) for your Google Cloud project (same as Firebase project). New projects often have it enabled by default.

## Flow summary

- **Client login** (not guest) → browser may prompt for notification permission → if granted, FCM token is obtained and sent to `POST /client/devices/register`.
- **New ClientNotification** → stored in DB (unchanged) → observer sends FCM to all devices of that client.
- **Foreground** → existing popup UI is used and toolbar badge updates.
- **Background** → browser shows the system notification; click opens `action_url` (e.g. payment or invoice).

Push does **not** mark notifications as read; read rules are unchanged (popup dismiss, action click, or explicit mark-read API).

## Security

- Token registration requires authenticated client; clients can only register their own tokens.
- Optional: call `POST /client/devices/unregister` with `fcm_token` on logout to remove the device.

## Troubleshooting

- **No push in browser**: Ensure the site is HTTPS, `firebase-messaging-sw.js` is at the root, and the Web app is added in Firebase with the correct domain.
- **Token not registered**: Check browser console for FCM errors; ensure VAPID key and Firebase config are set and that the user allowed notifications.
- **Backend not sending**: For HTTP v1, set `FCM_PROJECT_ID`, `FCM_CLIENT_EMAIL`, and `FCM_PRIVATE_KEY` (from the service account JSON). Check Laravel logs for “FCM v1 access token failed” or “FCM v1 send failed”. If using legacy, ensure `FCM_SERVER_KEY` is set.
