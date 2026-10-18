<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $tajuk }}</h2>
    </x-slot>

    <div class="py-2">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-50">
                    <tr>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                        <x-th-sortable lajur="anak" :urut-semasa="$urut" :arah-semasa="$arah">Anak</x-th-sortable>
                        <x-th-sortable lajur="nama" :urut-semasa="$urut" :arah-semasa="$arah">Yuran</x-th-sortable>
                        <x-th-sortable lajur="status" :urut-semasa="$urut" :arah-semasa="$arah">Status</x-th-sortable>
                        <x-th-sortable lajur="amaun" :urut-semasa="$urut" :arah-semasa="$arah" align="right">Amaun (RM)</x-th-sortable>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($senaraiItem as $item)
                        @php
                            $warna = match ($item['status']) {
                                'Dibayar' => 'bg-green-100 text-green-800',
                                'Sebahagian' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-red-100 text-red-800',
                            };
                        @endphp
                        <tr>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($senaraiItem->currentPage() - 1) * $senaraiItem->perPage() }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item['pelajar']->nama }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item['nama'] }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full {{ $warna }}">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                RM{{ number_format($item['status'] === 'Dibayar' ? $item['amaun'] : $item['baki'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="border border-gray-200 px-6 py-2 text-center text-sm text-gray-500">Tiada rekod yuran dijumpai.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="p-4">{{ $senaraiItem->links('vendor.pagination.compact') }}</div>
        </div>
    </div>
</x-app-layout>
