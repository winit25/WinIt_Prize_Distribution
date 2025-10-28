@if ($paginator->hasPages())
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>

    <div class="pagination-info text-center mt-3">
        <p class="text-muted">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} 
            of {{ $paginator->total() }} results
        </p>
    </div>
@endif

<style>
.pagination {
    margin: 0;
}

.page-link {
    border: 2px solid var(--border-light);
    color: var(--text-dark);
    font-weight: 500;
    padding: 0.75rem 1rem;
    margin: 0 0.25rem;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    text-decoration: none;
}

.page-link:hover {
    background-color: var(--secondary-blue);
    border-color: var(--secondary-blue);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.page-item.active .page-link {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    border-color: var(--secondary-blue);
    color: white;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.page-item.disabled .page-link {
    background-color: var(--bg-light);
    border-color: var(--border-light);
    color: var(--text-gray);
    cursor: not-allowed;
}

.pagination-info {
    font-size: 0.875rem;
}
</style>
