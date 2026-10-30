<?php

namespace Tests\Feature;

use App\Models\Bayaran;
use App\Models\BayaranItem;
use App\Models\KategoriYuran;
use App\Models\Notifikasi;
use App\Models\PaymentGatewayTransaksi;
use App\Models\PaymentGatewayTransaksiItem;
use App\Models\Pelajar;
use App\Models\Penjaga;
use App\Models\StrukturYuran;
use App\Models\TetapanYuranPelajar;
use App\Models\User;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Kes-kes tepi bayaran dalam talian: status pertengahan, yuran sedang diproses,
 * tunggakan tahun lepas, lebihan bayaran, semakan status DOKU dan nombor pesanan.
 */
class PaymentGatewayKesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Pelajar $pelajar;

    private TetapanYuranPelajar $tetapan;

    private int $tahun;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        config(['payment_gateway.merchant_id' => 'BRN-TEST-0001', 'payment_gateway.secret_key' => 'rahsia-ujian']);
        $this->tahun = now()->year;

        foreach (['Admin', 'Akauntan', 'Penjaga'] as $peranan) {
            Role::create(['name' => $peranan]);
        }
        User::factory()->create()->assignRole('Admin');
        $this->user = User::factory()->create();
        $this->user->assignRole('Penjaga');

        $penjaga = Penjaga::create(['user_id' => $this->user->id, 'nama' => 'Ahmad', 'no_telefon' => '0123456789', 'hubungan' => 'Bapa']);
        $this->pelajar = Pelajar::create(['no_daftar' => 'PLJ/2026/000001', 'nama' => 'Adam', 'jantina' => 'Lelaki', 'darjah' => 'Tahun 1', 'penjaga_id' => $penjaga->id, 'status' => 'Aktif']);
        $kategori = KategoriYuran::create(['nama' => 'Bulanan', 'bulanan' => true, 'status' => 'Aktif']);
        $struktur = StrukturYuran::create(['kategori_yuran_id' => $kategori->id, 'nama' => 'Januari', 'amaun' => 35, 'bulan_berkenaan' => [1], 'status' => 'Aktif']);
        $this->tetapan = TetapanYuranPelajar::create(['pelajar_id' => $this->pelajar->id, 'struktur_yuran_id' => $struktur->id, 'status' => 'Aktif']);
    }

    private function transaksi(string $status = 'Menunggu', ?int $tahun = null, string $orderId = 'KAFA1-20260101090000-ABCD'): PaymentGatewayTransaksi
    {
        $transaksi = PaymentGatewayTransaksi::create(['order_id' => $orderId, 'amaun' => 35, 'status' => $status]);
        PaymentGatewayTransaksiItem::create([
            'payment_gateway_transaksi_id' => $transaksi->id, 'pelajar_id' => $this->pelajar->id,
            'tetapan_yuran_pelajar_id' => $this->tetapan->id, 'bulan' => 1, 'tahun' => $tahun ?? $this->tahun,
            'keterangan' => 'Januari', 'amaun' => 35,
        ]);

        return $transaksi;
    }

    private function notifikasiDoku(string $status, float $amaun = 35, string $orderId = 'KAFA1-20260101090000-ABCD')
    {
        $token = app(PaymentGatewayService::class)->webhookToken();

        return $this->postJson('/payment-gateway/callback?token='.$token, [
            'order' => ['invoice_number' => $orderId, 'amount' => $amaun],
            'transaction' => ['status' => $status, 'reference_number' => 'REF123'],
        ]);
    }

    // A — status pertengahan

    public function test_pending_status_keeps_transaction_waiting_without_telling_parent(): void
    {
        $transaksi = $this->transaksi();

        $this->notifikasiDoku('PENDING')->assertOk();

        $this->assertSame('Menunggu', $transaksi->fresh()->status);
        $this->assertSame(0, Notifikasi::count());
    }

    public function test_pending_then_success_records_payment(): void
    {
        $transaksi = $this->transaksi();

        $this->notifikasiDoku('PENDING');
        $this->notifikasiDoku('SUCCESS');

        $this->assertSame('Berjaya', $transaksi->fresh()->status);
        $this->assertSame(1, Bayaran::count());
    }

    public function test_failed_status_tells_parent(): void
    {
        $transaksi = $this->transaksi();

        $this->notifikasiDoku('FAILED');

        $this->assertSame('Gagal', $transaksi->fresh()->status);
        $this->assertSame(1, Notifikasi::where('user_id', $this->user->id)->count());
    }

    // B — yuran sedang diproses

    public function test_fee_being_paid_online_cannot_be_selected_again(): void
    {
        $this->transaksi();
        $item = "{$this->pelajar->id}:{$this->tahun}:1:{$this->tetapan->id}";

        $this->actingAs($this->user)->get('/portal/bayar')
            ->assertOk()
            ->assertSee('Sedang diproses');

        $this->actingAs($this->user)->post('/portal/bayar', ['item' => [$item]])
            ->assertRedirect(route('portal.bayar-semua'));

        $this->assertSame(1, PaymentGatewayTransaksi::count());
    }

    // C — tunggakan tahun lepas

    public function test_last_year_unpaid_fee_can_be_paid(): void
    {
        $this->tetapan->forceFill(['created_at' => now()->subYear()->startOfYear()])->save();
        Http::fake(['*' => Http::response(['response' => ['payment' => ['url' => 'https://sandbox.doku.com/checkout/abc']]], 200)]);
        $tahunLepas = $this->tahun - 1;

        $this->actingAs($this->user)->get('/portal/bayar')->assertSee("Januari {$tahunLepas}");

        $this->actingAs($this->user)
            ->post('/portal/bayar', ['item' => ["{$this->pelajar->id}:{$tahunLepas}:1:{$this->tetapan->id}"]])
            ->assertRedirect('https://sandbox.doku.com/checkout/abc');

        $this->assertSame($tahunLepas, (int) PaymentGatewayTransaksiItem::sole()->tahun);
    }

    public function test_fee_set_this_year_does_not_create_last_year_debt(): void
    {
        $tahunLepas = $this->tahun - 1;

        $this->actingAs($this->user)->get('/portal/bayar')
            ->assertSee("Januari {$this->tahun}")
            ->assertDontSee("Januari {$tahunLepas}");
    }

    public function test_payment_across_two_years_gives_one_receipt_per_year(): void
    {
        $transaksi = $this->transaksi();
        $transaksi->update(['amaun' => 70]);
        PaymentGatewayTransaksiItem::create([
            'payment_gateway_transaksi_id' => $transaksi->id, 'pelajar_id' => $this->pelajar->id,
            'tetapan_yuran_pelajar_id' => $this->tetapan->id, 'bulan' => 1, 'tahun' => $this->tahun - 1,
            'keterangan' => 'Januari lepas', 'amaun' => 35,
        ]);

        $this->notifikasiDoku('SUCCESS', 70);

        $this->assertEqualsCanonicalizing([$this->tahun - 1, $this->tahun], Bayaran::pluck('tahun')->map(fn ($t) => (int) $t)->all());
    }

    // D — lebihan bayaran

    public function test_fee_paid_elsewhere_while_waiting_is_flagged_to_admin(): void
    {
        $transaksi = $this->transaksi();
        $kaunter = Bayaran::create([
            'no_bayaran' => 'BYR-K1', 'no_resit' => 'RST-K1', 'pelajar_id' => $this->pelajar->id, 'tahun' => $this->tahun,
            'amaun' => 35, 'kaedah_bayaran' => 'Tunai', 'tarikh_bayaran' => now(),
        ]);
        BayaranItem::create(['bayaran_id' => $kaunter->id, 'tetapan_yuran_pelajar_id' => $this->tetapan->id, 'bulan' => 1, 'amaun' => 35]);

        $this->notifikasiDoku('SUCCESS');

        $this->assertSame('Berjaya', $transaksi->fresh()->status);
        $this->assertStringContainsString('LEBIHAN', Bayaran::where('no_pesanan_gateway', $transaksi->order_id)->sole()->catatan);
        $this->assertSame(1, Notifikasi::where('judul', 'Lebihan Bayaran Dalam Talian')->count());
    }

    // E — semakan status DOKU oleh arahan berjadual

    private function tamatkan(): void
    {
        $this->artisan('payment-gateway:tamatkan-tertangguh')->assertSuccessful();
    }

    public function test_expiry_check_records_payment_doku_says_succeeded(): void
    {
        $transaksi = $this->transaksi();
        $this->travel(3)->hours();
        Http::fake(['*' => Http::response(['order' => ['amount' => 35], 'transaction' => ['status' => 'SUCCESS', 'reference_number' => 'REF9']], 200)]);

        $this->tamatkan();

        $this->assertSame('Berjaya', $transaksi->fresh()->status);
        $this->assertSame(1, Bayaran::count());
    }

    public function test_expiry_check_marks_failed_when_doku_says_failed(): void
    {
        $transaksi = $this->transaksi();
        $this->travel(3)->hours();
        Http::fake(['*' => Http::response(['transaction' => ['status' => 'FAILED']], 200)]);

        $this->tamatkan();

        $this->assertSame('Gagal', $transaksi->fresh()->status);
    }

    public function test_expiry_check_keeps_waiting_when_pending_or_unreachable(): void
    {
        $pending = $this->transaksi();
        $tiadaJawapan = $this->transaksi(orderId: 'KAFA1-20260101090000-WXYZ');
        $this->travel(3)->hours();
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'WXYZ')) {
                throw new ConnectionException('timeout');
            }

            return Http::response(['transaction' => ['status' => 'PENDING']], 200);
        });

        $this->tamatkan();

        $this->assertSame('Menunggu', $pending->fresh()->status);
        $this->assertSame('Menunggu', $tiadaJawapan->fresh()->status);
    }

    public function test_expiry_check_gives_up_after_final_limit(): void
    {
        $transaksi = $this->transaksi();
        $this->travel(25)->hours();
        Http::fake(['*' => Http::response(['transaction' => ['status' => 'PENDING']], 200)]);

        $this->tamatkan();

        $this->assertSame('Gagal', $transaksi->fresh()->status);
        $this->assertSame(1, Notifikasi::where('judul', 'Bayaran Tidak Lengkap')->count());
    }

    public function test_expiry_check_marks_failed_when_doku_has_no_record(): void
    {
        $transaksi = $this->transaksi();
        $this->travel(3)->hours();
        Http::fake(['*' => Http::response(['error' => 'not found'], 404)]);

        $this->tamatkan();

        $this->assertSame('Gagal', $transaksi->fresh()->status);
    }

    // G — nombor pesanan unik

    public function test_two_payments_in_same_second_get_different_order_numbers(): void
    {
        Http::fake(['*' => Http::response(['response' => ['payment' => ['url' => 'https://sandbox.doku.com/checkout/abc']]], 200)]);
        $this->freezeSecond();
        $item = ["{$this->pelajar->id}:{$this->tahun}:1:{$this->tetapan->id}"];

        $this->actingAs($this->user)->post('/portal/bayar', ['item' => $item]);
        PaymentGatewayTransaksi::query()->update(['status' => 'Gagal']);
        $this->actingAs($this->user)->post('/portal/bayar', ['item' => $item]);

        $this->assertSame(2, PaymentGatewayTransaksi::distinct('order_id')->count('order_id'));
    }
}
