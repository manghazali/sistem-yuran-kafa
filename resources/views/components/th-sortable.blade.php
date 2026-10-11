@props(['lajur', 'urutSemasa' => null, 'arahSemasa' => 'asc', 'align' => 'left'])

@php
    $arahBaharu = ($urutSemasa === $lajur && $arahSemasa === 'asc') ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['urut' => $lajur, 'arah' => $arahBaharu, 'page' => null]);
    $alignClass = match ($align) {
        'center' => 'text-center justify-center',
        'right' => 'text-right justify-end',
        default => 'text-left justify-start',
    };
@endphp

<th {{ $attributes->merge(['class' => "border border-gray-200 px-6 py-2 $alignClass text-sm font-medium text-gray-500"]) }}>
    <a href="{{ $url }}" class="inline-flex items-center gap-1.5 hover:text-gray-800">
        {{ $slot }}
        @if ($urutSemasa === $lajur)
            <i class="fas fa-sort-{{ $arahSemasa === 'asc' ? 'up' : 'down' }} text-[10px]"></i>
        @else
            <i class="fas fa-sort text-[10px] text-gray-300"></i>
        @endif
    </a>
</th>
