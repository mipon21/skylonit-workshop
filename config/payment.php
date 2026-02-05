<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lock Payments After Final Payment
    |--------------------------------------------------------------------------
    |
    | When true: After a Final Payment is created, the Add Payment button and
    | Delete Payment option are hidden. No new payments can be added.
    |
    | When false: Add and Delete remain available (development mode).
    | Set PAYMENT_LOCK_AFTER_FINAL=false in .env during development.
    |
    */

    'lock_after_final' => env('PAYMENT_LOCK_AFTER_FINAL', false),

];
