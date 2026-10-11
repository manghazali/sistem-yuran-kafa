<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('struktur_yuran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_yuran_id')->constrained('kategori_yuran')->cascadeOnDelete();
            $table->string('nama'); // cth: Yuran Bulanan Tahun 3, Yuran Pendaftaran Baharu
            $table->decimal('amaun', 10, 2);
            $table->enum('kitaran', ['Sekali', 'Bulanan', 'Tahunan'])->default('Bulanan');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete(); // null = untuk semua kelas
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('struktur_yuran');
    }
};