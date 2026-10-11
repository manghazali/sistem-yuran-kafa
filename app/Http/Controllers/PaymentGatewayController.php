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
    ) {
    }

    public function callback(Request $request)
    {
        Log::info('Payment gateway callback diterima', [
            'headers' => $request->headers->all(),
            'query' => $request->query(),
            'body' => $request->all(),
        ]);

        $tokenDiterima = $request->query('token');
        $tokenDijangka = $this->gateway->webhookToken();

        if ($tokenDiterima && ! hash_equals($tokenDijangka, (string) $tokenDiterima)) {
            Log::warning('Payment gateway callback: token SALAH — kemungkinan percubaan palsu, DITOLAK.', [
                'order_id' => $request->input('order.invoice_number'),
            ]);

            return response('Token tidak sah', 401);
        }

        if (! $tokenDiterima) {
            Log::warning('Payment gateway callback: token TIADA pada permintaan ini — tidak dapat sahkan sumber. '.
                'Pastikan URL Webhook di papan pemuka Doku menggunakan URL berserta ?token= yang betul.', [
                'order_id' => $request->input('order.invoice_number'),
            ]);
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

        if ($transaksi->status !== 'Menunggu') {
            return response('OK', 200);
        }

        $transaksi->transaction_id = $rujukan;
        $transaksi->msg = $statusTransaksi;
        $transaksi->payload_callback = json_encode($request->all());

        if ($statusTransaksi === 'SUCCESS') {
            $transaksi->status = 'Berjaya';
            $transaksi->save();

            DB::transaction(function () use ($transaksi) {
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
                        'catatan' => 'Bayaran automatik melalui gateway pembayaran (Order: '.$transaksi->order_id.')',
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

        return view('payment-gateway.selesai', compact('transaksi'));
    }
}
