<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Generalkan penamaan SenangPay -> Payment Gateway (sistem kini berpindah
     * ke DOKU) — pada jadual transaksi dan kunci tetapan yang tersimpan.
     */
    public function up(): void
    {
        Schema::rename('senangpay_transaksi', 'payment_gateway_transaksi');
        Schema::rename('senangpay_transaksi_item', 'payment_gateway_transaksi_item');

        Schema::table('payment_gateway_transaksi_item', function (Blueprint $table) {
            $table->renameColumn('senangpay_transaksi_id', 'payment_gateway_transaksi_id');
        });

        DB::table('tetapan')->where('kunci', 'senangpay_merchant_id')->update(['kunci' => 'payment_gateway_merchant_id']);
        DB::table('tetapan')->where('kunci', 'senangpay_secret_key')->update(['kunci' => 'payment_gateway_secret_key']);
        DB::table('tetapan')->where('kunci', 'senangpay_sandbox')->update(['kunci' => 'payment_gateway_sandbox']);
    }

    public function down(): void
    {
        DB::table('tetapan')->where('kunci', 'payment_gateway_merchant_id')->update(['kunci' => 'senangpay_merchant_id']);
        DB::table('tetapan')->where('kunci', 'payment_gateway_secret_key')->update(['kunci' => 'senangpay_secret_key']);
        DB::table('tetapan')->where('kunci', 'payment_gateway_sandbox')->update(['kunci' => 'senangpay_sandbox']);

        Schema::table('payment_gateway_transaksi_item', function (Blueprint $table) {
            $table->renameColumn('payment_gateway_transaksi_id', 'senangpay_transaksi_id');
        });

        Schema::rename('payment_gateway_transaksi_item', 'senangpay_transaksi_item');
        Schema::rename('payment_gateway_transaksi', 'senangpay_transaksi');
    }
};
