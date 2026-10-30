<?php

namespace App\Services;

use App\Exceptions\GerbangPembayaranTidakTersedia;
use App\Models\PaymentGatewayTransaksi;
use App\Models\PaymentGatewayTransaksiItem;
use App\Models\Penjaga;
use App\Models\Tetapan;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
     * Permintaan GET (tiada badan) tidak menyertakan komponen Digest.
     */
    public function janaSignature(string $requestId, string $requestTimestamp, string $requestTarget, ?string $digest): string
    {
        $komponen = "Client-Id:{$this->merchantId()}\n".
            "Request-Id:{$requestId}\n".
            "Request-Timestamp:{$requestTimestamp}\n".
            "Request-Target:{$requestTarget}".
            ($digest !== null ? "\nDigest:{$digest}" : '');

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
     * Sahkan header Signature pada notifikasi (webhook) daripada Doku.
     * Komponen sama seperti permintaan keluar; Request-Target ialah laluan URL
     * notifikasi kita. Laluan dengan dan tanpa query (?token=) kedua-duanya
     * dicuba kerana dokumentasi Doku tidak menyatakan sama ada query disertakan.
     */
    public function signatureNotifikasiSah(Request $request): bool
    {
        $signature = (string) $request->header('Signature');
        $requestId = (string) $request->header('Request-Id');
        $requestTimestamp = (string) $request->header('Request-Timestamp');

        if ($signature === '' || $requestId === '' || $requestTimestamp === '') {
            return false;
        }

        $digest = $this->janaDigest($request->getContent());
        $laluan = '/'.ltrim($request->path(), '/');
        $sasaran = array_unique([$laluan, $request->getRequestUri()]);

        foreach ($sasaran as $requestTarget) {
            if (hash_equals($this->janaSignature($requestId, $requestTimestamp, $requestTarget, $digest), $signature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Amaun untuk Doku: nombor bulat jika tiada sen (cth. 70), selain itu dua
     * tempat perpuluhan (cth. 12.5) — supaya yuran bersen tidak dibundarkan.
     */
    public function amaunUntukDoku(float $amaun): int|float
    {
        $amaun = round($amaun, 2);

        return floor($amaun) === $amaun ? (int) $amaun : $amaun;
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
                'amount' => $this->amaunUntukDoku($amaun),
                'invoice_number' => $orderId,
                // Butang "kembali ke merchant" pada halaman Doku — bawa penjaga ke
                // halaman keputusan, bukan ke URL notifikasi (webhook).
                'callback_url' => $urlSelesai,
                'callback_url_result' => $urlSelesai,
            ],
            'payment' => [
                'payment_due_date' => config('payment_gateway.tempoh_bayar_minit'),
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

        try {
            // Had masa supaya penjaga tidak menunggu lama jika DOKU lambat.
            $response = Http::timeout(20)->withHeaders([
                'Client-Id' => $this->merchantId(),
                'Request-Id' => $requestId,
                'Request-Timestamp' => $requestTimestamp,
                'Signature' => $signature,
            ])->withBody($jsonBody, 'application/json')
                ->post($this->urlAsas().ltrim($requestTarget, '/'));
        } catch (ConnectionException $e) {
            throw new GerbangPembayaranTidakTersedia('Doku tidak dapat dihubungi: '.$e->getMessage(), 0, $e);
        }

        $urlBayaran = $response->json('response.payment.url');

        if (! $response->successful() || ! $urlBayaran) {
            throw new GerbangPembayaranTidakTersedia('Gagal cipta pembayaran Doku: '.$response->status().' '.Str::limit($response->body(), 500));
        }

        return $urlBayaran;
    }

    /**
     * Tanya DOKU status terkini sesuatu pesanan (Check Status API).
     *
     * @return array{status: string, rujukan: ?string, amaun: mixed, payload: array}|null
     *                                                                                    null jika DOKU tidak dapat dihubungi atau jawapan tidak difahami;
     *                                                                                    status 'NOT_FOUND' jika DOKU tiada rekod pesanan ini.
     */
    public function semakStatus(string $orderId): ?array
    {
        $requestTarget = '/orders/v1/status/'.rawurlencode($orderId);
        $requestId = (string) Str::uuid();
        $requestTimestamp = now()->utc()->format('Y-m-d\TH:i:s\Z');

        try {
            $response = Http::timeout(20)->withHeaders([
                'Client-Id' => $this->merchantId(),
                'Request-Id' => $requestId,
                'Request-Timestamp' => $requestTimestamp,
                'Signature' => $this->janaSignature($requestId, $requestTimestamp, $requestTarget, null),
            ])->get($this->urlAsas().ltrim($requestTarget, '/'));
        } catch (ConnectionException $e) {
            Log::warning('Payment gateway: semakan status gagal dihubungi', ['order_id' => $orderId, 'ralat' => $e->getMessage()]);

            return null;
        }

        if ($response->status() === 404) {
            return ['status' => 'NOT_FOUND', 'rujukan' => null, 'amaun' => null, 'payload' => (array) $response->json()];
        }

        $status = $response->json('transaction.status');

        if (! $response->successful() || ! $status) {
            Log::warning('Payment gateway: semakan status tidak berjaya', ['order_id' => $orderId, 'http' => $response->status(), 'badan' => Str::limit($response->body(), 500)]);

            return null;
        }

        return [
            'status' => (string) $status,
            'rujukan' => $response->json('transaction.reference_number') ?: null,
            'amaun' => $response->json('order.amount'),
            'payload' => (array) $response->json(),
        ];
    }

    /**
     * @param  Collection<int, array{pelajar_id: int, nama_pelajar: string, tetapan_yuran_pelajar_id: int, bulan: int, tahun: int, label: string, keterangan: string, baki: float}>  $senaraiItem
     */
    public function mulakanTransaksiItem(Collection $senaraiItem, Penjaga $penjaga, string $emel, string $telefon): string
    {
        $jumlahKeseluruhan = $senaraiItem->sum('baki');
        // Akhiran rawak supaya dua tekanan dalam saat yang sama tidak bertembung.
        $orderId = 'KAFA'.$penjaga->id.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));

        $transaksi = DB::transaction(function () use ($senaraiItem, $jumlahKeseluruhan, $orderId) {
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

            return $transaksi;
        });

        try {
            return $this->ciptaPembayaran($orderId, (float) $jumlahKeseluruhan, $penjaga->nama, $emel, $telefon);
        } catch (GerbangPembayaranTidakTersedia $e) {
            // Penjaga belum dibawa ke DOKU, jadi tiada caj — tutup transaksi terus
            // supaya tidak tersangkut "Menunggu" (notis kuning dan notifikasi tamat tempoh).
            Log::error('Payment gateway: gagal cipta pembayaran', ['order_id' => $orderId, 'ralat' => $e->getMessage()]);
            $transaksi->update(['status' => 'Gagal', 'msg' => Str::limit('Gerbang pembayaran tidak tersedia: '.$e->getMessage(), 250)]);

            throw $e;
        }
    }
}
