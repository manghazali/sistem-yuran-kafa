<?php

namespace App\Observers;

use App\Models\LogAudit;
use App\Models\Pelajar;

class PelajarObserver
{
    public function created(Pelajar $pelajar): void
    {
        $this->catat('Cipta', $pelajar, "Pelajar {$pelajar->nama} ({$pelajar->no_daftar}) didaftarkan");
    }

    public function updated(Pelajar $pelajar): void
    {
        $this->catat('Kemaskini', $pelajar, "Rekod pelajar {$pelajar->nama} dikemaskini");
    }

    public function deleted(Pelajar $pelajar): void
    {
        $this->catat('Padam', $pelajar, "Pelajar {$pelajar->nama} ({$pelajar->no_daftar}) dipadam");
    }

    protected function catat(string $tindakan, Pelajar $pelajar, string $keterangan): void
    {
        LogAudit::create([
            'user_id' => auth()->id(),
            'tindakan' => $tindakan,
            'model' => 'Pelajar',
            'model_id' => $pelajar->id,
            'keterangan' => $keterangan,
            'ip_address' => request()->ip(),
        ]);
    }
}
