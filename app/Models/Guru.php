<?php

namespace App\Models;

use App\Notifications\GuruAkaunNotification;
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
    public function ciptaAtauResetAkaun(string $email): string
    {
        $kataLaluanBaharu = Str::password(10);

        if ($this->user_id) {
            $user = User::findOrFail($this->user_id);
            $user->update([
                'email' => $email,
                'password' => Hash::make($kataLaluanBaharu),
            ]);
        } else {
            $user = User::create([
                'name' => $this->nama,
                'email' => $email,
                'password' => Hash::make($kataLaluanBaharu),
                'phone' => $this->no_telefon,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('Guru');

            $this->update(['user_id' => $user->id]);
        }

        $user->notify(new GuruAkaunNotification($this, $email, $kataLaluanBaharu));

        return $kataLaluanBaharu;
    }
}
