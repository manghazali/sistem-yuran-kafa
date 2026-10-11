<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriYuran extends Model
{
    protected $table = 'kategori_yuran';

    protected $fillable = [
        'nama',
        'bulanan',
        'keterangan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'bulanan' => 'boolean',
        ];
    }

    public function strukturYuran(): HasMany
    {
        return $this->hasMany(StrukturYuran::class, 'kategori_yuran_id');
    }
}