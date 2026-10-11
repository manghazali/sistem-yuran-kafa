<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sejarah Bayaran') }}</h2>
    </x-slot>

    <div class="py-2">
        @if ($anak->count() > 1)
            <div class="mb-4">
                <form method="GET" class="flex gap-3">
                    <x-select name="pelajar_id" onchange="this.form.submit()">
                        <option value="">Semua Anak</option>
                        @foreach ($anak as $pelajar)
                            <option value="{{ $pelajar->id }}" @selected($pelajarDipilih?->id === $pelajar->id)>{{ $pelajar->nama }}</option>
                        @endforeach
                    </x-select>
                </form>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-50">
                    <tr>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                        <x-th-sortable lajur="no_bayaran" :urut-semasa="$urut" :arah-semasa="$arah">No. Bayaran</x-th-sortable>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Anak</th>
                        <x-th-sortable lajur="keterangan" :urut-semasa="$urut" :arah-semasa="$arah">Keterangan</x-th-sortable>
                        <x-th-sortable lajur="amaun" :urut-semasa="$urut" :arah-semasa="$arah">Amaun (RM)</x-th-sortable>
                        <x-th-sortable lajur="tarikh_bayaran" :urut-semasa="$urut" :arah-semasa="$arah">Tarikh</x-th-sortable>
                        <th class="border border-gray-200 px-6 py-2 text-center text-sm font-medium text-gray-500">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($bayaran as $item)
                        <tr>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($bayaran->currentPage() - 1) * $bayaran->perPage() }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->no_bayaran }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->pelajar->nama ?? '-' }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->keterangan }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm font-medium text-green-600">{{ number_format($item->amaun, 2) }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->tarikh_bayaran?->format('d/m/Y') }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-center text-sm">
                                <a href="{{ route('resit.show', $item) }}" class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1 text-sm font-small text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Resit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="border border-gray-200 px-6 py-2 text-center text-sm text-gray-500">
                                {{ $pelajarDipilih ? 'Tiada bayaran direkodkan lagi untuk '.$pelajarDipilih->nama.'.' : 'Tiada bayaran direkodkan lagi.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="p-4">{{ $bayaran->links('vendor.pagination.compact') }}</div>
        </div>
    </div>
</x-app-layout>
