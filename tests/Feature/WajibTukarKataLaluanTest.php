<?php

namespace Tests\Feature;

use App\Models\Penjaga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Kata laluan sementara mesti ditukar pada log masuk pertama.
 */
class WajibTukarKataLaluanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        foreach (['Admin', 'Penjaga'] as $peranan) {
            Role::create(['name' => $peranan]);
        }
    }

    public function test_new_account_from_email_must_change_password(): void
    {
        $penjaga = Penjaga::create(['nama' => 'Ahmad', 'no_telefon' => '0123', 'hubungan' => 'Bapa']);
        $penjaga->ciptaAtauResetAkaun('ahmad@contoh.my');

        $this->assertTrue($penjaga->fresh()->user->wajib_tukar_kata_laluan);
    }

    public function test_flagged_user_is_sent_to_change_password_page(): void
    {
        $user = User::factory()->create(['wajib_tukar_kata_laluan' => true]);

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('kata-laluan.tukar'));
        $this->actingAs($user)->get(route('kata-laluan.tukar'))->assertOk()->assertSee('Tukar Kata Laluan');
    }

    public function test_changing_password_clears_flag(): void
    {
        $user = User::factory()->create(['password' => Hash::make('sementara123'), 'wajib_tukar_kata_laluan' => true]);

        $this->actingAs($user)->put(route('kata-laluan.tukar.simpan'), [
            'current_password' => 'sementara123',
            'password' => 'KataBaharu#2026',
            'password_confirmation' => 'KataBaharu#2026',
        ])->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertFalse($user->wajib_tukar_kata_laluan);
        $this->assertTrue(Hash::check('KataBaharu#2026', $user->password));
    }

    public function test_wrong_temporary_password_or_same_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('sementara123'), 'wajib_tukar_kata_laluan' => true]);

        $this->actingAs($user)->put(route('kata-laluan.tukar.simpan'), [
            'current_password' => 'salah',
            'password' => 'KataBaharu#2026',
            'password_confirmation' => 'KataBaharu#2026',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put(route('kata-laluan.tukar.simpan'), [
            'current_password' => 'sementara123',
            'password' => 'sementara123',
            'password_confirmation' => 'sementara123',
        ])->assertSessionHasErrors('password');

        $this->assertTrue($user->fresh()->wajib_tukar_kata_laluan);
    }

    public function test_existing_users_are_not_affected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('kata-laluan.tukar'))->assertRedirect(route('dashboard'));
        $this->assertFalse((bool) $user->fresh()->wajib_tukar_kata_laluan);
    }

    public function test_admin_reset_sets_flag(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $pengguna = User::factory()->create();

        $this->actingAs($admin)->post(route('pengguna.reset-kata-laluan', $pengguna));

        $this->assertTrue($pengguna->fresh()->wajib_tukar_kata_laluan);
    }
}
