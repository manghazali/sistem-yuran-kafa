<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bayaran.no_rujukan menyimpan RUJUKAN transaksi gateway (cth: hash DOKU),
     * tetapi bukan No. Pesanan (order_id, cth: KAFA25-20261009161738) yang
     * dipaparkan di papan pemuka/penyata DOKU sendiri. Tanpa lajur ini, admin
     * tiada cara untuk padankan rekod Bayaran dengan penyata DOKU melainkan
     * mencari secara manual dalam pangkalan data.
     */
    public function up(): void
    {
        Schema::table('bayaran', function (Blueprint $table) {
            $table->string('no_pesanan_gateway')->nullable()->after('no_rujukan');
        });

        // Isi semula rekod sedia ada daripada payment_gateway_transaksi (padankan
        // melalui no_rujukan = transaction_id).
        DB::statement('
            UPDATE bayaran b
            INNER JOIN payment_gateway_transaksi t ON t.transaction_id = b.no_rujukan
            SET b.no_pesanan_gateway = t.order_id
            WHERE b.no_rujukan IS NOT NULL AND b.no_pesanan_gateway IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('bayaran', function (Blueprint $table) {
            $table->dropColumn('no_pesanan_gateway');
        });
    }
};
