<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Papan Pemuka</h1>
        <p class="mt-1 text-sm text-gray-600">Selamat kembali, {{ Auth::user()->name }}! Berikut ringkasan sistem hari ini.</p>
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

                <a href="{{ route('pelajar.index') }}" class="rounded-lg border border-indigo-200 bg-indigo-50 p-6 shadow-sm hover:shadow-md transition block">
                    <p class="text-sm text-gray-600">Pelajar Aktif</p>
                    <p class="mt-2 text-2xl font-bold text-indigo-600">{{ $jumlahPelajarAktif }}</p>
                    <p class="mt-2 text-xs text-gray-400">Berdaftar & aktif</p>
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
                        <h3 class="text-lg font-semibold text-gray-900">Aktiviti Terkini</h3>
                        @role('Admin')
                            <a href="{{ route('log-audit.index') }}" class="text-xs text-blue-600 hover:underline">Lihat Semua</a>
                        @endrole
                    </div>
                    <div class="divide-y divide-gray-200">
                        @forelse ($aktivitiTerkini as $log)
                            @php
                                $warna = match ($log->tindakan) {
                                    'Cipta' => 'bg-green-100 text-green-600',
                                    'Kemaskini' => 'bg-yellow-100 text-yellow-600',
                                    'Padam' => 'bg-red-100 text-red-600',
                                    'Log Masuk' => 'bg-blue-100 text-blue-600',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                                $ikon = match ($log->tindakan) {
                                    'Cipta' => '+',
                                    'Kemaskini' => '✎',
                                    'Padam' => '×',
                                    'Log Masuk' => '→',
                                    default => '•',
                                };
                            @endphp
                            <div class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full {{ $warna }} flex-shrink-0 font-bold text-sm">
                                        {{ $ikon }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $log->pengguna->name ?? 'Sistem' }} — {{ $log->tindakan }} {{ $log->model }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $log->keterangan }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center text-sm text-gray-400">Tiada aktiviti direkodkan lagi.</div>
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
