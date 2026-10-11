<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Senarai Bayaran') }}</h2>
            <a href="{{ route('bayaran.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                + Rekod Bayaran
            </a>
        </div>
    </x-slot>

    <div class="py-4">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <p class="text-sm text-gray-500">Jumlah Bayaran Hari Ini</p>
                <p class="text-2xl font-bold text-green-600">RM{{ number_format($jumlahHariIni, 2) }}</p>
            </div>

            <form method="GET" class="mb-4 flex gap-3">
                <x-searchable-select
                    name="pelajar_id"
                    :options="$senaraiPelajar->map(fn ($p) => ['value' => $p->id, 'label' => $p->nama.' ('.$p->no_daftar.')'])"
                    :selected="request('pelajar_id')"
                    empty-label="Semua Pelajar"
                    placeholder="Taip untuk cari nama / no. pendaftaran..."
                    class="flex-1"
                />
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md">Tapis</button>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                            <x-th-sortable lajur="no_bayaran" :urut-semasa="$urut" :arah-semasa="$arah">No. Bayaran</x-th-sortable>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Pelajar</th>
                            <x-th-sortable lajur="keterangan" :urut-semasa="$urut" :arah-semasa="$arah">Keterangan</x-th-sortable>
                            <x-th-sortable lajur="amaun" :urut-semasa="$urut" :arah-semasa="$arah">Amaun (RM)</x-th-sortable>
                            <x-th-sortable lajur="kaedah_bayaran" :urut-semasa="$urut" :arah-semasa="$arah">Kaedah</x-th-sortable>
                            <x-th-sortable lajur="tarikh_bayaran" :urut-semasa="$urut" :arah-semasa="$arah">Tarikh</x-th-sortable>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Dicatat Oleh</th>
                            <th class="border border-gray-200 px-6 py-2 text-right text-sm font-medium text-gray-500">Tindakan</th>
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
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->kaedah_bayaran }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->tarikh_bayaran?->format('d/m/Y') }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->pencatat->name ?? 'Sistem (Online)' }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-right text-sm space-x-1">
                                    <a href="{{ route('resit.show', $item) }}" class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1 text-sm font-small text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Resit</a>
                                    <form action="{{ route('bayaran.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Batalkan bayaran ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-1 text-sm font-small text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">Batalkan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="border border-gray-200 px-6 py-2 text-center text-sm text-gray-500">Tiada bayaran direkodkan lagi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="p-4">{{ $bayaran->links('vendor.pagination.compact') }}</div>
            </div>
    </div>
</x-app-layout>
