<?php

namespace App\Notifications;

use App\Models\Guru;
use App\Support\EmelTemplat;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuruAkaunNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Guru $guru,
        protected string $email,
        protected string $kataLaluan
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $baris = EmelTemplat::baris('akaun-guru', [
            '{NAMA}' => $this->guru->nama,
            '{EMEL}' => $this->email,
            '{KATA_LALUAN}' => $this->kataLaluan,
        ]);

        $mesej = (new MailMessage)->subject(EmelTemplat::subjek('akaun-guru'));

        foreach ($baris as $satuBaris) {
            $mesej->line($satuBaris);
        }

        return $mesej->action('Log Masuk', url('/login'))
            ->salutation('Terima kasih, Pihak Sekolah — SISTEM PENGURUSAN YURAN PELAJAR KAFA');
    }
}
