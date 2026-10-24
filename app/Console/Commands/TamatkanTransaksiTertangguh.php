<?php

namespace App\Console\Commands;

use App\Models\PaymentGatewayTransaksi;
use App\Services\NotifikasiService;
use Illuminate\Console\Command;

class TamatkanTransaksiTertangguh extends Command
{
    protected $signature = 'payment-gateway:tamatkan-tertangguh {--jam=2 : Bilangan jam tanpa respons sebelum transaksi dianggap tamat tempoh}';

    protected $description = 'Tandakan transaksi gerbang pembayaran yang masih "Menunggu" selepas tempoh tertentu sebagai "Gagal" (tamat tempoh/ditinggalkan oleh Penjaga), dan maklumkan Penjaga berkenaan.';

    public function __construct(protected NotifikasiService $notifikasi)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $jam = (int) $this->option('jam');

        $transaksiTertangguh = PaymentGatewayTransaksi::with('itemBayaran.pelajar')
            ->where('status', 'Menunggu')
            ->where('created_at', '<=', now()->subHours($jam))
            ->get();

        if ($transaksiTertangguh->isEmpty()) {
            $this->info('Tiada transaksi tertangguh.');

            return self::SUCCESS;
        }

        foreach ($transaksiTertangguh as $transaksi) {
            $transaksi->update([
                'status' => 'Gagal',
                'msg' => 'Tamat tempoh — tidak disahkan dalam masa '.$jam.' jam.',
            ]);

            $pelajarUnik = $transaksi->itemBayaran->pluck('pelajar')->filter()->unique('id');

            foreach ($pelajarUnik as $pelajar) {
                $this->notifikasi->hantarKePenjaga(
                    $pelajar,
                    'Bayaran Tidak Lengkap',
                    'Percubaan bayaran RM'.number_format($transaksi->amaun, 2)." bagi {$pelajar->nama} tidak selesai dan telah tamat tempoh. Sila cuba semula melalui Portal Penjaga."
                );
            }
        }

        $this->info("Ditamatkan: {$transaksiTertangguh->count()} transaksi.");

        return self::SUCCESS;
    }
}
