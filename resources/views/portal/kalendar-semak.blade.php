<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Semak Sebelum Bayar') }}</h2>
    </x-slot>

    <div class="py-2">
        <x-portal.tajuk-telefon tajuk="Semak Sebelum Bayar" :subtajuk="$pelajar->nama" :kembali="route('portal.kalendar', $pelajar)" />

        <div class="space-y-4">

            <div class="p-4 bg-blue-50 text-blue-800 text-sm rounded-md">
                Sila semak butiran di bawah. Tiada apa-apa dicaj lagi — anda akan
                dibawa ke gateway pembayaran untuk bayar selepas sahkan di sini.
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-200 p-4 lg:rounded-lg lg:border-0 lg:p-6">
                <div class="flex justify-between items-start mb-4 pb-4 border-b">
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $pelajar->nama }}</h3>
                        <p class="text-xs text-gray-500">Kelas: {{ $pelajar->kelas->nama_kelas ?? '-' }}</p>
                    </div>
                </div>

                <table class="w-full text-sm">
                    <tbody>
                        @foreach ($senaraiSemakan as $b)
                            @foreach ($b['butiran'] as $item)
                                <tr class="border-b last:border-b-0">
                                    <td class="py-2 text-gray-700">{{ $item['nama'] }}</td>
                                    <td class="py-2 text-right font-medium">RM{{ number_format($item['baki'], 2) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2">
                            <td class="pt-3 font-bold">Jumlah Keseluruhan</td>
                            <td class="pt-3 text-right font-bold text-green-600">RM{{ number_format($jumlahKeseluruhan, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex flex-col-reverse gap-3 lg:flex-row lg:items-center lg:justify-between">
                <a href="{{ route('portal.kalendar', $pelajar) }}" class="text-center text-sm text-gray-600 lg:text-left">&larr; Kembali & Ubah Pilihan</a>

                <form method="POST" action="{{ route('portal.kalendar.bayar', $pelajar) }}" class="w-full lg:w-auto">
                    @csrf
                    @foreach ($senaraiSemakan as $b)
                        @foreach ($b['butiran'] as $item)
                            <input type="hidden" name="item[]" value="{{ $b['bulan'] }}:{{ $item['id'] }}">
                        @endforeach
                    @endforeach
                    <button type="submit" class="w-full rounded-full px-6 py-3 bg-green-600 text-white font-semibold hover:bg-green-700 lg:w-auto lg:rounded-md lg:font-medium">
                        Bayar Sekarang — RM{{ number_format($jumlahKeseluruhan, 2) }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
