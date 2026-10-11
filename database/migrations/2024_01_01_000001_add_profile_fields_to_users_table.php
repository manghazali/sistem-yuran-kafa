<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email'); // No. Telefon
            $table->string('ic_number')->nullable()->after('phone'); // No. Kad Pengenalan
            $table->boolean('is_active')->default(true)->after('ic_number'); // Status Aktif
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'ic_number', 'is_active']);
        });
    }
};
