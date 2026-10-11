<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Laporkan Bayaran Diterima') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-6">
                    Gunakan borang ini jika seorang ibu bapa/penjaga menyerahkan bayaran tunai terus kepada anda.
                    Laporan ini akan disemak dan disahkan oleh Akauntan/Admin sebelum direkodkan secara rasmi.
                </p>

                <form method="POST" action="{{ route('permohonan-bayaran.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="pelajar_id" value="Pelajar" />
                        <x-searchable-select
                            name="pelajar_id"
                            :options="$senaraiPelajar->map(fn ($p) => ['value' => $p->id, 'label' => $p->nama.' ('.$p->no_daftar.')'])"
                            :selected="old('pelajar_id')"
                            empty-label="- Pilih Pelajar -"
                            placeholder="Taip untuk cari nama / no. pendaftaran..."
                            class="mt-1"
                        />
                        <x-input-error :messages="$errors->get('pelajar_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="amaun" value="Amaun Diterima (RM)" />
                        <x-text-input id="amaun" name="amaun" type="number" step="0.01" min="0.01" class="mt-1 block w-full" value="{{ old('amaun') }}" required />
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
                        <a href="{{ route('permohonan-bayaran.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                        <x-primary-button>Hantar Permohonan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
