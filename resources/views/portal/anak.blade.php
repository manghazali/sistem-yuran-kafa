<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Yuran') }} — {{ $pelajar->nama }} ({{ $tahun }})</h2>
    </x-slot>

    <div class="py-2">
        <x-portal.tajuk-telefon :tajuk="$pelajar->nama" :subtajuk="($pelajar->darjah ?? '-').' · '.($pelajar->kelas->nama_kelas ?? '-')" />

        <div class="mb-4 flex items-center justify-between rounded-2xl border bg-white px-4 py-3 shadow-sm {{ $jumlahBaki > 0 ? 'border-red-200' : 'border-green-200' }}">
            <span class="text-sm text-gray-600">Baki belum dibayar · {{ $tahun }}</span>
            <span class="text-lg font-bold {{ $jumlahBaki > 0 ? 'text-red-600' : 'text-green-600' }}">RM{{ number_format($jumlahBaki, 2) }}</span>
        </div>

        @if ($senaraiBulan->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
                Tiada yuran ditetapkan untuk {{ $pelajar->nama }} buat masa ini.
            </div>
        @else
            <div class="divide-y rounded-2xl border border-gray-200 bg-white shadow-sm lg:rounded-lg">
                @foreach ($senaraiBulan as $b)
                    @foreach ($b['butiran'] as $item)
                        @php
                            $warna = match ($item['status']) {
                                'Dibayar' => 'bg-green-100 text-green-800',
                                'Sebahagian' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-red-100 text-red-800',
                            };
                        @endphp
                        <div class="flex items-center gap-3 px-4 py-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-[15px] font-medium text-gray-900">{{ $item['nama'] }}</p>
                                <span class="mt-1 inline-block whitespace-nowrap rounded-full px-2 py-0.5 text-xs {{ $warna }}">{{ $item['status'] }}</span>
                            </div>
                            <span class="text-[15px] font-semibold {{ $item['baki'] > 0 ? 'text-gray-900' : 'text-green-700' }}">
                                RM{{ number_format($item['baki'] > 0 ? $item['baki'] : $item['amaun'], 2) }}
                            </span>
                        </div>
                    @endforeach
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
