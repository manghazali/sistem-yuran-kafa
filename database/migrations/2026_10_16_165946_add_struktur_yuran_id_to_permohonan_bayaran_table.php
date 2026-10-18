<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guru boleh pilih TERUS struktur yuran (dan bulan berkenaan, jika
     * struktur itu berulang) semasa melaporkan bayaran diterima — supaya
     * Admin/Akauntan tidak perlu agak/pilih semula bila mengesahkan,
     * borang Rekod Bayaran terus pra-isi dengan yuran yang tepat.
     */
    public function up(): void
    {
        Schema::table('permohonan_bayaran', function (Blueprint $table) {
            $table->foreignId('struktur_yuran_id')->nullable()->after('pelajar_id')->constrained('struktur_yuran')->nullOnDelete();
            $table->unsignedTinyInteger('bulan')->nullable()->after('struktur_yuran_id');
        });
    }

    public function down(): void
    {
        Schema::table('permohonan_bayaran', function (Blueprint $table) {
            $table->dropConstrainedForeignId('struktur_yuran_id');
            $table->dropColumn('bulan');
        });
    }
};
