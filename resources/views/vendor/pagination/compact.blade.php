@if ($paginator->hasPages())
    <div class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-sm text-gray-700">
            {{ __('Memaparkan') }}
            @if ($paginator->firstItem())
                <span class="font-medium">{{ $paginator->firstItem() }}</span>
                {{ __('-') }}
                <span class="font-medium">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {{ __('daripada') }}
            <span class="font-medium">{{ $paginator->total() }}</span>
            {{ __('rekod') }}
        </p>

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center gap-1">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-gray-300 bg-white border border-gray-200 rounded-md cursor-not-allowed">&lsaquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-green-700 bg-white border border-green-300 rounded-md hover:bg-green-50">&lsaquo;</a>
        @endif

        @php
            $lastPage = $paginator->lastPage();
            $current = $paginator->currentPage();
            $leadingPages = min(3, $lastPage);
        @endphp

        {{-- Leading Pages (1, 2, 3) --}}
        @for ($page = 1; $page <= $leadingPages; $page++)
            @if ($page == $current)
                <span aria-current="page" class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium bg-green-600 text-white border border-green-300 rounded-md">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}" class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-green-700 bg-white border border-green-300 rounded-md hover:bg-green-50">{{ $page }}</a>
            @endif
        @endfor

        {{-- Ellipsis + Last Page --}}
        @if ($lastPage > $leadingPages)
            @if ($lastPage > $leadingPages + 1)
                <span class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-gray-400">&hellip;</span>
            @endif

            @if ($current == $lastPage)
                <span aria-current="page" class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium bg-green-600 text-white border border-green-300 rounded-md">{{ $lastPage }}</span>
            @else
                <a href="{{ $paginator->url($lastPage) }}" class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-green-700 bg-white border border-green-300 rounded-md hover:bg-green-50">{{ $lastPage }}</a>
            @endif
        @endif

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-green-700 bg-white border border-green-300 rounded-md hover:bg-green-50">&rsaquo;</a>
        @else
            <span class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-gray-300 bg-white border border-gray-200 rounded-md cursor-not-allowed">&rsaquo;</span>
        @endif
    </nav>
    </div>
@endif
