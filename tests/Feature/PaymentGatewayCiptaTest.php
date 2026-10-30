<?php

namespace Tests\Feature;

use App\Models\KategoriYuran;
use App\Models\PaymentGatewayTransaksi;
use App\Models\Pelajar;
use App\Models\Penjaga;
use App\Models\StrukturYuran;
use App\Models\TetapanYuranPelajar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentGatewayCiptaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $item;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        config(['payment_gateway.merchant_id' => 'BRN-TEST-0001', 'payment_gateway.secret_key' => 'rahsia-ujian']);

        Role::create(['name' => 'Penjaga']);
        $this->user = User::factory()->create();
        $this->user->assignRole('Penjaga');

        $penjaga = Penjaga::create(['user_id' => $this->user->id, 'nama' => 'Ahmad', 'no_telefon' => '0123456789', 'email' => 'ahmad@contoh.my', 'hubungan' => 'Bapa']);
        $pelajar = Pelajar::create(['no_daftar' => 'PLJ/2026/000001', 'nama' => 'Adam', 'jantina' => 'Lelaki', 'darjah' => 'Tahun 1', 'penjaga_id' => $penjaga->id, 'status' => 'Aktif']);
        $kategori = KategoriYuran::create(['nama' => 'Bulanan', 'bulanan' => true, 'status' => 'Aktif']);
        $struktur = StrukturYuran::create(['kategori_yuran_id' => $kategori->id, 'nama' => 'Januari', 'amaun' => 35, 'bulan_berkenaan' => [1], 'status' => 'Aktif']);
        $tetapan = TetapanYuranPelajar::create(['pelajar_id' => $pelajar->id, 'struktur_yuran_id' => $struktur->id, 'status' => 'Aktif']);

        $this->item = "{$pelajar->id}:".now()->year.":1:{$tetapan->id}";
    }

    private function bayar()
    {
        return $this->actingAs($this->user)->post('/portal/bayar', ['item' => [$this->item]]);
    }

    public function test_doku_down_shows_friendly_message_and_closes_transaction(): void
    {
        Http::fake(['*' => Http::response('<html>503 Service Temporarily Unavailable</html>', 503)]);

        $this->bayar()
            ->assertRedirect(route('portal.bayar-semua.semak', ['item' => [$this->item]]))
            ->assertSessionHas('ralat');

        $this->assertSame('Gagal', PaymentGatewayTransaksi::sole()->status);
        Notification::assertNothingSent();
    }

    public function test_doku_unreachable_shows_friendly_message(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 28: timeout'));

        $this->bayar()->assertSessionHas('ralat');

        $this->assertSame('Gagal', PaymentGatewayTransaksi::sole()->status);
    }

    public function test_doku_ok_sends_parent_to_payment_page(): void
    {
        Http::fake(['*' => Http::response(['response' => ['payment' => ['url' => 'https://sandbox.doku.com/checkout/abc']]], 200)]);

        $this->bayar()->assertRedirect('https://sandbox.doku.com/checkout/abc');

        $this->assertSame('Menunggu', PaymentGatewayTransaksi::sole()->status);
    }

    public function test_error_message_is_shown_on_review_page(): void
    {
        $this->actingAs($this->user)
            ->withSession(['ralat' => 'Gerbang pembayaran tidak dapat dihubungi buat masa ini.'])
            ->get(route('portal.bayar-semua.semak', ['item' => [$this->item]]))
            ->assertOk()
            ->assertSee('Gerbang pembayaran tidak dapat dihubungi buat masa ini.');
    }
}
