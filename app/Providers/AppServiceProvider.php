<?php

namespace App\Providers;

use App\Models\Bayaran;
use App\Models\LogAudit;
use App\Models\LogEmel;
use App\Models\Notifikasi;
use App\Models\Pelajar;
use App\Models\Tetapan;
use App\Observers\BayaranObserver;
use App\Observers\PelajarObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Bayaran::observe(BayaranObserver::class);
        Pelajar::observe(PelajarObserver::class);

        $this->konfigurasiEmelDinamik();

        View::composer('layouts.app', function ($view) {
            if (! auth()->check()) {
                return;
            }

            $view->with([
                'notifikasiBelumDibaca' => Notifikasi::where('user_id', auth()->id())->belumDibaca()->count(),
                'senaraiNotifikasi' => Notifikasi::where('user_id', auth()->id())->orderByDesc('created_at')->limit(10)->get(),
            ]);
        });

        Event::listen(Login::class, function (Login $event) {
            LogAudit::create([
                'user_id' => $event->user->id,
                'tindakan' => 'Log Masuk',
                'model' => 'User',
                'model_id' => $event->user->id,
                'keterangan' => $event->user->name.' log masuk ke sistem.',
                'ip_address' => request()?->ip(),
            ]);
        });

        // Rekod setiap emel yang dihantar oleh sistem (mana-mana Mail/Notification)
        // supaya Admin boleh semak semula dalam Log Emel.
        Event::listen(MessageSent::class, function (MessageSent $event) {
            $mesej = $event->message;

            foreach ($mesej->getTo() ?? [] as $penerima) {
                LogEmel::create([
                    'kepada' => $penerima->getAddress(),
                    'nama_penerima' => $penerima->getName() ?: null,
                    'subjek' => (string) $mesej->getSubject(),
                    'kandungan_html' => $mesej->getHtmlBody(),
                    'kandungan_teks' => $mesej->getTextBody(),
                ]);
            }
        });
    }

    /**
     * Timpa tetapan mail (config/mail.php) dengan nilai dari Tetapan
     * (dikonfigurasi Admin melalui Tetapan Emel), jika ada. Dibalut
     * dalam try/catch kerana boot() ini juga berjalan semasa artisan
     * command lain (cth: migrate) sebelum jadual tetapan wujud.
     */
    protected function konfigurasiEmelDinamik(): void
    {
        try {
            if (! Schema::hasTable('tetapan')) {
                return;
            }

            $driver = Tetapan::get('emel_driver');
        } catch (\Throwable) {
            return;
        }

        if (! $driver) {
            return;
        }

        config(['mail.default' => $driver]);

        if ($driver === 'smtp') {
            $enkripsi = Tetapan::get('emel_enkripsi', 'tls');

            config([
                'mail.mailers.smtp.host' => Tetapan::get('emel_host') ?: config('mail.mailers.smtp.host'),
                'mail.mailers.smtp.port' => Tetapan::get('emel_port') ?: config('mail.mailers.smtp.port'),
                'mail.mailers.smtp.username' => Tetapan::get('emel_username') ?: config('mail.mailers.smtp.username'),
                'mail.mailers.smtp.password' => Tetapan::get('emel_password') ?: config('mail.mailers.smtp.password'),
                'mail.mailers.smtp.encryption' => $enkripsi === 'none' ? null : $enkripsi,
            ]);
        }

        config([
            'mail.from.address' => Tetapan::get('emel_alamat_dari') ?: config('mail.from.address'),
            'mail.from.name' => Tetapan::get('emel_nama_dari') ?: config('mail.from.name'),
        ]);
    }
}
