<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Struktur Yuran') }}</h2>
            <a href="{{ route('struktur-yuran.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-600">
                + Tambah Struktur Yuran
            </a>
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
                <x-text-input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama struktur..." class="flex-1" />
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md">Cari</button>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                            <x-th-sortable lajur="nama" :urut-semasa="$urut" :arah-semasa="$arah">Nama Struktur</x-th-sortable>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Kategori</th>
                            <x-th-sortable lajur="amaun" :urut-semasa="$urut" :arah-semasa="$arah">Amaun (RM)</x-th-sortable>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Kelas</th>
                            <x-th-sortable lajur="darjah" :urut-semasa="$urut" :arah-semasa="$arah">Tahun</x-th-sortable>
                            <x-th-sortable lajur="status" :urut-semasa="$urut" :arah-semasa="$arah">Status</x-th-sortable>
                            <th class="border border-gray-200 px-6 py-2 text-center text-sm font-medium text-gray-500">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($strukturYuran as $item)
                            <tr>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($strukturYuran->currentPage() - 1) * $strukturYuran->perPage() }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->nama }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->kategoriYuran->nama ?? '-' }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ number_format($item->amaun, 2) }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->kelas->nama_kelas ?? 'Semua Kelas' }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->darjah ?? 'Semua Tahun' }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $item->status === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-center text-sm space-x-1">
                                    <a href="{{ route('struktur-yuran.edit', $item) }}" class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1 text-sm font-small text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Kemaskini</a>
                                    <form action="{{ route('struktur-yuran.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Padam struktur yuran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-1 text-sm font-small text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">Padam</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Tiada struktur yuran lagi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="p-4">{{ $strukturYuran->links('vendor.pagination.compact') }}</div>
            </div>
    </div>
</x-app-layout>