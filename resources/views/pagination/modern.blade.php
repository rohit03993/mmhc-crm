@if ($paginator->hasPages())
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 modern-pagination-meta">
        <small class="text-muted">
            Showing {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} of {{ $paginator->total() }}
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

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link rounded-pill px-3">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link rounded-pill px-3" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

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
        .modern-pagination .page-link {
            border: 1px solid #e3e8ef;
            margin: 0 3px;
            color: #34495e;
            background: #fff;
        }
        .modern-pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.25);
        }
        .modern-pagination .page-link:hover {
            color: #2c3e50;
            background: #f8fafc;
            border-color: #d6deeb;
        }
        .modern-pagination-meta {
            border-top: 1px solid #eef2f7;
            padding-top: 8px;
        }
    </style>
@endif
