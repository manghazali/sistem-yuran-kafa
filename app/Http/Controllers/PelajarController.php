<?php

namespace App\Http\Controllers;

use App\Exports\PelajarTemplateExport;
use App\Imports\PelajarImport;
use App\Models\Kelas;
use App\Models\Pelajar;
use App\Models\Penjaga;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PelajarController extends Controller
{
    public const SENARAI_DARJAH = ['Tahun 1', 'Tahun 2', 'Tahun 3', 'Tahun 4', 'Tahun 5', 'Tahun 6'];

    public function index(Request $request)
    {
        $query = Pelajar::with(['kelas', 'penjaga']);

        if ($request->filled('cari')) {
            $cari = $request->string('cari');
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                    ->orWhere('no_daftar', 'like', "%{$cari}%");
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->input('kelas_id'));
        }

        if ($request->filled('darjah')) {
            $query->where('darjah', $request->input('darjah'));
        }

        [$urut, $arah] = $this->sahkanUrutan($request, ['no_daftar', 'nama', 'darjah', 'status'], 'nama');

        $pelajar = $query->orderBy($urut, $arah)->paginate(10)->withQueryString();
        $senaraiKelas = Kelas::orderBy('nama_kelas')->get();
        $senaraiDarjah = self::SENARAI_DARJAH;

        return view('pelajar.index', compact('pelajar', 'senaraiKelas', 'senaraiDarjah', 'urut', 'arah'));
    }

    public function create()
    {
        $senaraiKelas = Kelas::where('status', 'Aktif')->orderBy('nama_kelas')->get();
        $senaraiPenjaga = Penjaga::orderBy('nama')->get();
        $senaraiDarjah = self::SENARAI_DARJAH;

        return view('pelajar.create', compact('senaraiKelas', 'senaraiPenjaga', 'senaraiDarjah'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_kp' => ['nullable', 'string', 'max:20'],
            'tarikh_lahir' => ['nullable', 'date'],
            'jantina' => ['required', 'in:Lelaki,Perempuan'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'darjah' => ['nullable', 'in:'.implode(',', self::SENARAI_DARJAH)],
            'penjaga_id' => ['nullable', 'exists:penjaga,id'],
            'status' => ['required', 'in:Aktif,Tidak Aktif,Tamat'],
        ]);

        // No. Pendaftaran dijana automatik (Tetapan > Generate No. Pendaftaran)
        // — tidak lagi diisi manual oleh Admin semasa tambah pelajar baharu.
        $validated['no_daftar'] = Pelajar::janaNoDaftar();

        Pelajar::create($validated);

        return redirect()->route('pelajar.index')->with('status', 'Pelajar berjaya ditambah.');
    }

    public function edit(Pelajar $pelajar)
    {
        $senaraiKelas = Kelas::orderBy('nama_kelas')->get();
        $senaraiPenjaga = Penjaga::orderBy('nama')->get();
        $senaraiDarjah = self::SENARAI_DARJAH;

        return view('pelajar.edit', compact('pelajar', 'senaraiKelas', 'senaraiPenjaga', 'senaraiDarjah'));
    }

    public function update(Request $request, Pelajar $pelajar)
    {
        $validated = $request->validate([
            'no_daftar' => ['required', 'string', 'max:50', 'unique:pelajar,no_daftar,'.$pelajar->id],
            'nama' => ['required', 'string', 'max:255'],
            'no_kp' => ['nullable', 'string', 'max:20'],
            'tarikh_lahir' => ['nullable', 'date'],
            'jantina' => ['required', 'in:Lelaki,Perempuan'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'darjah' => ['nullable', 'in:'.implode(',', self::SENARAI_DARJAH)],
            'penjaga_id' => ['nullable', 'exists:penjaga,id'],
            'status' => ['required', 'in:Aktif,Tidak Aktif,Tamat'],
        ]);

        $pelajar->update($validated);

        return redirect()->route('pelajar.index')->with('status', 'Maklumat pelajar berjaya dikemaskini.');
    }

    public function destroy(Pelajar $pelajar)
    {
        if ($pelajar->bayaran()->exists()) {
            return back()->withErrors(['error' => 'Tidak boleh padam — pelajar ini mempunyai rekod bayaran.']);
        }

        if ($pelajar->tetapanYuranPelajar()->exists()) {
            return back()->withErrors(['error' => 'Tidak boleh padam — pelajar ini masih mempunyai tetapan yuran.']);
        }

        $pelajar->delete();

        return redirect()->route('pelajar.index')->with('status', 'Pelajar berjaya dipadam.');
    }

    public function import()
    {
        return view('pelajar.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'fail' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new PelajarImport;
        $import->import($request->file('fail'));

        $ringkasan = [
            'berjaya' => $import->bilangBerjaya,
            'dilangkau' => $import->dilangkau,
            'gagal' => $import->failures()
                ->map(fn ($failure) => "Baris {$failure->row()}: ".implode(', ', $failure->errors()))
                ->all(),
        ];

        return redirect()->route('pelajar.import')
            ->with('importRingkasan', $ringkasan);
    }

    public function importTemplate()
    {
        return Excel::download(new PelajarTemplateExport, 'templat-import-pelajar.xlsx');
    }
}
