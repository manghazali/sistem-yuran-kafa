<?php

namespace Tests\Feature;

use App\Models\Bayaran;
use App\Models\KategoriYuran;
use App\Models\PaymentGatewayTransaksi;
use App\Models\PaymentGatewayTransaksiItem;
use App\Models\Pelajar;
use App\Models\Penjaga;
use App\Models\StrukturYuran;
use App\Models\TetapanYuranPelajar;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentGatewayCallbackTest extends TestCase
{
    use RefreshDatabase;

    private PaymentGatewayTransaksi $transaksi;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        config(['payment_gateway.merchant_id' => 'BRN-TEST-0001', 'payment_gateway.secret_key' => 'rahsia-ujian']);

        $penjaga = Penjaga::create(['nama' => 'Ahmad bin Abdullah', 'no_telefon' => '0123456789', 'hubungan' => 'Bapa']);
        $pelajar = Pelajar::create(['no_daftar' => 'PLJ/2026/000001', 'nama' => 'Adam', 'jantina' => 'Lelaki', 'darjah' => 'Tahun 1', 'penjaga_id' => $penjaga->id, 'status' => 'Aktif']);
        $kategori = KategoriYuran::create(['nama' => 'Bulanan', 'bulanan' => true, 'status' => 'Aktif']);
        $struktur = StrukturYuran::create(['kategori_yuran_id' => $kategori->id, 'nama' => 'Januari', 'amaun' => 35, 'bulan_berkenaan' => [1], 'status' => 'Aktif']);
        $tetapan = TetapanYuranPelajar::create(['pelajar_id' => $pelajar->id, 'struktur_yuran_id' => $struktur->id, 'status' => 'Aktif']);

        $this->transaksi = PaymentGatewayTransaksi::create(['order_id' => 'KAFA1-20260101090000', 'amaun' => 35, 'status' => 'Menunggu']);
        PaymentGatewayTransaksiItem::create([
            'payment_gateway_transaksi_id' => $this->transaksi->id, 'pelajar_id' => $pelajar->id,
            'tetapan_yuran_pelajar_id' => $tetapan->id, 'bulan' => 1, 'tahun' => 2026, 'keterangan' => 'Januari 2026', 'amaun' => 35,
        ]);
    }

    private function urlCallback(?string $token = null): string
    {
        $token ??= app(PaymentGatewayService::class)->webhookToken();

        return '/payment-gateway/callback?token='.$token;
    }

    private function body(string $status = 'SUCCESS', float $amaun = 35): array
    {
        return [
            'order' => ['invoice_number' => 'KAFA1-20260101090000', 'amount' => $amaun],
            'transaction' => ['status' => $status, 'reference_number' => 'REF123'],
        ];
    }

    public function test_notification_without_token_is_rejected(): void
    {
        $this->postJson('/payment-gateway/callback', $this->body())->assertStatus(401);

        $this->assertSame('Menunggu', $this->transaksi->fresh()->status);
        $this->assertSame(0, Bayaran::count());
    }

    public function test_notification_with_wrong_token_is_rejected(): void
    {
        $this->postJson($this->urlCallback('salah'), $this->body())->assertStatus(401);

        $this->assertSame(0, Bayaran::count());
    }

    public function test_successful_notification_records_payment(): void
    {
        $this->postJson($this->urlCallback(), $this->body())->assertOk();

        $this->assertSame('Berjaya', $this->transaksi->fresh()->status);
        $bayaran = Bayaran::sole();
        $this->assertEquals(35, $bayaran->amaun);
        $this->assertSame(1, $bayaran->items()->count());
    }

    public function test_amount_mismatch_is_not_recorded(): void
    {
        $this->postJson($this->urlCallback(), $this->body('SUCCESS', 1))->assertOk();

        $this->assertSame('Menunggu', $this->transaksi->fresh()->status);
        $this->assertSame(0, Bayaran::count());
    }

    public function test_late_success_after_expiry_is_still_recorded(): void
    {
        $this->transaksi->update(['status' => 'Gagal']);

        $this->postJson($this->urlCallback(), $this->body())->assertOk();

        $this->assertSame('Berjaya', $this->transaksi->fresh()->status);
        $this->assertStringContainsString('selepas tamat tempoh', Bayaran::sole()->catatan);
    }

    public function test_repeated_notification_does_not_record_twice(): void
    {
        $this->postJson($this->urlCallback(), $this->body())->assertOk();
        $this->postJson($this->urlCallback(), $this->body())->assertOk();

        $this->assertSame(1, Bayaran::count());
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $this->postJson($this->urlCallback(), $this->body(), [
            'Signature' => 'HMACSHA256=palsu', 'Request-Id' => 'abc', 'Request-Timestamp' => '2026-01-01T01:00:00Z',
        ])->assertStatus(401);

        $this->assertSame(0, Bayaran::count());
    }

    public function test_valid_signature_is_accepted(): void
    {
        $gateway = app(PaymentGatewayService::class);
        $json = json_encode($this->body());
        $signature = $gateway->janaSignature('abc', '2026-01-01T01:00:00Z', '/payment-gateway/callback', $gateway->janaDigest($json));

        $this->call('POST', $this->urlCallback(), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_SIGNATURE' => $signature, 'HTTP_REQUEST_ID' => 'abc', 'HTTP_REQUEST_TIMESTAMP' => '2026-01-01T01:00:00Z',
        ], $json)->assertOk();

        $this->assertSame('Berjaya', $this->transaksi->fresh()->status);
    }

    public function test_browser_visit_is_redirected_to_result_page(): void
    {
        $this->get('/payment-gateway/callback?order_id=KAFA1-20260101090000')
            ->assertRedirect(route('payment-gateway.selesai', ['order_id' => 'KAFA1-20260101090000']));
    }

    public function test_amount_keeps_sen(): void
    {
        $gateway = app(PaymentGatewayService::class);

        $this->assertSame(70, $gateway->amaunUntukDoku(70.0));
        $this->assertSame(12.5, $gateway->amaunUntukDoku(12.5));
    }
}
