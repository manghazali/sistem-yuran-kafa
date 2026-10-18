<?php

namespace App\Http\Controllers;

use App\Models\Bayaran;
use App\Models\Guru;
use App\Models\LogAudit;
use App\Models\Pelajar;
use App\Models\PermohonanBayaran;
use App\Services\BakiBulananService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class DashboardController extends Controller
{
    public function __construct(protected BakiBulananService $bakiService)
    {
    }

    public function index(Request $request)
    {
        return Redirect::route($request->user()->dashboardRoute());
    }

    public function admin(Request $request)
    {
        $this->authorizeRole($request, ['Admin']);

        $pelajarAktif = Pelajar::where('status', 'Aktif')->get();

        $prafetchBaki = $this->bakiService->prafetch($pelajarAktif->pluck('id'), now()->year);

        $jumlahPelajarAktif = $pelajarAktif->count();
        $jumlahBakiTertunggak = $pelajarAktif->sum(fn ($p) => $this->bakiService->jumlahBakiTahunSemasa($p, $prafetchBaki, true));
        $jumlahBayaranHariIni = Bayaran::whereDate('tarikh_bayaran', today())->sum('amaun');

        $jumlahKutipanBulanIni = Bayaran::whereMonth('tarikh_bayaran', now()->month)
            ->whereYear('tarikh_bayaran', now()->year)
            ->sum('amaun');

        $trendBulanan = $this->trendKutipan6Bulan();

        $aktivitiTerkini = LogAudit::with('pengguna')->orderByDesc('created_at')->limit(6)->get();

        return view('dashboard.admin', compact(
            'jumlahPelajarAktif',
            'jumlahBakiTertunggak',
            'jumlahBayaranHariIni',
            'jumlahKutipanBulanIni',
            'trendBulanan',
            'aktivitiTerkini'
        ));
    }

    public function akauntan(Request $request)
    {
        $this->authorizeRole($request, ['Akauntan']);

        $pelajarAktif = Pelajar::where('status', 'Aktif')->get();

        $prafetchBaki = $this->bakiService->prafetch($pelajarAktif->pluck('id'), now()->year);

        $jumlahBakiTertunggak = $pelajarAktif->sum(fn ($p) => $this->bakiService->jumlahBakiTahunSemasa($p, $prafetchBaki, true));
        $jumlahBayaranHariIni = Bayaran::whereDate('tarikh_bayaran', today())->sum('amaun');

        $jumlahKutipanBulanIni = Bayaran::whereMonth('tarikh_bayaran', now()->month)
            ->whereYear('tarikh_bayaran', now()->year)
            ->sum('amaun');

        $jumlahPermohonanMenunggu = PermohonanBayaran::where('status', 'Menunggu')->count();

        $trendBulanan = $this->trendKutipan6Bulan();

        $bayaranTerkini = Bayaran::with('pelajar')->orderByDesc('created_at')->limit(6)->get();

        return view('dashboard.akauntan', compact(
            'jumlahBakiTertunggak',
            'jumlahBayaranHariIni',
            'jumlahKutipanBulanIni',
            'jumlahPermohonanMenunggu',
            'trendBulanan',
            'bayaranTerkini'
        ));
    }

    protected function trendKutipan6Bulan()
    {
        return collect(range(5, 0))->map(function ($i) {
            $bulan = now()->subMonths($i);

            $jumlah = Bayaran::whereMonth('tarikh_bayaran', $bulan->month)
                ->whereYear('tarikh_bayaran', $bulan->year)
                ->sum('amaun');

            return [
                'label' => $bulan->translatedFormat('M Y'),
                'jumlah' => (float) $jumlah,
            ];
        });
    }

    public function guru(Request $request)
    {
        $this->authorizeRole($request, ['Guru']);

        $guru = Guru::where('user_id', $request->user()->id)->with('guruKelas.kelas')->first();

        $pelajarKelasSaya = collect();
        $statusBayaranMengikutKelas = collect();
        $statusPermohonan = ['Menunggu' => 0, 'Disahkan' => 0, 'Ditolak' => 0];

        if ($guru && $guru->guruKelas->isNotEmpty()) {
            $query = Pelajar::where('status', 'Aktif')->where(function ($q) use ($guru) {
                foreach ($guru->guruKelas as $gk) {
                    $q->orWhere(function ($q2) use ($gk) {
                        $q2->where('kelas_id', $gk->kelas_id);

                        if ($gk->darjah) {
                            $q2->where('darjah', $gk->darjah);
                        }
                    });
                }
            });

            $pelajarKelasSaya = $query->get();

            $prafetchBaki = $this->bakiService->prafetch($pelajarKelasSaya->pluck('id'), now()->year);

            // Kumpulkan status bayaran (Dibayar / Tertunggak) mengikut setiap
            // penugasan Guru Kelas (bukan sekadar kelas_id pelajar) supaya
            // pelajar yang sama tidak terkumpul dua kali jika guru punya
            // lebih dari satu penugasan yang bertindih pada kelas yang sama.
            $statusBayaranMengikutKelas = $guru->guruKelas->map(function ($gk) use ($pelajarKelasSaya, $prafetchBaki) {
                $senarai = $pelajarKelasSaya->filter(function ($p) use ($gk, $prafetchBaki) {
                    if ($p->kelas_id !== $gk->kelas_id || ($gk->darjah && $p->darjah !== $gk->darjah)) {
                        return false;
                    }

                    // Pelajar tanpa sebarang Struktur Yuran ditetapkan tiada
                    // apa-apa untuk dikira dibayar/tertunggak — dikecualikan
                    // supaya carta tidak mengelirukan (nampak "dibayar"
                    // sedangkan sebenarnya tiada yuran langsung ditetapkan).
                    return $prafetchBaki['tetapan']->get($p->id, collect())->isNotEmpty();
                });

                // Jumlah RM sebenar (bukan sekadar bilangan pelajar) telah
                // dibayar berbanding tertunggak, dikira merentasi 12 bulan
                // tahun semasa bagi setiap pelajar dalam kumpulan ini.
                $jumlahDibayar = 0.0;
                $jumlahTertunggak = 0.0;

                foreach ($senarai as $p) {
                    $senaraiBulan = $this->bakiService->senaraiBulan($p, now()->year, $prafetchBaki);
                    $jumlahDibayar += $senaraiBulan->sum('dibayar');
                    $jumlahTertunggak += $senaraiBulan->sum('baki');
                }

                return [
                    'label' => ($gk->kelas->nama_kelas ?? '-').' ('.($gk->darjah ?? 'Semua Tahun').')',
                    'dibayar' => round($jumlahDibayar, 2),
                    'tertunggak' => round($jumlahTertunggak, 2),
                    'jumlah_pelajar' => $senarai->count(),
                ];
            })->filter(fn ($d) => $d['jumlah_pelajar'] > 0)->values();

            PermohonanBayaran::where('guru_id', $guru->id)
                ->selectRaw('status, count(*) as jumlah')
                ->groupBy('status')
                ->pluck('jumlah', 'status')
                ->each(function ($jumlah, $status) use (&$statusPermohonan) {
                    $statusPermohonan[$status] = $jumlah;
                });
        }

        return view('dashboard.guru', compact('guru', 'pelajarKelasSaya', 'statusBayaranMengikutKelas', 'statusPermohonan'));
    }

    protected function authorizeRole(Request $request, array $roles): void
    {
        if (! $request->user()->hasAnyRole($roles)) {
            abort(403);
        }
    }
}
