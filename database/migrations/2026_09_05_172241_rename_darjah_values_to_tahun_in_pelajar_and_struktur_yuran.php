<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tukar label nilai darjah daripada "Darjah N" kepada "Tahun N" (istilah
     * standard sekolah rendah), pada jadual pelajar dan struktur_yuran.
     * Dibuat 3 langkah supaya data sedia ada tidak hilang: lebarkan enum
     * dahulu, kemaskini nilai, baru sempitkan semula enum.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE pelajar MODIFY darjah ENUM('Darjah 1','Darjah 2','Darjah 3','Darjah 4','Darjah 5','Darjah 6','Tahun 1','Tahun 2','Tahun 3','Tahun 4','Tahun 5','Tahun 6') NULL");
        DB::statement("UPDATE pelajar SET darjah = REPLACE(darjah, 'Darjah ', 'Tahun ') WHERE darjah IS NOT NULL");
        DB::statement("ALTER TABLE pelajar MODIFY darjah ENUM('Tahun 1','Tahun 2','Tahun 3','Tahun 4','Tahun 5','Tahun 6') NULL");

        DB::statement("ALTER TABLE struktur_yuran MODIFY darjah ENUM('Darjah 1','Darjah 2','Darjah 3','Darjah 4','Darjah 5','Darjah 6','Tahun 1','Tahun 2','Tahun 3','Tahun 4','Tahun 5','Tahun 6') NULL");
        DB::statement("UPDATE struktur_yuran SET darjah = REPLACE(darjah, 'Darjah ', 'Tahun ') WHERE darjah IS NOT NULL");
        DB::statement("ALTER TABLE struktur_yuran MODIFY darjah ENUM('Tahun 1','Tahun 2','Tahun 3','Tahun 4','Tahun 5','Tahun 6') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE pelajar MODIFY darjah ENUM('Tahun 1','Tahun 2','Tahun 3','Tahun 4','Tahun 5','Tahun 6','Darjah 1','Darjah 2','Darjah 3','Darjah 4','Darjah 5','Darjah 6') NULL");
        DB::statement("UPDATE pelajar SET darjah = REPLACE(darjah, 'Tahun ', 'Darjah ') WHERE darjah IS NOT NULL");
        DB::statement("ALTER TABLE pelajar MODIFY darjah ENUM('Darjah 1','Darjah 2','Darjah 3','Darjah 4','Darjah 5','Darjah 6') NULL");

        DB::statement("ALTER TABLE struktur_yuran MODIFY darjah ENUM('Tahun 1','Tahun 2','Tahun 3','Tahun 4','Tahun 5','Tahun 6','Darjah 1','Darjah 2','Darjah 3','Darjah 4','Darjah 5','Darjah 6') NULL");
        DB::statement("UPDATE struktur_yuran SET darjah = REPLACE(darjah, 'Tahun ', 'Darjah ') WHERE darjah IS NOT NULL");
        DB::statement("ALTER TABLE struktur_yuran MODIFY darjah ENUM('Darjah 1','Darjah 2','Darjah 3','Darjah 4','Darjah 5','Darjah 6') NULL");
    }
};
