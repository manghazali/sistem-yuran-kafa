<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Log Audit') }}</h2>
    </x-slot>

    <div class="py-2 space-y-4">

            <form method="GET" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Pengguna</label>
                    <x-searchable-select
                        name="user_id"
                        :options="$senaraiPengguna->map(fn ($p) => ['value' => $p->id, 'label' => $p->name])"
                        :selected="request('user_id')"
                        empty-label="Semua"
                        placeholder="Taip untuk cari pengguna..."
                        class="w-48"
                    />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Jenis Rekod</label>
                    <x-select name="model">
                        <option value="">Semua</option>
                        @foreach ($senaraiModel as $model)
                            <option value="{{ $model }}" @selected(request('model') === $model)>{{ $model }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tarikh Mula</label>
                    <x-text-input type="date" name="tarikh_mula" value="{{ request('tarikh_mula') }}" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tarikh Akhir</label>
                    <x-text-input type="date" name="tarikh_akhir" value="{{ request('tarikh_akhir') }}" />
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
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Pengguna</th>
                            <x-th-sortable lajur="tindakan" :urut-semasa="$urut" :arah-semasa="$arah">Tindakan</x-th-sortable>
                            <x-th-sortable lajur="model" :urut-semasa="$urut" :arah-semasa="$arah">Rekod</x-th-sortable>
                            <x-th-sortable lajur="keterangan" :urut-semasa="$urut" :arah-semasa="$arah">Keterangan</x-th-sortable>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">IP</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($log as $item)
                            @php
                                $warnaTindakan = match ($item->tindakan) {
                                    'Cipta' => 'bg-green-100 text-green-800',
                                    'Kemaskini' => 'bg-yellow-100 text-yellow-800',
                                    'Padam' => 'bg-red-100 text-red-800',
                                    'Log Masuk' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <tr>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($log->currentPage() - 1) * $log->perPage() }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->pengguna->name ?? 'Sistem' }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $warnaTindakan }}">{{ $item->tindakan }}</span>
                                </td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->model }} @if($item->model_id) #{{ $item->model_id }} @endif</td>
                                <td class="border border-gray-200 px-6 py-2 text-sm text-gray-600">{{ $item->keterangan }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm text-gray-400">{{ $item->ip_address }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Tiada log lagi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="p-4">{{ $log->links('vendor.pagination.compact') }}</div>
            </div>
    </div>
</x-app-layout>
