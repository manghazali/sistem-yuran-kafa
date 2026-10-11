<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tindakan'); // cth: Cipta, Kemaskini, Padam, Log Masuk
            $table->string('model')->nullable(); // cth: Invois, Bayaran, Pelajar
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['model', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_audit');
    }
};
