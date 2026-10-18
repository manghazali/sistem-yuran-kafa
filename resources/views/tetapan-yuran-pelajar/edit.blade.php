<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kemaskini Tetapan Yuran') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-4 p-3 bg-gray-50 rounded-md text-sm text-gray-700">
                    <strong>Pelajar:</strong> {{ $tetapan->pelajar->nama ?? '-' }} &nbsp;|&nbsp;
                    <strong>Struktur Yuran:</strong> {{ $tetapan->strukturYuran->nama ?? '-' }}
                    (RM{{ number_format($tetapan->strukturYuran->amaun ?? 0, 2) }})
                </div>

                <form method="POST" action="{{ route('tetapan-yuran-pelajar.update', $tetapan) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="status" value="Status" />
                        <x-select id="status" name="status" class="mt-1 block w-full">
                            <option value="Aktif" @selected(old('status', $tetapan->status) === 'Aktif')>Aktif</option>
                            <option value="Tidak Aktif" @selected(old('status', $tetapan->status) === 'Tidak Aktif')>Tidak Aktif</option>
                        </x-select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('tetapan-yuran-pelajar.index') }}" class="text-sm text-gray-600">Batal</a>
                        <x-primary-button>Kemaskini</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>