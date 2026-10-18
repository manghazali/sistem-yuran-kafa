<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kemaskini Kategori Yuran') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('kategori-yuran.update', $kategoriYuran) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="nama" value="Nama Kategori" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" value="{{ old('nama', $kategoriYuran->nama) }}" required autofocus />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="bulanan" value="1" @checked(old('bulanan', $kategoriYuran->bulanan)) class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-sm font-medium text-gray-700">Kategori Bulanan</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1">Struktur Yuran bawah kategori ini akan papar pilihan bulan (Jan–Dis) dan Nama akan dijana automatik mengikut bulan dipilih.</p>
                        <x-input-error :messages="$errors->get('bulanan')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="keterangan" value="Keterangan" />
                        <x-textarea id="keterangan" name="keterangan" rows="3" class="mt-1 block w-full">{{ old('keterangan', $kategoriYuran->keterangan) }}</x-textarea>
                        <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <x-select id="status" name="status" class="mt-1 block w-full">
                            <option value="Aktif" @selected(old('status', $kategoriYuran->status) === 'Aktif')>Aktif</option>
                            <option value="Tidak Aktif" @selected(old('status', $kategoriYuran->status) === 'Tidak Aktif')>Tidak Aktif</option>
                        </x-select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('kategori-yuran.index') }}" class="text-sm text-gray-600">Batal</a>
                        <x-primary-button>Kemaskini</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>