<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $tajuk }}</h2>
    </x-slot>

    {{-- Paparan telefon --}}
    <div class="lg:hidden">
        <x-portal.tajuk-telefon tajuk="Yuran" :subtajuk="$tajuk">
            <div class="grid grid-cols-3 gap-1 rounded-full border border-gray-200 bg-white p-1 text-center text-sm font-semibold shadow-sm">
                @foreach (['portal.yuran.semua' => 'Semua', 'portal.yuran.belum-bayar' => 'Belum Bayar', 'portal.yuran.bulanan' => 'Bulanan'] as $laluan => $label)
                    <a href="{{ route($laluan, request()->only('pelajar_id')) }}"
                       class="rounded-full py-2 {{ request()->routeIs($laluan) ? 'bg-green-700 text-white' : 'text-gray-600' }}">{{ $label }}</a>
                @endforeach
            </div>
        </x-portal.tajuk-telefon>

        @if ($anak->count() > 1)
            <form method="GET" class="mb-4">
                <x-select name="pelajar_id" onchange="this.form.submit()" class="w-full rounded-xl py-2.5 text-[15px]">
                    <option value="">Semua Anak</option>
                    @foreach ($anak as $pelajar)
                        <option value="{{ $pelajar->id }}" @selected($pelajarDipilih?->id === $pelajar->id)>{{ $pelajar->nama }}</option>
                    @endforeach
                </x-select>
            </form>
        @endif

        <div class="space-y-3">
            @forelse ($senaraiItem as $item)
                @php
                    $warna = match ($item['status']) {
                        'Dibayar' => 'bg-green-100 text-green-800',
                        'Sebahagian' => 'bg-yellow-100 text-yellow-800',
                        default => 'bg-red-100 text-red-800',
                    };
                @endphp
                <div class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-900">{{ $item['nama'] }}</p>
                        <p class="truncate text-xs text-gray-500">{{ $item['pelajar']->nama }}</p>
                        <span class="mt-2 inline-block rounded-full px-2 py-0.5 text-xs font-medium {{ $warna }}">{{ $item['status'] }}</span>
                    </div>
                    <p class="flex-shrink-0 text-base font-bold {{ $item['status'] === 'Dibayar' ? 'text-green-700' : 'text-gray-900' }}">
                        RM{{ number_format($item['status'] === 'Dibayar' ? $item['amaun'] : $item['baki'], 2) }}
                    </p>
                </div>
            @empty
                <p class="rounded-2xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">Tiada rekod yuran dijumpai.</p>
            @endforelse
        </div>

        <div class="mt-4">{{ $senaraiItem->links('vendor.pagination.compact') }}</div>
    </div>

    {{-- Paparan desktop --}}
    <div class="hidden lg:block py-2">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-50">
                    <tr>
                        <th class="border border-gray-200 px-6 py-2 text-left text-sm font-medium text-gray-500">Bil.</th>
                        <x-th-sortable lajur="anak" :urut-semasa="$urut" :arah-semasa="$arah">Anak</x-th-sortable>
                        <x-th-sortable lajur="nama" :urut-semasa="$urut" :arah-semasa="$arah">Yuran</x-th-sortable>
                        <x-th-sortable lajur="status" :urut-semasa="$urut" :arah-semasa="$arah">Status</x-th-sortable>
                        <x-th-sortable lajur="amaun" :urut-semasa="$urut" :arah-semasa="$arah" align="right">Amaun (RM)</x-th-sortable>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($senaraiItem as $item)
                        @php
                            $warna = match ($item['status']) {
                                'Dibayar' => 'bg-green-100 text-green-800',
                                'Sebahagian' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-red-100 text-red-800',
                            };
                        @endphp
                        <tr>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $loop->iteration + ($senaraiItem->currentPage() - 1) * $senaraiItem->perPage() }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item['pelajar']->nama }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">{{ $item['nama'] }}</td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full {{ $warna }}">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                            <td class="border border-gray-200 px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                RM{{ number_format($item['status'] === 'Dibayar' ? $item['amaun'] : $item['baki'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="border border-gray-200 px-6 py-2 text-center text-sm text-gray-500">Tiada rekod yuran dijumpai.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="p-4">{{ $senaraiItem->links('vendor.pagination.compact') }}</div>
        </div>
    </div>
</x-app-layout>
