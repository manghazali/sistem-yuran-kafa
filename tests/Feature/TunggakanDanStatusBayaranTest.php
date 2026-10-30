<?php

namespace Tests\Feature;

use App\Models\KategoriYuran;
use App\Models\PaymentGatewayTransaksi;
use App\Models\PaymentGatewayTransaksiItem;
use App\Models\Pelajar;
use App\Models\Penjaga;
use App\Models\StrukturYuran;
use App\Models\TetapanYuranPelajar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Tunggakan tahun lepas di bahagian admin, dan halaman keputusan bayaran online.
 */
class TunggakanDanStatusBayaranTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $penjagaUser;

    private Pelajar $pelajar;

    private TetapanYuranPelajar $tetapan;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Admin', 'Akauntan', 'Penjaga'] as $peranan) {
            Role::create(['name' => $peranan]);
        }
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
        $this->penjagaUser = User::factory()->create();
        $this->penjagaUser->assignRole('Penjaga');

        $penjaga = Penjaga::create(['user_id' => $this->penjagaUser->id, 'nama' => 'Ahmad', 'no_telefon' => '0123456789', 'hubungan' => 'Bapa']);
        $this->pelajar = Pelajar::create(['no_daftar' => 'PLJ/2026/000001', 'nama' => 'Adam', 'jantina' => 'Lelaki', 'darjah' => 'Tahun 1', 'penjaga_id' => $penjaga->id, 'status' => 'Aktif']);
        $kategori = KategoriYuran::create(['nama' => 'Bulanan', 'bulanan' => true, 'status' => 'Aktif']);
        $struktur = StrukturYuran::create(['kategori_yuran_id' => $kategori->id, 'nama' => 'Januari', 'amaun' => 35, 'bulan_berkenaan' => [1], 'status' => 'Aktif']);
        $this->tetapan = TetapanYuranPelajar::create(['pelajar_id' => $this->pelajar->id, 'struktur_yuran_id' => $struktur->id, 'status' => 'Aktif']);
        $this->tetapan->forceFill(['created_at' => now()->subYear()->startOfYear()])->save();
    }

    private function transaksi(string $status): PaymentGatewayTransaksi
    {
        $transaksi = PaymentGatewayTransaksi::create(['order_id' => 'KAFA1-20260101090000-ABCD', 'amaun' => 35, 'status' => $status]);
        PaymentGatewayTransaksiItem::create([
            'payment_gateway_transaksi_id' => $transaksi->id, 'pelajar_id' => $this->pelajar->id,
            'tetapan_yuran_pelajar_id' => $this->tetapan->id, 'bulan' => 1, 'tahun' => now()->year,
            'keterangan' => 'Januari', 'amaun' => 35,
        ]);

        return $transaksi;
    }

    // Tunggakan tahun lepas — bahagian admin

    public function test_counter_payment_lists_last_year_unpaid_fee(): void
    {
        $tahunLepas = now()->year - 1;

        $this->actingAs($this->admin)->get('/bayaran/create?pelajar_id='.$this->pelajar->id)
            ->assertOk()
            ->assertSee("Januari {$tahunLepas}")
            ->assertSee('data-tahun="'.$tahunLepas.'"', false);
    }

    public function test_outstanding_report_includes_last_year(): void
    {
        $this->actingAs($this->admin)->get('/baki-tertunggak')
            ->assertOk()
            ->assertSee('Adam')
            ->assertSee('Januari '.(now()->year - 1));
    }

    // Halaman keputusan bayaran

    public function test_result_page_needs_login(): void
    {
        $this->transaksi('Berjaya');

        $this->get('/payment-gateway/selesai?order_id=KAFA1-20260101090000-ABCD')->assertRedirect('/login');
    }

    public function test_other_parent_cannot_see_the_transaction(): void
    {
        $this->transaksi('Berjaya');
        $lain = User::factory()->create();
        $lain->assignRole('Penjaga');

        $this->actingAs($lain)->get('/payment-gateway/selesai?order_id=KAFA1-20260101090000-ABCD')
            ->assertOk()
            ->assertSee('Rekod transaksi tidak dijumpai')
            ->assertDontSee('RM35.00');
    }

    public function test_owner_sees_waiting_page_that_refreshes_itself(): void
    {
        $this->transaksi('Menunggu');

        $this->actingAs($this->penjagaUser)->get('/payment-gateway/selesai?order_id=KAFA1-20260101090000-ABCD')
            ->assertOk()
            ->assertSee('dikemas kini sendiri')
            ->assertSee('cuba=1', false);

        $this->actingAs($this->penjagaUser)->get('/payment-gateway/selesai?order_id=KAFA1-20260101090000-ABCD&cuba=12')
            ->assertOk()
            ->assertSee('lebih lama daripada biasa')
            ->assertDontSee('cuba=13', false);
    }

    public function test_failed_page_explains_refund_without_technical_message(): void
    {
        $this->transaksi('Gagal')->update(['msg' => 'Gerbang pembayaran tidak tersedia: 503']);

        $this->actingAs($this->penjagaUser)->get('/payment-gateway/selesai?order_id=KAFA1-20260101090000-ABCD')
            ->assertSee('dipulangkan oleh bank')
            ->assertDontSee('503');
    }
}
