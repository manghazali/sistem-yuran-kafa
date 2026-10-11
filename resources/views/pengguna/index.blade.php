<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Pengurusan Pengguna') }}</h2>
            <a href="{{ route('pengguna.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                + Tambah Pengguna
            </a>
        </div>
    </x-slot>

    <div class="py-2">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
        @endif
        @error('pengguna')
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">{{ $message }}</div>
        @enderror
        @error('is_active')
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">{{ $message }}</div>
        @enderror

        <div class="mb-4">
            <form method="GET" class="flex gap-3">
                <x-text-input type="text" name="cari" placeholder="Cari nama atau emel..." value="{{ request('cari') }}" class="flex-1" />
                <x-select name="peranan">
                    <option value="">Semua Peranan</option>
                    @foreach (['Admin', 'Akauntan', 'Guru', 'Penjaga'] as $r)
                        <option value="{{ $r }}" @selected(request('peranan') === $r)>{{ $r }}</option>
                    @endforeach
                </x-select>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md">Cari</button>
            </form>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-50">
                    <tr>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                        <x-th-sortable lajur="name" :urut-semasa="$urut" :arah-semasa="$arah">Nama</x-th-sortable>
                        <x-th-sortable lajur="email" :urut-semasa="$urut" :arah-semasa="$arah">Emel</x-th-sortable>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Peranan</th>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Status</th>
                        <x-th-sortable lajur="created_at" :urut-semasa="$urut" :arah-semasa="$arah">Didaftar</x-th-sortable>
                        <th class="border border-gray-200 px-6 py-2 text-center text-sm font-medium text-gray-500">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($pengguna as $item)
                        <tr>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($pengguna->currentPage() - 1) * $pengguna->perPage() }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                {{ $item->name }}
                                @if ($item->id === auth()->id())
                                    <span class="ml-1 text-xs text-gray-400">(anda)</span>
                                @endif
                            </td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->email }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                @forelse ($item->roles as $peranan)
                                    <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800">{{ $peranan->name }}</span>
                                @empty
                                    <span class="text-gray-400">-</span>
                                @endforelse
                            </td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->created_at->format('d/m/Y') }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-center text-sm space-x-1">
                                @if ($item->id === auth()->id())
                                    <span class="text-xs text-gray-400">Guna menu Profil</span>
                                @else
                                    @php $terpautRekod = $item->hasAnyRole(['Guru', 'Penjaga']); @endphp

                                    @unless ($terpautRekod)
                                        <a href="{{ route('pengguna.edit', $item) }}" class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1 text-sm font-small text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Kemaskini</a>
                                    @endunless

                                    <form action="{{ route('pengguna.reset-kata-laluan', $item) }}" method="POST" class="inline" onsubmit="return confirm('Jana kata laluan baharu untuk {{ $item->name }}?');">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center rounded-md bg-blue-100 px-3 py-1 text-sm font-small text-gray-700 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500">Reset Kata Laluan</button>
                                    </form>

                                    @unless ($terpautRekod)
                                        <form action="{{ route('pengguna.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Padam pengguna ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-1 text-sm font-small text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">Padam</button>
                                        </form>
                                    @endunless

                                    @if ($terpautRekod)
                                        <p class="mt-1 text-xs text-gray-400">Kemaskini/Padam: guna menu {{ $item->hasRole('Guru') ? 'Guru' : 'Penjaga' }}.</p>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Tiada pengguna dijumpai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="p-4">{{ $pengguna->links('vendor.pagination.compact') }}</div>
        </div>
    </div>
</x-app-layout>
