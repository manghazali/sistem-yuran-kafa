<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Rekod Bayaran') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($permohonan)
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md text-sm text-blue-800">
                        <p class="font-semibold">Mengesahkan Permohonan Bayaran</p>
                        <p class="mt-1">Guru {{ $permohonan->guru->nama }} melaporkan terima bayaran tunai RM{{ number_format($permohonan->amaun, 2) }} daripada {{ $pelajarDipilih->nama }} pada {{ $permohonan->tarikh_terima->format('d/m/Y') }}.</p>
                        @if ($permohonan->catatan)
                            <p class="mt-1 italic">"{{ $permohonan->catatan }}"</p>
                        @endif
                        <p class="mt-2 text-xs text-blue-600">Semak & lengkapkan butiran di bawah sebelum rekod secara rasmi.</p>
                    </div>
                @endif

                <form method="GET" action="{{ route('bayaran.create') }}" class="mb-6">
                    <x-input-label for="pelajar_id" value="Pilih Pelajar" />
                    <x-searchable-select
                        name="pelajar_id"
                        :options="$senaraiPelajar->map(fn ($p) => ['value' => $p->id, 'label' => $p->nama.' ('.$p->no_daftar.')'])"
                        :selected="$pelajarDipilih?->id"
                        empty-label="- Pilih Pelajar -"
                        placeholder="Taip untuk cari nama / no. pendaftaran..."
                        :disabled="(bool) $permohonan"
                        auto-submit
                        class="mt-1"
                    />
                </form>

                @if ($pelajarDipilih)
                    <form method="POST" action="{{ route('bayaran.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="pelajar_id" value="{{ $pelajarDipilih->id }}">
                        @if ($permohonan)
                            <input type="hidden" name="permohonan_id" value="{{ $permohonan->id }}">
                        @endif

                        @if ($senaraiBulan->isNotEmpty())
                            <div>
                                <x-input-label value="Bulan Tertunggak (pilihan - untuk auto-isi)" />
                                <x-select id="pilih_bulan" class="mt-1 block w-full" onchange="isiBulan(this)">
                                    <option value="">- Bayaran Lain (bukan bulanan) -</option>
                                    @foreach ($senaraiBulan as $b)
                                        @php $namaButiran = $b['butiran']->pluck('nama')->implode(', '); @endphp
                                        <option value="{{ $b['bulan'] }}" data-tahun="{{ $b['tahun'] }}" data-baki="{{ $b['baki'] }}" data-keterangan="{{ $namaButiran }}">
                                            {{ $b['label'] }} — {{ $namaButiran }} (Baki: RM{{ number_format($b['baki'], 2) }})
                                        </option>
                                    @endforeach
                                </x-select>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="bulan" value="Bulan (1-12, kosongkan jika bukan bulanan)" />
                                <x-text-input type="number" id="bulan" name="bulan" min="1" max="12" class="mt-1 block w-full" value="{{ old('bulan') }}" />
                                <x-input-error :messages="$errors->get('bulan')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="tahun" value="Tahun" />
                                <x-text-input type="number" id="tahun" name="tahun" class="mt-1 block w-full" value="{{ old('tahun', date('Y')) }}" />
                                <x-input-error :messages="$errors->get('tahun')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="keterangan" value="Keterangan (pilihan - auto-isi jika bulan dipilih)" />
                            <x-text-input id="keterangan" name="keterangan" type="text" class="mt-1 block w-full" value="{{ old('keterangan') }}" placeholder="cth: Yuran Pendaftaran" />
                            <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="amaun" value="Amaun Bayaran (RM)" />
                            <x-text-input id="amaun" name="amaun" type="number" step="0.01" min="0.01" class="mt-1 block w-full" value="{{ old('amaun', $permohonan?->amaun) }}" required />
                            <x-input-error :messages="$errors->get('amaun')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="kaedah_bayaran" value="Kaedah Bayaran" />
                            <x-select id="kaedah_bayaran" name="kaedah_bayaran" class="mt-1 block w-full">
                                <option value="Tunai" selected>Tunai</option>
                                <option value="Pindahan Bank">Pindahan Bank</option>
                                <option value="Cek">Cek</option>
                                <option value="Online Banking">Online Banking</option>
                                <option value="Lain-lain">Lain-lain</option>
                            </x-select>
                            <x-input-error :messages="$errors->get('kaedah_bayaran')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tarikh_bayaran" value="Tarikh Bayaran" />
                            <x-text-input id="tarikh_bayaran" name="tarikh_bayaran" type="date" class="mt-1 block w-full" value="{{ old('tarikh_bayaran', $permohonan?->tarikh_terima->format('Y-m-d') ?? date('Y-m-d')) }}" required />
                            <x-input-error :messages="$errors->get('tarikh_bayaran')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="no_rujukan" value="No. Rujukan (pilihan)" />
                            <x-text-input id="no_rujukan" name="no_rujukan" type="text" class="mt-1 block w-full" value="{{ old('no_rujukan') }}" />
                            <x-input-error :messages="$errors->get('no_rujukan')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="catatan" value="Catatan (pilihan)" />
                            <x-textarea id="catatan" name="catatan" rows="2" class="mt-1 block w-full">{{ old('catatan', $permohonan ? 'Diterima oleh Guru '.$permohonan->guru->nama.(($permohonan->catatan) ? ' — '.$permohonan->catatan : '') : null) }}</x-textarea>
                            <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4">
                            <a href="{{ route('bayaran.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                            <x-primary-button>Rekod Bayaran</x-primary-button>
                        </div>
                    </form>

                    <script>
                        function isiBulan(select) {
                            const opsi = select.options[select.selectedIndex];
                            if (!opsi.value) return;
                            document.getElementById('bulan').value = opsi.value;
                            document.getElementById('tahun').value = opsi.dataset.tahun;
                            document.getElementById('amaun').value = parseFloat(opsi.dataset.baki).toFixed(2);
                            document.getElementById('keterangan').value = opsi.dataset.keterangan;
                        }
                    </script>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
