<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bayaran extends Model
{
    protected $table = 'bayaran';

    protected $fillable = [
        'no_bayaran',
        'no_resit',
        'pelajar_id',
        'bulan',
        'tahun',
        'keterangan',
        'amaun',
        'kaedah_bayaran',
        'tarikh_bayaran',
        'no_rujukan',
        'no_pesanan_gateway',
        'dicatat_oleh',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'amaun' => 'decimal:2',
            'tarikh_bayaran' => 'date',
        ];
    }

    public function pelajar(): BelongsTo
    {
        return $this->belongsTo(Pelajar::class, 'pelajar_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BayaranItem::class);
    }

    public function noResit(): string
    {
        // Lajur no_resit sentiasa diisi untuk rekod baharu (lihat janaNoResit()).
        // Fallback terbitan daripada no_bayaran hanya untuk rekod lama sebelum lajur ini wujud.
        return $this->no_resit ?: str_replace('BYR-', 'RSIT-', $this->no_bayaran);
    }

    /**
     * Format sesuatu No. Bayaran/Resit tanpa sebarang kesan sampingan (tiada
     * simpanan) — dikongsi oleh pratonton Tetapan dan janaNo*() sebenar supaya
     * kedua-duanya konsisten.
     */
    public static function formatNombor(string $format, int $tahun, int $nombor): string
    {
        return str_replace(
            ['{TAHUN}', '{NOMBOR}'],
            [$tahun, str_pad((string) $nombor, 6, '0', STR_PAD_LEFT)],
            $format
        );
    }

    /**
     * Jana No. Bayaran SEBENAR mengikut format/tahun/nombor-turutan di Tetapan >
     * Generate No. Bayaran, dan naikkan nombor turutan tersimpan. Dipanggil
     * sekali bagi SETIAP Bayaran dicipta (satu baris = satu rekod bayaran).
     */
    public static function janaNoBayaran(): string
    {
        $format = Tetapan::get('no_bayaran_format', 'BYR/{TAHUN}/{NOMBOR}');
        $tahun = (int) Tetapan::get('no_bayaran_tahun', now()->year);
        $nomborSemasa = (int) Tetapan::get('no_bayaran_nombor_semasa', 0);
        $nomborBaharu = $nomborSemasa + 1;

        Tetapan::set('no_bayaran_nombor_semasa', (string) $nomborBaharu);

        return static::formatNombor($format, $tahun, $nomborBaharu);
    }

    /**
     * Jana No. Resit SEBENAR mengikut format/tahun/nombor-turutan di Tetapan >
     * Generate No. Resit, dan naikkan nombor turutan tersimpan. Dipanggil sekali
     * sahaja semasa Bayaran dicipta (manual mahupun melalui gateway pembayaran).
     */
    public static function janaNoResit(): string
    {
        $format = Tetapan::get('no_resit_format', 'RESIT/{TAHUN}/{NOMBOR}');
        $tahun = (int) Tetapan::get('no_resit_tahun', now()->year);
        $nomborSemasa = (int) Tetapan::get('no_resit_nombor_semasa', 0);
        $nomborBaharu = $nomborSemasa + 1;

        Tetapan::set('no_resit_nombor_semasa', (string) $nomborBaharu);

        return static::formatNombor($format, $tahun, $nomborBaharu);
    }
}
