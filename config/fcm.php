<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FCM HTTP v1 API (recommended — Legacy API disabled in Firebase)
    |--------------------------------------------------------------------------
    | Service account credentials. From Firebase Console → Project Settings →
    | Service accounts → Generate new private key (or use existing JSON and
    | set client_email + private_key from it). private_key can be the raw
    | string with \n for newlines as in JSON.
    */
    'project_id' => env('FCM_PROJECT_ID'),
    'client_email' => env('FCM_CLIENT_EMAIL'),
    'private_key' => env('FCM_PRIVATE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | FCM Server Key (Legacy) — optional fallback
    |--------------------------------------------------------------------------
    | Only used if project_id/client_email/private_key are not set. Legacy API
    | is deprecated and disabled in many Firebase projects.
    */
    'server_key' => env('FCM_SERVER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Web (client) config — for FCM in the browser
    |--------------------------------------------------------------------------
    | From Firebase Console → Project Settings → General → Your apps (Web).
    */
    'public' => [
        'api_key' => env('FIREBASE_API_KEY'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_APP_ID'),
    ],

    'vapid_key' => env('FIREBASE_VAPID_KEY'),
];
