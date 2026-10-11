<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Generate No. Pendaftaran') }}</h2>
    </x-slot>
    <div class="py-4">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-3 rounded-md bg-green-50 border border-green-200 px-4 py-2 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Tetapan Nombor Pendaftaran</h3>
                    <p class="mt-1 text-sm text-gray-500">Tetapkan format dan nombor turutan bagi No. Pendaftaran pelajar baharu — dijana automatik, tidak perlu diisi manual.</p>
                </div>
                <form method="POST" action="{{ route('tetapan.no-daftar.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="p-4 space-y-4">
                        <div>
                            <label for="format" class="block text-sm font-medium text-gray-700">Format No. Pendaftaran</label>
                            <x-text-input type="text" id="format" name="format" value="{{ old('format', $format ?? 'PLJ/{TAHUN}/{NOMBOR}') }}" class="mt-1 block w-full" />
                            <p class="mt-1 text-xs text-gray-500">Gunakan <strong>{TAHUN}</strong> dan <strong>{NOMBOR}</strong>.</p>
                            @error('format')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="tahun" class="block text-sm font-medium text-gray-700">Tahun</label>
                            <x-text-input type="number" id="tahun" name="tahun" value="{{ old('tahun', $tahun ?? now()->year) }}" min="2000" max="2100" class="mt-1 block w-full" />
                            @error('tahun')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="nombor_semasa" class="block text-sm font-medium text-gray-700">Nombor Semasa</label>
                            <x-text-input type="number" id="nombor_semasa" name="nombor_semasa" value="{{ old('nombor_semasa', $nomborSemasa ?? 0) }}" min="0" class="mt-1 block w-full" />
                            <p class="mt-1 text-xs text-gray-500">Nombor terakhir yang telah digunakan.</p>
                            @error('nombor_semasa')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="rounded-md border border-gray-200 bg-gray-50 px-4 py-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">No. Pendaftaran Seterusnya</span>
                                <span class="text-lg font-bold text-green-600">{{ $preview ?? 'PLJ/' . now()->year . '/000001' }}</span>
                            </div>
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
