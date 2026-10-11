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
        Schema::create('permohonan_bayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('pelajar_id')->constrained('pelajar')->cascadeOnDelete();
            $table->decimal('amaun', 10, 2);
            $table->text('catatan')->nullable();
            $table->date('tarikh_terima');
            $table->enum('status', ['Menunggu', 'Disahkan', 'Ditolak'])->default('Menunggu');
            $table->foreignId('bayaran_id')->nullable()->constrained('bayaran')->nullOnDelete();
            $table->foreignId('disahkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('sebab_tolak')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan_bayaran');
    }
};
