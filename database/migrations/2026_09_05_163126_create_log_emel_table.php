<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_emel', function (Blueprint $table) {
            $table->id();
            $table->string('kepada'); // emel penerima
            $table->string('nama_penerima')->nullable();
            $table->string('subjek');
            $table->longText('kandungan_html')->nullable();
            $table->longText('kandungan_teks')->nullable();
            $table->timestamps();

            $table->index('kepada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_emel');
    }
};
