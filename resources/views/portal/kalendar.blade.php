<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kalendar Yuran') }} — {{ $pelajar->nama }} ({{ $tahun }})</h2>
    </x-slot>

    <div class="py-2">
        <div>
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-4">
                    Tandakan yuran mana-mana yang anda mahu bayar sekarang. Anda akan
                    disemak dahulu sebelum bayaran dimulakan.
                </p>

                @if ($senaraiBulan->isEmpty())
                    <div class="p-4 bg-gray-50 text-gray-600 rounded-md text-sm">
                        Tiada yuran bulanan ditetapkan untuk {{ $pelajar->nama }} buat masa ini.
                        Sila hubungi pihak sekolah.
                    </div>
                @else
                    <form method="GET" action="{{ route('portal.kalendar.semak', $pelajar) }}">

                        <div class="border rounded-md divide-y mb-4">
                            @foreach ($senaraiBulan as $b)
                                @foreach ($b['butiran'] as $item)
                                    @php
                                        $itemBolehPilih = $item['baki'] > 0;
                                        $warna = match ($item['status']) {
                                            'Dibayar' => 'bg-green-100 text-green-800',
                                            'Sebahagian' => 'bg-yellow-100 text-yellow-800',
                                            default => 'bg-red-100 text-red-800',
                                        };
                                    @endphp
                                    <div class="flex items-center gap-3 p-3 {{ $itemBolehPilih ? 'hover:bg-gray-50' : 'opacity-75' }}">
                                        @if ($itemBolehPilih)
                                            <input type="checkbox" name="item[]" value="{{ $b['bulan'] }}:{{ $item['id'] }}" class="rounded border-gray-300 item-checkbox" data-amaun="{{ $item['baki'] }}">
                                        @else
                                            <span class="w-4 h-4 flex items-center justify-center text-green-600 text-xs">✓</span>
                                        @endif

                                        <div class="flex-1">
                                            <p class="text-sm font-medium">{{ $item['nama'] }}</p>
                                            @if ($item['status'] === 'Sebahagian')
                                                <p class="text-xs text-gray-500">Baki dari jumlah asal RM{{ number_format($item['amaun'], 2) }}</p>
                                            @endif
                                        </div>

                                        <span class="px-2 py-1 text-xs rounded-full {{ $warna }}">
                                            {{ $item['status'] }}
                                        </span>

                                        <span class="text-sm font-semibold w-20 text-right">
                                            RM{{ number_format($itemBolehPilih ? $item['baki'] : $item['amaun'], 2) }}
                                        </span>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>

                        <div class="flex justify-between items-center border-t pt-4">
                            <button type="button" id="btn-pilih-semua" class="text-sm text-blue-600 hover:underline">Pilih/Nyahpilih Semua</button>
                            <p class="text-lg font-bold">Jumlah: RM<span id="jumlah-display">0.00</span></p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4">
                            <a href="{{ route('portal.index') }}" class="text-sm text-gray-600">Kembali</a>
                            <button type="submit" id="btn-bayar" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700 opacity-50" disabled>
                                Semak Sebelum Bayar &rarr;
                            </button>
                        </div>
                    </form>

                    <script>
                        function kiraJumlah() {
                            let jumlah = 0;
                            let adaDipilih = false;
                            document.querySelectorAll('.item-checkbox:checked').forEach(cb => {
                                jumlah += parseFloat(cb.dataset.amaun || 0);
                                adaDipilih = true;
                            });
                            document.getElementById('jumlah-display').textContent = jumlah.toFixed(2);
                            const btn = document.getElementById('btn-bayar');
                            btn.disabled = !adaDipilih;
                            btn.classList.toggle('opacity-50', !adaDipilih);
                        }

                        document.querySelectorAll('.item-checkbox').forEach(cb => cb.addEventListener('change', kiraJumlah));

                        document.getElementById('btn-pilih-semua').addEventListener('click', function () {
                            const semua = document.querySelectorAll('.item-checkbox');
                            const adaTakDipilih = Array.from(semua).some(cb => !cb.checked);
                            semua.forEach(cb => cb.checked = adaTakDipilih);
                            kiraJumlah();
                        });

                        kiraJumlah();
                    </script>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
