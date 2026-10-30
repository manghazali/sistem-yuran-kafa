<?php

namespace App\Models;

use App\Notifications\GuruAkaunNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Guru extends Model
{
    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nama',
        'no_pekerja',
        'no_kp',
        'no_telefon',
        'email',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Semua penugasan Kelas + Tahun bagi guru ini sebagai Guru Kelas —
     * seorang guru boleh jadi Guru Kelas bagi beberapa Kelas sekali gus.
     */
    public function guruKelas(): HasMany
    {
        return $this->hasMany(GuruKelas::class, 'guru_id');
    }

    /**
     * Cipta (atau reset) akaun log masuk untuk guru ini, hantar emel berisi
     * kata laluan sementara, dan pulangkan kata laluan itu (untuk paparan sekali).
     */
    /**
     * Ada emel tetapi belum ada akaun log masuk — menunggu butang "Hantar Emel Akaun".
     */
    public function scopeBelumAdaAkaun(Builder $query): Builder
    {
        return $query->whereNull('user_id')->whereNotNull('email')->where('email', '!=', '');
    }

    public function ciptaAtauResetAkaun(string $email): string
    {
        $kataLaluanBaharu = Str::password(10);

        if ($this->user_id) {
            $user = User::findOrFail($this->user_id);
            $user->update([
                'email' => $email,
                'password' => Hash::make($kataLaluanBaharu),
                'wajib_tukar_kata_laluan' => true,
            ]);
        } else {
            $user = User::create([
                'name' => $this->nama,
                'email' => $email,
                'password' => Hash::make($kataLaluanBaharu),
                'wajib_tukar_kata_laluan' => true,
                'phone' => $this->no_telefon,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('Guru');

            $this->update(['user_id' => $user->id]);
        }

        $this->update(['email' => $email]);

        $user->notify(new GuruAkaunNotification($this, $email, $kataLaluanBaharu));

        return $kataLaluanBaharu;
    }
}
