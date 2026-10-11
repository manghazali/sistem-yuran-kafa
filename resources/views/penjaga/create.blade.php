<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tambah Penjaga') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('penjaga.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="nama" value="Nama Penuh" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" value="{{ old('nama') }}" required autofocus />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="hubungan" value="Hubungan dengan Pelajar" />
                        <x-select id="hubungan" name="hubungan" class="mt-1 block w-full">
                            <option value="Bapa">Bapa</option>
                            <option value="Ibu">Ibu</option>
                            <option value="Penjaga Sah">Penjaga Sah</option>
                        </x-select>
                        <x-input-error :messages="$errors->get('hubungan')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="no_kp" value="No. Kad Pengenalan" />
                        <x-text-input id="no_kp" name="no_kp" type="text" class="mt-1 block w-full" value="{{ old('no_kp') }}" />
                        <x-input-error :messages="$errors->get('no_kp')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="no_telefon" value="No. Telefon" />
                        <x-text-input id="no_telefon" name="no_telefon" type="text" class="mt-1 block w-full" value="{{ old('no_telefon') }}" required />
                        <x-input-error :messages="$errors->get('no_telefon')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Emel (pilihan — akaun portal akan dicipta & dihantar automatik)" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email') }}" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="alamat" value="Alamat" />
                        <x-textarea id="alamat" name="alamat" rows="3" class="mt-1 block w-full">{{ old('alamat') }}</x-textarea>
                        <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('penjaga.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
