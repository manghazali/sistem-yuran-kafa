<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PenggunaAkaunNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    /**
     * Peranan yang boleh ditetapkan/ditukar terus melalui skrin ini.
     * Akaun Guru/Penjaga diurus melalui menu Guru/Penjaga supaya kekal
     * terpaut dengan rekod berkaitan (guru.user_id / penjaga.user_id) —
     * skrin ini hanya memaparkan akaun tersebut untuk makluman/tindakan
     * asas (nyahaktif/padam/reset kata laluan), bukan tukar peranan.
     *
     * @var array<int, string>
     */
    protected const PERANAN_BOLEH_URUS = ['Admin', 'Akauntan'];

    public function index(Request $request)
    {
        [$urut, $arah] = $this->sahkanUrutan($request, ['name', 'email', 'created_at'], 'name');

        $pengguna = User::query()
            ->with('roles')
            ->when($request->filled('cari'), function ($query) use ($request) {
                $cari = $request->string('cari');
                $query->where(function ($q) use ($cari) {
                    $q->where('name', 'like', "%{$cari}%")
                        ->orWhere('email', 'like', "%{$cari}%");
                });
            })
            ->when($request->filled('peranan'), fn ($query) => $query->whereHas(
                'roles',
                fn ($q) => $q->where('name', $request->string('peranan'))
            ))
            ->orderBy($urut, $arah)
            ->paginate(15)
            ->withQueryString();

        return view('pengguna.index', compact('pengguna', 'urut', 'arah'));
    }

    public function create()
    {
        return view('pengguna.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'ic_number' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(self::PERANAN_BOLEH_URUS)],
        ]);

        $kataLaluan = Str::password(10);

        $pengguna = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($kataLaluan),
            'phone' => $validated['phone'] ?? null,
            'ic_number' => $validated['ic_number'] ?? null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $pengguna->assignRole($validated['role']);
        $pengguna->notify(new PenggunaAkaunNotification($pengguna, $kataLaluan));

        return view('pengguna.berjaya', [
            'pengguna' => $pengguna,
            'kataLaluan' => $kataLaluan,
        ]);
    }

    public function edit(Request $request, User $pengguna)
    {
        if ($pengguna->id === $request->user()->id) {
            return redirect()->route('profile.edit')
                ->with('status', 'Guna halaman Profil untuk kemaskini akaun anda sendiri.');
        }

        $pengguna->load('roles');

        return view('pengguna.edit', [
            'pengguna' => $pengguna,
            'bolehTukarPeranan' => $pengguna->hasAnyRole(self::PERANAN_BOLEH_URUS),
        ]);
    }

    public function update(Request $request, User $pengguna)
    {
        abort_if($pengguna->id === $request->user()->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pengguna->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'ic_number' => ['nullable', 'string', 'max:20'],
            'role' => ['nullable', Rule::in(self::PERANAN_BOLEH_URUS)],
        ]);

        if ($pengguna->hasRole('Admin') && ! $request->boolean('is_active') && User::role('Admin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['is_active' => 'Tidak boleh nyahaktifkan Admin aktif terakhir dalam sistem.']);
        }

        $pengguna->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'ic_number' => $validated['ic_number'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Peranan hanya boleh ditukar untuk akaun Admin/Akauntan (berdiri
        // sendiri). Akaun Guru/Penjaga diurus melalui menu Guru/Penjaga.
        if (! empty($validated['role']) && $pengguna->hasAnyRole(self::PERANAN_BOLEH_URUS)) {
            $pengguna->syncRoles([$validated['role']]);
        }

        return redirect()->route('pengguna.index')->with('status', 'Maklumat pengguna berjaya dikemaskini.');
    }

    public function destroy(Request $request, User $pengguna)
    {
        abort_if($pengguna->id === $request->user()->id, 403);

        if ($pengguna->hasRole('Admin') && User::role('Admin')->count() <= 1) {
            return back()->withErrors(['pengguna' => 'Tidak boleh padam Admin terakhir dalam sistem.']);
        }

        $pengguna->delete();

        return redirect()->route('pengguna.index')->with('status', 'Pengguna berjaya dipadam.');
    }

    public function resetKataLaluan(Request $request, User $pengguna)
    {
        abort_if($pengguna->id === $request->user()->id, 403);

        $kataLaluanBaharu = Str::password(10);

        $pengguna->update(['password' => Hash::make($kataLaluanBaharu)]);
        $pengguna->notify(new PenggunaAkaunNotification($pengguna, $kataLaluanBaharu));

        return view('pengguna.berjaya', [
            'pengguna' => $pengguna,
            'kataLaluan' => $kataLaluanBaharu,
        ]);
    }
}
