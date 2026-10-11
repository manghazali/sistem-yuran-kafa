<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('senangpay_transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->foreignId('invois_id')->constrained('invois')->cascadeOnDelete();
            $table->decimal('amaun', 10, 2);
            $table->enum('status', ['Menunggu', 'Berjaya', 'Gagal'])->default('Menunggu');
            $table->string('transaction_id')->nullable();
            $table->string('msg')->nullable();
            $table->text('payload_callback')->nullable(); // simpan respons penuh untuk audit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('senangpay_transaksi');
    }
};
