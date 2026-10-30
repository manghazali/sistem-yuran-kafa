<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Tukar kata laluan sementara pada log masuk pertama (lihat WajibTukarKataLaluan).
 */
class TukarKataLaluanController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()->wajib_tukar_kata_laluan) {
            return redirect()->route('dashboard');
        }

        return view('auth.tukar-kata-laluan');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'Kata laluan sementara tidak betul.',
            'password.different' => 'Kata laluan baharu mesti berbeza daripada kata laluan sementara.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
            'wajib_tukar_kata_laluan' => false,
        ]);

        return redirect()->route('dashboard')->with('status', 'Kata laluan berjaya ditukar.');
    }
}
