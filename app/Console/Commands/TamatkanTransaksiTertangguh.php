<?php

namespace App\Console\Commands;

use App\Models\PaymentGatewayTransaksi;
use App\Services\KeputusanBayaranService;
use App\Services\NotifikasiService;
use App\Services\PaymentGatewayService;
use Illuminate\Console\Command;

class TamatkanTransaksiTertangguh extends Command
{
    protected $signature = 'payment-gateway:tamatkan-tertangguh {--jam= : Bilangan jam tanpa respons sebelum status disemak dengan DOKU (lalai: PAYMENT_GATEWAY_TAMAT_JAM)}';

    protected $description = 'Semak status transaksi gerbang pembayaran yang masih "Menunggu" dengan DOKU: rekod yang berjaya, tandakan yang gagal, dan tamatkan yang tiada keputusan terlalu lama.';

    public function __construct(
        protected NotifikasiService $notifikasi,
        protected PaymentGatewayService $gateway,
        protected KeputusanBayaranService $keputusan
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $jam = (int) ($this->option('jam') ?: config('payment_gateway.tamat_jam'));
        $jamAkhir = max($jam, (int) config('payment_gateway.tamat_akhir_jam'));

        $transaksiTertangguh = PaymentGatewayTransaksi::with('itemBayaran.pelajar')
            ->where('status', 'Menunggu')
            ->where('created_at', '<=', now()->subHours($jam))
            ->get();

        if ($transaksiTertangguh->isEmpty()) {
            $this->info('Tiada transaksi tertangguh.');

            return self::SUCCESS;
        }

        $kiraan = ['berjaya' => 0, 'gagal' => 0, 'menunggu' => 0, 'ditamatkan' => 0];

        foreach ($transaksiTertangguh as $transaksi) {
            // Tanya DOKU dahulu — notifikasi mungkin hilang walaupun bayaran berjaya.
            $status = $this->gateway->semakStatus($transaksi->order_id);

            if ($status && $status['status'] !== 'NOT_FOUND') {
                $hasil = $this->keputusan->proses($transaksi, $status['status'], $status['rujukan'], $status['amaun'], $status['payload']);

                if (in_array($hasil, ['berjaya', 'gagal'], true)) {
                    $kiraan[$hasil]++;

                    continue;
                }

                // Sudah diselesaikan oleh notifikasi DOKU pada masa yang sama.
                if ($hasil === 'diabaikan') {
                    continue;
                }
            }

            // DOKU tiada rekod pesanan ini: penjaga tidak pernah membayar.
            // Status belum muktamad atau DOKU tidak dapat dihubungi: tunggu hingga had akhir.
            $tiadaRekod = $status && $status['status'] === 'NOT_FOUND';

            if (! $tiadaRekod && $transaksi->created_at->gt(now()->subHours($jamAkhir))) {
                $kiraan['menunggu']++;

                continue;
            }

            $this->tamatkan($transaksi, $tiadaRekod ? 'Tiada rekod di DOKU — tidak dibayar' : "Tiada keputusan muktamad daripada DOKU dalam masa {$jamAkhir} jam");
            $kiraan['ditamatkan']++;
        }

        $this->info("Berjaya: {$kiraan['berjaya']}, Gagal: {$kiraan['gagal']}, Masih menunggu: {$kiraan['menunggu']}, Ditamatkan: {$kiraan['ditamatkan']}.");

        return self::SUCCESS;
    }

    protected function tamatkan(PaymentGatewayTransaksi $transaksi, string $sebab): void
    {
        $transaksi->update(['status' => 'Gagal', 'msg' => $sebab]);

        foreach ($transaksi->itemBayaran->pluck('pelajar')->filter()->unique('id') as $pelajar) {
            $this->notifikasi->hantarKePenjaga(
                $pelajar,
                'Bayaran Tidak Lengkap',
                'Percubaan bayaran RM'.number_format($transaksi->amaun, 2)." bagi {$pelajar->nama} tidak selesai dan telah tamat tempoh. Jika wang telah ditolak daripada akaun anda, hubungi pihak sekolah dengan tarikh dan amaun bayaran sebelum bayar semula."
            );
        }
    }
}
