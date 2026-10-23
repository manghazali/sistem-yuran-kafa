{{-- Suis Sejarah / Status untuk tab "Bayaran" di paparan telefon --}}
<div class="mb-5 grid grid-cols-2 gap-1 rounded-full border border-gray-200 bg-white p-1 text-center text-sm font-semibold shadow-sm lg:hidden">
    @foreach (['portal.sejarah-bayaran' => 'Sejarah', 'portal.status-pembayaran' => 'Status Dalam Talian'] as $laluan => $label)
        <a href="{{ route($laluan) }}"
           class="rounded-full py-2 {{ request()->routeIs($laluan) ? 'bg-green-700 text-white' : 'text-gray-600' }}">{{ $label }}</a>
    @endforeach
</div>
