@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $pages = [];

        if ($lastPage <= 5) {
            $pages = range(1, $lastPage);
        } elseif ($currentPage <= 3) {
            $pages = [1, 2, 3, 'ellipsis', $lastPage];
        } elseif ($currentPage >= $lastPage - 2) {
            $pages = [1, 'ellipsis', $lastPage - 2, $lastPage - 1, $lastPage];
        } else {
            $pages = [1, 'ellipsis', $currentPage - 1, $currentPage, $currentPage + 1, 'ellipsis', $lastPage];
        }
    @endphp

    <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-center">
        <div class="inline-flex flex-wrap items-center gap-1 rounded-full bg-white px-2 py-1">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium text-muted-gray">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M12.5 5L7.5 10L12.5 15" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium text-uber-black transition hover:bg-hover-light">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M12.5 5L7.5 10L12.5 15" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Previous
                </a>
            @endif

            <div class="flex items-center gap-1">
                @foreach ($pages as $page)
                    @if ($page === 'ellipsis')
                        <span class="px-3 py-2 text-sm font-medium text-body-gray">...</span>
                    @elseif ($page === $currentPage)
                        <span aria-current="page" class="inline-flex h-11 min-w-11 items-center justify-center rounded-[14px] border border-[#d9d9d9] bg-white px-4 text-sm font-semibold text-uber-black shadow-sm">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="inline-flex h-11 min-w-11 items-center justify-center rounded-[14px] px-4 text-sm font-semibold text-uber-black transition hover:bg-hover-light">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium text-uber-black transition hover:bg-hover-light">
                    Next
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            @else
                <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium text-muted-gray">
                    Next
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
