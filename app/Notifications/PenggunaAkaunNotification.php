<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\EmelTemplat;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PenggunaAkaunNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected User $pengguna,
        protected string $kataLaluan
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $baris = EmelTemplat::baris('akaun-pengguna', [
            '{NAMA}' => $this->pengguna->name,
            '{EMEL}' => $this->pengguna->email,
            '{KATA_LALUAN}' => $this->kataLaluan,
            '{PERANAN}' => $this->pengguna->getRoleNames()->implode(', ') ?: '-',
        ]);

        $mesej = (new MailMessage)->subject(EmelTemplat::subjek('akaun-pengguna'));

        foreach ($baris as $satuBaris) {
            $mesej->line($satuBaris);
        }

        return $mesej->action('Log Masuk', url('/login'))
            ->salutation('Terima kasih, Pihak Sekolah — SISTEM PENGURUSAN YURAN PELAJAR KAFA');
    }
}
