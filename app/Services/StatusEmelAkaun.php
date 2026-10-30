<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\LogEmel;
use App\Models\Penjaga;
use App\Support\EmelTemplat;
use Illuminate\Support\Collection;

/**
 * Status emel akaun log masuk bagi senarai Guru/Penjaga, berdasarkan Log Emel
 * (emel yang benar-benar dihantar oleh pelayan emel).
 */
class StatusEmelAkaun
{
    /**
     * @param  Collection<int, Guru|Penjaga>  $orang  dengan relasi user dimuatkan
     * @param  'guru'|'penjaga'  $jenis
     * @return Collection<int, array{kod: string, emel: ?string, masa: ?string}> ikut id
     *                                                                           kod: tiada (tiada emel), belum (belum dihantar),
     *                                                                           giliran (akaun dicipta, emel belum keluar), dihantar
     */
    public static function untuk(Collection $orang, string $jenis): Collection
    {
        $emelOrang = fn ($o) => $o->email ?: $o->user?->email;

        // Subjek semasa dan asal — emel lama masih dikenali jika Admin menukar subjek templat.
        $subjek = array_unique([EmelTemplat::subjek("akaun-{$jenis}"), EmelTemplat::DEFAULT_TEMPLAT["akaun-{$jenis}"]['subjek']]);

        $dihantar = LogEmel::whereIn('kepada', $orang->map($emelOrang)->filter()->unique()->values())
            ->whereIn('subjek', $subjek)
            ->selectRaw('kepada, MAX(created_at) as masa')
            ->groupBy('kepada')
            ->pluck('masa', 'kepada');

        return $orang->mapWithKeys(function ($o) use ($emelOrang, $dihantar) {
            $emel = $emelOrang($o);

            $kod = match (true) {
                ! $emel => 'tiada',
                ! $o->user_id => 'belum',
                $dihantar->has($emel) => 'dihantar',
                default => 'giliran',
            };

            return [$o->id => ['kod' => $kod, 'emel' => $emel, 'masa' => $dihantar->get($emel)]];
        });
    }
}
