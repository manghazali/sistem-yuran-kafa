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
                        <p class="text-sm text-gray-500 mt-2">Bayaran tidak diterima. Jika wang telah ditolak daripada akaun anda, ia akan dipulangkan oleh bank anda. Anda boleh cuba bayar semula.</p>
                    </div>
                @else
                    <div class="text-yellow-600">
                        <p class="text-lg font-semibold">Sedang Memproses...</p>
                        @if ($muatSemula)
                            <p class="text-sm text-gray-500 mt-2">Status bayaran anda sedang disahkan. Halaman ini akan dikemas kini sendiri — jangan bayar semula.</p>
                            <script>setTimeout(() => window.location.replace(@json($muatSemula)), 5000);</script>
                        @else
                            <p class="text-sm text-gray-500 mt-2">Pengesahan mengambil masa lebih lama daripada biasa. Resit akan muncul di tab Bayaran sebaik sahaja bayaran disahkan — jangan bayar semula.</p>
                        @endif
                    </div>
                @endif

                @if ($senaraiBayaran->isNotEmpty())
                    <div class="mt-6 space-y-2">
                        @foreach ($senaraiBayaran as $bayaran)
                            <a href="{{ route('resit.show', $bayaran) }}"
                               class="flex w-full items-center justify-center rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white hover:bg-green-700">
                                Lihat Resit{{ $senaraiBayaran->count() > 1 ? ' — '.($bayaran->pelajar->nama ?? '-') : '' }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
