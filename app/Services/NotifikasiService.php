<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\Pelajar;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class NotifikasiService
{
    /**
     * Hantar notifikasi kepada akaun Penjaga bagi seorang pelajar.
     * Tiada apa-apa berlaku jika pelajar tiada penjaga atau penjaga
     * belum ada akaun log masuk.
     */
    public function hantarKePenjaga(Pelajar $pelajar, string $judul, string $mesej): void
    {
        $penjaga = $pelajar->penjaga;

        if (! $penjaga || ! $penjaga->user_id) {
            return;
        }

        Notifikasi::create([
            'user_id' => $penjaga->user_id,
            'judul' => $judul,
            'mesej' => $mesej,
            'url' => Route::has('portal.kalendar') ? route('portal.kalendar', $pelajar) : null,
        ]);
    }

    /**
     * Hantar notifikasi kepada satu pengguna tertentu (cth: Guru, Admin).
     */
    public function hantar(int $userId, string $judul, string $mesej, ?string $url = null): void
    {
        Notifikasi::create([
            'user_id' => $userId,
            'judul' => $judul,
            'mesej' => $mesej,
            'url' => $url,
        ]);
    }

    /**
     * Hantar notifikasi kepada semua pengguna yang memegang peranan tertentu
     * (cth: maklumkan semua Admin/Akauntan apabila Guru hantar permohonan).
     */
    public function hantarKePeranan(array $peranan, string $judul, string $mesej, ?string $url = null): void
    {
        User::role($peranan)->get()->each(
            fn (User $user) => $this->hantar($user->id, $judul, $mesej, $url)
        );
    }
}
