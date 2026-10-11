<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tetapan extends Model
{
    protected $table = 'tetapan';

    protected $fillable = ['kunci', 'nilai'];

    /**
     * Nilai disulitkan (encrypted) secara automatik dalam pangkalan data
     * guna APP_KEY — walaupun seseorang akses DB terus, nilai (cth:
     * secret key gateway pembayaran) tak boleh dibaca tanpa APP_KEY aplikasi.
     */
    protected function casts(): array
    {
        return [
            'nilai' => 'encrypted',
        ];
    }

    /**
     * Dapatkan nilai tetapan mengikut kunci. Jika tiada dalam DB
     * (belum pernah ditetapkan oleh Admin), guna $default (biasanya
     * dari config/.env sebagai nilai awal/fallback).
     */
    public static function get(string $kunci, $default = null)
    {
        $rekod = static::where('kunci', $kunci)->first();

        if (! $rekod || $rekod->nilai === null || $rekod->nilai === '') {
            return $default;
        }

        return $rekod->nilai;
    }

    /**
     * Simpan/kemaskini nilai tetapan.
     */
    public static function set(string $kunci, $nilai): void
    {
        static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }
}
