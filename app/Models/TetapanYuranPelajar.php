<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TetapanYuranPelajar extends Model
{
    protected $table = 'tetapan_yuran_pelajar';

    protected $fillable = [
        'pelajar_id',
        'struktur_yuran_id',
        'status',
    ];

    public function pelajar(): BelongsTo
    {
        return $this->belongsTo(Pelajar::class, 'pelajar_id');
    }

    public function strukturYuran(): BelongsTo
    {
        return $this->belongsTo(StrukturYuran::class, 'struktur_yuran_id');
    }
}