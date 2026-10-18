<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tambah Guru') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('guru.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="nama" value="Nama Penuh" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" value="{{ old('nama') }}" required autofocus />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="no_pekerja" value="No. Pekerja" />
                        <x-text-input id="no_pekerja" name="no_pekerja" type="text" class="mt-1 block w-full" value="{{ old('no_pekerja') }}" />
                        <x-input-error :messages="$errors->get('no_pekerja')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="no_kp" value="No. Kad Pengenalan" />
                        <x-text-input id="no_kp" name="no_kp" type="text" class="mt-1 block w-full" value="{{ old('no_kp') }}" />
                        <x-input-error :messages="$errors->get('no_kp')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="no_telefon" value="No. Telefon" />
                        <x-text-input id="no_telefon" name="no_telefon" type="text" class="mt-1 block w-full" value="{{ old('no_telefon') }}" />
                        <x-input-error :messages="$errors->get('no_telefon')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Emel (pilihan — akaun log masuk akan dicipta & dihantar automatik)" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email') }}" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div x-data="{
                        baris: [
                            @if (old('kelas_id'))
                                @foreach (old('kelas_id') as $i => $kid)
                                    { kelas_id: '{{ $kid }}', darjah: '{{ old('darjah')[$i] ?? '' }}' },
                                @endforeach
                            @else
                                { kelas_id: '', darjah: '' },
                            @endif
                        ]
                    }">
                        <x-input-label value="Kelas & Tahun (Guru Kelas) — boleh tambah lebih daripada satu" />
                        <template x-for="(b, indeks) in baris" :key="indeks">
                            <div class="grid grid-cols-5 gap-2 mt-2 items-center">
                                <div class="col-span-2">
                                    <x-select name="kelas_id[]" x-model="b.kelas_id" class="block w-full">
                                        <option value="">- Pilih Kelas -</option>
                                        @foreach ($senaraiKelas as $kelas)
                                            <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <div class="col-span-2">
                                    <x-select name="darjah[]" x-model="b.darjah" class="block w-full">
                                        <option value="">- Semua Tahun -</option>
                                        @foreach ($senaraiDarjah as $darjah)
                                            <option value="{{ $darjah }}">{{ $darjah }}</option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <div>
                                    <button type="button" @click="baris.splice(indeks, 1)" x-show="baris.length > 1" class="text-red-600 hover:text-red-800 px-2 py-2">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <button type="button" @click="baris.push({ kelas_id: '', darjah: '' })" class="mt-2 text-sm text-green-600 hover:underline">+ Tambah Kelas</button>
                        <x-input-error :messages="$errors->get('kelas_id')" class="mt-2" />
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
                        <a href="{{ route('guru.index') }}" class="text-sm text-gray-600">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
