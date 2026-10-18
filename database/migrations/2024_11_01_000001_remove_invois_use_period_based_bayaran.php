<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bayaran', 'invois_id')) {
            $namaFk = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
                ->where('TABLE_NAME', 'bayaran')
                ->where('COLUMN_NAME', 'invois_id')
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->value('CONSTRAINT_NAME');

            if ($namaFk) {
                DB::statement("ALTER TABLE `bayaran` DROP FOREIGN KEY `{$namaFk}`");
            }

            Schema::table('bayaran', function (Blueprint $table) {
                $table->dropColumn('invois_id');
            });
        }

        // --- Tambah lajur baharu (hanya jika belum wujud, selamat diulang) ---
        Schema::table('bayaran', function (Blueprint $table) {
            if (! Schema::hasColumn('bayaran', 'pelajar_id')) {
                $table->foreignId('pelajar_id')->after('id')->constrained('pelajar')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('bayaran', 'bulan')) {
                $table->unsignedTinyInteger('bulan')->nullable()->after('pelajar_id');
            }
            if (! Schema::hasColumn('bayaran', 'tahun')) {
                $table->unsignedSmallInteger('tahun')->nullable()->after('bulan');
            }
            if (! Schema::hasColumn('bayaran', 'keterangan')) {
                $table->string('keterangan')->nullable()->after('tahun');
            }
        });

        // --- Buang pivot SenangPay lama, cipta versi baharu ---
        Schema::dropIfExists('senangpay_transaksi_invois');

        if (! Schema::hasTable('senangpay_transaksi_item')) {
            Schema::create('senangpay_transaksi_item', function (Blueprint $table) {
                $table->id();
                $table->foreignId('senangpay_transaksi_id')->constrained('senangpay_transaksi')->cascadeOnDelete();
                $table->foreignId('pelajar_id')->constrained('pelajar')->cascadeOnDelete();
                $table->unsignedTinyInteger('bulan')->nullable();
                $table->unsignedSmallInteger('tahun')->nullable();
                $table->string('keterangan')->nullable();
                $table->decimal('amaun', 10, 2);
                $table->timestamps();
            });
        }

        // --- Buang jadual invois sepenuhnya ---
        Schema::dropIfExists('invois_item');
        Schema::dropIfExists('invois');
    }

    public function down(): void
    {
        Schema::create('invois', function (Blueprint $table) {
            $table->id();
            $table->string('no_invois')->unique();
            $table->foreignId('pelajar_id')->constrained('pelajar')->cascadeOnDelete();
            $table->date('tarikh_invois');
            $table->date('tarikh_akhir_bayaran');
            $table->decimal('jumlah_keseluruhan', 10, 2)->default(0);
            $table->decimal('jumlah_dibayar', 10, 2)->default(0);
            $table->enum('status', ['Belum Bayar', 'Sebahagian', 'Dibayar', 'Dibatalkan'])->default('Belum Bayar');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('invois_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invois_id')->constrained('invois')->cascadeOnDelete();
            $table->foreignId('struktur_yuran_id')->nullable()->constrained('struktur_yuran')->nullOnDelete();
            $table->string('keterangan');
            $table->decimal('amaun', 10, 2);
            $table->timestamps();
        });

        Schema::dropIfExists('senangpay_transaksi_item');

        Schema::table('bayaran', function (Blueprint $table) {
            $table->dropForeign(['pelajar_id']);
            $table->dropColumn(['pelajar_id', 'bulan', 'tahun', 'keterangan']);
            $table->foreignId('invois_id')->nullable()->constrained('invois')->cascadeOnDelete();
        });
    }
};