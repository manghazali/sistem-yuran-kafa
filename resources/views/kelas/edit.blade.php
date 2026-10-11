<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kemaskini Kelas') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('kelas.update', $kelas) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="nama_kelas" value="Nama Kelas" />
                        <x-text-input id="nama_kelas" name="nama_kelas" type="text" class="mt-1 block w-full" value="{{ old('nama_kelas', $kelas->nama_kelas) }}" required autofocus />
                        <x-input-error :messages="$errors->get('nama_kelas')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="sesi" value="Sesi / Tahun" />
                        <x-text-input id="sesi" name="sesi" type="text" class="mt-1 block w-full" value="{{ old('sesi', $kelas->sesi) }}" />
                        <x-input-error :messages="$errors->get('sesi')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <x-select id="status" name="status" class="mt-1 block w-full">
                            <option value="Aktif" @selected(old('status', $kelas->status) === 'Aktif')>Aktif</option>
                            <option value="Tidak Aktif" @selected(old('status', $kelas->status) === 'Tidak Aktif')>Tidak Aktif</option>
                        </x-select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('kelas.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                        <x-primary-button>Kemaskini</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
