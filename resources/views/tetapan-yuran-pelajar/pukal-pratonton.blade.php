<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Pratonton: Tetapkan Yuran Pukal') }}</h2>
    </x-slot>

    <div class="py-2 space-y-6">
        <div class="p-4 bg-gray-50 border border-gray-200 rounded-md text-sm text-gray-700">
            <strong>Struktur Yuran:</strong> {{ $strukturYuran->nama }} — RM{{ number_format((float) $strukturYuran->amaun, 2) }}
            @if ($strukturYuran->kelas_id || $strukturYuran->darjah)
                <span class="text-xs text-gray-500">(khusus {{ $strukturYuran->kelas->nama_kelas ?? $strukturYuran->darjah }})</span>
            @endif
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 px-4 py-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Akan Ditetapkan ({{ $pelajarBaharu->count() }} pelajar)</h3>
            </div>
            @if ($pelajarBaharu->isEmpty())
                <p class="px-4 py-6 text-sm text-gray-500 text-center">Tiada pelajar baharu — semua yang sepadan tapisan sudah ada struktur yuran ini.</p>
            @else
                <div class="max-h-64 overflow-y-auto divide-y divide-gray-100">
                    @foreach ($pelajarBaharu as $pelajar)
                        <div class="px-4 py-2 text-sm text-gray-700">{{ $pelajar->nama }}</div>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($pelajarDilangkau->isNotEmpty())
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="border-b border-gray-200 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-700">Dilangkau — Sudah Ada Struktur Ini ({{ $pelajarDilangkau->count() }} pelajar)</h3>
                </div>
                <div class="max-h-40 overflow-y-auto divide-y divide-gray-100">
                    @foreach ($pelajarDilangkau as $pelajar)
                        <div class="px-4 py-2 text-sm text-gray-400">{{ $pelajar->nama }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('tetapan-yuran-pelajar.pukal.store') }}" class="bg-white shadow-sm sm:rounded-lg p-4 space-y-4">
            @csrf
            <input type="hidden" name="struktur_yuran_id" value="{{ $input['struktur_yuran_id'] }}">
            <input type="hidden" name="kelas_id" value="{{ $input['kelas_id'] ?? '' }}">
            <input type="hidden" name="darjah" value="{{ $input['darjah'] ?? '' }}">
            <input type="hidden" name="amaun_verride" value="{{ $input['amaun_verride'] ?? '' }}">
            <input type="hidden" name="status" value="{{ $input['status'] }}">

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('tetapan-yuran-pelajar.pukal') }}" class="text-sm text-gray-600 hover:underline">&larr; Kembali &amp; Ubah</a>
                <button type="submit" @disabled($pelajarBaharu->isEmpty()) class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    Sahkan &amp; Tetapkan kepada {{ $pelajarBaharu->count() }} Pelajar
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
