<?php

namespace App\Http\Controllers;

use App\Models\KategoriYuran;
use Illuminate\Http\Request;

class KategoriYuranController extends Controller
{
    public function index(Request $request)
    {
        $query = KategoriYuran::withCount('strukturYuran');

        if ($request->filled('cari')) {
            $query->where('nama', 'like', '%'.$request->string('cari').'%');
        }

        [$urut, $arah] = $this->sahkanUrutan($request, ['nama', 'keterangan', 'status'], 'nama');

        $kategoriYuran = $query->orderBy($urut, $arah)->paginate(10)->withQueryString();

        return view('kategori-yuran.index', compact('kategoriYuran', 'urut', 'arah'));
    }

    public function create()
    {
        return view('kategori-yuran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'status' => ['required', 'in:Aktif,Tidak Aktif'],
        ]);

        $validated['bulanan'] = $request->boolean('bulanan');

        KategoriYuran::create($validated);

        return redirect()->route('kategori-yuran.index')->with('status', 'Kategori yuran berjaya ditambah.');
    }

    public function edit(KategoriYuran $kategoriYuran)
    {
        return view('kategori-yuran.edit', compact('kategoriYuran'));
    }

    public function update(Request $request, KategoriYuran $kategoriYuran)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'status' => ['required', 'in:Aktif,Tidak Aktif'],
        ]);

        $validated['bulanan'] = $request->boolean('bulanan');

        $kategoriYuran->update($validated);

        return redirect()->route('kategori-yuran.index')->with('status', 'Kategori yuran berjaya dikemaskini.');
    }

    public function destroy(KategoriYuran $kategoriYuran)
    {
        if ($kategoriYuran->strukturYuran()->exists()) {
            return back()->withErrors(['error' => 'Tidak boleh padam — kategori ini masih digunakan oleh struktur yuran.']);
        }

        $kategoriYuran->delete();

        return redirect()->route('kategori-yuran.index')->with('status', 'Kategori yuran berjaya dipadam.');
    }
}