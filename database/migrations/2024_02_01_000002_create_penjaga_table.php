<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjaga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama');
            $table->string('no_kp')->nullable();
            $table->string('no_telefon');
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->enum('hubungan', ['Bapa', 'Ibu', 'Penjaga Sah'])->default('Bapa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjaga');
    }
};
