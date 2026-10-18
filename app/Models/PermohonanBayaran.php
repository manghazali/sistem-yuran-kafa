<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermohonanBayaran extends Model
{
    protected $table = 'permohonan_bayaran';

    protected $fillable = [
        'guru_id',
        'pelajar_id',
        'struktur_yuran_id',
        'bulan',
        'amaun',
        'catatan',
        'tarikh_terima',
        'status',
        'bayaran_id',
        'disahkan_oleh',
        'sebab_tolak',
    ];

    protected function casts(): array
    {
        return [
            'amaun' => 'decimal:2',
            'tarikh_terima' => 'date',
        ];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function pelajar(): BelongsTo
    {
        return $this->belongsTo(Pelajar::class, 'pelajar_id');
    }

    public function strukturYuran(): BelongsTo
    {
        return $this->belongsTo(StrukturYuran::class, 'struktur_yuran_id');
    }

    public function bayaran(): BelongsTo
    {
        return $this->belongsTo(Bayaran::class, 'bayaran_id');
    }

    public function pengesah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disahkan_oleh');
    }
}
