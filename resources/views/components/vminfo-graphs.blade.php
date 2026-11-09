<x-panel>
    <div class="tw:flex tw:justify-between tw:flex-wrap tw:gap-2">
        <x-filter name="vminfo" :fields="$filterFields" :initial="$filter" :reload="true"/>
        <x-date-range-picker :start="$from" :end="$to" :reload="true"></x-date-range-picker>
    </div>
    <hr class="tw:my-5"/>

    @if(empty($tiles))
        <p class="text-muted">{{ __('No virtual machines found.') }}</p>
    @else
        <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:lg:grid-cols-4 tw:gap-4">
            @foreach($tiles as $tile)
                <div>
                    <a href="{{ $tile['link'] }}">{!! $tile['graphTag'] !!}</a>
                </div>
            @endforeach
        </div>
    @endif

    <x-slot name="footer">
        <div class="tw:p-3 tw:flex tw:items-center tw:justify-between tw:flex-wrap tw:gap-4">
            <div class="tw:flex tw:items-center tw:gap-2 tw:text-sm">
                <label for="per_page" class="tw:whitespace-nowrap">{{ __('Per page') }}</label>
                <select id="per_page" class="form-control input-sm tw:w-auto" onchange="window.location.href = $(this).val()">
                    @foreach(\App\View\Components\VminfoGraphs::PER_PAGE_OPTIONS as $option)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $option, 'page' => 1]) }}" @selected($vms->perPage() == $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div>{{ $vms->links() }}</div>
        </div>
    </x-slot>
</x-panel>
