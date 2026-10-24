<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sejarah Bayaran') }}</h2>
    </x-slot>

    <div class="py-2">
        <x-portal.tajuk-telefon tajuk="Bayaran" subtajuk="Sejarah bayaran dan resit" />

        @if ($transaksiTertunda->isNotEmpty())
            <div class="mb-4 rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 lg:rounded-lg">
                <p class="font-semibold">
                    <i class="fas fa-hourglass-half mr-1"></i>
                    {{ $transaksiTertunda->count() }} bayaran dalam talian sedang diproses
                </p>
                <ul class="mt-1 space-y-0.5">
                    @foreach ($transaksiTertunda as $t)
                        <li>RM{{ number_format($t->amaun, 2) }} · {{ $t->created_at->format('d/m/Y h:i A') }}</li>
                    @endforeach
                </ul>
                <p class="mt-2 text-yellow-700">Resit akan muncul di sini sebaik sahaja bayaran disahkan. Jangan bayar semula.</p>
            </div>
        @endif

        @if ($anak->count() > 1)
            <div class="mb-4">
                <form method="GET" class="flex gap-3">
                    <x-select name="pelajar_id" onchange="this.form.submit()" class="w-full rounded-xl py-2.5 text-[15px] lg:w-auto lg:rounded-md lg:py-2 lg:text-sm">
                        <option value="">Semua Anak</option>
                        @foreach ($anak as $pelajar)
                            <option value="{{ $pelajar->id }}" @selected($pelajarDipilih?->id === $pelajar->id)>{{ $pelajar->nama }}</option>
                        @endforeach
                    </x-select>
                </form>
            </div>
        @endif

        {{-- Paparan telefon --}}
        <div class="space-y-3 lg:hidden">
            @forelse ($bayaran as $item)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900">{{ $item->keterangan }}</p>
                            <p class="truncate text-xs text-gray-500">{{ $item->pelajar->nama ?? '-' }}</p>
                        </div>
                        <p class="flex-shrink-0 text-base font-bold text-green-700">RM{{ number_format($item->amaun, 2) }}</p>
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3">
                        <p class="text-xs text-gray-500">{{ $item->tarikh_bayaran?->format('d/m/Y') }} · {{ $item->noResit() }}</p>
                        <a href="{{ route('resit.show', $item) }}" class="rounded-full bg-green-600 px-3 py-1 text-xs font-semibold text-white">Resit</a>
                    </div>
                </div>
            @empty
                <p class="rounded-2xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
                    {{ $pelajarDipilih ? 'Tiada bayaran direkodkan lagi untuk '.$pelajarDipilih->nama.'.' : 'Tiada bayaran direkodkan lagi.' }}
                </p>
            @endforelse
            <div class="pt-1">{{ $bayaran->links('vendor.pagination.compact') }}</div>
        </div>

        {{-- Paparan desktop --}}
        <div class="hidden lg:block bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-50">
                    <tr>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                        <x-th-sortable lajur="no_bayaran" :urut-semasa="$urut" :arah-semasa="$arah">No. Resit</x-th-sortable>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Anak</th>
                        <x-th-sortable lajur="keterangan" :urut-semasa="$urut" :arah-semasa="$arah">Keterangan</x-th-sortable>
                        <x-th-sortable lajur="amaun" :urut-semasa="$urut" :arah-semasa="$arah">Amaun (RM)</x-th-sortable>
                        <x-th-sortable lajur="tarikh_bayaran" :urut-semasa="$urut" :arah-semasa="$arah">Tarikh</x-th-sortable>
                        <th class="border border-gray-200 px-6 py-2 text-center text-sm font-medium text-gray-500">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($bayaran as $item)
                        <tr>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($bayaran->currentPage() - 1) * $bayaran->perPage() }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->noResit() }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->pelajar->nama ?? '-' }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->keterangan }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm font-medium text-green-600">{{ number_format($item->amaun, 2) }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item->tarikh_bayaran?->format('d/m/Y') }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-center text-sm">
                                <a href="{{ route('resit.show', $item) }}" class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1 text-sm font-small text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Resit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="border border-gray-200 px-6 py-2 text-center text-sm text-gray-500">
                                {{ $pelajarDipilih ? 'Tiada bayaran direkodkan lagi untuk '.$pelajarDipilih->nama.'.' : 'Tiada bayaran direkodkan lagi.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="p-4">{{ $bayaran->links('vendor.pagination.compact') }}</div>
        </div>
    </div>
</x-app-layout>
