<?php

namespace App\Http\Controllers;

use App\Models\Pelajar;
use Illuminate\Http\Request;

class NaikDarjahController extends Controller
{
    /**
     * Susunan darjah mengikut urutan menaik — digunakan untuk tentukan
     * darjah seterusnya bagi setiap pelajar semasa Naik Darjah pukal.
     *
     * @var array<int, string>
     */
    protected const SENARAI_DARJAH = PelajarController::SENARAI_DARJAH;

    public function index()
    {
        $kiraMengikutDarjah = Pelajar::where('status', 'Aktif')
            ->selectRaw('darjah, count(*) as bilangan')
            ->groupBy('darjah')
            ->pluck('bilangan', 'darjah');

        $pratonton = collect(self::SENARAI_DARJAH)->map(function ($darjah, $indeks) use ($kiraMengikutDarjah) {
            $akanJadi = self::SENARAI_DARJAH[$indeks + 1] ?? null;

            return [
                'darjah_semasa' => $darjah,
                'bilangan' => $kiraMengikutDarjah[$darjah] ?? 0,
                'darjah_akan_jadi' => $akanJadi,
                'tamat' => $akanJadi === null,
            ];
        });

        $bilanganTanpaDarjahSah = Pelajar::where('status', 'Aktif')
            ->whereNotIn('darjah', self::SENARAI_DARJAH)
            ->count();

        return view('naik-darjah.index', compact('pratonton', 'bilanganTanpaDarjahSah'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sahkan' => ['accepted'],
            'tamatkan_tahun_enam' => ['nullable', 'boolean'],
        ]);

        $tamatkanTahunEnam = $request->boolean('tamatkan_tahun_enam');
        $senarai = self::SENARAI_DARJAH;
        $bilanganDinaikkan = 0;
        $bilanganDitamatkan = 0;

        // Satu-satu (bukan mass update query) supaya PelajarObserver tetap
        // mencatat log audit bagi setiap pelajar yang terjejas, sama seperti
        // kemaskini individu melalui borang.
        foreach (Pelajar::where('status', 'Aktif')->whereIn('darjah', $senarai)->get() as $pelajar) {
            $indeks = array_search($pelajar->darjah, $senarai, true);
            $darjahSeterusnya = $senarai[$indeks + 1] ?? null;

            if ($darjahSeterusnya) {
                $pelajar->darjah = $darjahSeterusnya;
                $pelajar->save();
                $bilanganDinaikkan++;
            } elseif ($tamatkanTahunEnam) {
                $pelajar->status = 'Tamat';
                $pelajar->save();
                $bilanganDitamatkan++;
            }
        }

        return redirect()->route('naik-darjah.index')->with(
            'status',
            "Naik Darjah selesai: {$bilanganDinaikkan} pelajar dinaikkan darjah, {$bilanganDitamatkan} pelajar Tahun 6 ditandakan Tamat."
        );
    }
}
