<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelajar', function (Blueprint $table) {
            $table->id();
            $table->string('no_daftar')->unique(); // no. pendaftaran pelajar KAFA
            $table->string('nama');
            $table->string('no_kp')->nullable(); // no. KP / sijil lahir
            $table->date('tarikh_lahir')->nullable();
            $table->enum('jantina', ['Lelaki', 'Perempuan']);
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('penjaga_id')->nullable()->constrained('penjaga')->nullOnDelete();
            $table->enum('status', ['Aktif', 'Tidak Aktif', 'Tamat'])->default('Aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelajar');
    }
};
