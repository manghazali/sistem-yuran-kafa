<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Naik Darjah (Tahun Baharu)') }}</h2>
    </x-slot>

    <div class="py-2 space-y-6">
        @if (session('status'))
            <div class="p-4 bg-green-100 text-green-800 rounded-md text-sm">{{ session('status') }}</div>
        @endif
        @error('sahkan')
            <div class="p-4 bg-red-100 text-red-800 rounded-md text-sm">Sila tandakan kotak pengesahan sebelum teruskan.</div>
        @enderror

        <div class="p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md text-sm">
            Tindakan ini menaikkan darjah <strong>SEMUA</strong> pelajar aktif sekaligus (Tahun 1 → Tahun 2, dst).
            Guna sekali sahaja setiap permulaan tahun persekolahan. Kelas (bilik darjah) pelajar
            <strong>tidak</strong> disentuh — sila kemaskini pertukaran kelas secara berasingan selepas ini jika perlu.
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-gray-700">Pratonton Perubahan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Darjah Semasa</th>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bilangan Pelajar Aktif</th>
                            <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Akan Jadi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($pratonton as $baris)
                            <tr>
                                <td class="border border-gray-200 px-6 py-2 text-sm font-medium">{{ $baris['darjah_semasa'] }}</td>
                                <td class="border border-gray-200 px-6 py-2 text-sm">{{ $baris['bilangan'] }}</td>
                                <td class="border border-gray-200 px-6 py-2 text-sm">
                                    @if ($baris['darjah_akan_jadi'])
                                        <span class="text-gray-700">{{ $baris['darjah_akan_jadi'] }}</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800">Tamat (Graduan) — jika ditandakan di bawah</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($bilanganTanpaDarjahSah > 0)
                <div class="px-4 py-3 text-xs text-gray-500 border-t border-gray-100">
                    {{ $bilanganTanpaDarjahSah }} pelajar aktif tiada darjah sah (Tahun 1–6) ditetapkan — akan dilangkau, tidak terjejas.
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('naik-darjah.store') }}" class="bg-white shadow-sm sm:rounded-lg p-4 space-y-4"
            onsubmit="return confirm('Sahkan? Tindakan ini akan menaikkan darjah SEMUA pelajar aktif sekaligus dan tidak boleh dibuat asal secara automatik.');">
            @csrf
            <label class="flex items-start gap-2">
                <input type="checkbox" name="tamatkan_tahun_enam" value="1" checked class="mt-0.5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                <span class="text-sm text-gray-700">Juga tandakan pelajar <strong>Tahun 6</strong> sebagai <strong>Tamat</strong> (graduan) — nyahtanda jika tidak mahu.</span>
            </label>
            <label class="flex items-start gap-2">
                <input type="checkbox" name="sahkan" value="1" required class="mt-0.5 rounded border-gray-300 text-red-600 focus:ring-red-500">
                <span class="text-sm text-gray-700">Saya faham tindakan ini melibatkan <strong>semua pelajar aktif</strong> dan mahu teruskan.</span>
            </label>
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('pelajar.index') }}" class="text-sm text-gray-600">Batal</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Naikkan Darjah Semua Pelajar
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
