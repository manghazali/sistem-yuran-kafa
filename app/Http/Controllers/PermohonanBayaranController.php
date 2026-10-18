<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Pelajar;
use App\Models\PermohonanBayaran;
use App\Services\BakiBulananService;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;

class PermohonanBayaranController extends Controller
{
    public function __construct(protected NotifikasiService $notifikasi, protected BakiBulananService $bakiService)
    {
    }

    protected function guruSemasa(Request $request): ?Guru
    {
        return Guru::where('user_id', $request->user()->id)->first();
    }

    public function index(Request $request)
    {
        $query = PermohonanBayaran::with(['guru', 'pelajar.kelas', 'bayaran']);

        if ($request->user()->hasRole('Guru')) {
            $guru = $this->guruSemasa($request);
            $query->where('guru_id', $guru?->id ?? 0);
        } elseif ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        [$urut, $arah] = $this->sahkanUrutan($request, ['amaun', 'tarikh_terima', 'status', 'created_at'], 'created_at', 'desc');

        $permohonan = $query->orderBy($urut, $arah)->paginate(10)->withQueryString();

        return view('permohonan-bayaran.index', compact('permohonan', 'urut', 'arah'));
    }

    public function create(Request $request)
    {
        $guru = $this->guruSemasa($request);
        $guru?->load('guruKelas');

        abort_if(! $guru || $guru->guruKelas->isEmpty(), 403, 'Anda belum ditetapkan kepada mana-mana Kelas. Sila hubungi pihak pentadbiran.');

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

        $senaraiPelajar = $query->orderBy('nama')->get();

        // Senarai yuran tertunggak BAGI SETIAP pelajar dalam skop guru ini,
        // supaya guru boleh terus pilih struktur yuran yang tepat semasa
        // melapor — Admin/Akauntan tidak perlu agak/pilih semula bila
        // mengesahkan nanti (borang Rekod Bayaran pra-isi automatik).
        $itemTertunggakMengikutPelajar = $senaraiPelajar->mapWithKeys(function ($pelajar) {
            $item = $this->bakiService->senaraiBulan($pelajar, now()->year)
                ->flatMap(fn ($bulanData) => collect($bulanData['butiran'])->map(fn ($butiran) => [
                    'struktur_yuran_id' => $butiran['struktur_yuran_id'],
                    'bulan' => $butiran['berulang'] ? $bulanData['bulan'] : null,
                    'nama' => $butiran['nama'],
                    'baki' => $butiran['baki'],
                ]))
                ->filter(fn ($i) => $i['baki'] > 0)
                ->values();

            return [$pelajar->id => $item];
        });

        return view('permohonan-bayaran.create', compact('senaraiPelajar', 'itemTertunggakMengikutPelajar'));
    }

    public function store(Request $request)
    {
        $guru = $this->guruSemasa($request);

        abort_if(! $guru, 403);

        $validated = $request->validate([
            'pelajar_id' => ['required', 'exists:pelajar,id'],
            'struktur_yuran_id' => ['required', 'exists:struktur_yuran,id'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'amaun' => ['required', 'numeric', 'min:0.01'],
            'tarikh_terima' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $permohonan = PermohonanBayaran::create([
            'guru_id' => $guru->id,
            'pelajar_id' => $validated['pelajar_id'],
            'struktur_yuran_id' => $validated['struktur_yuran_id'] ?? null,
            'bulan' => $validated['bulan'] ?? null,
            'amaun' => $validated['amaun'],
            'tarikh_terima' => $validated['tarikh_terima'],
            'catatan' => $validated['catatan'] ?? null,
            'status' => 'Menunggu',
        ]);

        $pelajar = Pelajar::find($validated['pelajar_id']);

        $this->notifikasi->hantarKePeranan(
            ['Admin', 'Akauntan'],
            'Permohonan Bayaran Baharu',
            "Guru {$guru->nama} melaporkan bayaran tunai RM".number_format($permohonan->amaun, 2)." daripada {$pelajar->nama}. Sila semak dan sahkan.",
            route('permohonan-bayaran.index')
        );

        return redirect()->route('permohonan-bayaran.index')->with('status', 'Permohonan bayaran berjaya dihantar untuk disahkan.');
    }

    public function tolak(Request $request, PermohonanBayaran $permohonanBayaran)
    {
        abort_if($permohonanBayaran->status !== 'Menunggu', 403, 'Permohonan ini sudah diproses.');

        $validated = $request->validate([
            'sebab_tolak' => ['nullable', 'string', 'max:500'],
        ]);

        $permohonanBayaran->update([
            'status' => 'Ditolak',
            'sebab_tolak' => $validated['sebab_tolak'] ?? null,
            'disahkan_oleh' => $request->user()->id,
        ]);

        $permohonanBayaran->load('guru.user', 'pelajar');

        if ($permohonanBayaran->guru->user_id) {
            $this->notifikasi->hantar(
                $permohonanBayaran->guru->user_id,
                'Permohonan Bayaran Ditolak',
                "Permohonan bayaran RM".number_format($permohonanBayaran->amaun, 2)." bagi {$permohonanBayaran->pelajar->nama} telah ditolak.".
                    ($permohonanBayaran->sebab_tolak ? ' Sebab: '.$permohonanBayaran->sebab_tolak : ''),
                route('permohonan-bayaran.index')
            );
        }

        return redirect()->route('permohonan-bayaran.index')->with('status', 'Permohonan berjaya ditolak.');
    }
}
