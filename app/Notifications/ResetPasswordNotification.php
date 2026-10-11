<?php

namespace App\Notifications;

use App\Support\EmelTemplat;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $tempohTamat = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        $baris = EmelTemplat::baris('reset-password', [
            '{NAMA}' => $notifiable->name,
            '{TEMPOH_TAMAT}' => $tempohTamat,
        ]);

        $mesej = (new MailMessage)->subject(EmelTemplat::subjek('reset-password'));

        foreach ($baris as $satuBaris) {
            $mesej->line($satuBaris);
        }

        return $mesej->action('Tetapkan Semula Kata Laluan', $url)
            ->salutation('Terima kasih, Pihak Sekolah — SISTEM PENGURUSAN YURAN PELAJAR KAFA');
    }
}
