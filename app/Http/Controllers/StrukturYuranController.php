<?php

namespace App\Http\Controllers;

use App\Models\KategoriYuran;
use App\Models\Kelas;
use App\Models\StrukturYuran;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StrukturYuranController extends Controller
{
    public const SENARAI_DARJAH = PelajarController::SENARAI_DARJAH;

    public function index(Request $request)
    {
        $query = StrukturYuran::with(['kategoriYuran', 'kelas']);

        if ($request->filled('cari')) {
            $query->where('nama', 'like', '%'.$request->string('cari').'%');
        }

        [$urut, $arah] = $this->sahkanUrutan($request, ['nama', 'amaun', 'darjah', 'status'], 'nama');

        $strukturYuran = $query->orderBy($urut, $arah)->paginate(10)->withQueryString();

        return view('struktur-yuran.index', compact('strukturYuran', 'urut', 'arah'));
    }

    public function create()
    {
        $senaraiKategori = KategoriYuran::where('status', 'Aktif')->orderBy('nama')->get();
        $senaraiKelas = Kelas::orderBy('nama_kelas')->get();
        $senaraiDarjah = self::SENARAI_DARJAH;

        return view('struktur-yuran.create', compact('senaraiKategori', 'senaraiKelas', 'senaraiDarjah'));
    }

    /**
     * Sahkan + normalkan bulan_berkenaan & nama berdasarkan medan "Bulanan"
     * pada Kategori Yuran yang dipilih — hanya kategori yang ditandakan
     * Bulanan mewajibkan SATU bulan DAN memaksa Nama Struktur Yuran mengikut
     * bulan yang dipilih (cth: "Januari") — bukan ditaip bebas, walaupun
     * borang dipintas (devtools dll).
     *
     * @return array{bulan_berkenaan: ?array<int, int>, nama: ?string}
     */
    protected function bulanDanNamaDaripadaInput(Request $request, int $kategoriYuranId): array
    {
        $kategori = KategoriYuran::find($kategoriYuranId);

        if (! $kategori?->bulanan) {
            return ['bulan_berkenaan' => null, 'nama' => null];
        }

        $bulan = $request->input('bulan_berkenaan');

        if (empty($bulan)) {
            throw ValidationException::withMessages([
                'bulan_berkenaan' => "Sila pilih satu bulan — kategori \"{$kategori->nama}\" ditandakan Bulanan dan memerlukan bulan.",
            ]);
        }

        $bulanBerkenaan = [(int) $bulan];

        return [
            'bulan_berkenaan' => $bulanBerkenaan,
            'nama' => StrukturYuran::namaDaripadaBulan($bulanBerkenaan),
        ];
    }

    /**
     * Pastikan tiada Struktur Yuran lain dengan Kategori + Nama + skop
     * (Kelas/Tahun) yang SAMA — struktur bernama sama tapi skop BERBEZA
     * (cth: "Januari" untuk Tahun 1 vs "Januari" untuk Tahun 5, kadar
     * berlainan) kekal dibenarkan, hanya pertindihan TEPAT disekat.
     */
    protected function pastikanTiadaPertindihan(array $validated, ?int $strukturIdDiabaikan = null): void
    {
        $query = StrukturYuran::where('kategori_yuran_id', $validated['kategori_yuran_id'])
            ->where('nama', $validated['nama']);

        $validated['kelas_id'] ?? null
            ? $query->where('kelas_id', $validated['kelas_id'])
            : $query->whereNull('kelas_id');

        $validated['darjah'] ?? null
            ? $query->where('darjah', $validated['darjah'])
            : $query->whereNull('darjah');

        if ($strukturIdDiabaikan) {
            $query->where('id', '!=', $strukturIdDiabaikan);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'nama' => "Struktur yuran \"{$validated['nama']}\" untuk skop (Kelas/Tahun) yang sama sudah wujud.",
            ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_yuran_id' => ['required', 'exists:kategori_yuran,id'],
            'nama' => ['required', 'string', 'max:255'],
            'amaun' => ['required', 'numeric', 'min:0'],
            'bulan_berkenaan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'darjah' => ['nullable', 'in:'.implode(',', self::SENARAI_DARJAH)],
            'status' => ['required', 'in:Aktif,Tidak Aktif'],
        ]);

        $bulanDanNama = $this->bulanDanNamaDaripadaInput($request, $validated['kategori_yuran_id']);
        $validated['bulan_berkenaan'] = $bulanDanNama['bulan_berkenaan'];

        if ($bulanDanNama['nama'] !== null) {
            $validated['nama'] = $bulanDanNama['nama'];
        }

        $this->pastikanTiadaPertindihan($validated);

        StrukturYuran::create($validated);

        return redirect()->route('struktur-yuran.index')->with('status', 'Struktur yuran berjaya ditambah.');
    }

    public function edit(StrukturYuran $strukturYuran)
    {
        $senaraiKategori = KategoriYuran::where('status', 'Aktif')->orderBy('nama')->get();
        $senaraiKelas = Kelas::orderBy('nama_kelas')->get();
        $senaraiDarjah = self::SENARAI_DARJAH;

        return view('struktur-yuran.edit', compact('strukturYuran', 'senaraiKategori', 'senaraiKelas', 'senaraiDarjah'));
    }

    public function update(Request $request, StrukturYuran $strukturYuran)
    {
        $validated = $request->validate([
            'kategori_yuran_id' => ['required', 'exists:kategori_yuran,id'],
            'nama' => ['required', 'string', 'max:255'],
            'amaun' => ['required', 'numeric', 'min:0'],
            'bulan_berkenaan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'darjah' => ['nullable', 'in:'.implode(',', self::SENARAI_DARJAH)],
            'status' => ['required', 'in:Aktif,Tidak Aktif'],
        ]);

        $bulanDanNama = $this->bulanDanNamaDaripadaInput($request, $validated['kategori_yuran_id']);
        $validated['bulan_berkenaan'] = $bulanDanNama['bulan_berkenaan'];

        if ($bulanDanNama['nama'] !== null) {
            $validated['nama'] = $bulanDanNama['nama'];
        }

        $this->pastikanTiadaPertindihan($validated, $strukturYuran->id);

        $strukturYuran->update($validated);

        return redirect()->route('struktur-yuran.index')->with('status', 'Struktur yuran berjaya dikemaskini.');
    }

    public function destroy(StrukturYuran $strukturYuran)
    {
        if ($strukturYuran->tetapanPelajar()->exists()) {
            return back()->withErrors(['error' => 'Tidak boleh padam — struktur yuran ini masih ditetapkan kepada pelajar.']);
        }

        $strukturYuran->delete();

        return redirect()->route('struktur-yuran.index')->with('status', 'Struktur yuran berjaya dipadam.');
    }
}
