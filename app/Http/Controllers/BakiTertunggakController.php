<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Pelajar;
use App\Services\BakiBulananService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BakiTertunggakController extends Controller
{
    public function __construct(protected BakiBulananService $bakiService) {}

    public function index(Request $request)
    {
        [$mengikutPelajar, $urut, $arah] = $this->senaraiTertapis($request);

        $jumlahKeseluruhanBelumDibayar = $mengikutPelajar->sum('jumlah_baki');
        $senaraiKelas = Kelas::orderBy('nama_kelas')->get();
        $senaraiDarjah = PelajarController::SENARAI_DARJAH;

        return view('baki-tertunggak.index', compact(
            'mengikutPelajar',
            'jumlahKeseluruhanBelumDibayar',
            'senaraiKelas',
            'senaraiDarjah',
            'urut',
            'arah'
        ));
    }

    public function csv(Request $request): StreamedResponse
    {
        [$mengikutPelajar] = $this->senaraiTertapis($request);

        $namaFail = 'laporan-baki-belum-dibayar-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($mengikutPelajar) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Pelajar', 'Kelas', 'Yuran Belum Bayar', 'Bil. Bulan Belum Dibayar', 'Jumlah Baki (RM)']);

            foreach ($mengikutPelajar as $data) {
                fputcsv($handle, [
                    $data['pelajar']->nama ?? '-',
                    $data['pelajar']->kelas->nama_kelas ?? '-',
                    $data['senarai_yuran']->pluck('nama')->implode(', '),
                    $data['bilangan_bulan'],
                    number_format($data['jumlah_baki'], 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $namaFail, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function pdf(Request $request)
    {
        [$mengikutPelajar] = $this->senaraiTertapis($request);

        $jumlahKeseluruhanBelumDibayar = $mengikutPelajar->sum('jumlah_baki');
        $namaFail = 'laporan-baki-belum-dibayar-'.now()->format('Ymd').'.pdf';

        $pdf = Pdf::loadView('baki-tertunggak.pdf', compact('mengikutPelajar', 'jumlahKeseluruhanBelumDibayar'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($namaFail);
    }

    /**
     * Senarai pelajar dengan baki belum dibayar, ditapis mengikut Kelas/Tahun
     * (jika ada) dan diurutkan — dikongsi antara paparan jadual dan muat
     * turun CSV supaya kedua-duanya sentiasa konsisten.
     *
     * @return array{0: Collection, 1: string, 2: string}
     */
    protected function senaraiTertapis(Request $request): array
    {
        $query = Pelajar::with('kelas')->where('status', 'Aktif');

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->input('kelas_id'));
        }

        if ($request->filled('darjah')) {
            $query->where('darjah', $request->input('darjah'));
        }

        $senaraiPelajar = $query->orderBy('nama')->get();

        // Semua bulan yang belum dibayar — tunggakan tahun lepas dan sepanjang
        // tahun semasa (termasuk yuran bulan hadapan yang belum tiba).
        $mengikutPelajar = $senaraiPelajar->map(function ($pelajar) {
            $bulanBelumDibayar = $this->bakiService->bulanBelumBayar($pelajar);

            // Senarai nama yuran yang tertunggak (per item, bukan sekadar
            // bulan) — supaya laporan ini terus tunjuk YURAN mana yang
            // belum dibayar, bukan setakat bilangan bulan sahaja.
            $senaraiYuran = $bulanBelumDibayar
                ->flatMap(fn ($b) => collect($b['butiran'])->filter(fn ($i) => $i['baki'] > 0))
                ->values();

            return [
                'pelajar' => $pelajar,
                'bilangan_bulan' => $bulanBelumDibayar->count(),
                'jumlah_baki' => $bulanBelumDibayar->sum('baki'),
                'bulan' => $bulanBelumDibayar,
                'senarai_yuran' => $senaraiYuran,
            ];
        })->filter(fn ($d) => $d['jumlah_baki'] > 0)->values();

        [$urut, $arah] = $this->sahkanUrutan($request, ['nama', 'kelas', 'bilangan_bulan', 'jumlah_baki'], 'jumlah_baki', 'desc');

        $menurun = $arah === 'desc';
        $mengikutPelajar = match ($urut) {
            'nama' => $mengikutPelajar->sortBy(fn ($d) => $d['pelajar']->nama, SORT_REGULAR, $menurun),
            'kelas' => $mengikutPelajar->sortBy(fn ($d) => $d['pelajar']->kelas->nama_kelas ?? '', SORT_REGULAR, $menurun),
            default => $mengikutPelajar->sortBy($urut, SORT_REGULAR, $menurun),
        };

        return [$mengikutPelajar->values(), $urut, $arah];
    }
}
