<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bayaran', function (Blueprint $table) {
            $table->id();
            $table->string('no_bayaran')->unique(); // cth: BYR-202608-0001
            $table->foreignId('invois_id')->constrained('invois')->cascadeOnDelete();
            $table->decimal('amaun', 10, 2);
            $table->enum('kaedah_bayaran', ['Tunai', 'Pindahan Bank', 'Cek', 'Online Banking', 'Lain-lain'])->default('Tunai');
            $table->date('tarikh_bayaran');
            $table->string('no_rujukan')->nullable(); // no. resit bank / rujukan transaksi
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bayaran');
    }
};
