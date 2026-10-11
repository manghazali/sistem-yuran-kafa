<?php

namespace App\Http\Controllers;

use App\Models\Bayaran;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ResitController extends Controller
{
    public function show(Bayaran $bayaran)
    {
        $bayaran->load(['pelajar.kelas', 'pelajar.penjaga', 'pencatat']);

        if (Auth::user()->hasRole('Penjaga')) {
            $penjaga = Auth::user()->penjaga;
            abort_if(! $penjaga, 403);
            abort_unless($bayaran->pelajar?->penjaga_id === $penjaga->id, 403, 'Resit ini bukan milik anak anda.');
        }

        // Rekod BAHARU: satu transaksi (walaupun merangkumi beberapa bulan) ialah
        // SATU baris Bayaran sahaja, dengan pecahan bulan pada setiap BayaranItem.
        // Rekod LAMA (sebelum perubahan ini): satu transaksi gateway masih mungkin
        // berpecah kepada beberapa baris Bayaran berasingan yang kongsi no_rujukan
        // yang sama. Kumpulkan semula di sini (jika berkenaan) supaya kedua-dua
        // bentuk rekod dipaparkan sebagai SATU resit yang sama.
        $kumpulanBayaran = $bayaran->no_rujukan
            ? Bayaran::with(['pelajar.kelas', 'pelajar.penjaga', 'items.tetapanYuranPelajar.strukturYuran'])
                ->where('no_rujukan', $bayaran->no_rujukan)
                ->where('pelajar_id', $bayaran->pelajar_id)
                ->orderBy('id')
                ->get()
            : collect([$bayaran->load('items.tetapanYuranPelajar.strukturYuran')]);

        // Ratakan kepada senarai baris resit (satu baris = satu item/bulan), supaya
        // satu Bayaran yang merangkumi pelbagai bulan tetap dipaparkan ikut bulan.
        $bariResit = $kumpulanBayaran->flatMap(function ($satu) {
            if ($satu->items->isNotEmpty()) {
                return $satu->items->map(function ($item) use ($satu) {
                    $strukturYuran = $item->tetapanYuranPelajar?->strukturYuran;
                    $berulang = $strukturYuran?->bulan_berkenaan !== null;

                    // Struktur dengan bulan_berkenaan dipaparkan sebagai
                    // bulan/tahun SEBENAR dikenakan (cth: "Oktober 2026"),
                    // bukan nama statik Struktur Yuran — sama seperti
                    // BakiBulananService, supaya resit konsisten dengan apa
                    // yang Penjaga nampak di Kalendar Yuran (elak label salah
                    // jika nama struktur kebetulan nama bulan lain, cth:
                    // "Januari" dikenakan pada bulan Oktober).
                    $nama = $berulang && $item->bulan && $satu->tahun
                        ? Carbon::create($satu->tahun, $item->bulan, 1)->translatedFormat('F Y')
                        : ($strukturYuran?->nama ?? $satu->keterangan);

                    return [
                        'nama' => $nama,
                        'bulan' => $item->bulan,
                        'amaun' => $item->amaun,
                    ];
                });
            }

            // Bayaran manual tanpa butiran item — satu baris sahaja guna maklumat induk.
            return collect([[
                'nama' => $satu->keterangan,
                'bulan' => $satu->bulan,
                'amaun' => $satu->amaun,
            ]]);
        })->values();

        return view('resit.show', [
            'bayaran' => $kumpulanBayaran->first(),
            'bariResit' => $bariResit,
            'jumlahKeseluruhan' => $kumpulanBayaran->sum('amaun'),
        ]);
    }
}
