<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BayaranItem extends Model
{
    protected $table = 'bayaran_item';

    protected $fillable = [
        'bayaran_id',
        'tetapan_yuran_pelajar_id',
        'bulan',
        'amaun',
    ];

    protected function casts(): array
    {
        return [
            'amaun' => 'decimal:2',
        ];
    }

    public function bayaran(): BelongsTo
    {
        return $this->belongsTo(Bayaran::class);
    }

    public function tetapanYuranPelajar(): BelongsTo
    {
        return $this->belongsTo(TetapanYuranPelajar::class);
    }
}
