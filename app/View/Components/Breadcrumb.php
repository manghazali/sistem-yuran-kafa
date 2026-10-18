<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;

class Breadcrumb extends Component
{
    /** @var array<int, array{label: string, url: string|null}> */
    public array $items;

    public function __construct()
    {
        $this->items = $this->build();
    }

    public function render(): View
    {
        return view('components.breadcrumb');
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    protected function build(): array
    {
        $name = request()->route()?->getName();

        if (! $name) {
            return [];
        }

        $penjaga = auth()->user()?->hasRole('Penjaga') ?? false;
        $labelUtama = $penjaga ? __('Portal Penjaga') : __('Halaman Utama');
        $routeUtama = $penjaga ? 'portal.index' : 'dashboard';
        $lamanUtama = $penjaga
            ? ['portal.index']
            : ['dashboard', 'dashboard.admin', 'dashboard.akauntan', 'dashboard.guru'];

        if (in_array($name, $lamanUtama, true)) {
            return [['label' => $labelUtama, 'url' => null]];
        }

        $trail = [['label' => $labelUtama, 'route' => $routeUtama]];

        if ($name === 'resit.show' && $penjaga) {
            $trail = array_merge($trail, [
                ['label' => __('Sejarah Bayaran'), 'route' => 'portal.sejarah-bayaran'],
                ['label' => __('Resit Rasmi'), 'route' => null],
            ]);

            return array_map(fn (array $crumb) => [
                'label' => $crumb['label'],
                'url' => $this->resolveUrl($crumb['route'] ?? null),
            ], $trail);
        }

        $map = $this->map();

        $trail = array_merge($trail, $map[$name] ?? [
            ['label' => Str::headline(str_replace('.', ' ', $name)), 'route' => null],
        ]);

        return array_map(fn (array $crumb) => [
            'label' => $crumb['label'],
            'url' => $this->resolveUrl($crumb['route'] ?? null),
        ], $trail);
    }

    protected function resolveUrl(?string $routeName): ?string
    {
        if (! $routeName || ! Route::has($routeName)) {
            return null;
        }

        try {
            return route($routeName, request()->route()?->parameters() ?? []);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, array<int, array{label: string, route: string|null}>>
     */
    protected function map(): array
    {
        return [
            'guru.index' => [
                ['label' => __('Guru'), 'route' => null],
            ],
            'guru.create' => [
                ['label' => __('Guru'), 'route' => 'guru.index'],
                ['label' => __('Tambah Guru'), 'route' => null],
            ],
            'guru.edit' => [
                ['label' => __('Guru'), 'route' => 'guru.index'],
                ['label' => __('Kemaskini Guru'), 'route' => null],
            ],
            'guru.akaun.store' => [
                ['label' => __('Guru'), 'route' => 'guru.index'],
                ['label' => __('Akaun Log Masuk Berjaya Dicipta'), 'route' => null],
            ],
            'guru.store' => [
                ['label' => __('Guru'), 'route' => 'guru.index'],
                ['label' => __('Akaun Log Masuk Berjaya Dicipta'), 'route' => null],
            ],
            'guru.import' => [
                ['label' => __('Guru'), 'route' => 'guru.index'],
                ['label' => __('Import Excel'), 'route' => null],
            ],

            'penjaga.index' => [
                ['label' => __('Penjaga'), 'route' => null],
            ],
            'penjaga.create' => [
                ['label' => __('Penjaga'), 'route' => 'penjaga.index'],
                ['label' => __('Tambah Penjaga'), 'route' => null],
            ],
            'penjaga.edit' => [
                ['label' => __('Penjaga'), 'route' => 'penjaga.index'],
                ['label' => __('Kemaskini Penjaga'), 'route' => null],
            ],
            'penjaga.akaun.store' => [
                ['label' => __('Penjaga'), 'route' => 'penjaga.index'],
                ['label' => __('Akaun Log Masuk Berjaya Dicipta'), 'route' => null],
            ],
            'penjaga.store' => [
                ['label' => __('Penjaga'), 'route' => 'penjaga.index'],
                ['label' => __('Akaun Log Masuk Berjaya Dicipta'), 'route' => null],
            ],
            'penjaga.import' => [
                ['label' => __('Penjaga'), 'route' => 'penjaga.index'],
                ['label' => __('Import Excel'), 'route' => null],
            ],

            'pengguna.index' => [
                ['label' => __('Pengurusan Pengguna'), 'route' => null],
            ],
            'pengguna.create' => [
                ['label' => __('Pengurusan Pengguna'), 'route' => 'pengguna.index'],
                ['label' => __('Tambah Pengguna'), 'route' => null],
            ],
            'pengguna.edit' => [
                ['label' => __('Pengurusan Pengguna'), 'route' => 'pengguna.index'],
                ['label' => __('Kemaskini Pengguna'), 'route' => null],
            ],
            'pengguna.store' => [
                ['label' => __('Pengurusan Pengguna'), 'route' => 'pengguna.index'],
                ['label' => __('Akaun Berjaya Dicipta'), 'route' => null],
            ],
            'pengguna.reset-kata-laluan' => [
                ['label' => __('Pengurusan Pengguna'), 'route' => 'pengguna.index'],
                ['label' => __('Kata Laluan Berjaya Direset'), 'route' => null],
            ],

            'kelas.index' => [
                ['label' => __('Kelas'), 'route' => null],
            ],
            'kelas.create' => [
                ['label' => __('Kelas'), 'route' => 'kelas.index'],
                ['label' => __('Tambah Kelas'), 'route' => null],
            ],
            'kelas.edit' => [
                ['label' => __('Kelas'), 'route' => 'kelas.index'],
                ['label' => __('Kemaskini Kelas'), 'route' => null],
            ],

            'pelajar.index' => [
                ['label' => __('Pelajar'), 'route' => null],
            ],
            'pelajar.create' => [
                ['label' => __('Pelajar'), 'route' => 'pelajar.index'],
                ['label' => __('Tambah Pelajar'), 'route' => null],
            ],
            'pelajar.edit' => [
                ['label' => __('Pelajar'), 'route' => 'pelajar.index'],
                ['label' => __('Kemaskini Pelajar'), 'route' => null],
            ],
            'pelajar.import' => [
                ['label' => __('Pelajar'), 'route' => 'pelajar.index'],
                ['label' => __('Import Excel'), 'route' => null],
            ],
            'naik-darjah.index' => [
                ['label' => __('Pelajar'), 'route' => 'pelajar.index'],
                ['label' => __('Naik Darjah'), 'route' => null],
            ],

            'kategori-yuran.index' => [
                ['label' => __('Kategori Yuran'), 'route' => null],
            ],
            'kategori-yuran.create' => [
                ['label' => __('Kategori Yuran'), 'route' => 'kategori-yuran.index'],
                ['label' => __('Tambah Kategori Yuran'), 'route' => null],
            ],
            'kategori-yuran.edit' => [
                ['label' => __('Kategori Yuran'), 'route' => 'kategori-yuran.index'],
                ['label' => __('Kemaskini Kategori Yuran'), 'route' => null],
            ],

            'struktur-yuran.index' => [
                ['label' => __('Struktur Yuran'), 'route' => null],
            ],
            'struktur-yuran.create' => [
                ['label' => __('Struktur Yuran'), 'route' => 'struktur-yuran.index'],
                ['label' => __('Tambah Struktur Yuran'), 'route' => null],
            ],
            'struktur-yuran.edit' => [
                ['label' => __('Struktur Yuran'), 'route' => 'struktur-yuran.index'],
                ['label' => __('Kemaskini Struktur Yuran'), 'route' => null],
            ],

            'tetapan-yuran-pelajar.index' => [
                ['label' => __('Tetapan Yuran Pelajar'), 'route' => null],
            ],
            'tetapan-yuran-pelajar.create' => [
                ['label' => __('Tetapan Yuran Pelajar'), 'route' => 'tetapan-yuran-pelajar.index'],
                ['label' => __('Tetapkan Yuran kepada Pelajar'), 'route' => null],
            ],
            'tetapan-yuran-pelajar.edit' => [
                ['label' => __('Tetapan Yuran Pelajar'), 'route' => 'tetapan-yuran-pelajar.index'],
                ['label' => __('Kemaskini Tetapan Yuran'), 'route' => null],
            ],
            'tetapan-yuran-pelajar.pukal' => [
                ['label' => __('Tetapan Yuran Pelajar'), 'route' => 'tetapan-yuran-pelajar.index'],
                ['label' => __('Tetapkan Yuran Secara Pukal'), 'route' => null],
            ],

            'baki-tertunggak.index' => [
                ['label' => __('Laporan Kewangan'), 'route' => 'laporan.index'],
                ['label' => __('Laporan Baki Belum Dibayar'), 'route' => null],
            ],

            'permohonan-bayaran.index' => [
                ['label' => __('Permohonan Bayaran'), 'route' => null],
            ],
            'permohonan-bayaran.create' => [
                ['label' => __('Permohonan Bayaran'), 'route' => 'permohonan-bayaran.index'],
                ['label' => __('Laporkan Bayaran Diterima'), 'route' => null],
            ],

            'bayaran.index' => [
                ['label' => __('Bayaran'), 'route' => null],
            ],
            'bayaran.create' => [
                ['label' => __('Bayaran'), 'route' => 'bayaran.index'],
                ['label' => __('Rekod Bayaran'), 'route' => null],
            ],
            'bayaran.show' => [
                ['label' => __('Bayaran'), 'route' => 'bayaran.index'],
                ['label' => __('Butiran Bayaran'), 'route' => null],
            ],

            'resit.show' => [
                ['label' => __('Bayaran'), 'route' => 'bayaran.index'],
                ['label' => __('Resit Rasmi'), 'route' => null],
            ],

            'portal.yuran.semua' => [
                ['label' => __('Semua Yuran'), 'route' => null],
            ],
            'portal.yuran.belum-bayar' => [
                ['label' => __('Yuran Belum Bayar'), 'route' => null],
            ],
            'portal.yuran.bulanan' => [
                ['label' => __('Yuran Bulanan'), 'route' => null],
            ],
            'portal.status-pembayaran' => [
                ['label' => __('Status Pembayaran'), 'route' => null],
            ],
            'portal.sejarah-bayaran' => [
                ['label' => __('Sejarah Bayaran'), 'route' => null],
            ],
            'portal.kalendar' => [
                ['label' => __('Kalendar Yuran'), 'route' => null],
            ],
            'portal.kalendar.semak' => [
                ['label' => __('Kalendar Yuran'), 'route' => 'portal.kalendar'],
                ['label' => __('Semak Sebelum Bayar'), 'route' => null],
            ],

            'laporan.index' => [
                ['label' => __('Laporan Kewangan'), 'route' => null],
            ],
            'laporan.kutipan' => [
                ['label' => __('Laporan Kewangan'), 'route' => 'laporan.index'],
                ['label' => __('Laporan Kutipan'), 'route' => null],
            ],

            'log-audit.index' => [
                ['label' => __('Log Audit'), 'route' => null],
            ],

            'log-emel.index' => [
                ['label' => __('Log Emel'), 'route' => null],
            ],
            'log-emel.show' => [
                ['label' => __('Log Emel'), 'route' => 'log-emel.index'],
                ['label' => __('Butiran Emel'), 'route' => null],
            ],

            'tetapan.index' => [
                ['label' => __('Tetapan'), 'route' => null],
            ],
            'tetapan.payment-gateway' => [
                ['label' => __('Tetapan'), 'route' => 'tetapan.index'],
                ['label' => __('Tetapan Gateway Pembayaran'), 'route' => null],
            ],
            'tetapan.no-bayaran' => [
                ['label' => __('Tetapan'), 'route' => 'tetapan.index'],
                ['label' => __('Generate No. Bayaran'), 'route' => null],
            ],
            'tetapan.no-resit' => [
                ['label' => __('Tetapan'), 'route' => 'tetapan.index'],
                ['label' => __('Generate No. Resit'), 'route' => null],
            ],
            'tetapan.no-daftar' => [
                ['label' => __('Tetapan'), 'route' => 'tetapan.index'],
                ['label' => __('Generate No. Pendaftaran'), 'route' => null],
            ],
            'tetapan.maklumat-sekolah' => [
                ['label' => __('Tetapan'), 'route' => 'tetapan.index'],
                ['label' => __('Maklumat Sekolah'), 'route' => null],
            ],
            'tetapan.emel' => [
                ['label' => __('Tetapan'), 'route' => 'tetapan.index'],
                ['label' => __('Tetapan Emel'), 'route' => null],
            ],
            'tetapan.emel-templat' => [
                ['label' => __('Tetapan'), 'route' => 'tetapan.index'],
                ['label' => __('Templat Emel'), 'route' => null],
            ],

            'profile.edit' => [
                ['label' => __('Profil'), 'route' => null],
            ],

            'payment-gateway.selesai' => [
                ['label' => __('Status Bayaran'), 'route' => null],
            ],
        ];
    }
}
