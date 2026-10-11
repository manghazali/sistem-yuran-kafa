<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buang pautan 1-invois lama — satu transaksi kini boleh
        // merangkumi BANYAK invois (jadual pivot di bawah)
        Schema::table('senangpay_transaksi', function (Blueprint $table) {
            $table->dropForeign(['invois_id']);
            $table->dropColumn('invois_id');
        });

        Schema::create('senangpay_transaksi_invois', function (Blueprint $table) {
            $table->id();
            $table->foreignId('senangpay_transaksi_id')->constrained('senangpay_transaksi')->cascadeOnDelete();
            $table->foreignId('invois_id')->constrained('invois')->cascadeOnDelete();
            $table->decimal('amaun', 10, 2); // amaun untuk invois ni sahaja dalam transaksi ni
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('senangpay_transaksi_invois');

        Schema::table('senangpay_transaksi', function (Blueprint $table) {
            $table->foreignId('invois_id')->nullable()->constrained('invois')->cascadeOnDelete();
        });
    }
};