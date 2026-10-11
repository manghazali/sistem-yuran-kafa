<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * tarikh_mula dibuang terus — tiada lagi medan tarikh pada Tetapan Yuran
     * Pelajar langsung. Untuk struktur "Sekali" (bulan_berkenaan null),
     * bulan caj kini diterbitkan daripada created_at baris ini sendiri
     * (bulan ia DICIPTA = bulan ia dikenakan — cth: yuran pendaftaran
     * dikenakan pada bulan pelajar didaftarkan).
     */
    public function up(): void
    {
        // Backfill DAHULU: untuk rekod sedia ada struktur "Sekali" yang
        // tarikh_mula-nya BERBEZA daripada created_at, samakan created_at
        // dengan tarikh_mula asal supaya bulan caj yang sedia terpapar
        // (cth: Januari) KEKAL sama selepas lajur ini dibuang — bukan
        // tiba-tiba bertukar ke bulan rekod ini sebenarnya dicipta.
        DB::table('tetapan_yuran_pelajar as typ')
            ->join('struktur_yuran as sy', 'sy.id', '=', 'typ.struktur_yuran_id')
            ->whereNull('sy.bulan_berkenaan')
            ->whereNotNull('typ.tarikh_mula')
            ->update(['typ.created_at' => DB::raw('typ.tarikh_mula')]);

        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->dropColumn('tarikh_mula');
        });
    }

    public function down(): void
    {
        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->date('tarikh_mula')->nullable();
        });
    }
};
