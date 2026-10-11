<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kitaran Bayaran dibuang — tiada lagi kesan sebenar pada pengiraan
     * (bulan_berkenaan pada Struktur Yuran, ditentukan oleh Kategori Yuran,
     * sudah mengambil alih peranan ini sepenuhnya). Ia tinggal label yang
     * tidak digunakan di mana-mana logik lagi.
     */
    public function up(): void
    {
        Schema::table('struktur_yuran', function (Blueprint $table) {
            $table->dropColumn('kitaran');
        });
    }

    public function down(): void
    {
        Schema::table('struktur_yuran', function (Blueprint $table) {
            $table->enum('kitaran', ['Sekali', 'Bulanan', 'Tahunan'])->default('Bulanan')->after('amaun');
        });
    }
};
