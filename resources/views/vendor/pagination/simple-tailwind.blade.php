@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="tw:flex tw:justify-between">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:text-sm tw:font-medium tw:text-gray-500 tw:bg-white tw:border tw:border-gray-300 tw:cursor-default tw:leading-5 tw:rounded-md tw:dark:text-gray-600 tw:dark:bg-gray-800 tw:dark:border-gray-600">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:text-sm tw:font-medium tw:text-gray-700 tw:bg-white tw:border tw:border-gray-300 tw:leading-5 tw:rounded-md tw:hover:text-gray-500 tw:focus:outline-hidden tw:focus:ring tw:ring-gray-300 tw:focus:border-blue-300 tw:active:bg-gray-100 tw:active:text-gray-700 tw:transition tw:ease-in-out tw:duration-150 tw:dark:bg-gray-800 tw:dark:border-gray-600 tw:dark:text-gray-300 tw:dark:focus:border-blue-700 tw:dark:active:bg-gray-700 tw:dark:active:text-gray-300">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:text-sm tw:font-medium tw:text-gray-700 tw:bg-white tw:border tw:border-gray-300 tw:leading-5 tw:rounded-md tw:hover:text-gray-500 tw:focus:outline-hidden tw:focus:ring tw:ring-gray-300 tw:focus:border-blue-300 tw:active:bg-gray-100 tw:active:text-gray-700 tw:transition tw:ease-in-out tw:duration-150 tw:dark:bg-gray-800 tw:dark:border-gray-600 tw:dark:text-gray-300 tw:dark:focus:border-blue-700 tw:dark:active:bg-gray-700 tw:dark:active:text-gray-300">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:text-sm tw:font-medium tw:text-gray-500 tw:bg-white tw:border tw:border-gray-300 tw:cursor-default tw:leading-5 tw:rounded-md tw:dark:text-gray-600 tw:dark:bg-gray-800 tw:dark:border-gray-600">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
