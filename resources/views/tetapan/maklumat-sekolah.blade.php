<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Maklumat Sekolah') }}</h2>
    </x-slot>
    <div class="py-4">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-3 rounded-md bg-green-50 border border-green-200 px-4 py-2 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Maklumat Sekolah</h3>
                    <p class="mt-1 text-sm text-gray-500">Maklumat ini dipaparkan pada resit rasmi dan dokumen lain.</p>
                </div>
                <form method="POST" action="{{ route('tetapan.maklumat-sekolah.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="p-4 space-y-4">
                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700">Nama Sekolah</label>
                            <x-text-input type="text" id="nama" name="nama" value="{{ old('nama', $nama) }}" class="mt-1 block w-full" />
                            @error('nama')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                            <x-textarea id="alamat" name="alamat" rows="3" class="mt-1 block w-full">{{ old('alamat', $alamat) }}</x-textarea>
                            <p class="mt-1 text-xs text-gray-500">Setiap baris baharu akan dipaparkan sebagai satu baris berasingan pada resit.</p>
                            @error('alamat')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="telefon" class="block text-sm font-medium text-gray-700">No. Telefon</label>
                            <x-text-input type="text" id="telefon" name="telefon" value="{{ old('telefon', $telefon) }}" class="mt-1 block w-full" />
                            @error('telefon')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="flex items-center justify-end border-t border-gray-200 bg-gray-50 px-4 py-3">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
