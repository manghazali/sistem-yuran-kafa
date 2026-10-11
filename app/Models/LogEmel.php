<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogEmel extends Model
{
    protected $table = 'log_emel';

    protected $fillable = [
        'kepada',
        'nama_penerima',
        'subjek',
        'kandungan_html',
        'kandungan_teks',
    ];

    public function ringkasan(int $panjang = 120): string
    {
        $teks = $this->kandungan_teks ?: strip_tags((string) $this->kandungan_html);
        $teks = trim(preg_replace('/\s+/', ' ', $teks));

        return \Illuminate\Support\Str::limit($teks, $panjang);
    }
}
