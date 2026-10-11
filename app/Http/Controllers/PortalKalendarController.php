<?php

namespace App\Http\Controllers;

use App\Models\Pelajar;
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

    public function index(Pelajar $pelajar)
    {
        $this->sahkanMilikPenjaga($pelajar);

        $tahun = now()->year;
        $senaraiBulan = $this->bakiService->senaraiBulan($pelajar, $tahun);

        return view('portal.kalendar', compact('pelajar', 'senaraiBulan', 'tahun'));
    }

    public function semak(Request $request, Pelajar $pelajar)
    {
        $this->sahkanMilikPenjaga($pelajar);

        $validated = $request->validate([
            'item' => ['required', 'array', 'min:1'],
            'item.*' => ['string'],
        ], [
            'item.required' => 'Sila pilih sekurang-kurangnya satu yuran untuk dibayar.',
        ]);

        $tahun = now()->year;
        $senaraiSemakan = $this->kumpulPilihanItem($pelajar, $validated['item'], $tahun);

        if ($senaraiSemakan->isEmpty()) {
            return redirect()->route('portal.kalendar', $pelajar)
                ->with('status', 'Yuran yang dipilih sudah lunas atau tiada yuran berkaitan.');
        }

        $jumlahKeseluruhan = $senaraiSemakan->sum('baki');

        return view('portal.kalendar-semak', compact('pelajar', 'senaraiSemakan', 'jumlahKeseluruhan'));
    }

    public function bayar(Request $request, Pelajar $pelajar)
    {
        $penjaga = $this->sahkanMilikPenjaga($pelajar);

        $validated = $request->validate([
            'item' => ['required', 'array', 'min:1'],
            'item.*' => ['string'],
        ], [
            'item.required' => 'Sila pilih sekurang-kurangnya satu yuran untuk dibayar.',
        ]);

        $tahun = now()->year;

        $senaraiItem = $this->kumpulPilihanItem($pelajar, $validated['item'], $tahun)
            ->flatMap(fn ($b) => $b['butiran']->map(fn ($item) => [
                'pelajar_id' => $pelajar->id,
                'nama_pelajar' => $pelajar->nama,
                'tetapan_yuran_pelajar_id' => $item['id'],
                'bulan' => $b['bulan'],
                'tahun' => $b['tahun'],
                'label' => $b['label'],
                'keterangan' => $item['nama'],
                'baki' => $item['baki'],
            ]))
            ->values();

        if ($senaraiItem->isEmpty()) {
            return redirect()->route('portal.kalendar', $pelajar)
                ->with('status', 'Yuran yang dipilih sudah lunas atau tiada yuran berkaitan.');
        }

        $url = $this->gateway->mulakanTransaksiItem(
            $senaraiItem,
            $penjaga,
            $penjaga->email ?: Auth::user()->email,
            $penjaga->no_telefon ?: '0100000000'
        );

        return redirect()->away($url);
    }
}
