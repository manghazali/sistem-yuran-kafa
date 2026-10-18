<x-app-layout :latar-putih="true">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Resit Rasmi') }}</h2>
            <button onclick="window.print()" class="text-sm text-gray-600 hover:underline print:hidden">Cetak Resit</button>
        </div>
    </x-slot>

    <div class="py-4 print:py-0">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:px-0">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md print:hidden">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 print:shadow-none print:p-0 text-gray-900">

                <div class="flex justify-between items-start gap-4 mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Sekolah" class="h-32 w-32 object-contain shrink-0">
                    <div class="text-right">
                        <h1 class="text-xl font-bold uppercase">{{ \App\Models\Tetapan::get('sekolah_nama', "KAFA Integrasi Darus Sa'adah") }}</h1>
                        @foreach (explode("\n", \App\Models\Tetapan::get('sekolah_alamat', "Jalan DC 2/4, Desa Coalfields\n47000 Sungai Buloh\nSelangor")) as $barisAlamat)
                            <p class="text-xs text-gray-500 mt-1">{{ trim($barisAlamat) }}</p>
                        @endforeach
                        <p class="text-xs text-gray-500">Phone: {{ \App\Models\Tetapan::get('sekolah_telefon', '03-6038 8306') }}</p>
                    </div>
                </div>

                <div class="border-t-2 border-gray-800 mb-6"></div>

                <h2 class="text-center text-lg font-bold uppercase tracking-widest mb-6">Resit Pembayaran</h2>

                <p class="text-right text-sm font-bold mb-4">NO RESIT : {{ $bayaran->noResit() }}</p>

                <table class="text-sm mb-6">
                    <tr>
                        <td class="font-bold align-top pr-2 py-0.5">NAMA</td>
                        <td class="align-top py-0.5">: {{ strtoupper($bayaran->pelajar->nama ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold align-top pr-2 py-0.5">EMEL</td>
                        <td class="align-top py-0.5">: {{ strtoupper($bayaran->pelajar->penjaga->email ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold align-top pr-2 py-0.5">TAHUN</td>
                        <td class="align-top py-0.5">: {{ $bayaran->pelajar->darjah ? str_replace('Tahun ', '', $bayaran->pelajar->darjah) : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold align-top pr-2 py-0.5">KELAS</td>
                        <td class="align-top py-0.5">: {{ strtoupper($bayaran->pelajar->kelas->nama_kelas ?? '-') }}</td>
                    </tr>
                </table>

                <div class="border-t border-dotted border-gray-400 mb-4"></div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-gray-800">
                            <th class="text-left py-2 font-bold">Yuran</th>
                            <th class="text-left py-2 font-bold">Bulan</th>
                            <th class="text-right py-2 font-bold">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bariResit as $baris)
                            <tr>
                                <td class="py-2">{{ strtoupper($baris['nama']) }}</td>
                                <td class="py-2">{{ $baris['bulan'] ? \Illuminate\Support\Carbon::create($bayaran->tahun, $baris['bulan'], 1)->translatedFormat('F') : '-' }}</td>
                                <td class="py-2 text-right">RM {{ number_format($baris['amaun'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-800">
                            <td colspan="2" class="py-2 font-bold text-right">JUMLAH</td>
                            <td class="py-2 font-bold text-right">RM {{ number_format($jumlahKeseluruhan, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="border-t border-dotted border-gray-400 my-4"></div>

                <p class="text-sm">
                    <span class="font-bold">TARIKH</span> : {{ $bayaran->tarikh_bayaran?->format('d M Y') }}, {{ $bayaran->created_at?->format('h:i A') }}
                </p>

                <div class="text-center text-xs text-gray-400 mt-10 space-y-1">
                    <p>Terima kasih atas pembayaran anda.</p>
                    <p>Resit ini dijana secara komputer dan tidak memerlukan tandatangan.</p>
                    <p>Sekiranya ada sebarang pertanyaan, sila hubungi pentadbiran sekolah.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
