<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hujung setiap bulan, 8 pagi — peringatan baki tertunggak kepada Penjaga.
// Bulanan (bukan mingguan/harian) sengaja dipilih supaya tidak sepam
// Penjaga; arahan sendiri ada "guard" elak hantar berulang bagi peringatan
// yang sama dalam tempoh 6 hari.
Schedule::command('peringatan:baki-tertunggak')
    ->lastDayOfMonth('08:00')
    ->name('peringatan-baki-tertunggak')
    ->withoutOverlapping();

// Setiap jam — tandakan transaksi gateway pembayaran yang "Menunggu" lebih
// 2 jam (ditinggalkan Penjaga tanpa selesai) sebagai "Gagal", dan maklumkan.
Schedule::command('payment-gateway:tamatkan-tertangguh')
    ->hourly()
    ->name('payment-gateway-tamatkan-tertangguh')
    ->withoutOverlapping();
