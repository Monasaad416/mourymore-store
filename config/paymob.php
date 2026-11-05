<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paymob Intention API (Egypt)
    |--------------------------------------------------------------------------
    |
    | Use test keys from the Paymob dashboard while developing, then switch
    | to live keys in production. Amounts are sent in cents (EGP * 100).
    |
    */

    'base_url' => rtrim((string) env('PAYMOB_BASE_URL', 'https://accept.paymob.com'), '/'),

    'secret_key' => env('PAYMOB_SECRET_KEY'),

    'public_key' => env('PAYMOB_PUBLIC_KEY'),

    'hmac_secret' => env('PAYMOB_HMAC_SECRET'),

    // Single ID or comma-separated list (card, wallet, …).
    'integration_id' => env('PAYMOB_INTEGRATION_ID'),

    /*
    | When false (missing credentials), checkout still creates a pending
    | payment and opens the order page so local/dev can run without Paymob.
    */
    'enabled' => (bool) env('PAYMOB_ENABLED', true),

];
