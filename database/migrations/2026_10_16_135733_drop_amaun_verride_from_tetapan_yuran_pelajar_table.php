<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buang ciri "Amaun Diselaraskan" (override amaun per-pelajar) terus
     * daripada Tetapan Yuran Pelajar — tiada data sebenar guna lajur ini
     * (disahkan kosong sebelum migration ini ditulis), jadi selamat dibuang
     * terus tanpa perlu backup.
     */
    public function up(): void
    {
        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->dropColumn('amaun_verride');
        });
    }

    public function down(): void
    {
        Schema::table('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->decimal('amaun_verride', 10, 2)->nullable();
        });
    }
};
