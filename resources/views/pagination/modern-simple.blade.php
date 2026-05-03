@if ($paginator->hasPages())
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 modern-pagination-meta">
        <small class="text-muted">
            Showing {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }}
        </small>
    </div>
    <nav aria-label="Pagination Navigation" class="d-flex justify-content-center mt-2">
        <ul class="pagination pagination-sm modern-pagination mb-0">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link rounded-pill px-3">Previous</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link rounded-pill px-3" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
                </li>
            @endif

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link rounded-pill px-3" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link rounded-pill px-3">Next</span>
                </li>
            @endif
        </ul>
    </nav>

    <style>
        .modern-pagination-meta {
            border-top: 1px solid #eef2f7;
            padding-top: 8px;
        }
    </style>
@endif
