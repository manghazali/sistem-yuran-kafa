<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Portal Penjaga') }}</h2>
    </x-slot>

    @php
        $bulanBelumDibayar = $ringkasan->sum('bilangan_bulan_belum_dibayar');
    @endphp

    {{-- Paparan telefon: gaya aplikasi (kad baki, ruang, senarai anak) --}}
    <div class="lg:hidden space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Hai, {{ $penjaga->nama }}</h2>
            <p class="text-sm text-gray-500">Yuran anak-anak anda, semua di satu tempat.</p>
        </div>

        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-700 to-green-500 p-6 text-white shadow-lg shadow-green-700/20">
            <div class="absolute -right-10 -top-12 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="relative">
                <p class="text-xs font-semibold uppercase tracking-wider text-green-100">Baki belum dibayar · {{ now()->year }}</p>
                <p class="mt-1 text-4xl font-bold">RM{{ number_format($jumlahKeseluruhanBaki, 2) }}</p>

                <div class="mt-5 grid grid-cols-2 divide-x divide-white/25 border-t border-white/25 pt-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-green-100">Anak</p>
                        <p class="text-lg font-bold">{{ $ringkasan->count() }}</p>
                    </div>
                    <div class="pl-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-green-100">Bulan belum bayar</p>
                        <p class="text-lg font-bold">{{ $bulanBelumDibayar }}</p>
                    </div>
                </div>
            </div>
        </div>

        <a href="{{ route('portal.yuran.semua') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-green-700">
            Lihat butiran yuran <i class="fas fa-chevron-right text-xs"></i>
        </a>

        <div>
            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Ruang anda</p>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('portal.yuran.belum-bayar') }}" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-600">
                        <i class="fas fa-file-invoice-dollar text-lg"></i>
                        @if ($bulanBelumDibayar > 0)
                            <span class="absolute -right-2 -top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $bulanBelumDibayar }}</span>
                        @endif
                    </div>
                    <p class="mt-3 font-semibold text-gray-900">Belum Bayar</p>
                    <p class="text-xs text-gray-500">{{ $bulanBelumDibayar > 0 ? $bulanBelumDibayar.' bulan tertunggak' : 'Semua sudah dibayar' }}</p>
                </a>
                <a href="{{ route('portal.yuran.bulanan') }}" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <i class="fas fa-calendar-days text-lg"></i>
                    </div>
                    <p class="mt-3 font-semibold text-gray-900">Yuran Bulanan</p>
                    <p class="text-xs text-gray-500">Ikut bulan</p>
                </a>
                <a href="{{ route('portal.status-pembayaran') }}" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="fas fa-credit-card text-lg"></i>
                    </div>
                    <p class="mt-3 font-semibold text-gray-900">Status Bayaran</p>
                    <p class="text-xs text-gray-500">Bayaran dalam talian</p>
                </a>
                <a href="{{ route('portal.sejarah-bayaran') }}" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-green-700">
                        <i class="fas fa-receipt text-lg"></i>
                    </div>
                    <p class="mt-3 font-semibold text-gray-900">Sejarah</p>
                    <p class="text-xs text-gray-500">Bayaran &amp; resit</p>
                </a>
            </div>
        </div>

        <div>
            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Anak-anak anda</p>
            <div class="space-y-3">
                @forelse ($ringkasan as $data)
                    <a href="{{ route('portal.kalendar', $data['pelajar']) }}" class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-700">
                            <i class="fas fa-user-graduate text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-gray-900">{{ $data['pelajar']->nama }}</p>
                            <p class="text-xs text-gray-500">{{ $data['pelajar']->darjah ?? '-' }} · {{ $data['pelajar']->kelas->nama_kelas ?? '-' }}</p>
                            <p class="mt-1 text-sm font-bold {{ $data['jumlah_baki'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $data['jumlah_baki'] > 0 ? 'Baki RM'.number_format($data['jumlah_baki'], 2) : 'Tiada baki' }}
                            </p>
                        </div>
                        <span class="flex-shrink-0 rounded-full bg-green-600 px-3 py-1.5 text-xs font-semibold text-white">Bayar</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">Tiada rekod anak dipautkan kepada akaun anda. Sila hubungi pihak sekolah.</p>
                @endforelse
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

        <div class="overflow-hidden shadow-sm sm:rounded-lg p-6 border {{ $jumlahKeseluruhanBaki > 0 ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }}">
            <p class="text-sm text-gray-600">Jumlah Keseluruhan Baki Belum Dibayar</p>
            <p class="text-3xl font-bold {{ $jumlahKeseluruhanBaki > 0 ? 'text-red-600' : 'text-green-600' }}">
                RM{{ number_format($jumlahKeseluruhanBaki, 2) }}
            </p>
        </div>

        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-3">Anak-Anak Anda</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse ($ringkasan as $data)
                    <div class="overflow-hidden shadow-sm sm:rounded-lg p-5 border {{ $data['jumlah_baki'] > 0 ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }}">
                        <p class="font-semibold">{{ $data['pelajar']->nama }}</p>
                        <p class="text-sm text-gray-600">Tahun: {{ $data['pelajar']->darjah ?? '-' }}</p>
                        <p class="text-sm text-gray-600">Kelas: {{ $data['pelajar']->kelas->nama_kelas ?? '-' }}</p>

                        <div class="mt-3 pt-3 border-t {{ $data['jumlah_baki'] > 0 ? 'border-red-200' : 'border-green-200' }} flex justify-between items-center">
                            <div>
                                <p class="text-xs text-gray-500">Baki Belum Dibayar</p>
                                <p class="font-bold {{ $data['jumlah_baki'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    RM{{ number_format($data['jumlah_baki'], 2) }}
                                </p>
                            </div>
                            <a href="{{ route('portal.kalendar', $data['pelajar']) }}" class="text-sm bg-green-600 text-white px-3 py-1.5 rounded-md hover:bg-green-700">
                                Kalendar Yuran
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Tiada rekod anak dipautkan kepada akaun anda. Sila hubungi pihak sekolah.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
