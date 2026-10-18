<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tetapkan Yuran kepada Pelajar') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="relative bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('tetapan-yuran-pelajar.store') }}" class="space-y-4"
                    x-data="{
                        pelajarSkop: {
                            @foreach ($senaraiPelajar as $pelajar)
                                '{{ $pelajar->id }}': {
                                    kelas_id: @js($pelajar->kelas_id),
                                    darjah: @js($pelajar->darjah),
                                    label: @js(mb_strtolower($pelajar->nama.' '.$pelajar->no_daftar)),
                                    paparan: @js($pelajar->nama.' ('.$pelajar->no_daftar.')'),
                                },
                            @endforeach
                        },
                        strukturSkop: {
                            @foreach ($senaraiStruktur as $struktur)
                                '{{ $struktur->id }}': {
                                    kelas_id: @js($struktur->kelas_id),
                                    darjah: @js($struktur->darjah),
                                    label: @js(mb_strtolower($struktur->nama.' '.($struktur->kategoriYuran->nama ?? ''))),
                                    paparan: @js($struktur->nama.' — RM'.number_format($struktur->amaun, 2).' ('.$struktur->labelBulanBerkenaan().')'.(($struktur->kelas_id || $struktur->darjah) ? ' ['.($struktur->kelas->nama_kelas ?? $struktur->darjah).']' : '')),
                                },
                            @endforeach
                        },
                        pelajarSudahAdaStruktur: {
                            @foreach ($pelajarSudahAdaStruktur as $pelajarIdKey => $idSenarai)
                                '{{ $pelajarIdKey }}': @js($idSenarai->map(fn ($id) => (string) $id)->values()),
                            @endforeach
                        },
                        pelajarId: '{{ old('pelajar_id') }}',
                        pelajarCarian: '',
                        pelajarTerbuka: false,
                        init() {
                            this.pelajarCarian = this.pelajarTerpilihLabel;
                            this.strukturCarian = this.strukturRingkasan;
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
                            // Buang struktur yang telah dipilih tetapi tidak lagi sah
                            // (luar skop ATAU pelajar baharu ini sudah ada struktur
                            // tersebut) — elak hantar sesuatu yang pasti dilangkau.
                            this.strukturIds = this.strukturIds.filter(id => this.strukturSesuai(id));
                            this.strukturCarian = this.strukturRingkasan;
                        },
                        tutupPelajarDropdown() {
                            this.pelajarTerbuka = false;
                            this.pelajarCarian = this.pelajarTerpilihLabel;
                        },
                        strukturIds: @js(old('struktur_yuran_id', [])),
                        strukturCarian: '',
                        strukturTerbuka: false,
                        get strukturRingkasan() {
                            if (this.strukturIds.length === 0) return '';
                            if (this.strukturIds.length <= 2) {
                                return this.strukturIds.map(id => this.strukturSkop[id]?.paparan).filter(Boolean).join(', ');
                            }
                            return this.strukturIds.length + ' struktur yuran dipilih';
                        },
                        strukturSesuai(strukturId) {
                            const p = this.pelajarSkop[this.pelajarId];
                            if (! p) return true;
                            const s = this.strukturSkop[strukturId];
                            const kelasOk = ! s.kelas_id || s.kelas_id == p.kelas_id;
                            const darjahOk = ! s.darjah || s.darjah === p.darjah;
                            const belumAda = ! (this.pelajarSudahAdaStruktur[this.pelajarId] ?? []).includes(strukturId);
                            return kelasOk && darjahOk && belumAda;
                        },
                        strukturSepadanCarian(strukturId) {
                            if (! this.strukturSesuai(strukturId)) return false;
                            if (! this.strukturCarian.trim() || this.strukturCarian === this.strukturRingkasan) return true;
                            return this.strukturSkop[strukturId].label.includes(this.strukturCarian.trim().toLowerCase());
                        },
                        adaStrukturSepadan() {
                            return Object.keys(this.strukturSkop).some(id => this.strukturSepadanCarian(id));
                        },
                        togelStruktur(strukturId) {
                            const idx = this.strukturIds.indexOf(strukturId);
                            if (idx === -1) {
                                this.strukturIds.push(strukturId);
                            } else {
                                this.strukturIds.splice(idx, 1);
                            }
                        },
                        bukaStrukturDropdown() {
                            this.strukturTerbuka = true;
                            this.strukturCarian = this.strukturRingkasan;
                            this.$nextTick(() => this.$refs.strukturInput.select());
                        },
                        tutupStrukturDropdown() {
                            this.strukturTerbuka = false;
                            this.strukturCarian = this.strukturRingkasan;
                        },
                    }"
                    x-on:submit="if (! pelajarId) { $event.preventDefault(); pelajarTerbuka = true; $refs.pelajarInput.focus(); } else if (strukturIds.length === 0) { $event.preventDefault(); strukturTerbuka = true; $refs.strukturInput.focus(); }"
                >
                    @csrf

                    <div
                        x-show="pelajarTerbuka || strukturTerbuka" x-cloak
                        x-on:click="tutupPelajarDropdown(); tutupStrukturDropdown()"
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

                    <div>
                        <x-input-label for="struktur_cari" value="Struktur Yuran — boleh pilih lebih daripada satu" />
                        <div class="relative" :class="strukturTerbuka && 'z-20'" x-on:click.outside="tutupStrukturDropdown()">
                            <input
                                type="text"
                                id="struktur_cari"
                                x-ref="strukturInput"
                                x-model="strukturCarian"
                                x-on:focus="bukaStrukturDropdown()"
                                x-on:click="strukturTerbuka = true"
                                x-on:keydown.escape="tutupStrukturDropdown()"
                                autocomplete="off"
                                placeholder="Taip untuk cari struktur yuran..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm"
                            >
                            <template x-for="id in strukturIds" :key="id">
                                <input type="hidden" name="struktur_yuran_id[]" :value="id">
                            </template>
                            <ul x-show="strukturTerbuka" x-cloak
                                class="absolute z-20 mt-1 w-full max-h-60 overflow-auto rounded-md bg-white text-sm py-1 shadow-xl ring-1 ring-gray-900/10"
                            >
                                @foreach ($senaraiStruktur as $struktur)
                                    <li
                                        x-show="strukturSepadanCarian('{{ $struktur->id }}')"
                                        x-on:click="togelStruktur('{{ $struktur->id }}')"
                                        class="flex items-start gap-2 px-3 py-2 cursor-pointer hover:bg-green-50"
                                        :class="strukturIds.includes('{{ $struktur->id }}') ? 'bg-green-50' : ''"
                                    >
                                        <input
                                            type="checkbox"
                                            class="mt-0.5 rounded border-gray-300 text-green-600 focus:ring-green-500 pointer-events-none"
                                            tabindex="-1"
                                            :checked="strukturIds.includes('{{ $struktur->id }}')"
                                        >
                                        <span :class="strukturIds.includes('{{ $struktur->id }}') ? 'font-medium' : ''">
                                            {{ $struktur->nama }} — RM{{ number_format($struktur->amaun, 2) }} ({{ $struktur->labelBulanBerkenaan() }})
                                            @if ($struktur->kelas_id || $struktur->darjah)
                                                [{{ $struktur->kelas->nama_kelas ?? $struktur->darjah }}]
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                                <li x-show="! adaStrukturSepadan()" class="px-3 py-2 text-gray-400">Tiada struktur yuran sepadan.</li>
                            </ul>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Senarai ditapis automatik mengikut Kelas/Tahun pelajar yang dipilih di atas, dan struktur yang pelajar sudah ada turut disembunyikan. Klik untuk tanda/nyahtanda — tekan Esc atau klik di luar untuk tutup senarai.</p>
                        <x-input-error :messages="$errors->get('struktur_yuran_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <x-select id="status" name="status" class="mt-1 block w-full">
                            <option value="Aktif" selected>Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </x-select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('tetapan-yuran-pelajar.index') }}" class="text-sm text-gray-600">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
