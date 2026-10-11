<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pindahkan penugasan guru-kelas daripada satu-ke-satu (guru.kelas_id +
     * guru.darjah) kepada satu-ke-banyak: seorang Guru boleh jadi Guru Kelas
     * bagi BEBERAPA Kelas + Tahun sekali gus.
     *
     * Ditulis supaya selamat disambung semula (idempotent) jika terganggu
     * separuh jalan — setiap langkah disemak dahulu sebelum dijalankan.
     */
    public function up(): void
    {
        if (! Schema::hasTable('guru_kelas')) {
            Schema::create('guru_kelas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
                $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
                $table->enum('darjah', ['Tahun 1', 'Tahun 2', 'Tahun 3', 'Tahun 4', 'Tahun 5', 'Tahun 6'])->nullable();
                $table->timestamps();
            });
        }

        // Pindahkan data sedia ada: satu penugasan sedia ada pada guru.kelas_id
        // menjadi satu baris pertama dalam guru_kelas (hanya jika belum dibuat).
        if (Schema::hasColumn('guru', 'kelas_id') && DB::table('guru_kelas')->count() === 0) {
            DB::statement('
                INSERT INTO guru_kelas (guru_id, kelas_id, darjah, created_at, updated_at)
                SELECT id, kelas_id, darjah, NOW(), NOW() FROM guru WHERE kelas_id IS NOT NULL
            ');
        }

        if (Schema::hasColumn('guru', 'kelas_id')) {
            Schema::table('guru', function (Blueprint $table) {
                $table->dropForeign(['kelas_id']);
                $table->dropColumn(['kelas_id', 'darjah']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('guru', 'kelas_id')) {
            Schema::table('guru', function (Blueprint $table) {
                $table->foreignId('kelas_id')->nullable()->after('user_id')->constrained('kelas')->nullOnDelete();
                $table->enum('darjah', ['Tahun 1', 'Tahun 2', 'Tahun 3', 'Tahun 4', 'Tahun 5', 'Tahun 6'])->nullable()->after('kelas_id');
            });
        }

        // Pulihkan hanya penugasan PERTAMA setiap guru (satu-ke-satu lama
        // tidak boleh wakili lebih daripada satu kelas setiap guru).
        DB::statement('
            UPDATE guru
            INNER JOIN (
                SELECT guru_id, MIN(id) AS min_id FROM guru_kelas GROUP BY guru_id
            ) pertama ON pertama.guru_id = guru.id
            INNER JOIN guru_kelas ON guru_kelas.id = pertama.min_id
            SET guru.kelas_id = guru_kelas.kelas_id, guru.darjah = guru_kelas.darjah
        ');

        Schema::dropIfExists('guru_kelas');
    }
};
