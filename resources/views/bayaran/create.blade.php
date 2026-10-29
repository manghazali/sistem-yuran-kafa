<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Rekod Bayaran') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if ($permohonan)
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md text-sm text-blue-800">
                        <p class="font-semibold">Mengesahkan Permohonan Bayaran</p>
                        <p class="mt-1">Guru {{ $permohonan->guru->nama }} melaporkan terima bayaran tunai RM{{ number_format($permohonan->amaun, 2) }} daripada {{ $pelajarDipilih->nama }} pada {{ $permohonan->tarikh_terima->format('d/m/Y') }}.</p>
                        @if ($permohonan->catatan)
                            <p class="mt-1 italic">"{{ $permohonan->catatan }}"</p>
                        @endif
                        @if ($itemPermohonanDipilih)
                            <p class="mt-1">Struktur yuran dipilih oleh guru: <span class="font-semibold">{{ $itemPermohonanDipilih['nama'] }}</span> — butiran di bawah telah pra-isi.</p>
                        @elseif ($permohonan->strukturYuran)
                            <p class="mt-1">Struktur yuran dipilih oleh guru: <span class="font-semibold">{{ $permohonan->strukturYuran->nama }}</span> (baki semasa tidak ditemui — sila semak manual).</p>
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

                        @if ($senaraiItemTertunggak->isNotEmpty())
                            <div>
                                <x-input-label value="Struktur Yuran (pilihan - untuk auto-isi)" />
                                <x-select id="pilih_struktur" class="mt-1 block w-full" onchange="isiStruktur(this)">
                                    <option value="">- Bayaran Lain (bukan bulanan) -</option>
                                    @foreach ($senaraiItemTertunggak as $item)
                                        <option
                                            value="{{ $item['struktur_yuran_id'] }}"
                                            data-bulan="{{ $item['bulan'] }}"
                                            data-tahun="{{ $item['tahun'] }}"
                                            data-baki="{{ $item['baki'] }}"
                                            data-keterangan="{{ $item['nama'] }}"
                                            @selected($itemPermohonanDipilih && $itemPermohonanDipilih['struktur_yuran_id'] == $item['struktur_yuran_id'] && $itemPermohonanDipilih['bulan'] == $item['bulan'])
                                        >{{ $item['nama'] }} (Baki: RM{{ number_format($item['baki'], 2) }})</option>
                                    @endforeach
                                </x-select>
                            </div>
                        @endif

                        <input type="hidden" id="bulan" name="bulan" value="{{ old('bulan', $itemPermohonanDipilih['bulan'] ?? null) }}">
                        <input type="hidden" id="tahun" name="tahun" value="{{ old('tahun', $itemPermohonanDipilih['tahun'] ?? date('Y')) }}">
                        <input type="hidden" id="keterangan" name="keterangan" value="{{ old('keterangan', $itemPermohonanDipilih['nama'] ?? null) }}">

                        <div>
                            <x-input-label for="amaun" value="Amaun Bayaran (RM)" />
                            <x-text-input id="amaun" name="amaun" type="number" step="0.01" min="0.01" class="mt-1 block w-full" value="{{ old('amaun', $permohonan?->amaun) }}" required />
                            <x-input-error :messages="$errors->get('amaun')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="kaedah_bayaran" value="Kaedah Bayaran" />
                            <x-select id="kaedah_bayaran" name="kaedah_bayaran" class="mt-1 block w-full">
                                @foreach (\App\Models\Bayaran::SENARAI_KAEDAH as $kaedah)
                                    <option value="{{ $kaedah }}" @selected($kaedah === 'Tunai')>{{ $kaedah }}</option>
                                @endforeach
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
                            <a href="{{ route('bayaran.index') }}" class="text-sm text-gray-600">Batal</a>
                            <x-primary-button>Rekod Bayaran</x-primary-button>
                        </div>
                    </form>

                    <script>
                        function isiStruktur(select) {
                            const opsi = select.options[select.selectedIndex];
                            if (!opsi.value) return;
                            document.getElementById('bulan').value = opsi.dataset.bulan;
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
