<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dahulu "berulang setiap bulan atau sekali sahaja" ditentukan secara
     * TERSIRAT — ada Tarikh Tamat = berulang, kosong = sekali sahaja. Ini
     * mengelirukan (orang biasa anggap Tarikh Tamat kosong = "tiada had",
     * bukan "sekali je"). Lajur ini jadikan ia EKSPLISIT.
     */
    public function up(): void
    {
        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->boolean('berulang')->default(false)->after('tarikh_tamat');
        });

        // Backfill: kekalkan tingkah laku SEDIA ADA rekod lama secara tepat —
        // rekod yang sudah ada Tarikh Tamat sebelum ini bermakna ia berulang;
        // yang kosong bermakna sekali sahaja.
        DB::table('tetapan_yuran_pelajar')
            ->whereNotNull('tarikh_tamat')
            ->update(['berulang' => true]);
    }

    public function down(): void
    {
        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->dropColumn('berulang');
        });
    }
};
