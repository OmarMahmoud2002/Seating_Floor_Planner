@if ($paginator->hasPages())
    <nav class="pagination-nav" role="navigation" aria-label="التنقل بين الصفحات">
        <p class="pagination-summary">
            عرض {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} من {{ $paginator->total() }} نتيجة
        </p>

        <div class="pagination-list">
            @if ($paginator->onFirstPage())
                <span class="pagination-btn disabled" aria-disabled="true">السابق</span>
            @else
                <a class="pagination-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">السابق</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination-dots" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-page active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination-page" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pagination-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">التالي</a>
            @else
                <span class="pagination-btn disabled" aria-disabled="true">التالي</span>
            @endif
        </div>
    </nav>
@endif
