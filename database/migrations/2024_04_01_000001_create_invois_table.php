<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invois', function (Blueprint $table) {
            $table->id();
            $table->string('no_invois')->unique(); // cth: INV-202608-0001
            $table->foreignId('pelajar_id')->constrained('pelajar')->cascadeOnDelete();
            $table->date('tarikh_invois');
            $table->date('tarikh_akhir_bayaran');
            $table->decimal('jumlah_keseluruhan', 10, 2)->default(0);
            $table->decimal('jumlah_dibayar', 10, 2)->default(0); // dikemaskini di FASA 5
            $table->enum('status', ['Belum Bayar', 'Sebahagian', 'Lunas', 'Dibatalkan'])->default('Belum Bayar');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invois');
    }
};
