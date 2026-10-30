<?php

namespace Tests\Feature;

use App\Jobs\HantarEmelAkaunPukal;
use App\Models\Guru;
use App\Models\LogEmel;
use App\Models\Penjaga;
use App\Models\User;
use App\Notifications\GuruAkaunNotification;
use App\Notifications\PenjagaAkaunNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Import tanpa menghantar emel, kemudian butang "Hantar Emel Akaun".
 */
class HantarEmelAkaunTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        foreach (['Admin', 'Guru', 'Penjaga'] as $peranan) {
            Role::create(['name' => $peranan]);
        }
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
    }

    private function csvPenjaga(): UploadedFile
    {
        $isi = "nama,no_telefon,hubungan,emel\nAhmad,0123456789,Bapa,ahmad@contoh.my\nSiti,0198765432,Ibu,siti@contoh.my\n";

        return UploadedFile::fake()->createWithContent('penjaga.csv', $isi);
    }

    public function test_import_without_option_saves_email_but_sends_nothing(): void
    {
        $this->actingAs($this->admin)->post('/penjaga-import', ['fail' => $this->csvPenjaga()])->assertRedirect();

        $this->assertSame(2, Penjaga::belumAdaAkaun()->count());
        $this->assertSame(0, User::role('Penjaga')->count());
        Notification::assertNothingSent();
    }

    public function test_import_never_sends_even_if_old_option_is_posted(): void
    {
        $this->actingAs($this->admin)->post('/penjaga-import', ['fail' => $this->csvPenjaga(), 'hantar_emel' => '1']);

        $this->assertSame(0, User::role('Penjaga')->count());
        Notification::assertNothingSent();
    }

    public function test_list_page_shows_button_with_pending_count(): void
    {
        $this->actingAs($this->admin)->post('/penjaga-import', ['fail' => $this->csvPenjaga()]);

        $this->actingAs($this->admin)->get('/penjaga')->assertSee('Hantar Emel Akaun (2)');
    }

    public function test_button_queues_the_bulk_job(): void
    {
        Queue::fake();
        Penjaga::create(['nama' => 'Ahmad', 'no_telefon' => '0123', 'email' => 'ahmad@contoh.my', 'hubungan' => 'Bapa']);

        $this->actingAs($this->admin)->from('/penjaga')->post('/penjaga-hantar-emel-akaun')
            ->assertRedirect('/penjaga')
            ->assertSessionHas('status');

        Queue::assertPushed(HantarEmelAkaunPukal::class, fn ($job) => $job->jenis === 'penjaga');
    }

    public function test_bulk_job_creates_accounts_and_skips_shared_email(): void
    {
        Penjaga::create(['nama' => 'Ahmad', 'no_telefon' => '0123', 'email' => 'keluarga@contoh.my', 'hubungan' => 'Bapa']);
        Penjaga::create(['nama' => 'Siti', 'no_telefon' => '0198', 'email' => 'keluarga@contoh.my', 'hubungan' => 'Ibu']);

        (new HantarEmelAkaunPukal('penjaga'))->handle();

        $this->assertSame(1, User::role('Penjaga')->count());
        $this->assertSame(1, Penjaga::belumAdaAkaun()->count());
        Notification::assertSentTimes(PenjagaAkaunNotification::class, 1);
    }

    public function test_teacher_import_keeps_email_and_bulk_job_sends_later(): void
    {
        $isi = "nama,no_pekerja,emel\nCikgu Aminah,001,aminah@contoh.my\n";
        $this->actingAs($this->admin)->post('/guru-import', ['fail' => UploadedFile::fake()->createWithContent('guru.csv', $isi)]);

        $this->assertSame('aminah@contoh.my', Guru::sole()->email);
        Notification::assertNothingSent();

        (new HantarEmelAkaunPukal('guru'))->handle();

        $this->assertNotNull(Guru::sole()->user_id);
        Notification::assertSentTimes(GuruAkaunNotification::class, 1);
    }

    public function test_selected_rows_are_sent_including_resend_to_existing_account(): void
    {
        Queue::fake();
        $a = Penjaga::create(['nama' => 'Ahmad', 'no_telefon' => '0123', 'email' => 'ahmad@contoh.my', 'hubungan' => 'Bapa']);
        $b = Penjaga::create(['nama' => 'Siti', 'no_telefon' => '0198', 'email' => 'siti@contoh.my', 'hubungan' => 'Ibu']);
        Penjaga::create(['nama' => 'Ali', 'no_telefon' => '0111', 'hubungan' => 'Bapa']);

        $this->actingAs($this->admin)->from('/penjaga')->post('/penjaga-hantar-emel-akaun', ['ids' => [$a->id, $b->id]])
            ->assertRedirect('/penjaga');

        Queue::assertPushed(HantarEmelAkaunPukal::class, fn ($job) => $job->id === [$a->id, $b->id]);
    }

    public function test_resend_gives_existing_account_a_new_password(): void
    {
        $penjaga = Penjaga::create(['nama' => 'Ahmad', 'no_telefon' => '0123', 'email' => 'ahmad@contoh.my', 'hubungan' => 'Bapa']);
        $penjaga->ciptaAtauResetAkaun('ahmad@contoh.my');
        $kataLaluanLama = $penjaga->fresh()->user->password;

        (new HantarEmelAkaunPukal('penjaga', [$penjaga->id]))->handle();

        $this->assertNotSame($kataLaluanLama, $penjaga->fresh()->user->password);
        Notification::assertSentTimes(PenjagaAkaunNotification::class, 2);
    }

    public function test_list_shows_status_for_each_row(): void
    {
        Penjaga::create(['nama' => 'Tiada Emel', 'no_telefon' => '0111', 'hubungan' => 'Bapa']);
        Penjaga::create(['nama' => 'Belum', 'no_telefon' => '0122', 'email' => 'belum@contoh.my', 'hubungan' => 'Bapa']);
        $giliran = Penjaga::create(['nama' => 'Giliran', 'no_telefon' => '0133', 'email' => 'giliran@contoh.my', 'hubungan' => 'Bapa']);
        $giliran->ciptaAtauResetAkaun('giliran@contoh.my');
        $dihantar = Penjaga::create(['nama' => 'Dihantar', 'no_telefon' => '0144', 'email' => 'dihantar@contoh.my', 'hubungan' => 'Bapa']);
        $dihantar->ciptaAtauResetAkaun('dihantar@contoh.my');
        LogEmel::create(['kepada' => 'dihantar@contoh.my', 'subjek' => 'Akaun Portal Penjaga — SISTEM YURAN KAFA']);

        $this->actingAs($this->admin)->get('/penjaga')
            ->assertSeeInOrder(['Belum', 'Belum dihantar'])
            ->assertSeeInOrder(['Dihantar', 'Dihantar'])
            ->assertSee('Dalam giliran')
            ->assertSee('Tiada emel')
            ->assertSee('Hantar semula')
            ->assertSee('name="ids[]"', false);
    }
}
