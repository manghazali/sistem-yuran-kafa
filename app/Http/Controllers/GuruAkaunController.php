<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuruAkaunController extends Controller
{
    /**
     * Cipta akaun log masuk untuk seorang Guru (kali pertama sahaja).
     * Reset kata laluan untuk akaun yang sedia ada kini diurus melalui
     * menu Pengurusan Pengguna, bukan di sini.
     * Kata laluan dijana rawak dan dipaparkan SEKALI SAHAJA kepada Admin
     * untuk dikongsi secara selamat dengan guru berkenaan.
     */
    public function store(Request $request, Guru $guru)
    {
        abort_if($guru->user_id, 403, 'Akaun sudah wujud. Guna menu Pengurusan Pengguna untuk reset kata laluan.');

        $validated = $request->validate([
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($guru->user_id),
            ],
        ]);

        $kataLaluan = $guru->ciptaAtauResetAkaun($validated['email']);

        return view('guru-akaun.berjaya', [
            'guru' => $guru,
            'email' => $validated['email'],
            'kataLaluan' => $kataLaluan,
        ]);
    }
}
