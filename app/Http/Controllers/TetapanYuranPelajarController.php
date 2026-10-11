<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Pelajar;
use App\Models\StrukturYuran;
use App\Models\TetapanYuranPelajar;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TetapanYuranPelajarController extends Controller
{
    public function __construct(protected NotifikasiService $notifikasi)
    {
    }

    public function index(Request $request)
    {
        $query = TetapanYuranPelajar::with(['pelajar', 'strukturYuran.kategoriYuran']);

        if ($request->filled('pelajar_id')) {
            $query->where('pelajar_id', $request->input('pelajar_id'));
        }

        if ($request->filled('struktur_yuran_id')) {
            $query->where('struktur_yuran_id', $request->input('struktur_yuran_id'));
        }

        [$urut, $arah] = $this->sahkanUrutan($request, ['status', 'created_at'], 'created_at', 'desc');

        $tetapan = $query->orderBy($urut, $arah)->paginate(10)->withQueryString();
        $senaraiPelajar = Pelajar::orderBy('nama')->get();
        $senaraiStruktur = StrukturYuran::orderBy('nama')->get();

        return view('tetapan-yuran-pelajar.index', compact('tetapan', 'senaraiPelajar', 'senaraiStruktur', 'urut', 'arah'));
    }

    public function create()
    {
        $senaraiPelajar = Pelajar::where('status', 'Aktif')->orderBy('nama')->get();
        $senaraiStruktur = StrukturYuran::with(['kategoriYuran', 'kelas'])->where('status', 'Aktif')->orderBy('nama')->get();

        return view('tetapan-yuran-pelajar.create', compact('senaraiPelajar', 'senaraiStruktur'));
    }

    /**
     * Pastikan skop Kelas/Tahun Struktur Yuran (jika ada) sepadan dengan
     * pelajar yang hendak di-assign — elak pelajar Tahun 1 tersilap
     * ditetapkan struktur "khusus Tahun 5" dll. Struktur tanpa skop
     * (kelas_id & darjah kosong) terpakai untuk semua pelajar.
     */
    protected function sahkanSkopStruktur(Pelajar $pelajar, StrukturYuran $struktur): void
    {
        $kelasOk = ! $struktur->kelas_id || $struktur->kelas_id === $pelajar->kelas_id;
        $darjahOk = ! $struktur->darjah || $struktur->darjah === $pelajar->darjah;

        if (! $kelasOk || ! $darjahOk) {
            throw ValidationException::withMessages([
                'struktur_yuran_id' => "Struktur yuran \"{$struktur->nama}\" khusus untuk ".
                    ($struktur->kelas->nama_kelas ?? $struktur->darjah).
                    " — tidak sepadan dengan kelas/tahun {$pelajar->nama}.",
            ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pelajar_id' => ['required', 'exists:pelajar,id'],
            'struktur_yuran_id' => [
                'required',
                'exists:struktur_yuran,id',
                'unique:tetapan_yuran_pelajar,struktur_yuran_id,NULL,id,pelajar_id,'.$request->input('pelajar_id'),
            ],
            'amaun_verride' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:Aktif,Tidak Aktif'],
        ], [
            'struktur_yuran_id.unique' => 'Pelajar ini sudah ditetapkan struktur yuran ini.',
        ]);

        $this->sahkanSkopStruktur(
            Pelajar::findOrFail($validated['pelajar_id']),
            StrukturYuran::findOrFail($validated['struktur_yuran_id'])
        );

        $tetapan = TetapanYuranPelajar::create($validated);
        $tetapan->load('pelajar', 'strukturYuran');

        $this->notifikasi->hantarKePenjaga(
            $tetapan->pelajar,
            'Yuran Baharu Ditetapkan',
            "Yuran \"{$tetapan->strukturYuran->nama}\" telah ditetapkan kepada {$tetapan->pelajar->nama}."
        );

        return redirect()->route('tetapan-yuran-pelajar.index')->with('status', 'Yuran berjaya ditetapkan kepada pelajar.');
    }

    public function edit(TetapanYuranPelajar $tetapanYuranPelajar)
    {
        $senaraiPelajar = Pelajar::orderBy('nama')->get();
        $senaraiStruktur = StrukturYuran::with('kategoriYuran')->orderBy('nama')->get();

        return view('tetapan-yuran-pelajar.edit', [
            'tetapan' => $tetapanYuranPelajar,
            'senaraiPelajar' => $senaraiPelajar,
            'senaraiStruktur' => $senaraiStruktur,
        ]);
    }

    public function update(Request $request, TetapanYuranPelajar $tetapanYuranPelajar)
    {
        $validated = $request->validate([
            'amaun_verride' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:Aktif,Tidak Aktif'],
        ]);

        $tetapanYuranPelajar->update($validated);
        $tetapanYuranPelajar->load('pelajar', 'strukturYuran');

        $this->notifikasi->hantarKePenjaga(
            $tetapanYuranPelajar->pelajar,
            'Tetapan Yuran Dikemaskini',
            "Tetapan yuran \"{$tetapanYuranPelajar->strukturYuran->nama}\" bagi {$tetapanYuranPelajar->pelajar->nama} telah dikemaskini."
        );

        return redirect()->route('tetapan-yuran-pelajar.index')->with('status', 'Tetapan yuran berjaya dikemaskini.');
    }

    public function destroy(TetapanYuranPelajar $tetapanYuranPelajar)
    {
        $tetapanYuranPelajar->load('pelajar', 'strukturYuran');
        $pelajar = $tetapanYuranPelajar->pelajar;
        $mesej = "Tetapan yuran \"{$tetapanYuranPelajar->strukturYuran->nama}\" bagi {$pelajar->nama} telah dibatalkan.";

        $tetapanYuranPelajar->delete();

        $this->notifikasi->hantarKePenjaga($pelajar, 'Tetapan Yuran Dibatalkan', $mesej);

        return redirect()->route('tetapan-yuran-pelajar.index')->with('status', 'Tetapan yuran berjaya dipadam.');
    }

    public function pukal()
    {
        $senaraiStruktur = StrukturYuran::with('kategoriYuran')->where('status', 'Aktif')->orderBy('nama')->get();
        $senaraiKelas = Kelas::orderBy('nama_kelas')->get();
        $senaraiDarjah = PelajarController::SENARAI_DARJAH;

        return view('tetapan-yuran-pelajar.pukal', compact('senaraiStruktur', 'senaraiKelas', 'senaraiDarjah'));
    }

    protected function peraturanPukal(): array
    {
        return [
            'struktur_yuran_id' => ['required', 'exists:struktur_yuran,id'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'darjah' => ['nullable', 'in:'.implode(',', PelajarController::SENARAI_DARJAH)],
            'amaun_verride' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:Aktif,Tidak Aktif'],
        ];
    }

    /**
     * Senarai pelajar yang akan terkena struktur yuran pukal ini — tapisan
     * Admin (kelas_id/darjah dalam borang) DISILANGKAN dengan skop struktur
     * yuran itu sendiri (jika ada), supaya struktur "khusus Tahun 5" tidak
     * sesekali tersasar kepada tahun lain walaupun Admin tersilap pilih
     * tapisan yang lebih luas.
     *
     * @return array{senaraiPelajar: \Illuminate\Support\Collection, pelajarSudahAda: \Illuminate\Support\Collection, strukturYuran: StrukturYuran}
     */
    protected function pelajarSepadanUntukPukal(array $validated): array
    {
        $strukturYuran = StrukturYuran::with('kelas')->findOrFail($validated['struktur_yuran_id']);

        $query = Pelajar::where('status', 'Aktif');

        $kelasId = $validated['kelas_id'] ?? $strukturYuran->kelas_id;
        $darjah = $validated['darjah'] ?? $strukturYuran->darjah;

        // Jika Admin pilih tapisan BERBEZA daripada skop struktur sendiri,
        // skop struktur tetap diutamakan (silang, bukan Admin menang).
        if ($strukturYuran->kelas_id) {
            $kelasId = $strukturYuran->kelas_id;
        }

        if ($strukturYuran->darjah) {
            $darjah = $strukturYuran->darjah;
        }

        if (! empty($kelasId)) {
            $query->where('kelas_id', $kelasId);
        }

        if (! empty($darjah)) {
            $query->where('darjah', $darjah);
        }

        $senaraiPelajar = $query->orderBy('nama')->get(['id', 'nama', 'kelas_id', 'darjah']);

        $pelajarSudahAda = TetapanYuranPelajar::where('struktur_yuran_id', $strukturYuran->id)
            ->whereIn('pelajar_id', $senaraiPelajar->pluck('id'))
            ->pluck('pelajar_id');

        return [
            'senaraiPelajar' => $senaraiPelajar,
            'pelajarSudahAda' => $pelajarSudahAda,
            'strukturYuran' => $strukturYuran,
        ];
    }

    public function pukalPratonton(Request $request)
    {
        $validated = $request->validate($this->peraturanPukal());

        $hasil = $this->pelajarSepadanUntukPukal($validated);

        $pelajarBaharu = $hasil['senaraiPelajar']->whereNotIn('id', $hasil['pelajarSudahAda']);
        $pelajarDilangkau = $hasil['senaraiPelajar']->whereIn('id', $hasil['pelajarSudahAda']);

        return view('tetapan-yuran-pelajar.pukal-pratonton', [
            'strukturYuran' => $hasil['strukturYuran'],
            'pelajarBaharu' => $pelajarBaharu,
            'pelajarDilangkau' => $pelajarDilangkau,
            'input' => $validated,
        ]);
    }

    public function pukalStore(Request $request)
    {
        $validated = $request->validate($this->peraturanPukal());

        $hasil = $this->pelajarSepadanUntukPukal($validated);
        $strukturYuran = $hasil['strukturYuran'];
        $pelajarSudahAda = $hasil['pelajarSudahAda'];

        $bilangDitetapkan = 0;

        foreach ($hasil['senaraiPelajar'] as $pelajar) {
            if ($pelajarSudahAda->contains($pelajar->id)) {
                continue;
            }

            TetapanYuranPelajar::create([
                'pelajar_id' => $pelajar->id,
                'struktur_yuran_id' => $strukturYuran->id,
                'amaun_verride' => $validated['amaun_verride'] ?? null,
                'status' => $validated['status'],
            ]);

            $this->notifikasi->hantarKePenjaga(
                $pelajar,
                'Yuran Baharu Ditetapkan',
                "Yuran \"{$strukturYuran->nama}\" telah ditetapkan kepada {$pelajar->nama}."
            );

            $bilangDitetapkan++;
        }

        $bilangDilangkau = $pelajarSudahAda->count();

        return redirect()->route('tetapan-yuran-pelajar.index')
            ->with('status', "{$bilangDitetapkan} pelajar berjaya ditetapkan. {$bilangDilangkau} pelajar dilangkau (sudah ada struktur yuran ini).");
    }
}
