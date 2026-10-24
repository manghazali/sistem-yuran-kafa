<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Portal Penjaga') }}</h2>
    </x-slot>

    @php
        $yuranBelumDibayar = $ringkasan->sum('bilangan_yuran_belum_dibayar');
        // Satu butang bayar untuk semua anak — muncul bila mana-mana anak ada baki.
        $bolehBayarSemua = $ringkasan->contains(fn ($d) => $d['jumlah_baki'] > 0);
    @endphp

    {{-- Paparan telefon: gaya aplikasi (kad baki, ruang, senarai anak) --}}
    <div class="lg:hidden space-y-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Hai, {{ $penjaga->nama }}</h2>
            <p class="mt-1 text-sm leading-snug text-gray-500">Pendidikan adalah pelaburan terbaik untuk masa depan anak-anak anda. Pastikan yuran sentiasa kemas kini.</p>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-700 to-green-500 px-5 py-4 text-white shadow-lg shadow-green-700/20">
            <div class="absolute -right-10 -top-12 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="relative">
                <p class="text-xs font-semibold uppercase tracking-wider text-green-100">Baki belum dibayar · {{ now()->year }}</p>
                <p class="text-3xl font-bold">RM{{ number_format($jumlahKeseluruhanBaki, 2) }}</p>

                <div class="mt-3 grid grid-cols-2 divide-x divide-white/25 border-t border-white/25 pt-2">
                    <div>
                        <p class="text-[13px] text-green-100">Anak</p>
                        <p class="text-lg font-bold">{{ $ringkasan->count() }}</p>
                    </div>
                    <div class="pl-4">
                        <p class="whitespace-nowrap text-[13px] text-green-100">Yuran belum bayar</p>
                        <p class="text-lg font-bold">{{ $yuranBelumDibayar }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Anak-anak anda</p>
            <div class="space-y-2">
                @forelse ($ringkasan as $data)
                    <a href="{{ route('portal.anak', $data['pelajar']) }}" class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-3 py-2.5 shadow-sm">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-700">
                            <i class="fas fa-user-graduate text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-base font-semibold text-gray-900">{{ $data['pelajar']->nama }}</p>
                            <p class="text-[13px] text-gray-500">{{ $data['pelajar']->darjah ?? '-' }} · {{ $data['pelajar']->kelas->nama_kelas ?? '-' }}</p>
                            <p class="text-sm font-bold {{ $data['jumlah_baki'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $data['jumlah_baki'] > 0 ? 'Baki RM'.number_format($data['jumlah_baki'], 2) : 'Tiada baki' }}
                            </p>
                        </div>
                        <i class="fas fa-chevron-right flex-shrink-0 text-sm text-gray-400"></i>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">Tiada rekod anak dipautkan kepada akaun anda. Sila hubungi pihak sekolah.</p>
                @endforelse
            </div>

            @if ($bolehBayarSemua)
                <a href="{{ route('portal.bayar-semua') }}" class="mt-3 flex w-full items-center justify-center rounded-full bg-green-600 py-3 text-[15px] font-semibold text-white shadow-sm">
                    Bayar Semua Yuran
                </a>
            @endif
        </div>

        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Bantuan</p>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('portal.bantuan') }}" class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                    <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-green-50 text-green-700">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <p class="mt-2 text-[15px] font-semibold text-gray-900">Cara Guna</p>
                    <p class="text-[13px] leading-snug text-gray-500">Panduan langkah demi langkah</p>
                </a>
                <a href="{{ route('portal.bantuan', ['bahagian' => 'soalan']) }}" class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                    <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="fas fa-circle-question"></i>
                    </div>
                    <p class="mt-2 text-[15px] font-semibold text-gray-900">Soalan Lazim</p>
                    <p class="text-[13px] leading-snug text-gray-500">Jawapan untuk masalah biasa</p>
                </a>
            </div>
        </div>
    </div>

    {{-- Paparan desktop --}}
    <div class="hidden lg:block py-2 space-y-6">
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-green-700 to-green-500 p-8 shadow-sm">
            <div class="absolute -right-8 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute right-20 -bottom-8 h-28 w-28 rounded-full bg-white/10"></div>

            <div class="relative flex items-center justify-between gap-6">
                <div>
                    <h2 class="text-2xl font-bold text-white">Selamat Datang, {{ $penjaga->nama }}!</h2>
                    <p class="mt-2 text-sm text-green-100 max-w-md">Pendidikan adalah pelaburan terbaik untuk masa depan anak-anak anda. Pastikan yuran sentiasa kemas kini.</p>
                </div>
                <div class="hidden sm:flex h-24 w-24 items-center justify-center rounded-full bg-white/15 flex-shrink-0">
                    <i class="fas fa-graduation-cap text-5xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between gap-6 overflow-hidden shadow-sm sm:rounded-lg p-6 border {{ $jumlahKeseluruhanBaki > 0 ? 'bg-white border-gray-200 border-l-4 border-l-red-500' : 'bg-green-50 border-green-200' }}">
            <div>
                <p class="text-sm text-gray-600">Jumlah Keseluruhan Baki Belum Dibayar</p>
                <p class="text-3xl font-bold {{ $jumlahKeseluruhanBaki > 0 ? 'text-red-600' : 'text-green-600' }}">
                    RM{{ number_format($jumlahKeseluruhanBaki, 2) }}
                </p>
            </div>
            @if ($bolehBayarSemua)
                <a href="{{ route('portal.bayar-semua') }}" class="group inline-flex flex-shrink-0 items-center gap-2 rounded-full bg-green-600 px-7 py-3 text-base font-semibold text-white shadow-md shadow-green-700/20 transition hover:bg-green-700">
                    Bayar Semua Yuran
                    <i class="fas fa-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
                </a>
            @endif
        </div>

        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-3">Anak-Anak Anda</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse ($ringkasan as $data)
                    <a href="{{ route('portal.anak', $data['pelajar']) }}" class="block overflow-hidden bg-white shadow-sm sm:rounded-lg p-5 border border-gray-200 border-l-4 transition hover:shadow-md {{ $data['jumlah_baki'] > 0 ? 'border-l-red-500' : 'border-l-green-500' }}">
                        <p class="font-semibold">{{ $data['pelajar']->nama }}</p>
                        <p class="text-sm text-gray-600">Tahun: {{ $data['pelajar']->darjah ?? '-' }}</p>
                        <p class="text-sm text-gray-600">Kelas: {{ $data['pelajar']->kelas->nama_kelas ?? '-' }}</p>

                        <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                            <div>
                                <p class="text-xs text-gray-500">Baki Belum Dibayar</p>
                                <p class="font-bold {{ $data['jumlah_baki'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    RM{{ number_format($data['jumlah_baki'], 2) }}
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-gray-600">
                                Lihat <i class="fas fa-chevron-right text-xs"></i>
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">Tiada rekod anak dipautkan kepada akaun anda. Sila hubungi pihak sekolah.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
