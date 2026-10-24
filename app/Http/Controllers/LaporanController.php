<?php

namespace App\Http\Controllers;

use App\Models\Bayaran;
use App\Models\Kelas;
use App\Models\Pelajar;
use App\Services\BakiBulananService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function __construct(protected BakiBulananService $bakiService)
    {
    }

    public function index()
    {
        $jumlahDikutip = Bayaran::sum('amaun');

        $jumlahTertunggak = Pelajar::where('status', 'Aktif')->get()
            ->sum(fn ($p) => $this->bakiService->jumlahBakiTahunSemasa($p, null, true));

        $kutipanBulanIni = Bayaran::whereMonth('tarikh_bayaran', now()->month)
            ->whereYear('tarikh_bayaran', now()->year)
            ->sum('amaun');

        $jumlahBayaranHariIni = Bayaran::whereDate('tarikh_bayaran', today())->sum('amaun');

        // Jumlah bayaran mengikut Tahun (1-6) — pastikan semua 6 tahun
        // dipaparkan (0 jika tiada bayaran) supaya carta sentiasa konsisten.
        $mengikutTahunMentah = Bayaran::join('pelajar', 'pelajar.id', '=', 'bayaran.pelajar_id')
            ->whereNotNull('pelajar.darjah')
            ->selectRaw('pelajar.darjah, SUM(bayaran.amaun) as jumlah')
            ->groupBy('pelajar.darjah')
            ->pluck('jumlah', 'pelajar.darjah');

        $bayaranMengikutTahun = collect(['Tahun 1', 'Tahun 2', 'Tahun 3', 'Tahun 4', 'Tahun 5', 'Tahun 6'])
            ->map(fn ($darjah) => [
                'label' => $darjah,
                'jumlah' => (float) ($mengikutTahunMentah[$darjah] ?? 0),
            ]);

        // Jumlah bayaran mengikut Kelas.
        $bayaranMengikutKelas = Bayaran::join('pelajar', 'pelajar.id', '=', 'bayaran.pelajar_id')
            ->join('kelas', 'kelas.id', '=', 'pelajar.kelas_id')
            ->selectRaw('kelas.nama_kelas, SUM(bayaran.amaun) as jumlah')
            ->groupBy('kelas.nama_kelas')
            ->orderBy('kelas.nama_kelas')
            ->get()
            ->map(fn ($baris) => [
                'label' => $baris->nama_kelas,
                'jumlah' => (float) $baris->jumlah,
            ]);

        return view('laporan.index', compact(
            'jumlahDikutip',
            'jumlahTertunggak',
            'kutipanBulanIni',
            'jumlahBayaranHariIni',
            'bayaranMengikutTahun',
            'bayaranMengikutKelas'
        ));
    }

    public function kutipan(Request $request)
    {
        [$tarikhMula, $tarikhAkhir] = $this->julatTarikh($request);

        $query = $this->queryKutipan($request, $tarikhMula, $tarikhAkhir);

        // PENTING: kira agregat (sum/groupBy) daripada klon $query SEBELUM
        // orderBy() dikenakan di bawah untuk paginate() — orderBy() mengubah
        // builder tersebut secara "in place", jadi klon selepas itu akan
        // turut membawa ORDER BY tersebut sekali, menyebabkan ralat SQL
        // "ORDER BY ... not in GROUP BY" pada pertanyaan groupBy() di bawah.
        $jumlahKeseluruhan = (clone $query)->sum('amaun');

        $pecahanKaedah = (clone $query)
            ->selectRaw('kaedah_bayaran, SUM(amaun) as jumlah, COUNT(*) as bilangan')
            ->groupBy('kaedah_bayaran')
            ->get();

        [$urut, $arah] = $this->sahkanUrutan($request, ['no_bayaran', 'keterangan', 'amaun', 'kaedah_bayaran', 'tarikh_bayaran'], 'tarikh_bayaran', 'desc');

        $bayaran = $query->orderBy($urut, $arah)->paginate(10)->withQueryString();

        $senaraiKelas = Kelas::orderBy('nama_kelas')->get();
        $senaraiDarjah = PelajarController::SENARAI_DARJAH;

        return view('laporan.kutipan', compact(
            'bayaran',
            'jumlahKeseluruhan',
            'pecahanKaedah',
            'tarikhMula',
            'tarikhAkhir',
            'senaraiKelas',
            'senaraiDarjah',
            'urut',
            'arah'
        ));
    }

    public function kutipanCsv(Request $request): StreamedResponse
    {
        [$tarikhMula, $tarikhAkhir] = $this->julatTarikh($request);

        $query = $this->queryKutipan($request, $tarikhMula, $tarikhAkhir);

        $namaFail = 'laporan-kutipan-'.$tarikhMula->format('Ymd').'-'.$tarikhAkhir->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['No. Bayaran', 'Pelajar', 'Kelas', 'Keterangan', 'Amaun (RM)', 'Kaedah', 'Tarikh', 'No. Rujukan', 'No. Pesanan Gerbang Pembayaran']);

            $query->chunk(200, function ($kumpulan) use ($handle) {
                foreach ($kumpulan as $item) {
                    fputcsv($handle, [
                        $item->no_bayaran,
                        $item->pelajar->nama ?? '-',
                        $item->pelajar->kelas->nama_kelas ?? '-',
                        $item->keterangan,
                        number_format($item->amaun, 2, '.', ''),
                        $item->kaedah_bayaran,
                        $item->tarikh_bayaran?->format('d/m/Y'),
                        $item->no_rujukan ?? '-',
                        $item->no_pesanan_gateway ?? '-',
                    ]);
                }
            });

            fclose($handle);
        }, $namaFail, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function kutipanPdf(Request $request)
    {
        [$tarikhMula, $tarikhAkhir] = $this->julatTarikh($request);

        $query = $this->queryKutipan($request, $tarikhMula, $tarikhAkhir);

        $jumlahKeseluruhan = (clone $query)->sum('amaun');

        $pecahanKaedah = (clone $query)
            ->selectRaw('kaedah_bayaran, SUM(amaun) as jumlah, COUNT(*) as bilangan')
            ->groupBy('kaedah_bayaran')
            ->get();

        $bayaran = $query->orderBy('tarikh_bayaran', 'desc')->get();

        $namaFail = 'laporan-kutipan-'.$tarikhMula->format('Ymd').'-'.$tarikhAkhir->format('Ymd').'.pdf';

        $pdf = Pdf::loadView('laporan.kutipan-pdf', compact(
            'bayaran',
            'jumlahKeseluruhan',
            'pecahanKaedah',
            'tarikhMula',
            'tarikhAkhir'
        ))->setPaper('a4', 'landscape');

        return $pdf->download($namaFail);
    }

    /**
     * Pertanyaan Bayaran ditapis mengikut julat tarikh + Kaedah Bayaran +
     * Kelas + Tahun — dikongsi antara paparan jadual, pecahan kaedah, dan
     * muat turun CSV supaya semua sentiasa konsisten.
     */
    protected function queryKutipan(Request $request, \Carbon\Carbon $tarikhMula, \Carbon\Carbon $tarikhAkhir)
    {
        $query = Bayaran::with(['pelajar.kelas'])
            ->whereBetween('tarikh_bayaran', [$tarikhMula, $tarikhAkhir]);

        if ($request->filled('kaedah_bayaran')) {
            $query->where('kaedah_bayaran', $request->input('kaedah_bayaran'));
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('pelajar', fn ($q) => $q->where('kelas_id', $request->input('kelas_id')));
        }

        if ($request->filled('darjah')) {
            $query->whereHas('pelajar', fn ($q) => $q->where('darjah', $request->input('darjah')));
        }

        return $query;
    }

    protected function julatTarikh(Request $request): array
    {
        $tarikhMula = $request->filled('tarikh_mula')
            ? \Carbon\Carbon::parse($request->input('tarikh_mula'))->startOfDay()
            : now()->startOfMonth();

        $tarikhAkhir = $request->filled('tarikh_akhir')
            ? \Carbon\Carbon::parse($request->input('tarikh_akhir'))->endOfDay()
            : now()->endOfMonth();

        return [$tarikhMula, $tarikhAkhir];
    }
}
