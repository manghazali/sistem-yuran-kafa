<?php

namespace App\Support;

use App\Models\Tetapan;

class EmelTemplat
{
    public const DEFAULT_TEMPLAT = [
        'akaun-guru' => [
            'label' => 'Akaun Guru',
            'keterangan' => 'Dihantar apabila Admin cipta atau reset akaun log masuk untuk seorang Guru.',
            'subjek' => 'Akaun Log Masuk Guru — SISTEM YURAN KAFA',
            'mesej' => "Assalamualaikum / Salam sejahtera, {NAMA},\n\nAkaun log masuk anda untuk Sistem Pengurusan Yuran Pelajar KAFA telah disediakan.\n\nEmel Log Masuk: {EMEL}\nKata Laluan Sementara: {KATA_LALUAN}\n\nSila log masuk dan tukar kata laluan anda melalui menu Profil selepas log masuk kali pertama.",
            'placeholder' => ['{NAMA}', '{EMEL}', '{KATA_LALUAN}'],
        ],
        'akaun-penjaga' => [
            'label' => 'Akaun Penjaga',
            'keterangan' => 'Dihantar apabila Admin cipta atau reset akaun Portal Penjaga untuk seorang Penjaga.',
            'subjek' => 'Akaun Portal Penjaga — SISTEM YURAN KAFA',
            'mesej' => "Assalamualaikum / Salam sejahtera, {NAMA},\n\nAkaun Portal Penjaga anda untuk Sistem Pengurusan Yuran Pelajar KAFA telah disediakan.\n\nEmel Log Masuk: {EMEL}\nKata Laluan Sementara: {KATA_LALUAN}\n\nGuna akaun ini untuk menyemak kalendar yuran dan membuat bayaran bagi anak anda.\n\nSila log masuk dan tukar kata laluan anda melalui menu Profil selepas log masuk kali pertama.",
            'placeholder' => ['{NAMA}', '{EMEL}', '{KATA_LALUAN}'],
        ],
        'reset-password' => [
            'label' => 'Reset Kata Laluan',
            'keterangan' => 'Dihantar apabila pengguna memohon pautan tetapkan semula kata laluan melalui halaman Lupa Kata Laluan.',
            'subjek' => 'Tetapkan Semula Kata Laluan — SISTEM YURAN KAFA',
            'mesej' => "Assalamualaikum / Salam sejahtera, {NAMA},\n\nKami menerima permintaan untuk menetapkan semula kata laluan akaun anda.\n\nSila klik butang di bawah untuk menetapkan kata laluan baharu. Pautan ini akan tamat tempoh dalam {TEMPOH_TAMAT} minit.\n\nJika anda tidak memohon penetapan semula ini, abaikan sahaja emel ini — tiada tindakan lanjut diperlukan.",
            'placeholder' => ['{NAMA}', '{TEMPOH_TAMAT}'],
        ],
        'akaun-pengguna' => [
            'label' => 'Akaun Pengguna',
            'keterangan' => 'Dihantar apabila Admin cipta akaun baharu atau reset kata laluan untuk pengguna (Admin/Akauntan) melalui menu Pengurusan Pengguna.',
            'subjek' => 'Akaun Sistem — SISTEM YURAN KAFA',
            'mesej' => "Assalamualaikum / Salam sejahtera, {NAMA},\n\nAkaun anda ({PERANAN}) untuk Sistem Pengurusan Yuran Pelajar KAFA telah disediakan.\n\nEmel Log Masuk: {EMEL}\nKata Laluan Sementara: {KATA_LALUAN}\n\nSila log masuk dan tukar kata laluan anda melalui menu Profil selepas log masuk kali pertama.",
            'placeholder' => ['{NAMA}', '{EMEL}', '{KATA_LALUAN}', '{PERANAN}'],
        ],
    ];

    public static function subjek(string $kunci): string
    {
        return Tetapan::get("emel_templat_{$kunci}_subjek", self::DEFAULT_TEMPLAT[$kunci]['subjek']);
    }

    public static function mesej(string $kunci): string
    {
        return Tetapan::get("emel_templat_{$kunci}_mesej", self::DEFAULT_TEMPLAT[$kunci]['mesej']);
    }

    /**
     * Baris mesej (selepas placeholder digantikan) untuk disusun sebagai
     * siri ->line() pada MailMessage.
     *
     * @param  array<string, string>  $placeholder
     * @return array<int, string>
     */
    public static function baris(string $kunci, array $placeholder): array
    {
        return explode("\n", strtr(self::mesej($kunci), $placeholder));
    }
}
