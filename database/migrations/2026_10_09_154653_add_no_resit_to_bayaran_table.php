<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * No. Resit kini dijana mengikut format/tahun/nombor-turutan yang ditetapkan
     * di Tetapan > Generate No. Resit, bukan sekadar diterbitkan daripada no_bayaran.
     * Disimpan di sini (bukan dikira semula setiap kali) supaya nombor yang sama
     * kekal walaupun tetapan format berubah selepas resit itu dijana.
     */
    public function up(): void
    {
        Schema::table('bayaran', function (Blueprint $table) {
            $table->string('no_resit')->nullable()->after('no_bayaran');
        });
    }

    public function down(): void
    {
        Schema::table('bayaran', function (Blueprint $table) {
            $table->dropColumn('no_resit');
        });
    }
};
