<?php

namespace App\Services;

use App\Models\PaymentGatewayTransaksi;
use App\Models\PaymentGatewayTransaksiItem;
use App\Models\Penjaga;
use App\Models\Tetapan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentGatewayService
{
    public function merchantId(): string
    {
        return (string) Tetapan::get('payment_gateway_merchant_id', config('payment_gateway.merchant_id'));
    }

    public function secretKey(): string
    {
        return (string) Tetapan::get('payment_gateway_secret_key', config('payment_gateway.secret_key'));
    }

    public function sandbox(): bool
    {
        $nilai = Tetapan::get('payment_gateway_sandbox', config('payment_gateway.sandbox') ? '1' : '0');

        return in_array($nilai, ['1', 1, true, 'true'], true);
    }

    public function urlAsas(): string
    {
        return $this->sandbox()
            ? 'https://api-sandbox.doku.com/'
            : 'https://api.doku.com/';
    }

    /**
     * Token rahsia khusus untuk sahkan notifikasi (webhook) masuk daripada
     * Doku — disertakan sebagai query param pada override_notification_url.
     * Ini TIDAK bergantung kepada skema Signature/header rasmi Doku (yang
     * belum disahkan format sebenarnya untuk notifikasi, berbanding
     * respons API); kita yang kawal URL notifikasi sepenuhnya, jadi token
     * rawak 40-aksara ini mencukupi untuk sahkan permintaan itu benar-benar
     * datang melalui URL yang kita berikan kepada Doku, bukan sumber lain.
     * Dijana sekali dan disimpan kekal (encrypted) dalam Tetapan.
     */
    public function webhookToken(): string
    {
        $token = Tetapan::get('payment_gateway_webhook_token');

        if (! $token) {
            $token = Str::random(40);
            Tetapan::set('payment_gateway_webhook_token', $token);
        }

        return $token;
    }

    /**
     * Bina komponen tandatangan (Signature) mengikut format rasmi Doku:
     * satu komponen setiap baris, dipisah "\n", HMAC-SHA256 base64.
     */
    public function janaSignature(string $requestId, string $requestTimestamp, string $requestTarget, string $digest): string
    {
        $komponen = "Client-Id:{$this->merchantId()}\n".
            "Request-Id:{$requestId}\n".
            "Request-Timestamp:{$requestTimestamp}\n".
            "Request-Target:{$requestTarget}\n".
            "Digest:{$digest}";

        return 'HMACSHA256='.base64_encode(hash_hmac('sha256', $komponen, $this->secretKey(), true));
    }

    /**
     * Digest = SHA-256 badan (body) JSON, dienkod base64.
     */
    public function janaDigest(string $jsonBody): string
    {
        return base64_encode(hash('sha256', $jsonBody, true));
    }

    /**
     * Panggil Doku Checkout API (POST /checkout/v1/payment) untuk cipta
     * satu sesi pembayaran, dan pulangkan URL Checkout Doku untuk
     * alihkan penjaga ke sana.
     *
     * Rujukan: https://developers.doku.com/accept-payments/doku-checkout/integration-guide/backend-integration
     */
    public function ciptaPembayaran(string $orderId, float $amaun, string $namaPenuh, string $emel, string $telefon): string
    {
        $requestTarget = '/checkout/v1/payment';
        $urlSelesai = route('payment-gateway.selesai', ['order_id' => $orderId]);
        $urlCallback = route('payment-gateway.callback');
        $urlNotifikasi = $urlCallback.'?token='.$this->webhookToken();

        $body = [
            'order' => [
                'amount' => (int) round($amaun),
                'invoice_number' => $orderId,
                'callback_url' => $urlCallback,
                'callback_url_result' => $urlSelesai,
            ],
            'payment' => [
                'payment_due_date' => 60,
            ],
            'customer' => [
                'name' => $namaPenuh,
                'email' => $emel,
                'phone' => $telefon,
            ],
            'additional_info' => [
                'override_notification_url' => $urlNotifikasi,
            ],
        ];

        $jsonBody = json_encode($body);
        $digest = $this->janaDigest($jsonBody);
        $requestId = (string) Str::uuid();
        $requestTimestamp = now()->utc()->format('Y-m-d\TH:i:s\Z');
        $signature = $this->janaSignature($requestId, $requestTimestamp, $requestTarget, $digest);

        $response = Http::withHeaders([
            'Client-Id' => $this->merchantId(),
            'Request-Id' => $requestId,
            'Request-Timestamp' => $requestTimestamp,
            'Signature' => $signature,
        ])->withBody($jsonBody, 'application/json')
            ->post($this->urlAsas().ltrim($requestTarget, '/'));

        $urlBayaran = $response->json('response.payment.url');

        if (! $response->successful() || ! $urlBayaran) {
            throw new RuntimeException('Gagal cipta pembayaran Doku: '.$response->status().' '.$response->body());
        }

        return $urlBayaran;
    }

    /**
     * @param  Collection<int, array{pelajar_id: int, nama_pelajar: string, tetapan_yuran_pelajar_id: int, bulan: int, tahun: int, label: string, keterangan: string, baki: float}>  $senaraiItem
     */
    public function mulakanTransaksiItem(Collection $senaraiItem, Penjaga $penjaga, string $emel, string $telefon): string
    {
        $jumlahKeseluruhan = $senaraiItem->sum('baki');
        $orderId = 'KAFA'.$penjaga->id.'-'.now()->format('YmdHis');

        DB::transaction(function () use ($senaraiItem, $jumlahKeseluruhan, $orderId) {
            $transaksi = PaymentGatewayTransaksi::create([
                'order_id' => $orderId,
                'amaun' => $jumlahKeseluruhan,
                'status' => 'Menunggu',
            ]);

            foreach ($senaraiItem as $p) {
                PaymentGatewayTransaksiItem::create([
                    'payment_gateway_transaksi_id' => $transaksi->id,
                    'pelajar_id' => $p['pelajar_id'],
                    'tetapan_yuran_pelajar_id' => $p['tetapan_yuran_pelajar_id'],
                    'bulan' => $p['bulan'],
                    'tahun' => $p['tahun'],
                    'keterangan' => $p['keterangan'],
                    'amaun' => $p['baki'],
                ]);
            }
        });

        return $this->ciptaPembayaran($orderId, (float) $jumlahKeseluruhan, $penjaga->nama, $emel, $telefon);
    }
}
