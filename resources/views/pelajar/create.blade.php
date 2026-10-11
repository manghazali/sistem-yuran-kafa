<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tambah Pelajar') }}</h2>
    </x-slot>

    <div class="py-2">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('pelajar.store') }}" class="space-y-4">
                    @csrf

                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-md text-sm text-gray-500">
                        No. Pendaftaran akan dijana automatik selepas pelajar disimpan.
                    </div>

                    <div>
                        <x-input-label for="nama" value="Nama Penuh" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" value="{{ old('nama') }}" required autofocus />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="no_kp" value="No. KP / Sijil Lahir" />
                        <x-text-input id="no_kp" name="no_kp" type="text" class="mt-1 block w-full" value="{{ old('no_kp') }}" />
                        <x-input-error :messages="$errors->get('no_kp')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="tarikh_lahir" value="Tarikh Lahir" />
                        <x-text-input id="tarikh_lahir" name="tarikh_lahir" type="date" class="mt-1 block w-full" value="{{ old('tarikh_lahir') }}" />
                        <x-input-error :messages="$errors->get('tarikh_lahir')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="jantina" value="Jantina" />
                        <x-select id="jantina" name="jantina" class="mt-1 block w-full">
                            <option value="Lelaki">Lelaki</option>
                            <option value="Perempuan">Perempuan</option>
                        </x-select>
                        <x-input-error :messages="$errors->get('jantina')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="kelas_id" value="Kelas" />
                        <x-searchable-select
                            name="kelas_id"
                            :options="$senaraiKelas->map(fn ($k) => ['value' => $k->id, 'label' => $k->nama_kelas])"
                            :selected="old('kelas_id')"
                            empty-label="- Pilih Kelas -"
                            placeholder="Taip untuk cari kelas..."
                            class="mt-1"
                        />
                        <x-input-error :messages="$errors->get('kelas_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="darjah" value="Tahun" />
                        <x-select id="darjah" name="darjah" class="mt-1 block w-full">
                            <option value="">- Pilih Tahun -</option>
                            @foreach ($senaraiDarjah as $darjah)
                                <option value="{{ $darjah }}" @selected(old('darjah') === $darjah)>{{ $darjah }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('darjah')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="penjaga_id" value="Penjaga" />
                        <x-searchable-select
                            name="penjaga_id"
                            :options="$senaraiPenjaga->map(fn ($p) => ['value' => $p->id, 'label' => $p->nama.' ('.$p->hubungan.')'])"
                            :selected="old('penjaga_id')"
                            empty-label="- Pilih Penjaga -"
                            placeholder="Taip untuk cari nama penjaga..."
                            class="mt-1"
                        />
                        <x-input-error :messages="$errors->get('penjaga_id')" class="mt-2" />
                        <p class="text-xs text-gray-500 mt-1">Tiada penjaga yang sesuai? <a href="{{ route('penjaga.create') }}" class="text-blue-600 hover:underline" target="_blank">Tambah penjaga baharu</a> dahulu.</p>
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <x-select id="status" name="status" class="mt-1 block w-full">
                            <option value="Aktif" selected>Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                            <option value="Tamat">Tamat</option>
                        </x-select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('pelajar.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
