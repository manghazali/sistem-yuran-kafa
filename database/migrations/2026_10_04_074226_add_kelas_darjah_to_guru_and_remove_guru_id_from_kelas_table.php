<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pindahkan penugasan guru-kelas: dahulu satu Kelas ada satu Guru
     * (guru_id pada jadual kelas), tidak kira pelajar di dalamnya
     * merangkumi pelbagai Tahun. Sekarang setiap Guru ditugaskan kepada
     * satu Kelas + Tahun (guru boleh ajar Kelas yang sama tetapi Tahun
     * berlainan dengan guru lain).
     */
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->foreignId('kelas_id')->nullable()->after('user_id')->constrained('kelas')->nullOnDelete();
            $table->enum('darjah', ['Tahun 1', 'Tahun 2', 'Tahun 3', 'Tahun 4', 'Tahun 5', 'Tahun 6'])->nullable()->after('kelas_id');
        });

        // Pindahkan data sedia ada: jika kelas X ada guru_id = Y, tetapkan
        // guru Y punya kelas_id = X (tiada tahun khusus pada peringkat ini).
        DB::statement('UPDATE guru INNER JOIN kelas ON kelas.guru_id = guru.id SET guru.kelas_id = kelas.id');

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['guru_id']);
            $table->dropColumn('guru_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('guru_id')->nullable()->after('sesi')->constrained('guru')->nullOnDelete();
        });

        DB::statement('UPDATE kelas INNER JOIN guru ON guru.kelas_id = kelas.id SET kelas.guru_id = guru.id');

        Schema::table('guru', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropColumn(['kelas_id', 'darjah']);
        });
    }
};
