<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Log Emel') }}</h2>
    </x-slot>

    <div class="py-2 space-y-4">

            <form method="GET" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Kepada (emel)</label>
                    <x-text-input type="text" name="kepada" value="{{ request('kepada') }}" placeholder="cth: guru@contoh.com" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Subjek</label>
                    <x-text-input type="text" name="subjek" value="{{ request('subjek') }}" placeholder="cth: Akaun Log Masuk" />
                </div>
                <div>
                    <label class="block text-xs mb-1 invisible">&nbsp;</label>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md">Tapis</button>
                </div>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                            <x-th-sortable lajur="created_at" :urut-semasa="$urut" :arah-semasa="$arah">Tarikh & Masa</x-th-sortable>
                            <x-th-sortable lajur="kepada" :urut-semasa="$urut" :arah-semasa="$arah">Kepada</x-th-sortable>
                            <x-th-sortable lajur="subjek" :urut-semasa="$urut" :arah-semasa="$arah">Subjek</x-th-sortable>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Ringkasan</th>
                            <th class="border border-gray-200 px-6 py-2 text-center text-sm font-medium text-gray-500">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($logEmel as $item)
                            <tr>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($logEmel->currentPage() - 1) * $logEmel->perPage() }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                    {{ $item->kepada }}
                                    @if ($item->nama_penerima)
                                        <span class="text-xs text-gray-400">({{ $item->nama_penerima }})</span>
                                    @endif
                                </td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->subjek }}</td>
                                <td class="border border-gray-200 px-6 py-2 text-sm text-gray-500">{{ $item->ringkasan() }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-center text-sm">
                                    <a href="{{ route('log-emel.show', $item) }}" class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1 text-sm font-small text-white hover:bg-gray-600">Lihat</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Tiada emel dihantar lagi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="p-4">{{ $logEmel->links('vendor.pagination.compact') }}</div>
            </div>
    </div>
</x-app-layout>
