<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Bantuan') }}</h2>
    </x-slot>

    @php
        $bahagian = request('bahagian') === 'soalan' ? 'soalan' : 'cara';

        $namaSekolah = \App\Models\Tetapan::get('sekolah_nama', "KAFA Integrasi Darus Sa'adah");
        $telefonSekolah = \App\Models\Tetapan::get('sekolah_telefon', '03-6038 8306');
        $alamatSekolah = \App\Models\Tetapan::get('sekolah_alamat', "Jalan DC 2/4, Desa Coalfields\n47000 Sungai Buloh\nSelangor");

        $panduan = [
            ['ikon' => 'fa-magnifying-glass', 'tajuk' => 'Semak yuran anak', 'langkah' => [
                'Halaman Utama memaparkan jumlah baki dan senarai anak.',
                'Tekan kad anak untuk melihat semua yuran dan statusnya.',
                'Tab Yuran menyenaraikan yuran semua anak; guna pilihan Semua Anak untuk tapis.',
            ]],
            ['ikon' => 'fa-wallet', 'tajuk' => 'Bayar yuran', 'langkah' => [
                'Di Halaman Utama, tekan Bayar Semua Yuran.',
                'Tandakan yuran yang hendak dibayar (atau Pilih semua), kemudian Semak Sebelum Bayar.',
                'Semak jumlah, tekan Bayar Sekarang dan selesaikan bayaran melalui FPX, kad atau e-wallet.',
            ]],
            ['ikon' => 'fa-receipt', 'tajuk' => 'Dapatkan resit', 'langkah' => [
                'Buka tab Bayaran untuk melihat sejarah bayaran.',
                'Tekan Resit pada bayaran berkenaan.',
                'Tekan Cetak / Simpan PDF untuk mencetak atau menyimpan resit.',
            ]],
            ['ikon' => 'fa-key', 'tajuk' => 'Tukar kata laluan', 'langkah' => [
                'Buka tab Akaun.',
                'Di bahagian Kemaskini Kata Laluan, masukkan kata laluan semasa dan kata laluan baharu, kemudian Simpan.',
            ]],
        ];

        $soalan = [
            ['s' => 'Saya sudah bayar tetapi resit belum muncul?',
             'j' => 'Bayaran mungkin masih diproses oleh gerbang pembayaran. Notis kuning akan dipaparkan di tab Bayaran. Tunggu sebentar dan jangan bayar semula. Jika resit masih tiada selepas 3 jam, hubungi pihak sekolah dengan tarikh dan amaun bayaran.'],
            ['s' => 'Bayaran gagal atau saya batalkan, adakah wang saya ditolak?',
             'j' => 'Bayaran yang gagal atau dibatalkan tidak dikenakan caj. Jika bank anda menunjukkan wang telah ditolak, hubungi pihak sekolah dengan tarikh dan amaun bayaran untuk disemak.'],
            ['s' => 'Boleh saya bayar secara tunai?',
             'j' => 'Boleh. Bayar di kaunter sekolah atau kepada guru kelas. Bayaran akan direkodkan oleh pihak sekolah dan resit akan muncul di tab Bayaran.'],
            ['s' => 'Jumlah yuran salah atau anak saya tidak tersenarai?',
             'j' => 'Maklumat yuran dan pelajar diurus oleh pihak sekolah. Sila hubungi pentadbir sekolah untuk pembetulan.'],
            ['s' => 'Saya terlupa kata laluan?',
             'j' => 'Hubungi pentadbir sekolah untuk menetapkan semula kata laluan. Kata laluan baharu akan dihantar ke emel anda.'],
        ];
    @endphp

    <div class="py-2">
        <x-portal.tajuk-telefon :tajuk="$bahagian === 'soalan' ? 'Soalan Lazim' : 'Cara Guna'"
                                :subtajuk="$bahagian === 'soalan' ? 'Jawapan untuk masalah biasa' : 'Panduan langkah demi langkah'" />

        {{-- Telefon: satu bahagian ikut kad yang ditekan. Desktop: kedua-dua bahagian dipaparkan. --}}
        <div class="mx-auto max-w-3xl space-y-6">
            <div class="{{ $bahagian === 'cara' ? '' : 'hidden lg:block' }}">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Panduan ringkas</p>
                <div class="space-y-3">
                    @foreach ($panduan as $p)
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm lg:rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-700">
                                    <i class="fas {{ $p['ikon'] }}"></i>
                                </div>
                                <p class="text-base font-semibold text-gray-900">{{ $p['tajuk'] }}</p>
                            </div>
                            <ol class="mt-3 space-y-1.5 pl-1 text-sm text-gray-600">
                                @foreach ($p['langkah'] as $i => $langkah)
                                    <li class="flex gap-2">
                                        <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600">{{ $i + 1 }}</span>
                                        <span>{{ $langkah }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="{{ $bahagian === 'soalan' ? '' : 'hidden lg:block' }}">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Soalan lazim</p>
                <div class="divide-y overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm lg:rounded-lg">
                    @foreach ($soalan as $f)
                        <details class="group">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-[15px] font-medium text-gray-900 [&::-webkit-details-marker]:hidden">
                                {{ $f['s'] }}
                                <i class="fas fa-chevron-down flex-shrink-0 text-xs text-gray-400 transition-transform group-open:rotate-180"></i>
                            </summary>
                            <p class="px-4 pb-4 text-sm leading-relaxed text-gray-600">{{ $f['j'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
            {{-- Hubungi sekolah: di bawah Soalan Lazim sahaja (telefon); desktop sentiasa papar --}}
            <div class="{{ $bahagian === 'soalan' ? '' : 'hidden lg:block' }}">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Hubungi sekolah</p>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm lg:rounded-lg">
                    <p class="text-base font-semibold text-gray-900">{{ $namaSekolah }}</p>
                    @foreach (explode("\n", $alamatSekolah) as $baris)
                        <p class="text-sm text-gray-500">{{ trim($baris) }}</p>
                    @endforeach
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $telefonSekolah) }}"
                       class="mt-3 flex w-full items-center justify-center gap-2 rounded-full bg-green-600 py-3 text-[15px] font-semibold text-white shadow-sm lg:w-auto lg:inline-flex lg:px-6">
                        <i class="fas fa-phone"></i> Hubungi {{ $telefonSekolah }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
