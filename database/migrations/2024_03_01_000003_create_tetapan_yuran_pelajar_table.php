<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tetapan_yuran_pelajar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelajar_id')->constrained('pelajar')->cascadeOnDelete();
            $table->foreignId('struktur_yuran_id')->constrained('struktur_yuran')->cascadeOnDelete();
            $table->decimal('amaun_verride', 10, 2)->nullable(); // jika amaun berbeza dari struktur asal (cth: diskaun)
            $table->date('tarikh_mula');
            $table->date('tarikh_tamat')->nullable(); // null = tiada tarikh tamat
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->timestamps();

            // Elak pelajar yang sama ditetapkan struktur yuran yang sama berulang kali
            $table->unique(['pelajar_id', 'struktur_yuran_id'], 'unik_pelajar_struktur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tetapan_yuran_pelajar');
    }
};