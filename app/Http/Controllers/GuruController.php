<?php

namespace App\Http\Controllers;

use App\Exports\GuruTemplateExport;
use App\Imports\GuruImport;
use App\Jobs\HantarEmelAkaunPukal;
use App\Models\Guru;
use App\Models\GuruKelas;
use App\Models\Kelas;
use App\Services\StatusEmelAkaun;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        [$urut, $arah] = $this->sahkanUrutan($request, ['nama', 'no_pekerja', 'no_telefon', 'status'], 'nama');

        $guru = Guru::with(['user', 'guruKelas.kelas'])->orderBy($urut, $arah)->paginate(10)->withQueryString();

        $bilanganEmelMenunggu = HantarEmelAkaunPukal::bilanganMenunggu('guru');
        $statusEmel = StatusEmelAkaun::untuk($guru->getCollection()->load('user'), 'guru');

        return view('guru.index', compact('bilanganEmelMenunggu', 'statusEmel', 'guru', 'urut', 'arah'));
    }

    public function create()
    {
        $senaraiKelas = Kelas::orderBy('nama_kelas')->get();
        $senaraiDarjah = PelajarController::SENARAI_DARJAH;

        return view('guru.create', compact('senaraiKelas', 'senaraiDarjah'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_pekerja' => ['nullable', 'string', 'max:50', 'unique:guru,no_pekerja'],
            'no_kp' => ['nullable', 'string', 'max:20'],
            'no_telefon' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'kelas_id' => ['nullable', 'array'],
            'kelas_id.*' => ['nullable', 'exists:kelas,id'],
            'darjah' => ['nullable', 'array'],
            'darjah.*' => ['nullable', 'in:'.implode(',', PelajarController::SENARAI_DARJAH)],
            'status' => ['required', 'in:Aktif,Tidak Aktif'],
        ]);

        $email = $validated['email'] ?? null;
        $senaraiKelasGuru = $this->senaraiKelasGuruDariInput($validated);
        unset($validated['email'], $validated['kelas_id'], $validated['darjah']);

        $guru = Guru::create($validated);
        $this->simpanKelasGuru($guru, $senaraiKelasGuru);

        if (! $email) {
            return redirect()->route('guru.index')->with('status', 'Guru berjaya ditambah.');
        }

        $guru->ciptaAtauResetAkaun($email);

        return redirect()->route('guru.index')->with('status', "Guru berjaya ditambah. Emel log masuk telah dihantar ke {$email}.");
    }

    public function edit(Guru $guru)
    {
        $guru->load('guruKelas');
        $senaraiKelas = Kelas::orderBy('nama_kelas')->get();
        $senaraiDarjah = PelajarController::SENARAI_DARJAH;

        return view('guru.edit', compact('guru', 'senaraiKelas', 'senaraiDarjah'));
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_pekerja' => ['nullable', 'string', 'max:50', 'unique:guru,no_pekerja,'.$guru->id],
            'no_kp' => ['nullable', 'string', 'max:20'],
            'no_telefon' => ['nullable', 'string', 'max:20'],
            'kelas_id' => ['nullable', 'array'],
            'kelas_id.*' => ['nullable', 'exists:kelas,id'],
            'darjah' => ['nullable', 'array'],
            'darjah.*' => ['nullable', 'in:'.implode(',', PelajarController::SENARAI_DARJAH)],
            'status' => ['required', 'in:Aktif,Tidak Aktif'],
        ]);

        $senaraiKelasGuru = $this->senaraiKelasGuruDariInput($validated);
        unset($validated['kelas_id'], $validated['darjah']);

        $guru->update($validated);
        $this->simpanKelasGuru($guru, $senaraiKelasGuru);

        return redirect()->route('guru.index')->with('status', 'Maklumat guru berjaya dikemaskini.');
    }

    public function destroy(Guru $guru)
    {
        $guru->delete();

        return redirect()->route('guru.index')->with('status', 'Guru berjaya dipadam.');
    }

    public function import()
    {
        return view('guru.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'fail' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new GuruImport;
        $import->import($request->file('fail'));

        $ringkasan = [
            'berjaya' => $import->bilangBerjaya,
            'emelMenunggu' => $import->bilangEmelMenunggu,
            'dilangkau' => $import->dilangkau,
            'gagal' => $import->failures()
                ->map(fn ($failure) => "Baris {$failure->row()}: ".implode(', ', $failure->errors()))
                ->all(),
        ];

        return redirect()->route('guru.import')
            ->with('importRingkasan', $ringkasan);
    }

    /**
     * Cipta akaun dan hantar emel log masuk. Tanpa ids: semua guru yang ada emel
     * tetapi belum ada akaun. Dengan ids (dipilih atau satu baris): hanya mereka —
     * yang sudah ada akaun menerima kata laluan baharu. Dijalankan di latar
     * belakang; emel dihantar berperingkat mengikut EMEL_HAD_HARIAN.
     */
    public function hantarEmelAkaun(Request $request)
    {
        $request->validate(['ids' => ['nullable', 'array'], 'ids.*' => ['integer']]);
        $id = $request->filled('ids') ? array_map('intval', $request->input('ids')) : null;

        $bilangan = $id === null
            ? HantarEmelAkaunPukal::bilanganMenunggu('guru')
            : HantarEmelAkaunPukal::sasaran('guru', $id)->count();

        if ($bilangan === 0) {
            return redirect()->back()->with('status', $id === null
                ? 'Tiada guru yang menunggu emel akaun.'
                : 'Tiada guru dipilih yang mempunyai emel.');
        }

        HantarEmelAkaunPukal::dispatch('guru', $id);
        $hari = (int) ceil($bilangan / max(1, config('kafa.emel_had_harian')));

        return redirect()->back()->with('status', $hari > 1
            ? "Emel log masuk sedang disediakan untuk {$bilangan} guru, dihantar berperingkat — dijangka siap dalam {$hari} hari."
            : "Emel log masuk sedang dihantar kepada {$bilangan} guru. Status dikemas kini sebaik emel keluar.");
    }

    public function importTemplate()
    {
        return Excel::download(new GuruTemplateExport, 'templat-import-guru.xlsx');
    }

    /**
     * Susun pasangan (kelas_id, darjah) daripada dua array selari borang
     * (satu baris borang = satu indeks dalam kedua-dua array). Baris tanpa
     * kelas_id dilangkau (baris kosong yang tidak diisi Admin).
     *
     * @return array<int, array{kelas_id: int, darjah: ?string}>
     */
    protected function senaraiKelasGuruDariInput(array $validated): array
    {
        $senaraiKelasId = $validated['kelas_id'] ?? [];
        $senaraiDarjah = $validated['darjah'] ?? [];
        $pasangan = [];

        foreach ($senaraiKelasId as $indeks => $kelasId) {
            if (! $kelasId) {
                continue;
            }

            $pasangan[] = [
                'kelas_id' => (int) $kelasId,
                'darjah' => $senaraiDarjah[$indeks] ?? null,
            ];
        }

        return $pasangan;
    }

    /**
     * Gantikan sepenuhnya penugasan Kelas+Tahun guru ini dengan senarai baharu.
     */
    protected function simpanKelasGuru(Guru $guru, array $senaraiKelasGuru): void
    {
        $guru->guruKelas()->delete();

        foreach ($senaraiKelasGuru as $pasangan) {
            GuruKelas::create([
                'guru_id' => $guru->id,
                'kelas_id' => $pasangan['kelas_id'],
                'darjah' => $pasangan['darjah'],
            ]);
        }
    }
}
