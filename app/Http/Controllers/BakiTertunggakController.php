<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Pelajar;
use App\Services\BakiBulananService;
use Illuminate\Http\Request;

class BakiTertunggakController extends Controller
{
    public function __construct(protected BakiBulananService $bakiService)
    {
    }

    public function index(Request $request)
    {
        $query = Pelajar::with('kelas')->where('status', 'Aktif');

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->input('kelas_id'));
        }

        $senaraiPelajar = $query->orderBy('nama')->get();

        // Semua bulan yang belum dibayar sepanjang tahun (bukan setakat bulan
        // semasa) — supaya "Baki Belum Dibayar" turut merangkumi yuran bulan
        // hadapan yang belum tiba, bukan sekadar yang telah lepas tempoh.
        $mengikutPelajar = $senaraiPelajar->map(function ($pelajar) {
            $bulanBelumDibayar = $this->bakiService->senaraiBulan($pelajar, now()->year)
                ->filter(fn ($b) => $b['baki'] > 0)
                ->values();

            return [
                'pelajar' => $pelajar,
                'bilangan_bulan' => $bulanBelumDibayar->count(),
                'jumlah_baki' => $bulanBelumDibayar->sum('baki'),
                'bulan' => $bulanBelumDibayar,
            ];
        })->filter(fn ($d) => $d['jumlah_baki'] > 0)->values();

        [$urut, $arah] = $this->sahkanUrutan($request, ['nama', 'kelas', 'bilangan_bulan', 'jumlah_baki'], 'jumlah_baki', 'desc');

        $menurun = $arah === 'desc';
        $mengikutPelajar = match ($urut) {
            'nama' => $mengikutPelajar->sortBy(fn ($d) => $d['pelajar']->nama, SORT_REGULAR, $menurun),
            'kelas' => $mengikutPelajar->sortBy(fn ($d) => $d['pelajar']->kelas->nama_kelas ?? '', SORT_REGULAR, $menurun),
            default => $mengikutPelajar->sortBy($urut, SORT_REGULAR, $menurun),
        };
        $mengikutPelajar = $mengikutPelajar->values();

        $jumlahKeseluruhanBelumDibayar = $mengikutPelajar->sum('jumlah_baki');
        $senaraiKelas = Kelas::orderBy('nama_kelas')->get();

        return view('baki-tertunggak.index', compact(
            'mengikutPelajar',
            'jumlahKeseluruhanBelumDibayar',
            'senaraiKelas',
            'urut',
            'arah'
        ));
    }
}
