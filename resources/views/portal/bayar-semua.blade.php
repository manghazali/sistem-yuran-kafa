<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Bayar Yuran Semua Anak') }} ({{ $tahun }})</h2>
    </x-slot>

    <div class="py-2">
        <x-portal.tajuk-telefon tajuk="Bayar Semua Anak" :subtajuk="'Satu bayaran untuk semua anak · '.$tahun" />

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
        @endif

        @if ($senaraiAnak->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
                Tiada yuran tertunggak untuk anak-anak anda. Terima kasih!
            </div>
        @else
            <form method="GET" action="{{ route('portal.bayar-semua.semak') }}">
                <p class="mb-4 text-sm text-gray-500">
                    Tandakan yuran yang anda mahu bayar. Semua yuran yang dipilih dibayar dalam satu transaksi,
                    dan resit berasingan dikeluarkan untuk setiap anak.
                </p>

                <div class="space-y-4 mb-4">
                    @foreach ($senaraiAnak as $kumpulan)
                        @php $pelajar = $kumpulan['pelajar']; @endphp
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm lg:rounded-lg">
                            <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-3">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-700">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-base font-semibold text-gray-900">{{ $pelajar->nama }}</p>
                                    <p class="text-[13px] text-gray-500">{{ $pelajar->darjah ?? '-' }} · {{ $pelajar->kelas->nama_kelas ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="divide-y">
                                @foreach ($kumpulan['bulan'] as $b)
                                    @foreach ($b['butiran'] as $item)
                                        @if ($item['baki'] > 0)
                                            @php
                                                $warna = $item['status'] === 'Sebahagian' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800';
                                            @endphp
                                            <label class="flex cursor-pointer items-center gap-3 px-4 py-3 hover:bg-gray-50">
                                                <input type="checkbox" name="item[]" value="{{ $pelajar->id }}:{{ $b['bulan'] }}:{{ $item['id'] }}"
                                                       class="h-5 w-5 rounded border-gray-300 text-green-600 lg:h-4 lg:w-4 item-checkbox" data-amaun="{{ $item['baki'] }}">
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium">{{ $item['nama'] }}</p>
                                                    <span class="mt-1 inline-block whitespace-nowrap rounded-full px-2 py-0.5 text-xs {{ $warna }}">{{ $item['status'] }}</span>
                                                </div>
                                                <span class="text-sm font-semibold">RM{{ number_format($item['baki'], 2) }}</span>
                                            </label>
                                        @endif
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Di telefon, jumlah & butang kekal kelihatan di atas bar tab bawah --}}
                <div class="sticky bottom-[5.5rem] z-10 rounded-2xl border border-gray-200 bg-white p-4 shadow-lg lg:static lg:shadow-sm">
                    <div class="flex justify-between items-center">
                        <button type="button" id="btn-pilih-semua"
                                class="inline-flex items-center gap-2 whitespace-nowrap rounded-full border border-green-600 px-3 py-1.5 text-sm font-semibold text-green-700 transition hover:bg-green-50">
                            <i id="ikon-pilih-semua" class="far fa-square"></i>
                            <span id="label-pilih-semua">Pilih semua</span>
                        </button>
                        <p class="whitespace-nowrap text-lg font-bold">Jumlah: RM<span id="jumlah-display">0.00</span></p>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('portal.index') }}" class="hidden text-sm text-gray-600 lg:inline">Kembali</a>
                        <button type="submit" id="btn-bayar" class="w-full rounded-full bg-green-600 px-4 py-3 text-sm font-semibold text-white hover:bg-green-700 lg:w-auto opacity-50" disabled>
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

                    // Butang pilih semua: tanda penuh bila semua dipilih
                    const semua = document.querySelectorAll('.item-checkbox');
                    const semuaDipilih = semua.length > 0 && Array.from(semua).every(cb => cb.checked);
                    const btnSemua = document.getElementById('btn-pilih-semua');
                    document.getElementById('ikon-pilih-semua').className = semuaDipilih ? 'fas fa-square-check' : 'far fa-square';
                    document.getElementById('label-pilih-semua').textContent = semuaDipilih ? 'Nyahpilih' : 'Pilih semua';
                    btnSemua.classList.toggle('bg-green-600', semuaDipilih);
                    btnSemua.classList.toggle('text-white', semuaDipilih);
                    btnSemua.classList.toggle('text-green-700', !semuaDipilih);
                    btnSemua.classList.toggle('hover:bg-green-50', !semuaDipilih);
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
</x-app-layout>
