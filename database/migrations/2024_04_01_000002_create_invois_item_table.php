<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invois_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invois_id')->constrained('invois')->cascadeOnDelete();
            $table->foreignId('struktur_yuran_id')->nullable()->constrained('struktur_yuran')->nullOnDelete();
            $table->string('keterangan'); // cth: "Yuran Bulanan Tahun 3 - Ogos 2026"
            $table->decimal('amaun', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invois_item');
    }
};
