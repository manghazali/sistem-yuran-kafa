<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelajar extends Model
{
    protected $table = 'pelajar';

    protected $fillable = [
        'no_daftar',
        'nama',
        'no_kp',
        'tarikh_lahir',
        'jantina',
        'kelas_id',
        'darjah',
        'penjaga_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tarikh_lahir' => 'date',
        ];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function penjaga(): BelongsTo
    {
        return $this->belongsTo(Penjaga::class, 'penjaga_id');
    }

    public function tetapanYuranPelajar(): HasMany
    {
        return $this->hasMany(TetapanYuranPelajar::class, 'pelajar_id');
    }

    public function bayaran(): HasMany
    {
        return $this->hasMany(Bayaran::class, 'pelajar_id');
    }

    /**
     * Format sesuatu No. Pendaftaran tanpa sebarang kesan sampingan (tiada
     * simpanan) — dikongsi oleh pratonton Tetapan dan janaNoDaftar() sebenar
     * supaya kedua-duanya konsisten.
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
     * Jana No. Pendaftaran SEBENAR mengikut format/tahun/nombor-turutan di
     * Tetapan > Generate No. Pendaftaran, dan naikkan nombor turutan
     * tersimpan. Dipanggil sekali bagi SETIAP pelajar baharu didaftarkan.
     */
    public static function janaNoDaftar(): string
    {
        $format = Tetapan::get('no_daftar_format', 'PLJ/{TAHUN}/{NOMBOR}');
        $tahun = (int) Tetapan::get('no_daftar_tahun', now()->year);
        $nomborSemasa = (int) Tetapan::get('no_daftar_nombor_semasa', 0);
        $nomborBaharu = $nomborSemasa + 1;

        Tetapan::set('no_daftar_nombor_semasa', (string) $nomborBaharu);

        return static::formatNombor($format, $tahun, $nomborBaharu);
    }
}
