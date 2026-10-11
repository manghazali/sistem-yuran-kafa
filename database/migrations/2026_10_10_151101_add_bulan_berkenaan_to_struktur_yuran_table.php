<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bulan mana struktur yuran ini terpakai (cth: [1,2,3,...,12] untuk
     * yuran bulanan sepanjang tahun, atau [3] sahaja untuk yuran peperiksaan
     * pertengahan tahun). Hanya relevan bila kitaran bukan "Sekali" — kosong
     * (null) bermakna struktur ini TIDAK berasaskan senarai bulan (ikut
     * tarikh_mula pada Tetapan Yuran Pelajar sebagai gantinya).
     */
    public function up(): void
    {
        Schema::table('struktur_yuran', function (Blueprint $table) {
            $table->json('bulan_berkenaan')->nullable()->after('kitaran');
        });

        // Backfill: struktur yang sedia ada mempunyai rekod Tetapan Yuran
        // Pelajar "berulang" (ciri sesi lepas) — terbitkan bulan_berkenaan
        // daripada bulan SEBENAR rekod-rekod berulang tersebut (bukan andaikan
        // semua 12 bulan), supaya struktur seperti "Januari" yang sebelum ini
        // hanya pernah ditetapkan untuk bulan Januari kekal [1] sahaja.
        $rekodBerulang = DB::table('tetapan_yuran_pelajar')
            ->where('berulang', true)
            ->get(['struktur_yuran_id', 'tarikh_mula']);

        $bulanMengikutStruktur = $rekodBerulang
            ->groupBy('struktur_yuran_id')
            ->map(fn ($kumpulan) => $kumpulan->map(fn ($r) => (int) date('n', strtotime($r->tarikh_mula)))->unique()->sort()->values());

        foreach ($bulanMengikutStruktur as $strukturId => $senaraiBulan) {
            DB::table('struktur_yuran')
                ->where('id', $strukturId)
                ->update(['bulan_berkenaan' => json_encode($senaraiBulan->all())]);
        }
    }

    public function down(): void
    {
        Schema::table('struktur_yuran', function (Blueprint $table) {
            $table->dropColumn('bulan_berkenaan');
        });
    }
};
