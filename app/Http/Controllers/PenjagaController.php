<?php

namespace App\Http\Controllers;

use App\Exports\PenjagaTemplateExport;
use App\Imports\PenjagaImport;
use App\Models\Penjaga;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PenjagaController extends Controller
{
    public function index(Request $request)
    {
        $query = Penjaga::query();

        if ($request->filled('cari')) {
            $cari = $request->string('cari');
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                    ->orWhere('no_kp', 'like', "%{$cari}%")
                    ->orWhere('no_telefon', 'like', "%{$cari}%");
            });
        }

        [$urut, $arah] = $this->sahkanUrutan($request, ['nama', 'hubungan', 'no_telefon'], 'nama');

        $penjaga = $query->orderBy($urut, $arah)->paginate(10)->withQueryString();

        return view('penjaga.index', compact('penjaga', 'urut', 'arah'));
    }

    public function create()
    {
        return view('penjaga.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_kp' => ['nullable', 'string', 'max:20'],
            'no_telefon' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'alamat' => ['nullable', 'string'],
            'hubungan' => ['required', 'in:Bapa,Ibu,Penjaga Sah'],
        ]);

        $email = $validated['email'] ?? null;
        unset($validated['email']);

        $penjaga = Penjaga::create($validated);

        if (! $email) {
            return redirect()->route('penjaga.index')->with('status', 'Penjaga berjaya ditambah.');
        }

        $penjaga->ciptaAtauResetAkaun($email);

        return redirect()->route('penjaga.index')->with('status', "Penjaga berjaya ditambah. Emel log masuk telah dihantar ke {$email}.");
    }

    public function edit(Penjaga $penjaga)
    {
        return view('penjaga.edit', compact('penjaga'));
    }

    public function update(Request $request, Penjaga $penjaga)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_kp' => ['nullable', 'string', 'max:20'],
            'no_telefon' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'hubungan' => ['required', 'in:Bapa,Ibu,Penjaga Sah'],
        ]);

        $penjaga->update($validated);

        return redirect()->route('penjaga.index')->with('status', 'Maklumat penjaga berjaya dikemaskini.');
    }

    public function destroy(Penjaga $penjaga)
    {
        if ($penjaga->anak()->exists()) {
            return back()->withErrors(['error' => 'Tidak boleh padam — penjaga ini masih mempunyai anak berdaftar.']);
        }

        $penjaga->delete();

        return redirect()->route('penjaga.index')->with('status', 'Penjaga berjaya dipadam.');
    }

    public function import()
    {
        return view('penjaga.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'fail' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new PenjagaImport;
        $import->import($request->file('fail'));

        $ringkasan = [
            'berjaya' => $import->bilangBerjaya,
            'akaunDicipta' => $import->bilangAkaunDicipta,
            'dilangkau' => $import->dilangkau,
            'gagal' => $import->failures()
                ->map(fn ($failure) => "Baris {$failure->row()}: ".implode(', ', $failure->errors()))
                ->all(),
        ];

        return redirect()->route('penjaga.import')
            ->with('importRingkasan', $ringkasan);
    }

    public function importTemplate()
    {
        return Excel::download(new PenjagaTemplateExport, 'templat-import-penjaga.xlsx');
    }
}
