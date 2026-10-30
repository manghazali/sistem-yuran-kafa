<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simpan emel guru walaupun akaun log masuk belum dicipta, supaya emel
     * akaun boleh dihantar kemudian (butang "Hantar Emel Akaun").
     */
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->string('email')->nullable()->after('no_telefon');
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
