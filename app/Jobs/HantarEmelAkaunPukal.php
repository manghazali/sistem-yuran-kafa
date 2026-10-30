<?php

namespace App\Jobs;

use App\Models\Guru;
use App\Models\Penjaga;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Cipta akaun log masuk dan hantar emel bagi Penjaga/Guru. Tanpa $id: semua
 * yang ada emel tetapi belum ada akaun. Dengan $id: hanya yang dipilih —
 * yang sudah ada akaun menerima kata laluan baharu (hantar semula). Emel
 * sendiri dihantar berperingkat oleh had 'emel-harian' (EMEL_HAD_HARIAN).
 */
class HantarEmelAkaunPukal implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    /**
     * @param  'penjaga'|'guru'  $jenis
     * @param  array<int, int>|null  $id
     */
    public function __construct(public string $jenis, public ?array $id = null) {}

    public function handle(): void
    {
        $dicipta = 0;
        $dilangkau = 0;

        static::sasaran($this->jenis, $this->id)->each(function ($orang) use (&$dicipta, &$dilangkau) {
            $emel = $orang->email ?: $orang->user?->email;

            // Emel yang sudah dipakai akaun lain (cth. ibu dan bapa berkongsi emel) dilangkau.
            if (! $emel || User::where('email', $emel)->where('id', '!=', $orang->user_id)->exists()) {
                $dilangkau++;

                return;
            }

            $orang->ciptaAtauResetAkaun($emel);
            $dicipta++;
        });

        Log::info("Hantar emel akaun {$this->jenis}: {$dicipta} dihantar, {$dilangkau} dilangkau.");
    }

    /**
     * Penjaga/Guru yang akan menerima emel.
     *
     * @param  array<int, int>|null  $id
     */
    public static function sasaran(string $jenis, ?array $id = null)
    {
        $model = $jenis === 'guru' ? Guru::class : Penjaga::class;

        $query = $id === null
            ? $model::belumAdaAkaun()
            : $model::whereIn('id', $id)->where(fn ($q) => $q->where('email', '!=', '')->orWhereNotNull('user_id'));

        return $query->with('user')->orderBy('id')->get();
    }

    /**
     * Bilangan yang akan menerima emel jika butang "Hantar Emel Akaun" ditekan sekarang.
     */
    public static function bilanganMenunggu(string $jenis): int
    {
        return ($jenis === 'guru' ? Guru::class : Penjaga::class)::belumAdaAkaun()->count();
    }
}
