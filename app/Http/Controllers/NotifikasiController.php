<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function baca(Request $request, Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->user_id === $request->user()->id, 403);

        if (! $notifikasi->dibaca_pada) {
            $notifikasi->update(['dibaca_pada' => now()]);
        }

        return $notifikasi->url ? redirect($notifikasi->url) : back();
    }

    public function bacaSemua(Request $request)
    {
        Notifikasi::where('user_id', $request->user()->id)
            ->belumDibaca()
            ->update(['dibaca_pada' => now()]);

        return back();
    }
}
