{{-- nothing matched the search: what was searched, why it may still exist, and where to look next --}}
<div class="tw:grid tw:gap-4">
    <x-panel class="tw:mb-0!">
        <div class="tw:flex tw:flex-col tw:items-center tw:justify-center tw:py-5 tw:text-center">
            @if($query !== '')
                <h3 class="tw:font-mono tw:font-semibold tw:text-lg tw:mt-0 tw:mb-1 tw:wrap-anywhere">{{ \Illuminate\Support\Str::limit($query, 120) }}</h3>
            @endif

            <x-help-heading id="not-found" :label="$query === '' || $hasFilters ? __('search.not_found.empty_search') : __('search.not_found.title')" class="tw:mb-3 tw:w-full tw:[&>div]:justify-center">
                {{ $hasFilters ? __('search.not_found.filtered') : __('search.not_found.checked') . ' ' . __('search.not_found.still_possible') }}
            </x-help-heading>

            @if($resolved)
                <p class="tw:mb-3">
                    <span class="tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.not_found.resolves_to') }}</span>
                    @foreach($resolved as $ip)
                        <span class="tw:font-mono">{{ $ip->compressed() }}</span>
                    @endforeach
                </p>
            @endif

            @if($vendor)
                <p class="tw:mb-3"><span class="tw:text-gray-500 tw:dark:text-dark-white-400">{{ __('search.not_found.vendor') }}</span> {{ $vendor }}</p>
            @endif

            <div class="tw:flex tw:flex-wrap tw:justify-center tw:gap-2">
                @if($hasFilters)
                    <a class="btn btn-default" href="{{ route('search', ['q' => $query, 'filter' => '']) }}">{{ __('search.not_found.without_filters') }}</a>
                @endif
                @if($network)
                    <a class="btn btn-default" href="{{ route('search', ['q' => $network]) }}">{{ __('search.not_found.search_network', ['network' => $network]) }}</a>
                @endif
                @if($ouiPrefix)
                    <a class="btn btn-default" href="{{ route('search', ['q' => $ouiPrefix]) }}">{{ __('search.not_found.search_vendor_prefix', ['prefix' => $ouiPrefix]) }}</a>
                @endif
            </div>
        </div>
    </x-panel>

    @if($subnet?->count() > 0)
        <x-device.overview.panel :title="__('search.host.same_subnet')" icon="fa fa-sitemap" class="tw:mb-0!">
            <div class="tw:p-3 tw:grid tw:gap-2 tw:sm:grid-cols-2 tw:lg:grid-cols-3">
                @include('search.partials.subnet', ['subnet' => $subnet, 'limit' => 9, 'itemClass' => 'tw:min-w-0'])
            </div>
            @if($subnet->count() > 9)
                <div class="tw:px-3 tw:pb-3">@include('search.partials.subnet-more', ['subnet' => $subnet, 'limit' => 9])</div>
            @endif
        </x-device.overview.panel>
    @endif
</div>
