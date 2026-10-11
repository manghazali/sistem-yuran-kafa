<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bulan yang dikenakan kini ditentukan pada Struktur Yuran (bulan_berkenaan),
     * bukan lagi pada setiap tetapan pelajar — jadi tarikh_tamat & berulang
     * tidak lagi diperlukan di sini. tarikh_mula kekal (jadi pilihan) sebagai
     * "mula kira dari bila" bagi pelajar yang mendaftar lewat (lepas bulan
     * struktur mula terpakai).
     */
    public function up(): void
    {
        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->date('tarikh_mula')->nullable()->change();
            $table->dropColumn(['tarikh_tamat', 'berulang']);
        });

        // Kekangan unik kembali kepada (pelajar_id, struktur_yuran_id) sahaja
        // — sekarang hanya SATU baris setiap pasangan diperlukan, memandangkan
        // senarai bulan sudah berada pada Struktur Yuran, bukan di sini lagi.
        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->unique(['pelajar_id', 'struktur_yuran_id'], 'unik_pelajar_struktur');
        });

        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->dropUnique('unik_pelajar_struktur_tarikh');
        });
    }

    public function down(): void
    {
        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->unique(['pelajar_id', 'struktur_yuran_id', 'tarikh_mula'], 'unik_pelajar_struktur_tarikh');
        });

        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->dropUnique('unik_pelajar_struktur');
        });

        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->date('tarikh_mula')->nullable(false)->change();
            $table->boolean('berulang')->default(false);
            $table->date('tarikh_tamat')->nullable();
        });
    }
};
