<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'ic_number',
        'is_active',
        'wajib_tukar_kata_laluan',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'wajib_tukar_kata_laluan' => 'boolean',
        ];
    }

    /**
     * Guna templat emel tersuai (boleh disunting Admin di Tetapan > Templat
     * Emel) untuk notifikasi reset kata laluan, menggantikan versi lalai
     * Laravel (Bahasa Inggeris, tidak bertemplat).
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Papan pemuka mengikut peranan pengguna selepas log masuk.
     */
    public function dashboardRoute(): string
    {
        return match (true) {
            $this->hasRole('Admin') => 'dashboard.admin',
            $this->hasRole('Akauntan') => 'dashboard.akauntan',
            $this->hasRole('Guru') => 'dashboard.guru',
            $this->hasRole('Penjaga') => 'portal.index',
            default => 'dashboard',
        };
    }

    /**
     * Mendapatkan hubungan dengan model Penjaga.
     */
    public function penjaga(): HasOne
    {
        return $this->hasOne(Penjaga::class, 'user_id');
    }
}
