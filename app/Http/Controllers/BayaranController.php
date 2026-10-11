<?php

namespace App\Http\Controllers;

use App\Models\Bayaran;
use App\Models\Pelajar;
use App\Models\PermohonanBayaran;
use App\Services\BakiBulananService;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;

class BayaranController extends Controller
{
    public function __construct(protected BakiBulananService $bakiService, protected NotifikasiService $notifikasi)
    {
    }

    public function index(Request $request)
    {
        $query = Bayaran::with(['pelajar.kelas', 'pencatat']);

        if ($request->filled('pelajar_id')) {
            $query->where('pelajar_id', $request->input('pelajar_id'));
        }

        [$urut, $arah] = $this->sahkanUrutan($request, ['no_bayaran', 'keterangan', 'amaun', 'kaedah_bayaran', 'tarikh_bayaran'], 'tarikh_bayaran', 'desc');

        $bayaran = $query->orderBy($urut, $arah)->paginate(10)->withQueryString();
        $senaraiPelajar = Pelajar::orderBy('nama')->get();
        $jumlahHariIni = Bayaran::whereDate('tarikh_bayaran', today())->sum('amaun');

        return view('bayaran.index', compact('bayaran', 'senaraiPelajar', 'jumlahHariIni', 'urut', 'arah'));
    }

    public function create(Request $request)
    {
        $senaraiPelajar = Pelajar::where('status', 'Aktif')->orderBy('nama')->get();
        $pelajarDipilih = null;
        $senaraiBulan = collect();
        $permohonan = null;

        if ($request->filled('permohonan_id')) {
            $permohonan = PermohonanBayaran::with('guru')
                ->where('status', 'Menunggu')
                ->find($request->input('permohonan_id'));
        }

        $pelajarIdDipilih = $permohonan?->pelajar_id ?? $request->input('pelajar_id');

        if ($pelajarIdDipilih) {
            $pelajarDipilih = Pelajar::find($pelajarIdDipilih);

            if ($pelajarDipilih) {
                $senaraiBulan = $this->bakiService->senaraiBulan($pelajarDipilih, now()->year)
                    ->filter(fn ($b) => $b['baki'] > 0)
                    ->values();
            }
        }

        return view('bayaran.create', compact('senaraiPelajar', 'pelajarDipilih', 'senaraiBulan', 'permohonan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pelajar_id' => ['required', 'exists:pelajar,id'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'amaun' => ['required', 'numeric', 'min:0.01'],
            'kaedah_bayaran' => ['required', 'in:Tunai,Pindahan Bank,Cek,Online Banking,Lain-lain'],
            'tarikh_bayaran' => ['required', 'date'],
            'no_rujukan' => ['nullable', 'string', 'max:100'],
            'catatan' => ['nullable', 'string'],
            'permohonan_id' => ['nullable', 'exists:permohonan_bayaran,id'],
        ]);

        $keterangan = $validated['keterangan']
            ?? (($validated['bulan'] ?? null) && ($validated['tahun'] ?? null)
                ? 'Yuran Bulanan - '.\Illuminate\Support\Carbon::create($validated['tahun'], $validated['bulan'], 1)->translatedFormat('F Y')
                : 'Bayaran Yuran');

        $bayaran = Bayaran::create([
            'no_bayaran' => Bayaran::janaNoBayaran(),
            'no_resit' => Bayaran::janaNoResit(),
            'pelajar_id' => $validated['pelajar_id'],
            'bulan' => $validated['bulan'] ?? null,
            'tahun' => $validated['tahun'] ?? null,
            'keterangan' => $keterangan,
            'amaun' => $validated['amaun'],
            'kaedah_bayaran' => $validated['kaedah_bayaran'],
            'tarikh_bayaran' => $validated['tarikh_bayaran'],
            'no_rujukan' => $validated['no_rujukan'] ?? null,
            'dicatat_oleh' => auth()->id(),
            'catatan' => $validated['catatan'] ?? null,
        ]);

        if (! empty($validated['permohonan_id'])) {
            $permohonan = PermohonanBayaran::where('status', 'Menunggu')->find($validated['permohonan_id']);

            if ($permohonan) {
                $permohonan->update([
                    'status' => 'Disahkan',
                    'bayaran_id' => $bayaran->id,
                    'disahkan_oleh' => auth()->id(),
                ]);

                $permohonan->load('guru');

                if ($permohonan->guru->user_id) {
                    $this->notifikasi->hantar(
                        $permohonan->guru->user_id,
                        'Permohonan Bayaran Disahkan',
                        "Permohonan bayaran RM".number_format($permohonan->amaun, 2)." bagi {$bayaran->pelajar->nama} telah disahkan dan direkodkan rasmi.",
                        route('permohonan-bayaran.index')
                    );
                }
            }
        }

        return redirect()->route('resit.show', $bayaran)->with('status', 'Bayaran berjaya direkodkan.');
    }

    public function show(Bayaran $bayaran)
    {
        $bayaran->load(['pelajar.kelas', 'pencatat']);

        return view('bayaran.show', compact('bayaran'));
    }

    public function destroy(Bayaran $bayaran)
    {
        $bayaran->delete();

        return redirect()->route('bayaran.index')->with('status', 'Bayaran berjaya dibatalkan.');
    }
}
