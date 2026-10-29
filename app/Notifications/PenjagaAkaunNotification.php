<?php

namespace App\Notifications;

use App\Models\Penjaga;
use App\Support\EmelTemplat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Middleware\RateLimited;

class PenjagaAkaunNotification extends Notification implements ShouldQueue
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

    /**
     * Dihadkan ke limiter 'emel-harian' (lihat AppServiceProvider) supaya
     * import pukal (cth: >100 Penjaga sekali gus) tidak lebihi had emel
     * harian pembekal SMTP — lebihan dijadualkan semula automatik untuk
     * hari berikutnya.
     */
    public function middleware(object $notifiable, string $channel): array
    {
        return [new RateLimited('emel-harian')];
    }

    /**
     * Guna had masa (bukan had cubaan) supaya job yang 'release' berulang
     * kali akibat had kadar tidak ditandakan gagal sebelum sempat dihantar
     * pada hari berikutnya.
     */
    public function retryUntil(): \DateTimeInterface
    {
        return now()->addDays(config('kafa.emel_cuba_hari'));
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
