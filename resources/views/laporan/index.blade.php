<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Laporan Kewangan') }}</h2>
    </x-slot>

    <div class="py-2 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-lg border border-green-200 bg-green-50 p-6 shadow-sm">
                <p class="text-sm text-gray-600">Jumlah Dikutip (Keseluruhan)</p>
                <p class="mt-2 text-2xl font-bold text-green-600">RM{{ number_format($jumlahDikutip, 2) }}</p>
                <p class="mt-2 text-xs text-gray-400">Sejak sistem digunakan</p>
            </div>

            <div class="rounded-lg border border-blue-200 bg-blue-50 p-6 shadow-sm">
                <p class="text-sm text-gray-600">Kutipan Bulan Ini</p>
                <p class="mt-2 text-2xl font-bold text-blue-600">RM{{ number_format($kutipanBulanIni, 2) }}</p>
                <p class="mt-2 text-xs text-gray-400">{{ now()->translatedFormat('F Y') }}</p>
            </div>

            <div class="rounded-lg border border-red-200 bg-red-50 p-6 shadow-sm">
                <p class="text-sm text-gray-600">Baki Belum Dibayar</p>
                <p class="mt-2 text-2xl font-bold text-red-600">RM{{ number_format($jumlahTertunggak, 2) }}</p>
                <p class="mt-2 text-xs text-gray-400">Merentasi semua pelajar aktif</p>
            </div>

            <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-6 shadow-sm">
                <p class="text-sm text-gray-600">Bayaran Hari Ini</p>
                <p class="mt-2 text-2xl font-bold text-indigo-600">RM{{ number_format($jumlahBayaranHariIni, 2) }}</p>
                <p class="mt-2 text-xs text-gray-400">{{ now()->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Bayaran Mengikut Tahun</h3>
                </div>
                <div class="p-6 h-48">
                    <canvas id="cartaBayaranTahun"></canvas>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Bayaran Mengikut Kelas</h3>
                </div>
                <div class="p-6 h-48">
                    <canvas id="cartaBayaranKelas"></canvas>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Laporan Terperinci</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('laporan.kutipan') }}" class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md hover:border-green-300 transition block">
                    <div class="absolute left-0 top-0 h-full w-1 bg-green-500"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-semibold text-lg text-gray-900">Laporan Kutipan</p>
                            <p class="text-sm text-gray-500 mt-1">Senarai bayaran mengikut julat tarikh, pecahan ikut kaedah bayaran, boleh muat turun CSV.</p>
                        </div>
                        <span class="text-green-600 text-xl flex-shrink-0 transition-transform group-hover:translate-x-1">&rarr;</span>
                    </div>
                </a>
                <a href="{{ route('baki-tertunggak.index') }}" class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md hover:border-red-300 transition block">
                    <div class="absolute left-0 top-0 h-full w-1 bg-red-500"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-semibold text-lg text-gray-900">Laporan Baki Belum Dibayar</p>
                            <p class="text-sm text-gray-500 mt-1">Senarai pelajar dengan bulan belum dibayar, dikumpul mengikut pelajar.</p>
                        </div>
                        <span class="text-red-600 text-xl flex-shrink-0 transition-transform group-hover:translate-x-1">&rarr;</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new Chart(document.getElementById('cartaBayaranTahun'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($bayaranMengikutTahun->pluck('label')) !!},
                    datasets: [{
                        label: 'Jumlah (RM)',
                        data: {!! json_encode($bayaranMengikutTahun->pluck('jumlah')) !!},
                        backgroundColor: '#4f46e5',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => 'RM' + v } } }
                }
            });

            new Chart(document.getElementById('cartaBayaranKelas'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($bayaranMengikutKelas->pluck('label')) !!},
                    datasets: [{
                        label: 'Jumlah (RM)',
                        data: {!! json_encode($bayaranMengikutKelas->pluck('jumlah')) !!},
                        backgroundColor: '#16a34a',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => 'RM' + v } } }
                }
            });
        });
    </script>
</x-app-layout>
