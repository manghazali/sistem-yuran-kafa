<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Papan Pemuka Admin') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-700">
                    Selamat datang, {{ auth()->user()->name }}
                    ({{ auth()->user()->getRoleNames()->join(', ') }}).
                </p>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                    <div class="bg-gray-50 rounded-lg p-4 border">
                        <p class="text-sm text-gray-500">Jumlah Pelajar</p>
                        <p class="text-2xl font-bold">—</p>
                        <p class="text-xs text-gray-400">Akan datang di Fasa 2</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 border">
                        <p class="text-sm text-gray-500">Baki Belum Dibayar</p>
                        <p class="text-2xl font-bold">—</p>
                        <p class="text-xs text-gray-400">Akan datang di Fasa 4</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 border">
                        <p class="text-sm text-gray-500">Bayaran Hari Ini</p>
                        <p class="text-2xl font-bold">—</p>
                        <p class="text-xs text-gray-400">Akan datang di Fasa 5</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 border">
                        <p class="text-sm text-gray-500">Laporan</p>
                        <p class="text-2xl font-bold">—</p>
                        <p class="text-xs text-gray-400">Akan datang di Fasa 8</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
