<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Senarai Pelajar') }}</h2>
            <div class="flex items-center gap-2">
                @role('Admin')
                    <a href="{{ route('naik-darjah.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-100">
                        <i class="fas fa-arrow-up-right-dots mr-2"></i> Naik Darjah
                    </a>
                @endrole
                <a href="{{ route('pelajar.import') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-100">
                    <i class="fas fa-file-import mr-2"></i> Import Excel
                </a>
                <a href="{{ route('pelajar.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                    + Tambah Pelajar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-2">

            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif
            @error('error')
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">{{ $message }}</div>
            @enderror

            <form method="GET" class="mb-4 flex gap-3">
                <x-text-input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama / no. daftar..." class="flex-1" />
                <x-searchable-select
                    name="kelas_id"
                    :options="$senaraiKelas->map(fn ($k) => ['value' => $k->id, 'label' => $k->nama_kelas])"
                    :selected="request('kelas_id')"
                    empty-label="Semua Kelas"
                    placeholder="Taip untuk cari kelas..."
                    class="w-48"
                />
                <x-select name="darjah">
                    <option value="">Semua Tahun</option>
                    @foreach ($senaraiDarjah as $darjah)
                        <option value="{{ $darjah }}" @selected(request('darjah') === $darjah)>{{ $darjah }}</option>
                    @endforeach
                </x-select>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md">Cari</button>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                            <x-th-sortable lajur="no_daftar" :urut-semasa="$urut" :arah-semasa="$arah">No. Daftar</x-th-sortable>
                            <x-th-sortable lajur="nama" :urut-semasa="$urut" :arah-semasa="$arah">Nama</x-th-sortable>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Kelas</th>
                            <x-th-sortable lajur="darjah" :urut-semasa="$urut" :arah-semasa="$arah">Tahun</x-th-sortable>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Penjaga</th>
                            <x-th-sortable lajur="status" :urut-semasa="$urut" :arah-semasa="$arah">Status</x-th-sortable>
                            <th class="border border-gray-200 px-6 py-2 text-center text-sm font-medium text-gray-500">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($pelajar as $item)
                            <tr>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($pelajar->currentPage() - 1) * $pelajar->perPage() }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->no_daftar }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->nama }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->kelas->nama_kelas ?? '-' }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->darjah ?? '-' }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->penjaga->nama ?? '-' }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $item->status === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-center text-sm space-x-1">
                                    <a href="{{ route('pelajar.edit', $item) }}" class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1 text-sm font-small text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Kemaskini</a>
                                    <form action="{{ route('pelajar.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Padam pelajar ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-1 text-sm font-small text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">Padam</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Tiada pelajar dijumpai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="p-4">{{ $pelajar->links('vendor.pagination.compact') }}</div>
            </div>
    </div>
</x-app-layout>
