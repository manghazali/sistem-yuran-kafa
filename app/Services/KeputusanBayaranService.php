<?php

namespace App\Services;

use App\Models\Bayaran;
use App\Models\BayaranItem;
use App\Models\PaymentGatewayTransaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Proses keputusan bayaran dalam talian daripada DOKU — sama ada melalui
 * notifikasi (webhook) atau semakan status oleh arahan berjadual.
 */
class KeputusanBayaranService
{
    /** Status DOKU yang muktamad gagal. Status lain (cth. PENDING) dibiar menunggu. */
    public const STATUS_GAGAL = ['FAILED', 'EXPIRED', 'CANCELLED', 'CANCELED', 'REJECTED', 'DECLINED', 'VOID', 'VOIDED'];

    public function __construct(
        protected NotifikasiService $notifikasi,
        protected BakiBulananService $baki
    ) {}

    /**
     * @return string Keputusan: 'berjaya', 'gagal', 'menunggu' atau 'diabaikan'
     */
    public function proses(PaymentGatewayTransaksi $transaksi, string $status, ?string $rujukan, mixed $amaunDibayar, array $payload): string
    {
        $status = strtoupper(trim($status));

        // Kunci baris transaksi supaya dua keputusan serentak (notifikasi dan
        // semakan berjadual) tidak merekod bayaran yang sama dua kali.
        return DB::transaction(function () use ($transaksi, $status, $rujukan, $amaunDibayar, $payload) {
            $transaksi = PaymentGatewayTransaksi::with('itemBayaran.pelajar')->lockForUpdate()->findOrFail($transaksi->id);

            // Sudah Berjaya: abaikan keputusan berulang. Sudah Gagal: hanya SUCCESS
            // yang lewat masih diterima — wang sudah ditolak, jadi mesti direkod.
            if ($transaksi->status === 'Berjaya' || ($transaksi->status === 'Gagal' && $status !== 'SUCCESS')) {
                return 'diabaikan';
            }

            $lewat = $transaksi->status === 'Gagal';

            if ($status === 'SUCCESS') {
                if ($amaunDibayar === null) {
                    Log::warning('Payment gateway: amaun tidak disertakan — amaun tidak dapat disemak.', ['order_id' => $transaksi->order_id]);
                } elseif (abs((float) $amaunDibayar - (float) $transaksi->amaun) > 0.009) {
                    Log::error('Payment gateway: amaun tidak sepadan — bayaran TIDAK direkod, semak secara manual.', [
                        'order_id' => $transaksi->order_id, 'amaun_transaksi' => $transaksi->amaun, 'amaun_dibayar' => $amaunDibayar,
                    ]);
                    $transaksi->update([
                        'msg' => 'Amaun tidak sepadan (dibayar: '.$amaunDibayar.') — semak secara manual',
                        'payload_callback' => json_encode($payload),
                    ]);

                    return 'menunggu';
                }

                $transaksi->update(['status' => 'Berjaya', 'transaction_id' => $rujukan, 'msg' => $status, 'payload_callback' => json_encode($payload)]);
                $this->rekodBayaran($transaksi, $lewat);

                return 'berjaya';
            }

            if (in_array($status, self::STATUS_GAGAL, true)) {
                $transaksi->update(['status' => 'Gagal', 'transaction_id' => $rujukan, 'msg' => $status, 'payload_callback' => json_encode($payload)]);

                foreach ($transaksi->itemBayaran->pluck('pelajar')->filter()->unique('id') as $pelajar) {
                    $this->notifikasi->hantarKePenjaga(
                        $pelajar,
                        'Bayaran Tidak Berjaya',
                        'Percubaan bayaran RM'.number_format($transaksi->amaun, 2)." bagi {$pelajar->nama} tidak berjaya. Jika wang telah ditolak daripada akaun anda, ia akan dipulangkan oleh bank anda. Hubungi pihak sekolah jika tidak diterima dalam beberapa hari bekerja. Anda boleh cuba bayar semula melalui Portal Penjaga."
                    );
                }

                return 'gagal';
            }

            // Status pertengahan (cth. PENDING) — bayaran mungkin masih berjaya; terus menunggu.
            $transaksi->update(['msg' => $status ?: 'Tiada status', 'payload_callback' => json_encode($payload)]);

            return 'menunggu';
        });
    }

    /**
     * Satu Bayaran (resit) bagi setiap anak bagi setiap tahun dalam transaksi.
     * Pecahan bulan disimpan pada BayaranItem supaya baki bulanan kekal tepat.
     */
    protected function rekodBayaran(PaymentGatewayTransaksi $transaksi, bool $lewat): void
    {
        $lebihan = [];

        foreach ($transaksi->itemBayaran->groupBy(fn ($i) => $i->pelajar_id.'-'.$i->tahun) as $kumpulan) {
            $pertama = $kumpulan->first();

            $sudahDirekod = Bayaran::where('no_pesanan_gateway', $transaksi->order_id)
                ->where('pelajar_id', $pertama->pelajar_id)
                ->where('tahun', $pertama->tahun)
                ->exists();

            if ($sudahDirekod) {
                continue;
            }

            // Yuran yang sudah dibayar melalui saluran lain (kaunter, guru) semasa
            // bayaran ini menunggu — wang tetap diterima, jadi direkod dan ditanda.
            $bakiSemasa = $pertama->pelajar
                ? $this->baki->senaraiBulan($pertama->pelajar, (int) $pertama->tahun)
                    ->flatMap(fn ($b) => $b['butiran']->mapWithKeys(fn ($i) => [$b['bulan'].':'.$i['id'] => $i['baki']]))
                : collect();
            $lebihanAnak = $kumpulan->filter(fn ($i) => $i->tetapan_yuran_pelajar_id
                && (float) $bakiSemasa->get($i->bulan.':'.$i->tetapan_yuran_pelajar_id, 0) + 0.009 < (float) $i->amaun);

            $bulanUnik = $kumpulan->pluck('bulan')->unique();

            $bayaran = Bayaran::create([
                'no_bayaran' => Bayaran::janaNoBayaran(),
                'no_resit' => Bayaran::janaNoResit(),
                'pelajar_id' => $pertama->pelajar_id,
                // Bulan induk hanya jika semua item untuk bulan yang sama.
                'bulan' => $bulanUnik->count() === 1 ? $bulanUnik->first() : null,
                'tahun' => $pertama->tahun,
                'keterangan' => $kumpulan->pluck('keterangan')->unique()->implode(', '),
                'amaun' => $kumpulan->sum('amaun'),
                'kaedah_bayaran' => 'Online Banking',
                'tarikh_bayaran' => now(),
                'no_rujukan' => $transaksi->transaction_id,
                'no_pesanan_gateway' => $transaksi->order_id,
                'dicatat_oleh' => null,
                'catatan' => 'Bayaran automatik melalui gerbang pembayaran (Order: '.$transaksi->order_id.')'
                    .($lewat ? ' — pengesahan diterima selepas tamat tempoh' : '')
                    .($lebihanAnak->isNotEmpty() ? ' — LEBIHAN: '.$lebihanAnak->pluck('keterangan')->implode(', ').' telah dibayar melalui saluran lain' : ''),
            ]);

            foreach ($kumpulan as $item) {
                if ($item->tetapan_yuran_pelajar_id) {
                    BayaranItem::create([
                        'bayaran_id' => $bayaran->id,
                        'tetapan_yuran_pelajar_id' => $item->tetapan_yuran_pelajar_id,
                        'bulan' => $item->bulan,
                        'amaun' => $item->amaun,
                    ]);
                }
            }

            if ($lebihanAnak->isNotEmpty()) {
                $lebihan[] = ($pertama->pelajar?->nama ?? 'Pelajar').' ('.$lebihanAnak->pluck('keterangan')->implode(', ').') — '.$bayaran->no_resit;
            }
        }

        if ($lebihan) {
            $this->notifikasi->hantarKePeranan(
                ['Admin', 'Akauntan'],
                'Lebihan Bayaran Dalam Talian',
                'Yuran berikut dibayar dalam talian tetapi telah dibayar melalui saluran lain: '.implode('; ', $lebihan).'. Semak dan pulangkan lebihan kepada penjaga.'
            );
        }
    }
}
