<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Laporkan Bayaran Diterima') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="relative bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-6">
                    Gunakan borang ini jika seorang ibu bapa/penjaga menyerahkan bayaran tunai terus kepada anda.
                    Laporan ini akan disemak dan disahkan oleh Akauntan/Admin sebelum direkodkan secara rasmi.
                </p>

                <form method="POST" action="{{ route('permohonan-bayaran.store') }}" class="space-y-4"
                    x-on:submit="if (! pelajarId) { $event.preventDefault(); alert('Sila pilih Pelajar.'); } else if (! itemTerpilih) { $event.preventDefault(); alert('Sila pilih Struktur Yuran.'); }"
                    x-data="{
                        pelajarSkop: {
                            @foreach ($senaraiPelajar as $pelajar)
                                '{{ $pelajar->id }}': {
                                    label: @js(mb_strtolower($pelajar->nama.' '.$pelajar->no_daftar)),
                                    paparan: @js($pelajar->nama.' ('.$pelajar->no_daftar.')'),
                                },
                            @endforeach
                        },
                        itemTertunggak: {
                            @foreach ($itemTertunggakMengikutPelajar as $pelajarIdKey => $items)
                                '{{ $pelajarIdKey }}': @js($items),
                            @endforeach
                        },
                        pelajarId: '{{ old('pelajar_id') }}',
                        pelajarCarian: '',
                        pelajarTerbuka: false,
                        strukturKunci: '',
                        strukturTerbuka: false,
                        init() {
                            this.pelajarCarian = this.pelajarTerpilihLabel;
                        },
                        get pelajarTerpilihLabel() {
                            return this.pelajarSkop[this.pelajarId]?.paparan ?? '';
                        },
                        pelajarSepadanCarian(pelajarId) {
                            if (! this.pelajarCarian.trim() || this.pelajarCarian === this.pelajarTerpilihLabel) return true;
                            return this.pelajarSkop[pelajarId].label.includes(this.pelajarCarian.trim().toLowerCase());
                        },
                        adaPelajarSepadan() {
                            return Object.keys(this.pelajarSkop).some(id => this.pelajarSepadanCarian(id));
                        },
                        pilihPelajar(pelajarId) {
                            this.pelajarId = pelajarId;
                            this.pelajarCarian = this.pelajarSkop[pelajarId].paparan;
                            this.pelajarTerbuka = false;
                            this.strukturKunci = '';
                        },
                        tutupPelajarDropdown() {
                            this.pelajarTerbuka = false;
                            this.pelajarCarian = this.pelajarTerpilihLabel;
                        },
                        get senaraiItemPelajar() {
                            return this.itemTertunggak[this.pelajarId] ?? [];
                        },
                        get itemTerpilih() {
                            return this.senaraiItemPelajar.find((item, i) => this.kunci(item, i) === this.strukturKunci) ?? null;
                        },
                        kunci(item, i) {
                            return item.struktur_yuran_id + '-' + item.bulan + '-' + i;
                        },
                        pilihStruktur(item, i) {
                            this.strukturKunci = this.kunci(item, i);
                            this.strukturTerbuka = false;
                            if (item.baki > 0) {
                                this.$refs.amaunInput.value = item.baki.toFixed(2);
                            }
                        },
                    }"
                >
                    @csrf

                    <div
                        x-show="pelajarTerbuka || strukturTerbuka" x-cloak
                        x-on:click="tutupPelajarDropdown(); strukturTerbuka = false"
                        class="absolute inset-0 z-10 rounded-lg bg-gray-900/5"
                    ></div>

                    <div>
                        <x-input-label for="pelajar_cari" value="Pelajar" />
                        <div class="relative" :class="pelajarTerbuka && 'z-20'" x-on:click.outside="tutupPelajarDropdown()">
                            <input
                                type="text"
                                id="pelajar_cari"
                                x-ref="pelajarInput"
                                x-model="pelajarCarian"
                                x-on:focus="pelajarTerbuka = true; $event.target.select()"
                                x-on:click="pelajarTerbuka = true"
                                x-on:keydown.escape="tutupPelajarDropdown()"
                                autocomplete="off"
                                placeholder="Taip untuk cari nama / no. pendaftaran..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm"
                            >
                            <input type="hidden" name="pelajar_id" x-model="pelajarId">
                            <ul x-show="pelajarTerbuka" x-cloak
                                class="absolute z-20 mt-1 w-full max-h-60 overflow-auto rounded-md bg-white text-sm py-1 shadow-xl ring-1 ring-gray-900/10"
                            >
                                @foreach ($senaraiPelajar as $pelajar)
                                    <li
                                        x-show="pelajarSepadanCarian('{{ $pelajar->id }}')"
                                        x-on:click="pilihPelajar('{{ $pelajar->id }}')"
                                        class="px-3 py-2 cursor-pointer hover:bg-green-50"
                                        :class="pelajarId === '{{ $pelajar->id }}' ? 'bg-green-50 font-medium' : ''"
                                    >{{ $pelajar->nama }} ({{ $pelajar->no_daftar }})</li>
                                @endforeach
                                <li x-show="! adaPelajarSepadan()" class="px-3 py-2 text-gray-400">Tiada pelajar sepadan.</li>
                            </ul>
                        </div>
                        <x-input-error :messages="$errors->get('pelajar_id')" class="mt-2" />
                    </div>

                    <div x-show="pelajarId" x-cloak>
                        <x-input-label value="Struktur Yuran" />
                        <div class="relative" :class="strukturTerbuka && 'z-20'" x-on:click.outside="strukturTerbuka = false">
                            <button
                                type="button"
                                x-on:click="strukturTerbuka = ! strukturTerbuka"
                                class="mt-1 flex items-center justify-between w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm px-3 py-2 bg-white text-left"
                            >
                                <span x-text="itemTerpilih ? (itemTerpilih.nama + ' (Baki: RM' + itemTerpilih.baki.toFixed(2) + ')') : '- Pilih struktur yuran yang tertunggak -'" :class="! itemTerpilih && 'text-gray-400'"></span>
                                <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <input type="hidden" name="struktur_yuran_id" :value="itemTerpilih?.struktur_yuran_id ?? ''" required>
                            <input type="hidden" name="bulan" :value="itemTerpilih?.bulan ?? ''">
                            <ul x-show="strukturTerbuka" x-cloak
                                class="absolute z-20 mt-1 w-full max-h-60 overflow-auto rounded-md bg-white text-sm py-1 shadow-xl ring-1 ring-gray-900/10"
                            >
                                <template x-for="(item, i) in senaraiItemPelajar" :key="kunci(item, i)">
                                    <li
                                        x-on:click="pilihStruktur(item, i)"
                                        class="px-3 py-2 cursor-pointer hover:bg-green-50"
                                        :class="strukturKunci === kunci(item, i) ? 'bg-green-50 font-medium' : ''"
                                        x-text="item.nama + ' — Baki: RM' + item.baki.toFixed(2)"
                                    ></li>
                                </template>
                                <li x-show="senaraiItemPelajar.length === 0" class="px-3 py-2 text-gray-400">Tiada yuran tertunggak untuk pelajar ini.</li>
                            </ul>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Amaun Diterima di bawah akan diisi automatik mengikut baki — Admin/Akauntan tidak perlu pilih semula semasa sahkan.</p>
                        <x-input-error :messages="$errors->get('struktur_yuran_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="amaun" value="Amaun Diterima (RM)" />
                        <x-text-input id="amaun" x-ref="amaunInput" name="amaun" type="number" step="0.01" min="0.01" class="mt-1 block w-full" value="{{ old('amaun') }}" required />
                        <x-input-error :messages="$errors->get('amaun')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="tarikh_terima" value="Tarikh Terima" />
                        <x-text-input id="tarikh_terima" name="tarikh_terima" type="date" class="mt-1 block w-full" value="{{ old('tarikh_terima', date('Y-m-d')) }}" required />
                        <x-input-error :messages="$errors->get('tarikh_terima')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="catatan" value="Catatan (pilihan)" />
                        <x-textarea id="catatan" name="catatan" rows="2" class="mt-1 block w-full" placeholder="cth: Untuk yuran bulan Januari">{{ old('catatan') }}</x-textarea>
                        <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('permohonan-bayaran.index') }}" class="text-sm text-gray-600">Batal</a>
                        <x-primary-button>Hantar Permohonan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
