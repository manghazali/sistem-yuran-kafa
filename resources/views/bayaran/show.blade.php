<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Butiran Bayaran') }} {{ $bayaran->no_bayaran }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-3">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Pelajar:</span> {{ $bayaran->pelajar->nama ?? '-' }}</div>
                    <div><span class="text-gray-500">Keterangan:</span> {{ $bayaran->keterangan }}</div>
                    <div><span class="text-gray-500">Amaun:</span> RM{{ number_format($bayaran->amaun, 2) }}</div>
                    <div><span class="text-gray-500">Kaedah:</span> {{ $bayaran->kaedah_bayaran }}</div>
                    <div><span class="text-gray-500">Tarikh:</span> {{ $bayaran->tarikh_bayaran?->format('d/m/Y') }}</div>
                    <div><span class="text-gray-500">No. Rujukan:</span> {{ $bayaran->no_rujukan ?? '-' }}</div>
                    @if ($bayaran->no_pesanan_gateway)
                        <div><span class="text-gray-500">No. Pesanan Gerbang Pembayaran (DOKU):</span> {{ $bayaran->no_pesanan_gateway }}</div>
                    @endif
                    <div><span class="text-gray-500">Dicatat oleh:</span> {{ $bayaran->pencatat->name ?? 'Sistem (Online)' }}</div>
                </div>

                @if ($bayaran->catatan)
                    <div class="text-sm pt-2 border-t"><span class="text-gray-500">Catatan:</span> {{ $bayaran->catatan }}</div>
                @endif

                <div class="pt-4 flex gap-3">
                    <a href="{{ route('resit.show', $bayaran) }}" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md">Lihat / Cetak Resit</a>
                    <a href="{{ route('bayaran.index') }}" class="px-4 py-2 text-sm text-gray-600">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
