<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tetapan', function (Blueprint $table) {
            $table->id();
            $table->string('kunci')->unique(); // cth: senangpay_merchant_id
            $table->text('nilai')->nullable(); // disulitkan (encrypted) secara automatik
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tetapan');
    }
};
