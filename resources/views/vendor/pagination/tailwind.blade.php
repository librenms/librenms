@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="tw:flex tw:items-center tw:justify-between">
        <div class="tw:flex tw:justify-between tw:flex-1 tw:sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:text-sm tw:font-medium tw:text-gray-500 tw:bg-white tw:border tw:border-gray-300 tw:cursor-default tw:leading-5 tw:rounded-md tw:dark:text-gray-600 tw:dark:bg-gray-800 tw:dark:border-gray-600">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:text-sm tw:font-medium tw:text-gray-700 tw:bg-white tw:border tw:border-gray-300 tw:leading-5 tw:rounded-md tw:hover:text-gray-500 tw:focus:outline-hidden tw:focus:ring tw:ring-gray-300 tw:focus:border-blue-300 tw:active:bg-gray-100 tw:active:text-gray-700 tw:transition tw:ease-in-out tw:duration-150 tw:dark:bg-gray-800 tw:dark:border-gray-600 tw:dark:text-gray-300 tw:dark:focus:border-blue-700 tw:dark:active:bg-gray-700 tw:dark:active:text-gray-300">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:ml-3 tw:text-sm tw:font-medium tw:text-gray-700 tw:bg-white tw:border tw:border-gray-300 tw:leading-5 tw:rounded-md tw:hover:text-gray-500 tw:focus:outline-hidden tw:focus:ring tw:ring-gray-300 tw:focus:border-blue-300 tw:active:bg-gray-100 tw:active:text-gray-700 tw:transition tw:ease-in-out tw:duration-150 tw:dark:bg-gray-800 tw:dark:border-gray-600 tw:dark:text-gray-300 tw:dark:focus:border-blue-700 tw:dark:active:bg-gray-700 tw:dark:active:text-gray-300">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:ml-3 tw:text-sm tw:font-medium tw:text-gray-500 tw:bg-white tw:border tw:border-gray-300 tw:cursor-default tw:leading-5 tw:rounded-md tw:dark:text-gray-600 tw:dark:bg-gray-800 tw:dark:border-gray-600">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <div class="tw:hidden tw:sm:flex-1 tw:sm:flex tw:sm:items-center tw:sm:justify-between">
            <div>
                <p class="tw:text-sm tw:text-gray-700 tw:leading-5 tw:dark:text-gray-400 tw:mr-2">
                    @if ($paginator->firstItem())
                        <span class="tw:font-medium">{{ $paginator->firstItem() }}</span>-<span class="tw:font-medium">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="tw:font-medium">{{ $paginator->total() }}</span>
                </p>
            </div>

            <div>
                <span class="tw:relative tw:z-0 tw:inline-flex tw:rtl:flex-row-reverse tw:shadow-sm tw:rounded-md">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="tw:relative tw:inline-flex tw:items-center tw:px-2 tw:py-2 tw:text-sm tw:font-medium tw:text-gray-500 tw:bg-white tw:border tw:border-gray-300 tw:cursor-default tw:rounded-l-md tw:leading-5 tw:dark:bg-gray-800 tw:dark:border-gray-600" aria-hidden="true">
                                <svg class="tw:w-5 tw:h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="tw:relative tw:inline-flex tw:items-center tw:px-2 tw:py-2 tw:text-sm tw:font-medium tw:text-gray-500 tw:bg-white tw:border tw:border-gray-300 tw:rounded-l-md tw:leading-5 tw:hover:text-gray-400 tw:focus:z-10 tw:focus:outline-hidden tw:focus:ring tw:ring-gray-300 tw:focus:border-blue-300 tw:active:bg-gray-100 tw:active:text-gray-500 tw:transition tw:ease-in-out tw:duration-150 tw:dark:bg-gray-800 tw:dark:border-gray-600 tw:dark:active:bg-gray-700 tw:dark:focus:border-blue-800" aria-label="{{ __('pagination.previous') }}">
                            <svg class="tw:w-5 tw:h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:-ml-px tw:text-sm tw:font-medium tw:text-gray-700 tw:bg-white tw:border tw:border-gray-300 tw:cursor-default tw:leading-5 tw:dark:bg-gray-800 tw:dark:border-gray-600">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:-ml-px tw:text-sm tw:font-medium tw:text-gray-500 tw:bg-white tw:border tw:border-gray-300 tw:cursor-default tw:leading-5 tw:dark:bg-gray-800 tw:dark:border-gray-600">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:-ml-px tw:text-sm tw:font-medium tw:text-gray-700 tw:bg-white tw:border tw:border-gray-300 tw:leading-5 tw:hover:text-gray-500 tw:focus:z-10 tw:focus:outline-hidden tw:focus:ring tw:ring-gray-300 tw:focus:border-blue-300 tw:active:bg-gray-100 tw:active:text-gray-700 tw:transition tw:ease-in-out tw:duration-150 tw:dark:bg-gray-800 tw:dark:border-gray-600 tw:dark:text-gray-400 tw:dark:hover:text-gray-300 tw:dark:active:bg-gray-700 tw:dark:focus:border-blue-800" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="tw:relative tw:inline-flex tw:items-center tw:px-2 tw:py-2 tw:-ml-px tw:text-sm tw:font-medium tw:text-gray-500 tw:bg-white tw:border tw:border-gray-300 tw:rounded-r-md tw:leading-5 tw:hover:text-gray-400 tw:focus:z-10 tw:focus:outline-hidden tw:focus:ring tw:ring-gray-300 tw:focus:border-blue-300 tw:active:bg-gray-100 tw:active:text-gray-500 tw:transition tw:ease-in-out tw:duration-150 tw:dark:bg-gray-800 tw:dark:border-gray-600 tw:dark:active:bg-gray-700 tw:dark:focus:border-blue-800" aria-label="{{ __('pagination.next') }}">
                            <svg class="tw:w-5 tw:h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="tw:relative tw:inline-flex tw:items-center tw:px-2 tw:py-2 tw:-ml-px tw:text-sm tw:font-medium tw:text-gray-500 tw:bg-white tw:border tw:border-gray-300 tw:cursor-default tw:rounded-r-md tw:leading-5 tw:dark:bg-gray-800 tw:dark:border-gray-600" aria-hidden="true">
                                <svg class="tw:w-5 tw:h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
