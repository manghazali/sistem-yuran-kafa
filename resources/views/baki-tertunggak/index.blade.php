<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Laporan Baki Belum Dibayar') }}</h2>
    </x-slot>

    <div class="py-2">
        <form method="GET" class="mb-6 bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-3 items-end">
            <div class="w-48">
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
            <a href="{{ route('baki-tertunggak.csv', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md">Muat Turun CSV</a>
            <a href="{{ route('baki-tertunggak.pdf', request()->query()) }}" class="px-4 py-2 bg-red-600 text-white text-sm rounded-md">Muat Turun PDF</a>
        </form>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
            <p class="text-sm text-gray-500">Jumlah Keseluruhan Belum Dibayar</p>
            <p class="text-3xl font-bold text-red-600">RM{{ number_format($jumlahKeseluruhanBelumDibayar, 2) }}</p>
            <p class="text-sm text-gray-500 mt-1">Merangkumi {{ $mengikutPelajar->count() }} pelajar dengan bulan belum dibayar.</p>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-50">
                    <tr>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                        <x-th-sortable lajur="nama" :urut-semasa="$urut" :arah-semasa="$arah">Pelajar</x-th-sortable>
                        <x-th-sortable lajur="kelas" :urut-semasa="$urut" :arah-semasa="$arah">Kelas</x-th-sortable>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Yuran Belum Bayar</th>
                        <x-th-sortable lajur="bilangan_bulan" :urut-semasa="$urut" :arah-semasa="$arah">Bil. Bulan Belum Dibayar</x-th-sortable>
                        <x-th-sortable lajur="jumlah_baki" :urut-semasa="$urut" :arah-semasa="$arah">Jumlah Baki (RM)</x-th-sortable>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($mengikutPelajar as $data)
                        <tr>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $data['pelajar']->nama ?? '-' }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $data['pelajar']->kelas->nama_kelas ?? '-' }}</td>
                            <td class="border border-gray-200 px-6 py-2 text-sm">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($data['senarai_yuran'] as $yuran)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-800" title="Baki: RM{{ number_format($yuran['baki'], 2) }}">
                                            {{ $yuran['nama'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $data['bilangan_bulan'] }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm font-semibold text-red-600">{{ number_format($data['jumlah_baki'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="border border-gray-200 px-6 py-2 text-center text-sm text-gray-500">Tiada baki belum dibayar. Semua yuran telah dibayar.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</x-app-layout>
