<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Akaun Berjaya Dicipta') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="p-4 bg-green-50 text-green-800 rounded-md text-sm mb-4">
                    Emel telah dihantar kepada <strong>{{ $pengguna->email }}</strong> dengan butiran log masuk ini.
                </div>

                <div class="p-4 bg-yellow-50 text-yellow-800 rounded-md text-sm mb-6">
                    Kata laluan ini <strong>hanya dipaparkan sekali sahaja</strong> di sini juga.
                    Jika emel tidak sampai, salin dan kongsi secara selamat dengan
                    <strong>{{ $pengguna->name }}</strong> sekarang — ia tidak akan dipaparkan lagi
                    selepas anda tinggalkan halaman ini.
                </div>

                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 rounded-md">
                        <p class="text-xs text-gray-500 uppercase">Emel Log Masuk</p>
                        <p class="text-lg font-mono font-semibold">{{ $pengguna->email }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-md">
                        <p class="text-xs text-gray-500 uppercase">Kata Laluan Sementara</p>
                        <p class="text-lg font-mono font-semibold">{{ $kataLaluan }}</p>
                    </div>

                    <p class="text-xs text-gray-500">
                        Nasihatkan pengguna untuk log masuk di <code>{{ url('/login') }}</code> dan tukar
                        kata laluan mereka selepas log masuk kali pertama (guna menu Profil).
                    </p>
                </div>

                <div class="pt-6">
                    <a href="{{ route('pengguna.index') }}" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md">Kembali ke Pengurusan Pengguna</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
