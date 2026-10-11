<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Status Bayaran') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 text-center">

                @if (! $transaksi)
                    <div class="text-yellow-600">
                        <p class="text-lg font-semibold">Rekod transaksi tidak dijumpai.</p>
                        <p class="text-sm text-gray-500 mt-2">Jika bayaran anda berjaya, sila semak semula di sejarah bayaran selepas beberapa saat.</p>
                    </div>
                @elseif ($transaksi->status === 'Berjaya')
                    <div class="text-green-600">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <p class="text-lg font-semibold">Bayaran Berjaya!</p>
                        <p class="text-sm text-gray-500 mt-2">RM{{ number_format($transaksi->amaun, 2) }} telah diterima.</p>
                    </div>
                @elseif ($transaksi->status === 'Gagal')
                    <div class="text-red-600">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        <p class="text-lg font-semibold">Bayaran Tidak Berjaya</p>
                        <p class="text-sm text-gray-500 mt-2">{{ $transaksi->msg ?? 'Sila cuba lagi atau hubungi pihak sekolah.' }}</p>
                    </div>
                @else
                    <div class="text-yellow-600">
                        <p class="text-lg font-semibold">Sedang Memproses...</p>
                        <p class="text-sm text-gray-500 mt-2">Status bayaran anda sedang disahkan. Sila semak semula sebentar lagi.</p>
                    </div>
                @endif

                @if ($transaksi && $transaksi->itemBayaran->isNotEmpty())
                    <div class="mt-6 text-left border-t pt-4">
                        <p class="text-xs text-gray-500 uppercase mb-2">Tempoh Diliputi</p>
                        <div class="space-y-1 text-sm">
                            @foreach ($transaksi->itemBayaran as $item)
                                <div class="flex justify-between">
                                    <span>{{ $item->keterangan }} ({{ $item->pelajar->nama ?? '-' }})</span>
                                    <span class="font-medium">RM{{ number_format($item->amaun, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-6">
                    <a href="{{ route('portal.sejarah-bayaran') }}" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md">Lihat Sejarah Bayaran</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
