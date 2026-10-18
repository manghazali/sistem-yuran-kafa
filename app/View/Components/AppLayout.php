<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * @param  bool  $latarPutih  Guna latar belakang putih (bukan kelabu lalai)
     *                            untuk kawasan kandungan — cth: halaman Resit
     *                            Rasmi yang perlu kelihatan seperti dokumen
     *                            cetakan, bukan skrin aplikasi biasa.
     */
    public function __construct(public bool $latarPutih = false)
    {
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
