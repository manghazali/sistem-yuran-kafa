@props(['tajuk', 'subtajuk' => null, 'kembali' => null])

{{-- Tajuk halaman Portal Penjaga untuk paparan telefon (header biasa disembunyikan di telefon). --}}
<div class="mb-5 flex items-center gap-3 lg:hidden">
    @if ($kembali)
        <a href="{{ $kembali }}" class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm" aria-label="Kembali">
            <i class="fas fa-chevron-left text-sm"></i>
        </a>
    @endif
    <div class="min-w-0">
        <h2 class="truncate text-xl font-bold text-gray-900">{{ $tajuk }}</h2>
        @if ($subtajuk)
            <p class="truncate text-sm text-gray-500">{{ $subtajuk }}</p>
        @endif
    </div>
</div>
