<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Senarai Guru') }}</h2>
            <div class="flex items-center gap-2">
                @if ($bilanganEmelMenunggu > 0)
                    <form method="POST" action="{{ route('guru.hantar-emel-akaun') }}"
                          onsubmit="return confirm('Cipta akaun dan hantar emel log masuk kepada {{ $bilanganEmelMenunggu }} guru? Emel dihantar berperingkat, kira-kira {{ config('kafa.emel_had_harian') }} sehari (siap dalam ~{{ (int) ceil($bilanganEmelMenunggu / max(1, config('kafa.emel_had_harian'))) }} hari).')">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-green-600 text-green-700 text-sm font-medium rounded-md hover:bg-green-50">
                            <i class="fas fa-envelope mr-2"></i> Hantar Emel Akaun ({{ $bilanganEmelMenunggu }})
                        </button>
                    </form>
                @endif
                <a href="{{ route('guru.import') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-100">
                    <i class="fas fa-file-import mr-2"></i> Import Excel
                </a>
                <a href="{{ route('guru.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                    + Tambah Guru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-2">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
        @endif
        @error('email')
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">{{ $message }}</div>
        @enderror

        <div class="mb-4">
            <form method="GET" class="mb-4 flex gap-3">
                <x-text-input type="text" name="nama" placeholder="Cari nama guru..." value="{{ request('nama') }}" class="flex-1" />
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md">Cari</button>
            </form>
            <p class="text-xs text-gray-400">Nota: "Hantar semula" mencipta kata laluan baharu dan menghantarnya ke emel guru.</p>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto" x-data="{ dipilih: [], semua: @js($statusEmel->reject(fn ($st) => $st['kod'] === 'tiada')->keys()->values()) }">
                {{-- Muncul apabila ada baris ditanda: hantar emel log masuk kepada yang dipilih --}}
                <form id="borang-hantar-emel" method="POST" action="{{ route('guru.hantar-emel-akaun') }}" x-show="dipilih.length" x-cloak
                      class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-green-50 px-4 py-3"
                      @submit="if (! confirm('Hantar emel log masuk kepada ' + dipilih.length + ' guru dipilih? Yang sudah ada akaun akan menerima kata laluan baharu.')) $event.preventDefault()">
                    @csrf
                    <span class="text-sm text-gray-700"><span x-text="dipilih.length"></span> guru dipilih</span>
                    <button type="submit" class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                        <i class="fas fa-envelope mr-2"></i> Hantar emel kepada&nbsp;<span x-text="dipilih.length"></span>&nbsp;dipilih
                    </button>
                </form>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-50">
                    <tr>
                        <th class="border border-gray-200 px-3 py-2 text-center"><input type="checkbox" class="rounded border-gray-300 text-green-600" title="Pilih semua di halaman ini" :checked="semua.length && dipilih.length === semua.length" @change="dipilih = $event.target.checked ? [...semua] : []"></th>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                        <x-th-sortable lajur="nama" :urut-semasa="$urut" :arah-semasa="$arah">Nama</x-th-sortable>
                        <x-th-sortable lajur="no_pekerja" :urut-semasa="$urut" :arah-semasa="$arah">No. Pekerja</x-th-sortable>
                        <x-th-sortable lajur="no_telefon" :urut-semasa="$urut" :arah-semasa="$arah">No. Telefon</x-th-sortable>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Kelas &amp; Tahun</th>
                        <x-th-sortable lajur="status" :urut-semasa="$urut" :arah-semasa="$arah">Status</x-th-sortable>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Akaun Portal</th>
                        <th class="border border-gray-200 px-6 py-2 text-center text-sm font-medium text-gray-500">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($guru as $item)
                        <tr>
                            @php $status = $statusEmel[$item->id]; @endphp
                            <td class="border border-gray-200 px-3 py-2 text-center">
                                @if ($status['kod'] !== 'tiada')
                                    <input type="checkbox" form="borang-hantar-emel" name="ids[]" value="{{ $item->id }}" x-model.number="dipilih" class="rounded border-gray-300 text-green-600">
                                @endif
                            </td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($guru->currentPage() - 1) * $guru->perPage() }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->nama }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->no_pekerja ?? '-' }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->no_telefon ?? '-' }}</td>
                            <td class="border border-gray-200 px-6 py-2 text-sm">
                                @forelse ($item->guruKelas as $gk)
                                    <div class="whitespace-nowrap text-sm">{{ $gk->kelas->nama_kelas ?? '-' }} <span class="text-gray-400">({{ $gk->darjah ?? 'Semua Tahun' }})</span></div>
                                @empty
                                    <span class="text-gray-400">-</span>
                                @endforelse
                            </td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full {{ $item->status === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                <x-status-emel-akaun :status="$status" />
                            </td>
                            <td class="border border-gray-200 px-4 py-2 text-center text-sm space-x-1 space-y-1">
                                <a href="{{ route('guru.edit', $item) }}" class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1 text-sm font-small text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Kemaskini</a>

                                @if ($status['kod'] !== 'tiada')
                                    <form action="{{ route('guru.hantar-emel-akaun') }}" method="POST" class="inline"
                                          onsubmit="return confirm('{{ $status['kod'] === 'belum' ? 'Cipta akaun dan hantar emel log masuk' : 'Hantar semula emel log masuk dengan kata laluan baharu' }} kepada {{ $status['emel'] }}?');">
                                        @csrf
                                        <input type="hidden" name="ids[]" value="{{ $item->id }}">
                                        <button type="submit" class="inline-flex items-center rounded-md bg-green-50 px-3 py-1 text-sm font-small text-green-700 ring-1 ring-green-600 hover:bg-green-100">
                                            <i class="fas fa-envelope mr-1"></i> {{ $status['kod'] === 'belum' ? 'Hantar emel' : 'Hantar semula' }}
                                        </button>
                                    </form>
                                @endif

                                @if ($status['kod'] === 'tiada')
                                    <button type="button" class="inline-flex items-center rounded-md bg-blue-100 px-3 py-1 text-sm font-small text-gray-700 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500" onclick="document.getElementById('form-akaun-{{ $item->id }}').classList.toggle('hidden')">
                                        Cipta Akaun
                                    </button>
                                @endif

                                <form action="{{ route('guru.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Padam guru ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-1 text-sm font-small text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">Padam</button>
                                </form>

                                @if ($status['kod'] === 'tiada')
                                    <div id="form-akaun-{{ $item->id }}" class="hidden mt-2 text-center">
                                        <form action="{{ route('guru.akaun.store', $item) }}" method="POST" class="flex gap-2 items-center justify-center">
                                            @csrf
                                            <x-text-input type="email" name="email" value="{{ $item->user->email ?? '' }}" placeholder="emel@contoh.com" required />
                                            <button type="submit" class="px-3 py-1 bg-green-600 text-white text-xs rounded-md">Sahkan</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">Tiada guru didaftarkan lagi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="p-4">{{ $guru->links('vendor.pagination.compact') }}</div>
        </div>
    </div>
</x-app-layout>
