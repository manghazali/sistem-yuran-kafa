<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrukturYuran extends Model
{
    protected $table = 'struktur_yuran';

    protected $fillable = [
        'kategori_yuran_id',
        'nama',
        'amaun',
        'bulan_berkenaan',
        'kelas_id',
        'darjah',
        'status',
    ];

    /**
     * @var array<int, string>
     */
    public const NAMA_BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
        5 => 'Mei', 6 => 'Jun', 7 => 'Julai', 8 => 'Ogos',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember',
    ];

    /**
     * Terbitkan Nama Struktur Yuran daripada senarai bulan_berkenaan (cth:
     * "Januari" bagi [1], "Januari, Mac" bagi [1,3]) — untuk kategori
     * "Yuran Bulanan", nama SENTIASA mengikut bulan yang dipilih, bukan
     * ditaip bebas.
     *
     * @param  array<int, int>  $bulanBerkenaan
     */
    public static function namaDaripadaBulan(array $bulanBerkenaan): string
    {
        return collect($bulanBerkenaan)
            ->sort()
            ->map(fn ($bulan) => self::NAMA_BULAN[(int) $bulan] ?? $bulan)
            ->implode(', ');
    }

    protected function casts(): array
    {
        return [
            'amaun' => 'decimal:2',
            'bulan_berkenaan' => 'array',
        ];
    }

    public function kategoriYuran(): BelongsTo
    {
        return $this->belongsTo(KategoriYuran::class, 'kategori_yuran_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function tetapanPelajar(): HasMany
    {
        return $this->hasMany(TetapanYuranPelajar::class, 'struktur_yuran_id');
    }

    /**
     * Label ringkas bulan_berkenaan untuk paparan (cth: "Semua Bulan",
     * "Jan, Mac, Jun", atau "Bulan semasa dicipta" jika tiada senarai bulan
     * langsung — kategori bukan "Yuran Bulanan", jadi bulan dikenakan
     * ditentukan oleh created_at tetapan pelajar, bukan senarai tetap).
     */
    public function labelBulanBerkenaan(): string
    {
        if ($this->bulan_berkenaan === null) {
            return 'Bulan semasa dicipta';
        }

        if (count($this->bulan_berkenaan) === 12) {
            return 'Semua Bulan';
        }

        $singkatan = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];

        return collect($this->bulan_berkenaan)
            ->sort()
            ->map(fn ($bulan) => $singkatan[$bulan - 1] ?? $bulan)
            ->implode(', ');
    }
}