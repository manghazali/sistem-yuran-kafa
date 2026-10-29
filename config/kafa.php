<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Had emel akaun sehari
    |--------------------------------------------------------------------------
    |
    | Bilangan emel akaun Guru/Penjaga yang dihantar sehari. Lebihan
    | dihantar hari berikutnya. 85 sesuai untuk pelan percuma Resend
    | (100/hari); naikkan (cth. 2000) jika guna pelan Pro.
    |
    */

    'emel_had_harian' => (int) env('EMEL_HAD_HARIAN', 85),

    // Berapa hari emel akaun yang tertangguh (kerana had harian) terus dicuba
    // sebelum dianggap gagal. Mesti cukup untuk semua akaun dihantar.
    'emel_cuba_hari' => (int) env('EMEL_CUBA_HARI', 14),

    /*
    |--------------------------------------------------------------------------
    | Peringatan baki tertunggak
    |--------------------------------------------------------------------------
    |
    | Hari dalam bulan: "akhir" (hari terakhir) atau nombor 1–28.
    | Masa dalam format 24 jam, waktu Malaysia.
    |
    */

    'peringatan_hari' => env('PERINGATAN_HARI', 'akhir'),

    'peringatan_masa' => env('PERINGATAN_MASA', '08:00'),

    // Peringatan yang belum dibaca tidak diulang dalam tempoh ini.
    'peringatan_selang_hari' => (int) env('PERINGATAN_SELANG_HARI', 6),
];
