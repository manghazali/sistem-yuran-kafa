<?php

namespace App\Http\Controllers;

use App\Models\Bayaran;
use App\Models\Pelajar;
use App\Models\PaymentGatewayTransaksi;
use App\Services\BakiBulananService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PortalController extends Controller
{
    public function __construct(protected BakiBulananService $bakiService)
    {
    }

    protected function penjagaSemasa()
    {
        $penjaga = Auth::user()->penjaga;

        abort_if(! $penjaga, 403, 'Akaun anda tidak dipautkan kepada mana-mana rekod penjaga. Sila hubungi pihak sekolah.');

        return $penjaga;
    }

    public function index()
    {
        $penjaga = $this->penjagaSemasa();
        $anak = $penjaga->anak()->with('kelas')->get();

        $prafetchBaki = $this->bakiService->prafetch($anak->pluck('id'), now()->year);

        $ringkasan = $anak->map(function ($pelajar) use ($prafetchBaki) {
            // Semua bulan yang belum dibayar (bukan setakat bulan semasa) — termasuk
            // yuran bulan hadapan yang belum tiba tarikh, bukan sekadar yang tertunggak.
            $bulanBelumDibayar = $this->bakiService->senaraiBulan($pelajar, now()->year, $prafetchBaki)
                ->filter(fn ($b) => $b['baki'] > 0);

            return [
                'pelajar' => $pelajar,
                'jumlah_baki' => $bulanBelumDibayar->sum('baki'),
                'bilangan_bulan_belum_dibayar' => $bulanBelumDibayar->count(),
                'bilangan_yuran_belum_dibayar' => $bulanBelumDibayar->sum(fn ($b) => $b['butiran']->where('baki', '>', 0)->count()),
            ];
        });

        $jumlahKeseluruhanBaki = $ringkasan->sum('jumlah_baki');

        return view('portal.index', compact('penjaga', 'ringkasan', 'jumlahKeseluruhanBaki'));
    }

    /**
     * Kumpulkan semua item yuran (merentasi semua anak) tahun semasa,
     * setiap item ditanda dengan pelajar berkaitan.
     */
    protected function semuaItemYuran(?Pelajar $pelajarDipilih = null): Collection
    {
        $penjaga = $this->penjagaSemasa();
        $anak = $pelajarDipilih ? collect([$pelajarDipilih]) : $penjaga->anak()->with('kelas')->get();
        $tahun = now()->year;

        return $anak->flatMap(function ($pelajar) use ($tahun) {
            return $this->bakiService->senaraiBulan($pelajar, $tahun)
                ->flatMap(fn ($b) => $b['butiran']->map(fn ($item) => $item + [
                    'pelajar' => $pelajar,
                ]));
        })->values();
    }

    /**
     * Senarai anak penjaga semasa dan anak yang dipilih dalam dropdown (pelajar_id),
     * hanya jika anak itu milik penjaga ini.
     *
     * @return array{0: Collection, 1: ?Pelajar}
     */
    protected function penapisAnak(Request $request): array
    {
        $anak = $this->penjagaSemasa()->anak()->with('kelas')->get();

        $pelajarDipilih = $request->filled('pelajar_id')
            ? $anak->firstWhere('id', (int) $request->input('pelajar_id'))
            : null;

        return [$anak, $pelajarDipilih];
    }

    /**
     * Bungkus koleksi (dalam memori, bukan query DB) kepada LengthAwarePaginator
     * supaya boleh guna ->links() sama seperti senarai lain dalam sistem.
     */
    protected function paginateKoleksi(Collection $items, int $perPage = 10): LengthAwarePaginator
    {
        $halaman = (int) (request()->input('page', 1));

        return new LengthAwarePaginator(
            $items->forPage($halaman, $perPage)->values(),
            $items->count(),
            $perPage,
            $halaman,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Urutkan koleksi item yuran (dalam memori) mengikut lajur yang disahkan.
     */
    protected function urutkanItemYuran(Collection $items, Request $request): Collection
    {
        [$urut, $arah] = $this->sahkanUrutan($request, ['anak', 'nama', 'status', 'amaun'], 'anak');

        $menurun = $arah === 'desc';

        return match ($urut) {
            'anak' => $items->sortBy(fn ($i) => $i['pelajar']->nama, SORT_REGULAR, $menurun),
            'amaun' => $items->sortBy(fn ($i) => $i['status'] === 'Dibayar' ? $i['amaun'] : $i['baki'], SORT_REGULAR, $menurun),
            default => $items->sortBy($urut, SORT_REGULAR, $menurun),
        };
    }

    public function yuranSemua(Request $request)
    {
        [$urut, $arah] = $this->sahkanUrutan($request, ['anak', 'nama', 'status', 'amaun'], 'anak');
        [$anak, $pelajarDipilih] = $this->penapisAnak($request);

        $senaraiItem = $this->paginateKoleksi(
            $this->urutkanItemYuran($this->semuaItemYuran($pelajarDipilih), $request)->values()
        );

        return view('portal.yuran', [
            'tajuk' => 'Semua Yuran',
            'senaraiItem' => $senaraiItem,
            'urut' => $urut,
            'arah' => $arah,
            'anak' => $anak,
            'pelajarDipilih' => $pelajarDipilih,
        ]);
    }

    public function yuranBelumBayar(Request $request)
    {
        [$urut, $arah] = $this->sahkanUrutan($request, ['anak', 'nama', 'status', 'amaun'], 'anak');
        [$anak, $pelajarDipilih] = $this->penapisAnak($request);

        $senaraiItem = $this->paginateKoleksi(
            $this->urutkanItemYuran(
                $this->semuaItemYuran($pelajarDipilih)->filter(fn ($item) => $item['status'] !== 'Dibayar')->values(),
                $request
            )->values()
        );

        return view('portal.yuran', [
            'tajuk' => 'Yuran Belum Bayar',
            'senaraiItem' => $senaraiItem,
            'urut' => $urut,
            'arah' => $arah,
            'anak' => $anak,
            'pelajarDipilih' => $pelajarDipilih,
        ]);
    }

    public function yuranBulanan(Request $request)
    {
        [$urut, $arah] = $this->sahkanUrutan($request, ['anak', 'nama', 'status', 'amaun'], 'anak');
        [$anak, $pelajarDipilih] = $this->penapisAnak($request);

        $senaraiItem = $this->paginateKoleksi(
            $this->urutkanItemYuran(
                $this->semuaItemYuran($pelajarDipilih)->filter(fn ($item) => $item['berulang'])->values(),
                $request
            )->values()
        );

        return view('portal.yuran', [
            'tajuk' => 'Yuran Bulanan',
            'senaraiItem' => $senaraiItem,
            'urut' => $urut,
            'arah' => $arah,
            'anak' => $anak,
            'pelajarDipilih' => $pelajarDipilih,
        ]);
    }

    public function sejarahBayaran(Request $request)
    {
        $penjaga = $this->penjagaSemasa();
        $anak = $penjaga->anak()->with('kelas')->get();
        $anakIds = $anak->pluck('id');

        $pelajarDipilih = $request->filled('pelajar_id')
            ? $anak->firstWhere('id', (int) $request->input('pelajar_id'))
            : null;

        [$urut, $arah] = $this->sahkanUrutan($request, ['no_bayaran', 'keterangan', 'amaun', 'tarikh_bayaran'], 'tarikh_bayaran', 'desc');

        $bayaran = Bayaran::with('pelajar')
            ->whereIn('pelajar_id', $pelajarDipilih ? [$pelajarDipilih->id] : $anakIds)
            ->orderBy($urut, $arah)
            ->paginate(10)
            ->withQueryString();

        // Bayaran dalam talian yang belum disahkan gateway — dipaparkan sebagai notis supaya
        // penjaga tahu wang mereka sedang diproses dan tidak membayar dua kali.
        $transaksiTertunda = PaymentGatewayTransaksi::where('status', 'Menunggu')
            ->whereHas('itemBayaran', fn ($q) => $q->whereIn('pelajar_id', $anakIds))
            ->latest()
            ->get();

        return view('portal.sejarah-bayaran', compact('bayaran', 'anak', 'pelajarDipilih', 'urut', 'arah', 'transaksiTertunda'));
    }
}
