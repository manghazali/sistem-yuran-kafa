<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Semak Sebelum Bayar') }}</h2>
    </x-slot>

    <div class="py-2">
        <x-portal.tajuk-telefon tajuk="Semak Sebelum Bayar" subtajuk="Semua anak" :kembali="route('portal.bayar-semua')" />

        <div class="space-y-4">
            @if (session('ralat'))
                <div class="flex gap-3 p-4 bg-red-50 text-red-800 text-sm rounded-md">
                    <i class="fas fa-circle-exclamation mt-0.5"></i>
                    <span>{{ session('ralat') }}</span>
                </div>
            @endif

            <div class="p-4 bg-blue-50 text-blue-800 text-sm rounded-md">
                Sila semak butiran di bawah. Tiada apa-apa dicaj lagi — anda akan
                dibawa ke gerbang pembayaran untuk bayar selepas sahkan di sini.
                Resit berasingan akan dikeluarkan untuk setiap anak.
            </div>

            @foreach ($senaraiSemakan as $kumpulan)
                <div class="bg-white shadow-sm rounded-2xl border border-gray-200 p-4 lg:rounded-lg lg:border-0 lg:p-6">
                    <div class="mb-3 border-b pb-3">
                        <h3 class="font-semibold text-gray-800">{{ $kumpulan['pelajar']->nama }}</h3>
                        <p class="text-xs text-gray-500">Kelas: {{ $kumpulan['pelajar']->kelas->nama_kelas ?? '-' }}</p>
                    </div>
                    <table class="w-full text-sm">
                        <tbody>
                            @foreach ($kumpulan['bulan'] as $b)
                                @foreach ($b['butiran'] as $item)
                                    <tr class="border-b last:border-b-0">
                                        <td class="py-2 text-gray-700">{{ $item['nama'] }}</td>
                                        <td class="py-2 text-right font-medium">RM{{ number_format($item['baki'], 2) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t">
                                <td class="pt-2 text-gray-500">Jumlah {{ $kumpulan['pelajar']->nama }}</td>
                                <td class="pt-2 text-right font-semibold">RM{{ number_format($kumpulan['bulan']->sum('baki'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endforeach

            <div class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm lg:rounded-lg">
                <span class="font-bold">Jumlah Keseluruhan</span>
                <span class="text-lg font-bold text-green-600">RM{{ number_format($jumlahKeseluruhan, 2) }}</span>
            </div>

            <div class="flex lg:justify-end">
                {{-- Elak tekan dua kali: butang dimatikan sebaik borang dihantar --}}
                <form method="POST" action="{{ route('portal.bayar-semua.bayar') }}" class="w-full lg:w-auto"
                      x-data="{ dihantar: false }" @submit="dihantar = true">
                    @csrf
                    @foreach ($senaraiSemakan as $kumpulan)
                        @foreach ($kumpulan['bulan'] as $b)
                            @foreach ($b['butiran'] as $item)
                                <input type="hidden" name="item[]" value="{{ $kumpulan['pelajar']->id }}:{{ $b['tahun'] }}:{{ $b['bulan'] }}:{{ $item['id'] }}">
                            @endforeach
                        @endforeach
                    @endforeach
                    <button type="submit" :disabled="dihantar" :class="dihantar && 'opacity-60'" class="w-full rounded-full px-6 py-3 bg-green-600 text-white font-semibold hover:bg-green-700 lg:w-auto lg:rounded-md lg:font-medium">
                        Bayar Sekarang — RM{{ number_format($jumlahKeseluruhan, 2) }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
