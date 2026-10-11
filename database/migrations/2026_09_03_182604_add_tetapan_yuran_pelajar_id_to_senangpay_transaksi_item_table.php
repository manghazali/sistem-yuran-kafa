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
        Schema::table('senangpay_transaksi_item', function (Blueprint $table) {
            $table->foreignId('tetapan_yuran_pelajar_id')->nullable()->after('pelajar_id')
                ->constrained('tetapan_yuran_pelajar')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('senangpay_transaksi_item', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tetapan_yuran_pelajar_id');
        });
    }
};
