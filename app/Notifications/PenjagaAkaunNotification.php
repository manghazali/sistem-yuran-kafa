<?php

namespace App\Notifications;

use App\Models\Penjaga;
use App\Support\EmelTemplat;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PenjagaAkaunNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Penjaga $penjaga,
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
        $baris = EmelTemplat::baris('akaun-penjaga', [
            '{NAMA}' => $this->penjaga->nama,
            '{EMEL}' => $this->email,
            '{KATA_LALUAN}' => $this->kataLaluan,
        ]);

        $mesej = (new MailMessage)->subject(EmelTemplat::subjek('akaun-penjaga'));

        foreach ($baris as $satuBaris) {
            $mesej->line($satuBaris);
        }

        return $mesej->action('Log Masuk', url('/login'))
            ->salutation('Terima kasih, Pihak Sekolah — SISTEM PENGURUSAN YURAN PELAJAR KAFA');
    }
}
