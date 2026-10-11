<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gantikan padanan nama "Yuran Bulanan" (rapuh — pecah jika kategori
     * ditukar nama) dengan satu lajur eksplisit. Admin tandakan kategori
     * mana yang patut papar/wajibkan pilihan bulan pada Struktur Yuran.
     */
    public function up(): void
    {
        Schema::table('kategori_yuran', function (Blueprint $table) {
            $table->boolean('bulanan')->default(false)->after('nama');
        });

        // Backfill: kekalkan tingkah laku sedia ada — kategori yang sedia
        // ada bernama tepat "Yuran Bulanan" ditandakan bulanan=true.
        DB::table('kategori_yuran')
            ->whereRaw('LOWER(TRIM(nama)) = ?', ['yuran bulanan'])
            ->update(['bulanan' => true]);
    }

    public function down(): void
    {
        Schema::table('kategori_yuran', function (Blueprint $table) {
            $table->dropColumn('bulanan');
        });
    }
};
