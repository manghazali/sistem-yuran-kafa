<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Senarai Penjaga') }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('penjaga.import') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-100">
                    <i class="fas fa-file-import mr-2"></i> Import Excel
                </a>
                <a href="{{ route('penjaga.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-600">
                    + Tambah Penjaga
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
            @error('email')
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">{{ $message }}</div>
            @enderror

            <form method="GET" class="mb-4 flex gap-3">
                <x-text-input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama / no. KP / no. telefon..." class="flex-1" />
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md">Cari</button>
            </form>

            <p class="mb-4 text-xs text-gray-400">Nota: untuk reset kata laluan penjaga yang sudah ada akaun, guna menu Pengurusan Pengguna.</p>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                            <x-th-sortable lajur="nama" :urut-semasa="$urut" :arah-semasa="$arah">Nama</x-th-sortable>
                            <x-th-sortable lajur="hubungan" :urut-semasa="$urut" :arah-semasa="$arah">Hubungan</x-th-sortable>
                            <x-th-sortable lajur="no_telefon" :urut-semasa="$urut" :arah-semasa="$arah">No. Telefon</x-th-sortable>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil. Anak</th>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Akaun Portal</th>
                            <th class="border border-gray-200 px-6 py-2 text-center text-sm font-medium text-gray-500">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($penjaga as $item)
                            <tr>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($penjaga->currentPage() - 1) * $penjaga->perPage() }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->nama }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->hubungan }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->no_telefon }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->anak()->count() }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                    @if ($item->user_id)
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">Belum Dicipta</span>
                                    @endif
                                </td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-center text-sm space-x-1">
                                    <a href="{{ route('penjaga.edit', $item) }}" class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1 text-sm font-small text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Kemaskini</a>

                                    @if (! $item->user_id)
                                        <button type="button" class="inline-flex items-center rounded-md bg-blue-100 px-3 py-1 text-sm font-small text-gray-700 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500" onclick="document.getElementById('form-akaun-{{ $item->id }}').classList.toggle('hidden')">
                                            Cipta Akaun
                                        </button>
                                    @endif

                                    <form action="{{ route('penjaga.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Padam penjaga ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-1 text-sm font-small text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">Padam</button>
                                    </form>

                                    @if (! $item->user_id)
                                        <div id="form-akaun-{{ $item->id }}" class="hidden mt-2 text-center">
                                            <form action="{{ route('penjaga.akaun.store', $item) }}" method="POST" class="flex gap-2 items-center justify-center">
                                                @csrf
                                                <x-text-input type="email" name="email" value="{{ $item->email }}" placeholder="emel@contoh.com" required />
                                                <button type="submit" class="px-3 py-1 bg-green-600 text-white text-xs rounded-md">Sahkan</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Tiada penjaga didaftarkan lagi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="p-4">{{ $penjaga->links('vendor.pagination.compact') }}</div>
            </div>
    </div>
</x-app-layout>
