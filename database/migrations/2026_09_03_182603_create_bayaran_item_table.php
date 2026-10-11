<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pautkan satu Bayaran kepada struktur yuran (tetapan_yuran_pelajar) yang
     * sebenarnya dibayar — supaya status "Belum Bayar/Sebahagian/Lunas" boleh
     * ditunjukkan bagi setiap item, bukan setahap bulan sahaja.
     */
    public function up(): void
    {
        Schema::create('bayaran_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bayaran_id')->constrained('bayaran')->cascadeOnDelete();
            $table->foreignId('tetapan_yuran_pelajar_id')->nullable()->constrained('tetapan_yuran_pelajar')->nullOnDelete();
            $table->decimal('amaun', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bayaran_item');
    }
};
