<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Papan Pemuka</h1>
        <p class="mt-1 text-sm text-gray-600">Selamat kembali, {{ Auth::user()->name }}!</p>
    </x-slot>

    <div class="py-2">
        <div class="space-y-6">
            @if (! $guru)
                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                    Akaun anda tidak dipautkan kepada mana-mana rekod Guru. Sila hubungi pihak pentadbiran.
                </div>
            @elseif ($guru->guruKelas->isEmpty())
                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                    Anda belum ditetapkan kepada mana-mana Kelas. Sila hubungi pihak pentadbiran untuk tetapkan Kelas dan Tahun anda.
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <p class="text-sm text-gray-600 mb-2">Kelas Saya ({{ $guru->guruKelas->count() }})</p>
                        <div class="max-h-32 overflow-y-auto space-y-1 pr-1">
                            @foreach ($guru->guruKelas as $gk)
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $gk->kelas->nama_kelas ?? '-' }}
                                    <span class="text-xs font-normal text-gray-400">({{ $gk->darjah ?? 'Semua Tahun' }})</span>
                                </p>
                            @endforeach
                        </div>
                    </div>

                    <a href="{{ route('pelajar.index') }}" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition block">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Jumlah Pelajar</p>
                                <p class="mt-2 text-2xl font-bold text-gray-900">{{ $pelajarKelasSaya->count() }}</p>
                                <p class="mt-2 text-xs text-gray-400">Merentasi semua kelas anda, aktif</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('permohonan-bayaran.create') }}" class="rounded-lg bg-green-700 text-white p-6 shadow-sm hover:bg-green-800 transition block">
                        <p class="font-semibold">+ Laporkan Bayaran Diterima</p>
                        <p class="text-sm text-green-100 mt-1">Terima bayaran tunai daripada ibu bapa/penjaga? Laporkan di sini.</p>
                    </a>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2 rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">Status Bayaran Pelajar Mengikut Kelas</h3>
                            <p class="text-sm text-gray-500 mt-1">Tahun {{ now()->year }} — bilangan pelajar Telah Bayar berbanding Tertunggak bagi setiap kelas anda.</p>
                        </div>
                        <div class="p-6">
                            <canvas id="cartaStatusBayaranGuru" height="120"></canvas>
                        </div>
                    </div>

                    <a href="{{ route('permohonan-bayaran.index') }}" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition block">
                        <p class="text-sm text-gray-600 mb-3">Permohonan Bayaran Saya</p>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div>
                                <p class="text-xl font-bold text-yellow-600">{{ $statusPermohonan['Menunggu'] }}</p>
                                <p class="text-xs text-gray-400">Menunggu</p>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-green-600">{{ $statusPermohonan['Disahkan'] }}</p>
                                <p class="text-xs text-gray-400">Disahkan</p>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-red-600">{{ $statusPermohonan['Ditolak'] }}</p>
                                <p class="text-xs text-gray-400">Ditolak</p>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </div>

    @if ($guru && $guru->guruKelas->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const ctxStatusBayaranGuru = document.getElementById('cartaStatusBayaranGuru');
                if (ctxStatusBayaranGuru) {
                    new Chart(ctxStatusBayaranGuru, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($statusBayaranMengikutKelas->pluck('label')) !!},
                            datasets: [
                                {
                                    label: 'Telah Bayar',
                                    data: {!! json_encode($statusBayaranMengikutKelas->pluck('lunas')) !!},
                                    backgroundColor: '#16a34a',
                                    borderRadius: 6,
                                },
                                {
                                    label: 'Tertunggak',
                                    data: {!! json_encode($statusBayaranMengikutKelas->pluck('tertunggak')) !!},
                                    backgroundColor: '#dc2626',
                                    borderRadius: 6,
                                },
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: true, position: 'bottom' } },
                            scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } }
                        }
                    });
                }
            });
        </script>
    @endif
</x-app-layout>
