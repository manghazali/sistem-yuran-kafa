<?php

namespace App\Http\Controllers;

use App\Models\Penjaga;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PenjagaAkaunController extends Controller
{
    /**
     * Cipta akaun log masuk untuk seorang Penjaga (kali pertama sahaja).
     * Reset kata laluan untuk akaun yang sedia ada kini diurus melalui
     * menu Pengurusan Pengguna, bukan di sini.
     * Kata laluan dijana rawak dan dipaparkan SEKALI SAHAJA kepada Admin
     * untuk dikongsi secara selamat dengan penjaga berkenaan.
     */
    public function store(Request $request, Penjaga $penjaga)
    {
        abort_if($penjaga->user_id, 403, 'Akaun sudah wujud. Guna menu Pengurusan Pengguna untuk reset kata laluan.');

        $validated = $request->validate([
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($penjaga->user_id),
            ],
        ]);

        $kataLaluan = $penjaga->ciptaAtauResetAkaun($validated['email']);

        return view('penjaga-akaun.berjaya', [
            'penjaga' => $penjaga,
            'email' => $validated['email'],
            'kataLaluan' => $kataLaluan,
        ]);
    }
}
