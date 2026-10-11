<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supaya satu pembayaran (cth: bayar 3 bulan sekali gus melalui gateway)
     * boleh direkod sebagai SATU baris Bayaran sahaja, setiap BayaranItem kini
     * menyimpan bulan sendiri — tidak lagi bergantung semata-mata kepada
     * bayaran.bulan induk (yang jadi tidak tentu bila satu Bayaran merangkumi
     * pelbagai bulan).
     */
    public function up(): void
    {
        Schema::table('bayaran_item', function (Blueprint $table) {
            $table->unsignedTinyInteger('bulan')->nullable()->after('tetapan_yuran_pelajar_id');
        });

        // Isi semula data sedia ada daripada bulan induk (Bayaran) — pada ketika
        // ini setiap Bayaran lama masih mewakili satu bulan sahaja.
        DB::statement('
            UPDATE bayaran_item bi
            INNER JOIN bayaran b ON b.id = bi.bayaran_id
            SET bi.bulan = b.bulan
            WHERE bi.bulan IS NULL AND b.bulan IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('bayaran_item', function (Blueprint $table) {
            $table->dropColumn('bulan');
        });
    }
};
