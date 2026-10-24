@props(['tajuk', 'subtajuk' => null, 'kembali' => null])

@php
    // Setiap halaman dalaman ada butang kembali; lalai ke Portal Penjaga.
    $kembali ??= route('portal.index');
@endphp

{{--
    Tajuk halaman Portal Penjaga untuk paparan telefon (header biasa disembunyikan di telefon).
    Kekal di atas (sticky) semasa kandungan diskrol. Kandungan slot (cth. suis tab) turut kekal.
--}}
<div class="sticky top-0 z-20 -mx-4 -mt-5 mb-4 bg-gray-50/95 px-4 pt-5 pb-3 backdrop-blur lg:hidden print:hidden">
    <div class="flex items-center gap-3 {{ $slot->isNotEmpty() ? 'mb-4' : '' }}">
        <a href="{{ $kembali }}" class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm" aria-label="Kembali">
            <i class="fas fa-chevron-left text-sm"></i>
        </a>
        <div class="min-w-0">
            <h2 class="truncate text-xl font-bold text-gray-900">{{ $tajuk }}</h2>
            @if ($subtajuk)
                <p class="truncate text-sm text-gray-500">{{ $subtajuk }}</p>
            @endif
        </div>
    </div>

    {{ $slot }}
</div>
