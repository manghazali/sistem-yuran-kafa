<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pengguna dengan kata laluan sementara dibawa ke halaman Tukar Kata Laluan
 * sehingga mereka menetapkan kata laluan sendiri.
 */
class WajibTukarKataLaluan
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->wajib_tukar_kata_laluan && ! $request->routeIs('kata-laluan.tukar', 'kata-laluan.tukar.simpan', 'logout')) {
            return redirect()->route('kata-laluan.tukar');
        }

        return $next($request);
    }
}
