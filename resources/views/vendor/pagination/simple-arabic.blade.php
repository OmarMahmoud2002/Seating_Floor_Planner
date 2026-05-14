@if ($paginator->hasPages())
    <nav class="pagination-nav" role="navigation" aria-label="التنقل بين الصفحات">
        <div class="pagination-list">
            @if ($paginator->onFirstPage())
                <span class="pagination-btn disabled" aria-disabled="true">السابق</span>
            @else
                <a class="pagination-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">السابق</a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="pagination-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">التالي</a>
            @else
                <span class="pagination-btn disabled" aria-disabled="true">التالي</span>
            @endif
        </div>
    </nav>
@endif
