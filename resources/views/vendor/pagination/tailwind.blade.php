@if ($paginator->hasPages())
<nav class="paginator" role="navigation" aria-label="{{ __('Pagination Navigation') }}">

    {{-- Versión compacta: solo Prev / Next (se suele usar en móviles) --}}
    <div class="paginator--compact">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span aria-disabled="true">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </div>

    {{-- Versión completa: texto “Mostrando …” + números de página + flechas --}}
    <div class="paginator--complete">
        <p class="showing">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="ff-semibold">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="ff-semibold">{{ $paginator->lastItem() }} </span>
            @else
                {{ $paginator->count() }}
            @endif
            {!! __('of') !!}
            <span class="ff-semibold">{{ $totalWithHero ?? $paginator->total() }}</span>
             {!! __('results') !!}
        </p>

        <span class="navigation">
            {{-- Enlace a página anterior --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <span aria-hidden="true" class="navigation--left-arrow">
                        {{-- Icono flecha izquierda --}}
                        <svg fill="currentColor" viewBox="0 0 20 20" width="20" height="20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">
                    <svg fill="currentColor" viewBox="0 0 20 20" width="24" height="24">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </a>
            @endif

            {{-- Elementos de paginación --}}
            @foreach ($elements as $element)
                {{-- Separador “…” --}}
                @if (is_string($element))
                    <span aria-disabled="true">{{ $element }}</span>
                @endif

                {{-- Array de páginas [número => url] --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Enlace a página siguiente --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">
                    <svg fill="currentColor" viewBox="0 0 20 20" width="24" height="24">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                </a>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <span aria-hidden="true" class="navigation--right-arrow">
                        {{-- Icono flecha derecha --}}
                        <svg fill="currentColor" viewBox="0 0 20 20" width="20" height="20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                </span>
            @endif

        </span>
    </div>
</nav>
@endif
