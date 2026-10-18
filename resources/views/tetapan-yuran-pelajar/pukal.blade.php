<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tetapkan Yuran Secara Pukal') }}</h2>
    </x-slot>

    <div class="py-2">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-600 mb-4">
                    {{ __('Tetapkan satu struktur yuran kepada semua pelajar aktif sekali gus, atau tapis kepada kelas / darjah tertentu sahaja. Pelajar yang sudah ada struktur yuran ini akan dilangkau secara automatik.') }}
                </p>

                <form method="POST" action="{{ route('tetapan-yuran-pelajar.pukal.pratonton') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="struktur_yuran_id" value="Struktur Yuran" />
                        <x-searchable-select
                            name="struktur_yuran_id"
                            :options="$senaraiStruktur->map(fn ($s) => ['value' => $s->id, 'label' => $s->nama.' — RM'.number_format($s->amaun, 2).' ('.$s->labelBulanBerkenaan().')'])"
                            :selected="old('struktur_yuran_id')"
                            empty-label="- Pilih Struktur Yuran -"
                            placeholder="Taip untuk cari nama struktur yuran..."
                            required
                            class="mt-1"
                        />
                        <x-input-error :messages="$errors->get('struktur_yuran_id')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="kelas_id" value="Tapis kepada Kelas (pilihan)" />
                            <x-searchable-select
                                name="kelas_id"
                                :options="$senaraiKelas->map(fn ($k) => ['value' => $k->id, 'label' => $k->nama_kelas])"
                                :selected="old('kelas_id')"
                                empty-label="Semua Kelas"
                                placeholder="Taip untuk cari kelas..."
                                class="mt-1"
                            />
                            <x-input-error :messages="$errors->get('kelas_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="darjah" value="Tapis kepada Tahun (pilihan)" />
                            <x-select id="darjah" name="darjah" class="mt-1 block w-full">
                                <option value="">Semua Tahun</option>
                                @foreach ($senaraiDarjah as $darjah)
                                    <option value="{{ $darjah }}" @selected(old('darjah') === $darjah)>{{ $darjah }}</option>
                                @endforeach
                            </x-select>
                            <x-input-error :messages="$errors->get('darjah')" class="mt-2" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 -mt-2">{{ __('cth: pilih Tahun 5 untuk hanya tetapkan yuran UPPK kepada pelajar Tahun 5 sahaja.') }}</p>

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
                        <x-primary-button>Pratonton &rarr;</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
