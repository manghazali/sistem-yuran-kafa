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
        'amaun_verride',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amaun_verride' => 'decimal:2',
        ];
    }

    public function pelajar(): BelongsTo
    {
        return $this->belongsTo(Pelajar::class, 'pelajar_id');
    }

    public function strukturYuran(): BelongsTo
    {
        return $this->belongsTo(StrukturYuran::class, 'struktur_yuran_id');
    }

    /**
     * Amaun sebenar yang perlu dibayar — guna override jika ada,
     * jika tidak guna amaun asal dari struktur yuran.
     */
    public function amaunSebenar(): float
    {
        return (float) ($this->amaun_verride ?? $this->strukturYuran->amaun);
    }
}