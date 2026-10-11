@if (count($items) > 1)
    <nav aria-label="Breadcrumb" class="bg-white border-b border-gray-200 bg-gray-50 px-4 py-2 sm:px-6 lg:px-4 print:hidden">
        <ol class="flex flex-wrap items-center gap-1 text-sm text-gray-500 p-4">
            @foreach ($items as $item)
                <li class="flex items-center gap-1">
                    @if (! $loop->first)
                        <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
                    @endif
                    @if ($loop->first)
                        <i class="fas fa-house text-xs"></i>
                    @endif
                    @if ($item['url'] && ! $loop->last)
                        <a href="{{ $item['url'] }}" class="hover:text-gray-700">{{ $item['label'] }}</a>
                    @else
                        <span class="font-medium text-gray-700">{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
