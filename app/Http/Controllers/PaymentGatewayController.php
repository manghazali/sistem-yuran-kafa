<?php

namespace App\Http\Controllers;

use App\Models\Bayaran;
use App\Models\PaymentGatewayTransaksi;
use App\Services\KeputusanBayaranService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $gateway,
        protected KeputusanBayaranService $keputusan
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

        $transaksi = PaymentGatewayTransaksi::where('order_id', $orderId)->first();

        if (! $transaksi) {
            return response('Order ID tidak dijumpai', 404);
        }

        $this->keputusan->proses($transaksi, $statusTransaksi, $rujukan ?: null, $request->input('order.amount'), $request->all());

        return response('OK', 200);
    }

    public function selesai(Request $request)
    {
        $transaksi = PaymentGatewayTransaksi::with('itemBayaran.pelajar')
            ->where('order_id', $request->input('order_id'))
            ->first();

        // Hanya penjaga pemilik transaksi (atau Admin/Akauntan) boleh melihatnya.
        $user = $request->user();
        $milikSendiri = $transaksi && $transaksi->itemBayaran
            ->contains(fn ($item) => $item->pelajar && $user->penjaga && $item->pelajar->penjaga_id === $user->penjaga->id);

        if ($transaksi && ! $milikSendiri && ! $user->hasAnyRole(['Admin', 'Akauntan'])) {
            $transaksi = null;
        }

        // Satu Bayaran (resit) dicipta bagi setiap anak dalam transaksi yang berjaya.
        $senaraiBayaran = $transaksi && $transaksi->status === 'Berjaya'
            ? Bayaran::with('pelajar')->where('no_pesanan_gateway', $transaksi->order_id)->get()
            : collect();

        // Semasa menunggu pengesahan DOKU, halaman dimuat semula sendiri setiap 5 saat
        // selama kira-kira seminit.
        $cubaan = min((int) $request->query('cuba', 0), 12);
        $muatSemula = $transaksi && $transaksi->status === 'Menunggu' && $cubaan < 12
            ? route('payment-gateway.selesai', ['order_id' => $transaksi->order_id, 'cuba' => $cubaan + 1])
            : null;

        return view('payment-gateway.selesai', compact('transaksi', 'senaraiBayaran', 'muatSemula'));
    }
}
