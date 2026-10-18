<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Laporan Kutipan') }}</h2>
    </x-slot>

    <div class="py-2">
        <form method="GET" class="mb-6 bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tarikh Mula</label>
                <x-text-input type="date" name="tarikh_mula" value="{{ $tarikhMula->format('Y-m-d') }}" />
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tarikh Akhir</label>
                <x-text-input type="date" name="tarikh_akhir" value="{{ $tarikhAkhir->format('Y-m-d') }}" />
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Kaedah Bayaran</label>
                <x-select name="kaedah_bayaran">
                    <option value="">Semua</option>
                    <option value="Tunai" @selected(request('kaedah_bayaran') === 'Tunai')>Tunai</option>
                    <option value="Pindahan Bank" @selected(request('kaedah_bayaran') === 'Pindahan Bank')>Pindahan Bank</option>
                    <option value="Cek" @selected(request('kaedah_bayaran') === 'Cek')>Cek</option>
                    <option value="Online Banking" @selected(request('kaedah_bayaran') === 'Online Banking')>Online Banking</option>
                    <option value="Lain-lain" @selected(request('kaedah_bayaran') === 'Lain-lain')>Lain-lain</option>
                </x-select>
            </div>
            <div class="w-44">
                <label class="block text-xs text-gray-500 mb-1">Kelas</label>
                <x-searchable-select
                    name="kelas_id"
                    :options="$senaraiKelas->map(fn ($k) => ['value' => $k->id, 'label' => $k->nama_kelas])"
                    :selected="request('kelas_id')"
                    empty-label="Semua Kelas"
                    placeholder="Taip untuk cari kelas..."
                />
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tahun</label>
                <x-select name="darjah">
                    <option value="">Semua Tahun</option>
                    @foreach ($senaraiDarjah as $darjah)
                        <option value="{{ $darjah }}" @selected(request('darjah') === $darjah)>{{ $darjah }}</option>
                    @endforeach
                </x-select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md">Tapis</button>
            <a href="{{ route('laporan.kutipan.csv', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md">Muat Turun CSV</a>
            <a href="{{ route('laporan.kutipan.pdf', request()->query()) }}" class="px-4 py-2 bg-red-600 text-white text-sm rounded-md">Muat Turun PDF</a>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500">Jumlah Kutipan dalam Julat Ini</p>
                <p class="text-3xl font-bold text-green-600">RM{{ number_format($jumlahKeseluruhan, 2) }}</p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-2">Pecahan Mengikut Kaedah</p>
                <div class="space-y-1 text-sm">
                    @forelse ($pecahanKaedah as $item)
                        <div class="flex justify-between">
                            <span>{{ $item->kaedah_bayaran }} ({{ $item->bilangan }})</span>
                            <span class="font-medium">RM{{ number_format($item->jumlah, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-gray-400">Tiada data.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-50">
                    <tr>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                        <x-th-sortable lajur="no_bayaran" :urut-semasa="$urut" :arah-semasa="$arah">No. Bayaran</x-th-sortable>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Pelajar</th>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Kelas</th>
                        <x-th-sortable lajur="keterangan" :urut-semasa="$urut" :arah-semasa="$arah">Keterangan</x-th-sortable>
                        <x-th-sortable lajur="amaun" :urut-semasa="$urut" :arah-semasa="$arah">Amaun (RM)</x-th-sortable>
                        <x-th-sortable lajur="kaedah_bayaran" :urut-semasa="$urut" :arah-semasa="$arah">Kaedah</x-th-sortable>
                        <x-th-sortable lajur="tarikh_bayaran" :urut-semasa="$urut" :arah-semasa="$arah">Tarikh</x-th-sortable>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">No. Pesanan Gateway</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($bayaran as $item)
                        <tr>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($bayaran->currentPage() - 1) * $bayaran->perPage() }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->no_bayaran }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->pelajar->nama ?? '-' }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->pelajar->kelas->nama_kelas ?? '-' }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->keterangan }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm font-medium text-green-600">{{ number_format($item->amaun, 2) }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->kaedah_bayaran }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->tarikh_bayaran?->format('d/m/Y') }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->no_pesanan_gateway ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="border border-gray-200 px-6 py-2 text-center text-sm text-gray-500">Tiada bayaran dalam julat tarikh ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="p-4">{{ $bayaran->links('vendor.pagination.compact') }}</div>
        </div>
    </div>
</x-app-layout>
