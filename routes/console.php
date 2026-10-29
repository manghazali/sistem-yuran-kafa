<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sebulan sekali — peringatan baki tertunggak kepada Penjaga. Lalai: hari
// terakhir bulan, 8 pagi (PERINGATAN_HARI / PERINGATAN_MASA). Bulanan (bukan
// mingguan/harian) sengaja dipilih supaya tidak sepam Penjaga; arahan sendiri
// ada "guard" elak hantar berulang (PERINGATAN_SELANG_HARI).
$hariPeringatan = config('kafa.peringatan_hari');
$masaPeringatan = config('kafa.peringatan_masa');
$peringatan = Schedule::command('peringatan:baki-tertunggak');

if (is_numeric($hariPeringatan)) {
    $peringatan->monthlyOn(min(28, max(1, (int) $hariPeringatan)), $masaPeringatan);
} else {
    $peringatan->lastDayOfMonth($masaPeringatan);
}

$peringatan->name('peringatan-baki-tertunggak')->withoutOverlapping();

// Setiap jam — tandakan transaksi gateway pembayaran yang "Menunggu" lebih
// PAYMENT_GATEWAY_TAMAT_JAM (lalai 2 jam) sebagai "Gagal", dan maklumkan.
Schedule::command('payment-gateway:tamatkan-tertangguh')
    ->hourly()
    ->name('payment-gateway-tamatkan-tertangguh')
    ->withoutOverlapping();
