<?php

namespace App\Observers;

use App\Models\Bayaran;
use App\Models\LogAudit;
use Illuminate\Support\Facades\Auth;

class BayaranObserver
{
    public function created(Bayaran $bayaran): void
    {
        $this->log('Cipta', $bayaran, "Bayaran {$bayaran->no_bayaran} direkod untuk {$bayaran->pelajar->nama} (RM".number_format($bayaran->amaun, 2)." via {$bayaran->kaedah_bayaran})");
    }

    public function deleted(Bayaran $bayaran): void
    {
        $this->log('Padam', $bayaran, "Bayaran {$bayaran->no_bayaran} dibatalkan (RM".number_format($bayaran->amaun, 2).")");
    }

    protected function log(string $tindakan, Bayaran $bayaran, string $keterangan): void
    {
        LogAudit::create([
            'user_id' => Auth::id(),
            'tindakan' => $tindakan,
            'model' => 'Bayaran',
            'model_id' => $bayaran->id,
            'keterangan' => $keterangan,
            'ip_address' => request()?->ip(),
        ]);
    }
}
