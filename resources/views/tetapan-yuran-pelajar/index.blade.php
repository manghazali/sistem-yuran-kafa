<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tetapan Yuran Pelajar') }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('tetapan-yuran-pelajar.pukal') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-100">
                    <i class="fas fa-layer-group mr-2"></i> Tetapkan Pukal
                </a>
                <a href="{{ route('tetapan-yuran-pelajar.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-600">
                    + Tetapkan Yuran
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-2">

            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <form method="GET" class="mb-4 flex gap-3">
                <x-searchable-select
                    name="pelajar_id"
                    :options="$senaraiPelajar->map(fn ($p) => ['value' => $p->id, 'label' => $p->nama.' ('.$p->no_daftar.')'])"
                    :selected="request('pelajar_id')"
                    empty-label="Semua Pelajar"
                    placeholder="Taip untuk cari nama / no. pendaftaran..."
                    class="flex-1"
                />
                <x-searchable-select
                    name="struktur_yuran_id"
                    :options="$senaraiStruktur->map(fn ($s) => ['value' => $s->id, 'label' => $s->nama])"
                    :selected="request('struktur_yuran_id')"
                    empty-label="Semua Struktur Yuran"
                    placeholder="Taip untuk cari struktur yuran..."
                    class="flex-1"
                />
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md">Tapis</button>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Pelajar</th>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Struktur Yuran</th>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Amaun (RM)</th>
                            <x-th-sortable lajur="status" :urut-semasa="$urut" :arah-semasa="$arah">Status</x-th-sortable>
                            <th class="border border-gray-200 px-6 py-2 text-right text-sm font-medium text-gray-500">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($tetapan as $item)
                            <tr>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($tetapan->currentPage() - 1) * $tetapan->perPage() }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->pelajar->nama ?? '-' }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                    {{ $item->strukturYuran->nama ?? '-' }}
                                    <span class="text-xs text-gray-400">({{ $item->strukturYuran->kategoriYuran->nama ?? '-' }})</span>
                                </td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                    {{ number_format($item->amaun_verride ?? $item->strukturYuran->amaun ?? 0, 2) }}
                                    @if ($item->amaun_verride)
                                        <span class="text-xs text-orange-600">(diselaraskan)</span>
                                    @endif
                                </td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $item->status === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-right text-sm space-x-1">
                                    <a href="{{ route('tetapan-yuran-pelajar.edit', $item) }}" class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1 text-sm font-small text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Kemaskini</a>
                                    <form action="{{ route('tetapan-yuran-pelajar.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Padam tetapan yuran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-1 text-sm font-small text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">Padam</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Tiada yuran ditetapkan lagi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="p-4">{{ $tetapan->links('vendor.pagination.compact') }}</div>
            </div>
    </div>
</x-app-layout>