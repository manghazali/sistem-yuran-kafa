<?php

namespace App\Http\Controllers;

use App\Models\Bayaran;
use App\Models\BayaranItem;
use App\Models\PaymentGatewayTransaksi;
use App\Services\NotifikasiService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $gateway,
        protected NotifikasiService $notifikasi
    ) {}

    public function callback(Request $request)
    {
        Log::info('Payment gateway callback diterima', [
            'headers' => $request->headers->all(),
            'query' => $request->query(),
            'body' => $request->all(),
        ]);

        // Pelayar (bukan Doku) yang sampai ke sini dibawa ke halaman keputusan.
        if ($request->isMethod('get')) {
            return redirect()->route('payment-gateway.selesai', ['order_id' => $request->query('order_id')]);
        }

        // Token wajib — tanpanya sesiapa boleh menghantar "SUCCESS" palsu.
        $tokenDiterima = (string) $request->query('token');

        if ($tokenDiterima === '' || ! hash_equals($this->gateway->webhookToken(), $tokenDiterima)) {
            Log::warning('Payment gateway callback: token tiada atau salah — DITOLAK.', [
                'order_id' => $request->input('order.invoice_number'),
            ]);

            return response('Token tidak sah', 401);
        }

        // Jika Doku menghantar Signature, ia mesti sah.
        if ($request->hasHeader('Signature') && ! $this->gateway->signatureNotifikasiSah($request)) {
            Log::warning('Payment gateway callback: Signature tidak sah — DITOLAK.', [
                'order_id' => $request->input('order.invoice_number'),
            ]);

            return response('Signature tidak sah', 401);
        }

        $orderId = (string) $request->input('order.invoice_number');
        $statusTransaksi = (string) $request->input('transaction.status');
        $rujukan = (string) $request->input('transaction.reference_number');

        if (! $orderId) {
            Log::warning('Payment gateway callback: order.invoice_number tiada', $request->all());

            return response('Order ID tiada', 400);
        }

        $transaksi = PaymentGatewayTransaksi::with('itemBayaran.pelajar')
            ->where('order_id', $orderId)
            ->first();

        if (! $transaksi) {
            return response('Order ID tidak dijumpai', 404);
        }

        // Sudah Berjaya: abaikan notifikasi berulang. Sudah Gagal: hanya SUCCESS
        // yang lewat (selepas tamat tempoh 2 jam) masih diterima — wang sudah
        // ditolak daripada penjaga, jadi bayaran mesti direkod.
        if ($transaksi->status === 'Berjaya' || ($transaksi->status === 'Gagal' && $statusTransaksi !== 'SUCCESS')) {
            return response('OK', 200);
        }

        $lewat = $transaksi->status === 'Gagal';

        // Amaun yang dibayar mesti sama dengan amaun transaksi (jika Doku menyertakannya).
        $amaunDibayar = $request->input('order.amount');

        if ($statusTransaksi === 'SUCCESS' && $amaunDibayar === null) {
            Log::warning('Payment gateway callback: order.amount tiada — amaun tidak dapat disemak.', ['order_id' => $orderId]);
        }

        if ($statusTransaksi === 'SUCCESS' && $amaunDibayar !== null && abs((float) $amaunDibayar - (float) $transaksi->amaun) > 0.009) {
            Log::error('Payment gateway callback: amaun tidak sepadan — bayaran TIDAK direkod, semak secara manual.', [
                'order_id' => $orderId,
                'amaun_transaksi' => $transaksi->amaun,
                'amaun_dibayar' => $amaunDibayar,
            ]);

            $transaksi->update([
                'msg' => 'Amaun tidak sepadan (dibayar: '.($amaunDibayar ?? 'tiada').') — semak secara manual',
                'payload_callback' => json_encode($request->all()),
            ]);

            return response('OK', 200);
        }

        $transaksi->transaction_id = $rujukan;
        $transaksi->msg = $statusTransaksi;
        $transaksi->payload_callback = json_encode($request->all());

        if ($statusTransaksi === 'SUCCESS') {
            $transaksi->status = 'Berjaya';
            $transaksi->save();

            DB::transaction(function () use ($transaksi, $lewat) {
                // SATU transaksi (walaupun merangkumi beberapa bulan sekali gus) kini
                // direkod sebagai SATU baris Bayaran sahaja bagi setiap pelajar — bukan
                // satu Bayaran berasingan bagi setiap bulan. Pecahan bulan disimpan pada
                // setiap BayaranItem (lajur bulan sendiri), supaya pengiraan baki bulanan
                // (BakiBulananService) tetap tepat walaupun induknya merangkumi > 1 bulan.
                $kumpulanMengikutPelajar = $transaksi->itemBayaran->groupBy('pelajar_id');

                foreach ($kumpulanMengikutPelajar as $kumpulan) {
                    $pertama = $kumpulan->first();

                    $sudahDirekod = Bayaran::where('no_rujukan', $transaksi->transaction_id)
                        ->where('pelajar_id', $pertama->pelajar_id)
                        ->exists();

                    if ($sudahDirekod) {
                        continue;
                    }

                    $bulanUnik = $kumpulan->pluck('bulan')->unique();
                    $tahunUnik = $kumpulan->pluck('tahun')->unique();

                    $bayaran = Bayaran::create([
                        'no_bayaran' => Bayaran::janaNoBayaran(),
                        'no_resit' => Bayaran::janaNoResit(),
                        'pelajar_id' => $pertama->pelajar_id,
                        // Hanya isi bulan induk jika kesemua item dalam transaksi ini
                        // untuk bulan yang sama — jika merentasi beberapa bulan, biar
                        // null (pecahan sebenar ada pada setiap BayaranItem).
                        'bulan' => $bulanUnik->count() === 1 ? $bulanUnik->first() : null,
                        'tahun' => $tahunUnik->first(),
                        'keterangan' => $kumpulan->pluck('keterangan')->unique()->implode(', '),
                        'amaun' => $kumpulan->sum('amaun'),
                        'kaedah_bayaran' => 'Online Banking',
                        'tarikh_bayaran' => now(),
                        'no_rujukan' => $transaksi->transaction_id,
                        'no_pesanan_gateway' => $transaksi->order_id,
                        'dicatat_oleh' => null,
                        'catatan' => 'Bayaran automatik melalui gerbang pembayaran (Order: '.$transaksi->order_id.')'
                            .($lewat ? ' — pengesahan diterima selepas tamat tempoh' : ''),
                    ]);

                    foreach ($kumpulan as $item) {
                        if (! $item->tetapan_yuran_pelajar_id) {
                            continue;
                        }

                        BayaranItem::create([
                            'bayaran_id' => $bayaran->id,
                            'tetapan_yuran_pelajar_id' => $item->tetapan_yuran_pelajar_id,
                            'bulan' => $item->bulan,
                            'amaun' => $item->amaun,
                        ]);
                    }
                }
            });
        } else {
            $transaksi->status = 'Gagal';
            $transaksi->save();

            // Maklumkan Penjaga supaya mereka tahu bayaran tidak berjaya, bukan
            // senyap sahaja — sebelum ini Penjaga cuma nampak status jika mereka
            // masih di halaman "selesai" tepat pada masa gagal.
            $pelajarUnik = $transaksi->itemBayaran->pluck('pelajar')->filter()->unique('id');

            foreach ($pelajarUnik as $pelajar) {
                $this->notifikasi->hantarKePenjaga(
                    $pelajar,
                    'Bayaran Tidak Berjaya',
                    'Percubaan bayaran RM'.number_format($transaksi->amaun, 2)." bagi {$pelajar->nama} tidak berjaya. Sila cuba semula melalui Portal Penjaga."
                );
            }
        }

        return response('OK', 200);
    }

    public function selesai(Request $request)
    {
        $transaksi = PaymentGatewayTransaksi::with('itemBayaran.pelajar')
            ->where('order_id', $request->input('order_id'))
            ->first();

        // Satu Bayaran (resit) dicipta bagi setiap anak dalam transaksi yang berjaya.
        $senaraiBayaran = $transaksi && $transaksi->status === 'Berjaya'
            ? Bayaran::with('pelajar')->where('no_pesanan_gateway', $transaksi->order_id)->get()
            : collect();

        return view('payment-gateway.selesai', compact('transaksi', 'senaraiBayaran'));
    }
}
