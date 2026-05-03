@if ($paginator->total() > 0)
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pt-3 border-top modern-pagination-bar">
        <small class="text-muted modern-pagination-summary">
            Showing <strong class="text-dark">{{ $paginator->firstItem() }}</strong>–<strong class="text-dark">{{ $paginator->lastItem() }}</strong>
            of <strong class="text-dark">{{ $paginator->total() }}</strong>
        </small>
        <small class="text-muted">Page {{ $paginator->currentPage() }} of {{ max(1, $paginator->lastPage()) }}</small>
    </div>
@endif
@if ($paginator->total() > 0)
    <nav aria-label="Pagination" class="d-flex justify-content-center flex-wrap mt-2 mb-1">
        <ul class="pagination pagination-sm modern-pagination mb-0 flex-wrap justify-content-center">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link modern-page-link"><i class="fas fa-chevron-left small me-1"></i>Prev</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link modern-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="fas fa-chevron-left small me-1"></i>Prev</a>
                </li>
            @endif

            @if ($paginator->hasPages())
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled d-none d-sm-block" aria-disabled="true">
                            <span class="page-link modern-page-link modern-page-ellipsis">{{ $element }}</span>
                        </li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link modern-page-link modern-page-active">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link modern-page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @else
                <li class="page-item active" aria-current="page">
                    <span class="page-link modern-page-link modern-page-active">1</span>
                </li>
            @endif

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link modern-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next<i class="fas fa-chevron-right small ms-1"></i></a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link modern-page-link">Next<i class="fas fa-chevron-right small ms-1"></i></span>
                </li>
            @endif
        </ul>
    </nav>

    <style>
        .modern-pagination-bar {
            border-color: rgba(148, 163, 184, 0.35) !important;
        }
        .modern-pagination .modern-page-link {
            border: 1px solid rgba(148, 163, 184, 0.45);
            margin: 0.2rem;
            color: #334155;
            background: #fff;
            border-radius: 0.5rem;
            padding: 0.35rem 0.75rem;
            font-weight: 500;
            min-width: 2.25rem;
            text-align: center;
        }
        .modern-pagination .modern-page-link:hover {
            color: #1d4ed8;
            background: rgba(37, 99, 235, 0.06);
            border-color: rgba(37, 99, 235, 0.35);
        }
        .modern-pagination .page-item.active .modern-page-active,
        .modern-pagination .page-item.active .page-link.modern-page-active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
        }
        .modern-pagination .page-item.disabled .modern-page-link {
            color: #94a3b8;
            background: #f8fafc;
        }
        .modern-page-ellipsis {
            border: none !important;
            background: transparent !important;
        }
    </style>
@endif
