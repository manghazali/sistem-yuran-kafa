<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $query = Kelas::withCount('pelajar');

        if ($request->filled('cari')) {
            $query->where('nama_kelas', 'like', '%'.$request->string('cari').'%');
        }

        [$urut, $arah] = $this->sahkanUrutan($request, ['nama_kelas', 'sesi', 'status'], 'nama_kelas');

        $kelas = $query->orderBy($urut, $arah)->paginate(10)->withQueryString();

        return view('kelas.index', compact('kelas', 'urut', 'arah'));
    }

    public function create()
    {
        return view('kelas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:255'],
            'sesi' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:Aktif,Tidak Aktif'],
        ]);

        Kelas::create($validated);

        return redirect()->route('kelas.index')->with('status', 'Kelas berjaya ditambah.');
    }

    public function edit(Kelas $kelas)
    {
        return view('kelas.edit', compact('kelas'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:255'],
            'sesi' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:Aktif,Tidak Aktif'],
        ]);

        $kelas->update($validated);

        return redirect()->route('kelas.index')->with('status', 'Kelas berjaya dikemaskini.');
    }

    public function destroy(Kelas $kelas)
    {
        if ($kelas->pelajar()->exists()) {
            return back()->withErrors(['error' => 'Tidak boleh padam — kelas ini masih mempunyai pelajar berdaftar.']);
        }

        $kelas->delete();

        return redirect()->route('kelas.index')->with('status', 'Kelas berjaya dipadam.');
    }
}
