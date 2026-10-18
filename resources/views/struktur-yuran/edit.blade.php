<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kemaskini Struktur Yuran') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('struktur-yuran.update', $strukturYuran) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    @include('struktur-yuran._kategori-nama-bulan', ['senaraiKategori' => $senaraiKategori, 'kategoriIdSemasa' => (string) $strukturYuran->kategori_yuran_id, 'namaSemasa' => $strukturYuran->nama, 'bulanBerkenaanSemasa' => $strukturYuran->bulan_berkenaan ?? []])

                    <div>
                        <x-input-label for="amaun" value="Amaun (RM)" />
                        <x-text-input id="amaun" name="amaun" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('amaun', $strukturYuran->amaun) }}" required />
                        <x-input-error :messages="$errors->get('amaun')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="kelas_id" value="Khusus untuk Kelas (pilihan)" />
                        <x-searchable-select
                            name="kelas_id"
                            :options="$senaraiKelas->map(fn ($k) => ['value' => $k->id, 'label' => $k->nama_kelas])"
                            :selected="$strukturYuran->kelas_id"
                            empty-label="Semua Kelas"
                            placeholder="Taip untuk cari kelas..."
                            class="mt-1"
                        />
                        <x-input-error :messages="$errors->get('kelas_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="darjah" value="Khusus untuk Tahun (pilihan)" />
                        <x-select id="darjah" name="darjah" class="mt-1 block w-full">
                            <option value="">Semua Tahun</option>
                            @foreach ($senaraiDarjah as $darjah)
                                <option value="{{ $darjah }}" @selected(old('darjah', $strukturYuran->darjah) === $darjah)>{{ $darjah }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('darjah')" class="mt-2" />
                        <p class="text-xs text-gray-500 mt-1">cth: pilih Tahun 5 untuk yuran UPPK yang hanya dikenakan kepada pelajar Tahun 5.</p>
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <x-select id="status" name="status" class="mt-1 block w-full">
                            <option value="Aktif" @selected(old('status', $strukturYuran->status) === 'Aktif')>Aktif</option>
                            <option value="Tidak Aktif" @selected(old('status', $strukturYuran->status) === 'Tidak Aktif')>Tidak Aktif</option>
                        </x-select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('struktur-yuran.index') }}" class="text-sm text-gray-600">Batal</a>
                        <x-primary-button>Kemaskini</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>