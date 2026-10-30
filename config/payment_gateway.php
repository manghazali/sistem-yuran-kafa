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

    // Tempoh pautan bayaran DOKU sah (minit).
    'tempoh_bayar_minit' => (int) env('PAYMENT_GATEWAY_TEMPOH_BAYAR_MINIT', 60),

    // Selepas tempoh ini (jam) tanpa keputusan, sistem bertanya status kepada DOKU.
    'tamat_jam' => (int) env('PAYMENT_GATEWAY_TAMAT_JAM', 2),

    // Jika DOKU masih belum memberi keputusan muktamad (cth. PENDING atau tidak
    // dapat dihubungi) selepas tempoh ini (jam), transaksi ditanda Gagal.
    'tamat_akhir_jam' => (int) env('PAYMENT_GATEWAY_TAMAT_AKHIR_JAM', 24),
];
