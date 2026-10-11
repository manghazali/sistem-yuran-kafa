<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Portal Penjaga') }}</h2>
    </x-slot>

    <div class="py-2 space-y-6">
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

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <p class="text-sm text-gray-500">Jumlah Keseluruhan Baki Belum Dibayar</p>
            <p class="text-3xl font-bold {{ $jumlahKeseluruhanBaki > 0 ? 'text-red-600' : 'text-green-600' }}">
                RM{{ number_format($jumlahKeseluruhanBaki, 2) }}
            </p>
        </div>

        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-3">Anak-Anak Anda</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse ($ringkasan as $data)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                        <p class="font-semibold">{{ $data['pelajar']->nama }}</p>
                        <p class="text-sm text-gray-500">Tahun: {{ $data['pelajar']->darjah ?? '-' }}</p>
                        <p class="text-sm text-gray-500">Kelas: {{ $data['pelajar']->kelas->nama_kelas ?? '-' }}</p>

                        <div class="mt-3 pt-3 border-t flex justify-between items-center">
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

        <div>
            <a href="{{ route('portal.sejarah-bayaran') }}" class="text-sm text-blue-600 hover:underline">Lihat Sejarah Bayaran &rarr;</a>
        </div>
    </div>
</x-app-layout>
