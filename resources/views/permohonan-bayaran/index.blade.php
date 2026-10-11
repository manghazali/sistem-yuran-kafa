<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Permohonan Bayaran') }}</h2>
            @role('Guru')
                <a href="{{ route('permohonan-bayaran.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-600">
                    + Laporkan Bayaran Diterima
                </a>
            @endrole
        </div>
    </x-slot>

    <div class="py-2">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
        @endif

        @hasanyrole('Admin|Akauntan')
            <form method="GET" class="mb-4 flex gap-3">
                <x-select name="status" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Menunggu" @selected(request('status') === 'Menunggu')>Menunggu</option>
                    <option value="Disahkan" @selected(request('status') === 'Disahkan')>Disahkan</option>
                    <option value="Ditolak" @selected(request('status') === 'Ditolak')>Ditolak</option>
                </x-select>
            </form>
        @endhasanyrole

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Guru</th>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Pelajar</th>
                            <x-th-sortable lajur="amaun" :urut-semasa="$urut" :arah-semasa="$arah">Amaun (RM)</x-th-sortable>
                            <x-th-sortable lajur="tarikh_terima" :urut-semasa="$urut" :arah-semasa="$arah">Tarikh Terima</x-th-sortable>
                            <x-th-sortable lajur="status" :urut-semasa="$urut" :arah-semasa="$arah">Status</x-th-sortable>
                            <th class="border border-gray-200 px-6 py-2 text-center text-sm font-medium text-gray-500">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($permohonan as $item)
                            @php
                                $warna = match ($item->status) {
                                    'Disahkan' => 'bg-green-100 text-green-800',
                                    'Ditolak' => 'bg-red-100 text-red-800',
                                    default => 'bg-yellow-100 text-yellow-800',
                                };
                            @endphp
                            <tr>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($permohonan->currentPage() - 1) * $permohonan->perPage() }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->guru->nama ?? '-' }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                    {{ $item->pelajar->nama ?? '-' }}
                                    <p class="text-xs text-gray-400">{{ $item->pelajar->kelas->nama_kelas ?? '-' }}</p>
                                </td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm font-medium">{{ number_format($item->amaun, 2) }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->tarikh_terima->format('d/m/Y') }}</td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $warna }}">{{ $item->status }}</span>
                                    @if ($item->status === 'Ditolak' && $item->sebab_tolak)
                                        <p class="text-xs text-gray-400 mt-1">{{ $item->sebab_tolak }}</p>
                                    @endif
                                </td>
                                <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-center text-sm space-x-1">
                                    @if ($item->status === 'Menunggu')
                                        @hasanyrole('Admin|Akauntan')
                                            <a href="{{ route('bayaran.create', ['permohonan_id' => $item->id]) }}" class="inline-flex items-center rounded-md bg-green-600 px-3 py-1 text-sm font-small text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">Sahkan</a>
                                            <form action="{{ route('permohonan-bayaran.tolak', $item) }}" method="POST" class="inline" onsubmit="return isiSebabTolak(this);">
                                                @csrf
                                                <input type="hidden" name="sebab_tolak">
                                                <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-1 text-sm font-small text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">Tolak</button>
                                            </form>
                                        @endhasanyrole
                                        @role('Guru')
                                            <span class="text-gray-400">Menunggu semakan</span>
                                        @endrole
                                    @elseif ($item->status === 'Disahkan' && $item->bayaran_id)
                                        @hasanyrole('Admin|Akauntan')
                                            <a href="{{ route('resit.show', $item->bayaran_id) }}" class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1 text-sm font-small text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Lihat Resit</a>
                                        @else
                                            <span class="text-gray-400">Telah disahkan</span>
                                        @endhasanyrole
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Tiada permohonan bayaran lagi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $permohonan->links('vendor.pagination.compact') }}</div>
        </div>
    </div>

    <script>
        function isiSebabTolak(form) {
            const sebab = prompt('Sebab tolak permohonan ini (pilihan):');
            if (sebab === null) {
                return false;
            }
            form.querySelector('input[name="sebab_tolak"]').value = sebab;
            return true;
        }
    </script>
</x-app-layout>
