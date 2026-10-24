<?php

namespace App\Http\Controllers;

use App\Models\Pelajar;
use App\Models\Penjaga;
use App\Services\BakiBulananService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PortalKalendarController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $gateway,
        protected BakiBulananService $bakiService
    ) {
    }

    protected function sahkanMilikPenjaga(Pelajar $pelajar)
    {
        $penjaga = Auth::user()->penjaga;
        abort_if(! $penjaga, 403);
        abort_unless($pelajar->penjaga_id === $penjaga->id, 403, 'Pelajar ini bukan anak anda.');

        return $penjaga;
    }

    /**
     * Terjemah pilihan item (nilai checkbox "bulan:tetapan_yuran_pelajar_id") kepada
     * subjumlah setiap bulan — hanya jumlah item yang dipilih, bukan baki keseluruhan bulan.
     *
     * @param  array<int, string>  $pilihanItem
     */
    protected function kumpulPilihanItem(Pelajar $pelajar, array $pilihanItem, int $tahun): Collection
    {
        $senaraiBulan = $this->bakiService->senaraiBulan($pelajar, $tahun);

        $idTerpilihMengikutBulan = collect($pilihanItem)
            ->map(fn ($nilai) => explode(':', $nilai, 2))
            ->filter(fn ($bahagian) => count($bahagian) === 2)
            ->groupBy(fn ($bahagian) => (int) $bahagian[0])
            ->map(fn ($kumpulan) => $kumpulan->map(fn ($bahagian) => (int) $bahagian[1]));

        return $senaraiBulan
            ->map(function ($b) use ($idTerpilihMengikutBulan) {
                $idTerpilih = $idTerpilihMengikutBulan->get($b['bulan'], collect());

                $butiranTerpilih = $b['butiran']
                    ->filter(fn ($item) => $idTerpilih->contains($item['id']) && $item['baki'] > 0)
                    ->values();

                if ($butiranTerpilih->isEmpty()) {
                    return null;
                }

                return array_merge($b, [
                    'butiran' => $butiranTerpilih,
                    'baki' => round($butiranTerpilih->sum('baki'), 2),
                ]);
            })
            ->filter(fn ($b) => $b && $b['baki'] > 0)
            ->values();
    }

    /**
     * Pilihan merentasi semua anak (nilai checkbox "pelajar_id:bulan:tetapan_yuran_pelajar_id").
     * Hanya anak milik penjaga semasa diambil kira; nilai untuk pelajar lain diabaikan.
     *
     * @param  array<int, string>  $pilihanItem
     * @return Collection<int, array{pelajar: Pelajar, bulan: Collection}>
     */
    protected function kumpulPilihanSemuaAnak(Penjaga $penjaga, array $pilihanItem, int $tahun): Collection
    {
        $pilihanMengikutPelajar = collect($pilihanItem)
            ->map(fn ($nilai) => explode(':', $nilai, 2))
            ->filter(fn ($bahagian) => count($bahagian) === 2)
            ->groupBy(fn ($bahagian) => (int) $bahagian[0])
            ->map(fn ($kumpulan) => $kumpulan->map(fn ($bahagian) => $bahagian[1])->all());

        return $penjaga->anak()->with('kelas')->get()
            ->filter(fn ($pelajar) => $pilihanMengikutPelajar->has($pelajar->id))
            ->map(fn ($pelajar) => [
                'pelajar' => $pelajar,
                'bulan' => $this->kumpulPilihanItem($pelajar, $pilihanMengikutPelajar->get($pelajar->id), $tahun),
            ])
            ->filter(fn ($kumpulan) => $kumpulan['bulan']->isNotEmpty())
            ->values();
    }

    protected function penjagaSemasa(): Penjaga
    {
        $penjaga = Auth::user()->penjaga;
        abort_if(! $penjaga, 403);

        return $penjaga;
    }

    public function semuaAnak()
    {
        $penjaga = $this->penjagaSemasa();
        $tahun = now()->year;

        $senaraiAnak = $penjaga->anak()->with('kelas')->get()
            ->map(fn ($pelajar) => [
                'pelajar' => $pelajar,
                'bulan' => $this->bakiService->senaraiBulan($pelajar, $tahun),
            ])
            ->filter(fn ($kumpulan) => $kumpulan['bulan']->contains(fn ($b) => $b['baki'] > 0))
            ->values();

        return view('portal.bayar-semua', compact('senaraiAnak', 'tahun'));
    }

    public function semuaAnakSemak(Request $request)
    {
        $penjaga = $this->penjagaSemasa();

        $validated = $request->validate([
            'item' => ['required', 'array', 'min:1'],
            'item.*' => ['string'],
        ], [
            'item.required' => 'Sila pilih sekurang-kurangnya satu yuran untuk dibayar.',
        ]);

        $senaraiSemakan = $this->kumpulPilihanSemuaAnak($penjaga, $validated['item'], now()->year);

        if ($senaraiSemakan->isEmpty()) {
            return redirect()->route('portal.bayar-semua')
                ->with('status', 'Yuran yang dipilih sudah dibayar atau tiada yuran berkaitan.');
        }

        $jumlahKeseluruhan = $senaraiSemakan->sum(fn ($k) => $k['bulan']->sum('baki'));

        return view('portal.bayar-semua-semak', compact('senaraiSemakan', 'jumlahKeseluruhan'));
    }

    public function semuaAnakBayar(Request $request)
    {
        $penjaga = $this->penjagaSemasa();

        $validated = $request->validate([
            'item' => ['required', 'array', 'min:1'],
            'item.*' => ['string'],
        ], [
            'item.required' => 'Sila pilih sekurang-kurangnya satu yuran untuk dibayar.',
        ]);

        // Satu transaksi untuk semua anak; webhook memecahkannya kepada satu resit bagi setiap anak.
        $senaraiItem = $this->kumpulPilihanSemuaAnak($penjaga, $validated['item'], now()->year)
            ->flatMap(fn ($k) => $k['bulan']->flatMap(fn ($b) => $b['butiran']->map(fn ($item) => [
                'pelajar_id' => $k['pelajar']->id,
                'nama_pelajar' => $k['pelajar']->nama,
                'tetapan_yuran_pelajar_id' => $item['id'],
                'bulan' => $b['bulan'],
                'tahun' => $b['tahun'],
                'label' => $b['label'],
                'keterangan' => $item['nama'],
                'baki' => $item['baki'],
            ])))
            ->values();

        if ($senaraiItem->isEmpty()) {
            return redirect()->route('portal.bayar-semua')
                ->with('status', 'Yuran yang dipilih sudah dibayar atau tiada yuran berkaitan.');
        }

        $url = $this->gateway->mulakanTransaksiItem(
            $senaraiItem,
            $penjaga,
            $penjaga->email ?: Auth::user()->email,
            $penjaga->no_telefon ?: '0100000000'
        );

        return redirect()->away($url);
    }

    /**
     * Paparan sahaja (tanpa pilih/bayar) — senarai yuran seorang anak dan statusnya.
     */
    public function lihat(Pelajar $pelajar)
    {
        $this->sahkanMilikPenjaga($pelajar);

        $tahun = now()->year;
        $senaraiBulan = $this->bakiService->senaraiBulan($pelajar, $tahun);
        $jumlahBaki = $senaraiBulan->sum('baki');

        return view('portal.anak', compact('pelajar', 'senaraiBulan', 'tahun', 'jumlahBaki'));
    }
}
