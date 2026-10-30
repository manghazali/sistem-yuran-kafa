<?php

namespace App\Http\Controllers;

use App\Exceptions\GerbangPembayaranTidakTersedia;
use App\Models\PaymentGatewayTransaksiItem;
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
    ) {}

    protected function sahkanMilikPenjaga(Pelajar $pelajar)
    {
        $penjaga = Auth::user()->penjaga;
        abort_if(! $penjaga, 403);
        abort_unless($pelajar->penjaga_id === $penjaga->id, 403, 'Pelajar ini bukan anak anda.');

        return $penjaga;
    }

    /**
     * Terjemah pilihan item bagi seorang pelajar kepada subjumlah setiap bulan —
     * hanya item yang dipilih, masih ada baki, dan tidak sedang dibayar dalam talian.
     *
     * @param  Collection<int, array{0: int, 1: int, 2: int}>  $pilihan  [tahun, bulan, tetapan_yuran_pelajar_id]
     * @param  Collection<int, string>  $sedangDiproses  kunci "pelajar:tahun:bulan:id"
     */
    protected function kumpulPilihanItem(Pelajar $pelajar, Collection $pilihan, Collection $sedangDiproses): Collection
    {
        $kunciTerpilih = $pilihan->map(fn ($p) => implode(':', $p))->flip();

        return $this->bakiService->bulanBelumBayar($pelajar)
            ->map(function ($b) use ($pelajar, $kunciTerpilih, $sedangDiproses) {
                $butiranTerpilih = $b['butiran']
                    ->filter(fn ($item) => $kunciTerpilih->has("{$b['tahun']}:{$b['bulan']}:{$item['id']}")
                        && $item['baki'] > 0
                        && ! $sedangDiproses->contains("{$pelajar->id}:{$b['tahun']}:{$b['bulan']}:{$item['id']}"))
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
     * Pilihan merentasi semua anak (nilai checkbox "pelajar_id:tahun:bulan:tetapan_yuran_pelajar_id").
     * Hanya anak milik penjaga semasa diambil kira; nilai untuk pelajar lain diabaikan.
     *
     * @param  array<int, string>  $pilihanItem
     * @return Collection<int, array{pelajar: Pelajar, bulan: Collection}>
     */
    protected function kumpulPilihanSemuaAnak(Penjaga $penjaga, array $pilihanItem): Collection
    {
        $pilihanMengikutPelajar = collect($pilihanItem)
            ->map(fn ($nilai) => array_map('intval', explode(':', $nilai)))
            ->filter(fn ($bahagian) => count($bahagian) === 4)
            ->groupBy(fn ($bahagian) => $bahagian[0])
            ->map(fn ($kumpulan) => $kumpulan->map(fn ($bahagian) => array_slice($bahagian, 1)));

        $anak = $penjaga->anak()->with('kelas')->get();
        $sedangDiproses = $this->itemSedangDiproses($anak->pluck('id'));

        return $anak
            ->filter(fn ($pelajar) => $pilihanMengikutPelajar->has($pelajar->id))
            ->map(fn ($pelajar) => [
                'pelajar' => $pelajar,
                'bulan' => $this->kumpulPilihanItem($pelajar, $pilihanMengikutPelajar->get($pelajar->id), $sedangDiproses),
            ])
            ->filter(fn ($kumpulan) => $kumpulan['bulan']->isNotEmpty())
            ->values();
    }

    /**
     * Item yang sedang dibayar dalam talian (transaksi masih "Menunggu") — tidak boleh
     * dipilih semula sehingga keputusan diterima, supaya tidak dibayar dua kali.
     *
     * @return Collection<int, string> kunci "pelajar:tahun:bulan:tetapan_yuran_pelajar_id"
     */
    protected function itemSedangDiproses(Collection $pelajarId): Collection
    {
        return PaymentGatewayTransaksiItem::whereIn('pelajar_id', $pelajarId)
            ->whereHas('transaksi', fn ($q) => $q->where('status', 'Menunggu'))
            ->get()
            ->map(fn ($i) => "{$i->pelajar_id}:{$i->tahun}:{$i->bulan}:{$i->tetapan_yuran_pelajar_id}")
            ->unique()
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
        $anak = $penjaga->anak()->with('kelas')->get();
        $sedangDiproses = $this->itemSedangDiproses($anak->pluck('id'));

        // Termasuk tunggakan tahun lepas (lihat BakiBulananService::tahunBolehBayar()).
        $senaraiAnak = $anak
            ->map(fn ($pelajar) => [
                'pelajar' => $pelajar,
                'bulan' => $this->bakiService->bulanBelumBayar($pelajar),
            ])
            ->filter(fn ($kumpulan) => $kumpulan['bulan']->isNotEmpty())
            ->values();

        return view('portal.bayar-semua', compact('senaraiAnak', 'tahun', 'sedangDiproses'));
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

        $senaraiSemakan = $this->kumpulPilihanSemuaAnak($penjaga, $validated['item']);

        if ($senaraiSemakan->isEmpty()) {
            return redirect()->route('portal.bayar-semua')
                ->with('status', 'Yuran yang dipilih sudah dibayar, sedang diproses atau tiada yuran berkaitan.');
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
        $senaraiItem = $this->kumpulPilihanSemuaAnak($penjaga, $validated['item'])
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
                ->with('status', 'Yuran yang dipilih sudah dibayar, sedang diproses atau tiada yuran berkaitan.');
        }

        try {
            $url = $this->gateway->mulakanTransaksiItem(
                $senaraiItem,
                $penjaga,
                $penjaga->email ?: Auth::user()->email,
                $penjaga->no_telefon ?: '0100000000'
            );
        } catch (GerbangPembayaranTidakTersedia) {
            // Kembali ke halaman semakan dengan pilihan yang sama supaya penjaga boleh cuba semula.
            return redirect()->route('portal.bayar-semua.semak', ['item' => $validated['item']])
                ->with('ralat', 'Gerbang pembayaran tidak dapat dihubungi buat masa ini. Tiada caj dikenakan. Sila cuba sebentar lagi.');
        }

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
