<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kekangan unik lama (pelajar_id, struktur_yuran_id) menghalang pelajar
     * yang sama diberi struktur yuran yang sama LEBIH DARI SEKALI — ini
     * menyekat ciri "pilih berbilang bulan" (setiap bulan dipilih kini jadi
     * SATU baris berasingan bagi pasangan pelajar+struktur yang SAMA).
     * Gantikan dengan kekangan unik yang turut ambil kira tarikh_mula, supaya
     * baris berganda masih disekat, tapi bulan BERBEZA dibenarkan.
     */
    public function up(): void
    {
        // Tambah index baharu DAHULU (bukan padam dulu) — MySQL enggan padam
        // index lama selagi ia menyokong kekangan foreign key pada
        // struktur_yuran_id, melainkan ada index lain sedia ada untuk
        // teruskan menyokongnya.
        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->unique(['pelajar_id', 'struktur_yuran_id', 'tarikh_mula'], 'unik_pelajar_struktur_tarikh');
        });

        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->dropUnique('unik_pelajar_struktur');
        });
    }

    public function down(): void
    {
        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->unique(['pelajar_id', 'struktur_yuran_id'], 'unik_pelajar_struktur');
        });

        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->dropUnique('unik_pelajar_struktur_tarikh');
        });
    }
};
