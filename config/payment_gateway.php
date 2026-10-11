<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kelayakan Payment Gateway (DOKU)
    |--------------------------------------------------------------------------
    |
    | Dapatkan Client-Id (Merchant ID) dan Secret Key dari papan pemuka DOKU
    | anda: https://dashboard.doku.com (akaun sebenar) atau
    | https://dashboard-sandbox.doku.com (akaun ujian/sandbox — guna ini
    | dahulu semasa pembangunan).
    |
    */

    'merchant_id' => env('PAYMENT_GATEWAY_MERCHANT_ID'),

    'secret_key' => env('PAYMENT_GATEWAY_SECRET_KEY'),

    // true = guna api-sandbox.doku.com (ujian, tiada wang sebenar)
    // false = guna api.doku.com (produksi, wang sebenar)
    'sandbox' => env('PAYMENT_GATEWAY_SANDBOX', true),
];
