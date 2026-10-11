<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Papan Pemuka</h1>
        <p class="mt-1 text-sm text-gray-600">Selamat kembali, {{ Auth::user()->name }}! Berikut ringkasan kewangan hari ini.</p>
    </x-slot>

    <div class="py-2">
        <div class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('laporan.index') }}" class="rounded-lg border border-green-200 bg-green-50 p-6 shadow-sm hover:shadow-md transition block">
                    <p class="text-sm text-gray-600">Kutipan Bulan Ini</p>
                    <p class="mt-2 text-2xl font-bold text-green-600">RM{{ number_format($jumlahKutipanBulanIni, 2) }}</p>
                    <p class="mt-2 text-xs text-gray-400">{{ now()->translatedFormat('F Y') }}</p>
                </a>

                <a href="{{ route('baki-tertunggak.index') }}" class="rounded-lg border border-red-200 bg-red-50 p-6 shadow-sm hover:shadow-md transition block">
                    <p class="text-sm text-gray-600">Baki Belum Dibayar</p>
                    <p class="mt-2 text-2xl font-bold text-red-600">RM{{ number_format($jumlahBakiTertunggak, 2) }}</p>
                    <p class="mt-2 text-xs text-gray-400">Tahun {{ now()->year }}</p>
                </a>

                <a href="{{ route('bayaran.index') }}" class="rounded-lg border border-blue-200 bg-blue-50 p-6 shadow-sm hover:shadow-md transition block">
                    <p class="text-sm text-gray-600">Bayaran Hari Ini</p>
                    <p class="mt-2 text-2xl font-bold text-blue-600">RM{{ number_format($jumlahBayaranHariIni, 2) }}</p>
                    <p class="mt-2 text-xs text-gray-400">{{ now()->format('d/m/Y') }}</p>
                </a>

                <a href="{{ route('permohonan-bayaran.index') }}" class="rounded-lg border border-amber-200 bg-amber-50 p-6 shadow-sm hover:shadow-md transition block">
                    <p class="text-sm text-gray-600">Permohonan Menunggu</p>
                    <p class="mt-2 text-2xl font-bold text-amber-600">{{ $jumlahPermohonanMenunggu }}</p>
                    <p class="mt-2 text-xs text-gray-400">Perlu disahkan</p>
                </a>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Trend Kutipan (6 Bulan Terakhir)</h3>
                    </div>
                    <div class="p-6">
                        <canvas id="cartaTrendKutipan" height="140"></canvas>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Bayaran Terkini</h3>
                        <a href="{{ route('bayaran.index') }}" class="text-xs text-blue-600 hover:underline">Lihat Semua</a>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @forelse ($bayaranTerkini as $item)
                            <div class="px-6 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $item->pelajar->nama ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->no_bayaran }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $item->tarikh_bayaran?->format('d/m/Y') }}</p>
                                    </div>
                                    <p class="text-sm font-semibold text-green-600 flex-shrink-0">RM{{ number_format($item->amaun, 2) }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center text-sm text-gray-400">Tiada bayaran direkodkan lagi.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('cartaTrendKutipan');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($trendBulanan->pluck('label')) !!},
                    datasets: [{
                        label: 'Kutipan (RM)',
                        data: {!! json_encode($trendBulanan->pluck('jumlah')) !!},
                        backgroundColor: '#4f46e5',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => 'RM' + v } } }
                }
            });
        });
    </script>
</x-app-layout>
