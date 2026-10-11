<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Status Pembayaran') }}</h2>
    </x-slot>

    <div class="py-2">
        <p class="text-sm text-gray-500 mb-4">
            Senarai percubaan bayaran melalui gateway pembayaran, termasuk yang masih menunggu atau gagal.
            Bayaran yang berjaya juga direkodkan dalam <a href="{{ route('portal.sejarah-bayaran') }}" class="text-blue-600 hover:underline">Sejarah Bayaran</a>.
        </p>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-50">
                    <tr>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                        <x-th-sortable lajur="created_at" :urut-semasa="$urut" :arah-semasa="$arah">Tarikh</x-th-sortable>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Butiran</th>
                        <x-th-sortable lajur="status" :urut-semasa="$urut" :arah-semasa="$arah">Status</x-th-sortable>
                        <x-th-sortable lajur="amaun" :urut-semasa="$urut" :arah-semasa="$arah" align="right">Amaun (RM)</x-th-sortable>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($transaksi as $item)
                        @php
                            $warna = match ($item->status) {
                                'Berjaya' => 'bg-green-100 text-green-800',
                                'Gagal' => 'bg-red-100 text-red-800',
                                default => 'bg-yellow-100 text-yellow-800',
                            };
                        @endphp
                        <tr>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($transaksi->currentPage() - 1) * $transaksi->perPage() }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            <td class="border border-gray-200 px-6 py-2 text-sm">
                                {{ $item->itemBayaran->pluck('keterangan')->implode(' • ') }}
                                <p class="text-xs text-gray-400">{{ $item->itemBayaran->pluck('pelajar.nama')->unique()->implode(', ') }}</p>
                            </td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full {{ $warna }}">{{ $item->status }}</span>
                            </td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-right text-sm font-medium">RM{{ number_format($item->amaun, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="border border-gray-200 px-6 py-2 text-center text-sm text-gray-500">Tiada percubaan bayaran direkodkan lagi.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="p-4">{{ $transaksi->links('vendor.pagination.compact') }}</div>
        </div>
    </div>
</x-app-layout>
