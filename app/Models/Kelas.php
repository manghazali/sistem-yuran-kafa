<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'sesi',
        'status',
    ];

    public function pelajar(): HasMany
    {
        return $this->hasMany(Pelajar::class, 'kelas_id');
    }
}
