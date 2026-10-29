<?php

namespace App\Console\Commands;

use App\Models\Notifikasi;
use App\Models\Pelajar;
use App\Services\BakiBulananService;
use App\Services\NotifikasiService;
use Illuminate\Console\Command;

class HantarPeringatanBakiTertunggak extends Command
{
    protected $signature = 'peringatan:baki-tertunggak';

    protected $description = 'Hantar notifikasi peringatan kepada Penjaga yang anaknya mempunyai baki tertunggak (bulan lepas/semasa) yang belum dibayar.';

    public function __construct(
        protected BakiBulananService $bakiService,
        protected NotifikasiService $notifikasi
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $tahun = now()->year;

        $pelajar = Pelajar::where('status', 'Aktif')
            ->whereHas('penjaga', fn ($q) => $q->whereNotNull('user_id'))
            ->with('penjaga')
            ->get();

        if ($pelajar->isEmpty()) {
            $this->info('Tiada pelajar aktif dengan akaun Penjaga untuk disemak.');

            return self::SUCCESS;
        }

        $prafetch = $this->bakiService->prafetch($pelajar->pluck('id'), $tahun);

        $bilanganDihantar = 0;

        foreach ($pelajar as $satu) {
            $baki = $this->bakiService->jumlahBakiTahunSemasa($satu, $prafetch);

            if ($baki <= 0) {
                continue;
            }

            $penjagaUserId = $satu->penjaga->user_id;

            // Elak hantar berulang-ulang jika peringatan yang sama (belum dibaca)
            // masih "baharu" — jangan sepam Penjaga setiap kali arahan ini jalan.
            $sudahDihantarBaruBaru = Notifikasi::where('user_id', $penjagaUserId)
                ->where('judul', 'Peringatan Baki Tertunggak')
                ->where('mesej', 'like', '%'.$satu->nama.'%')
                ->whereNull('dibaca_pada')
                ->where('created_at', '>=', now()->subDays(config('kafa.peringatan_selang_hari')))
                ->exists();

            if ($sudahDihantarBaruBaru) {
                continue;
            }

            $this->notifikasi->hantarKePenjaga(
                $satu,
                'Peringatan Baki Tertunggak',
                'Baki tertunggak bagi '.$satu->nama.' buat masa ini ialah RM'.number_format($baki, 2).'. Sila buat bayaran secepat mungkin melalui Portal Penjaga.'
            );

            $bilanganDihantar++;
        }

        $this->info("Peringatan dihantar kepada {$bilanganDihantar} penjaga.");

        return self::SUCCESS;
    }
}
