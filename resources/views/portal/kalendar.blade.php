<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kalendar Yuran') }} — {{ $pelajar->nama }} ({{ $tahun }})</h2>
    </x-slot>

    <div class="py-2">
        <x-portal.tajuk-telefon :tajuk="$pelajar->nama" :subtajuk="'Kalendar yuran '.$tahun" :kembali="route('portal.index')" />

        <div>
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm rounded-2xl border border-gray-200 p-4 lg:overflow-hidden lg:rounded-lg lg:border-0 lg:p-6">
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
                                            <input type="checkbox" name="item[]" value="{{ $b['bulan'] }}:{{ $item['id'] }}" class="h-5 w-5 rounded border-gray-300 text-green-600 lg:h-4 lg:w-4 item-checkbox" data-amaun="{{ $item['baki'] }}">
                                        @else
                                            <span class="w-4 h-4 flex items-center justify-center text-green-600 text-xs">✓</span>
                                        @endif

                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium">{{ $item['nama'] }}</p>
                                            @if ($item['status'] === 'Sebahagian')
                                                <p class="text-xs text-gray-500">Baki dari jumlah asal RM{{ number_format($item['amaun'], 2) }}</p>
                                            @endif
                                            <span class="mt-1 inline-block whitespace-nowrap rounded-full px-2 py-0.5 text-xs lg:hidden {{ $warna }}">{{ $item['status'] }}</span>
                                        </div>

                                        <span class="hidden px-2 py-1 text-xs rounded-full lg:inline {{ $warna }}">
                                            {{ $item['status'] }}
                                        </span>

                                        <span class="text-sm font-semibold w-20 text-right">
                                            RM{{ number_format($itemBolehPilih ? $item['baki'] : $item['amaun'], 2) }}
                                        </span>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>

                        {{-- Di telefon, jumlah & butang kekal kelihatan di atas bar tab bawah --}}
                        <div class="sticky bottom-[5.5rem] z-10 rounded-2xl border border-gray-200 bg-white p-4 shadow-lg lg:static lg:rounded-none lg:border-0 lg:border-t lg:p-0 lg:pt-4 lg:shadow-none">
                            <div class="flex justify-between items-center">
                                <button type="button" id="btn-pilih-semua" class="text-sm text-blue-600 hover:underline"><span class="lg:hidden">Pilih semua</span><span class="hidden lg:inline">Pilih/Nyahpilih Semua</span></button>
                                <p class="whitespace-nowrap text-lg font-bold">Jumlah: RM<span id="jumlah-display">0.00</span></p>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4">
                                <a href="{{ route('portal.index') }}" class="hidden text-sm text-gray-600 lg:inline">Kembali</a>
                                <button type="submit" id="btn-bayar" class="w-full rounded-full bg-green-600 px-4 py-3 text-sm font-semibold text-white hover:bg-green-700 lg:w-auto lg:rounded-md lg:bg-gray-800 lg:py-2 lg:font-medium lg:hover:bg-gray-700 opacity-50" disabled>
                                    Semak Sebelum Bayar &rarr;
                                </button>
                            </div>
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
