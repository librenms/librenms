<x-panel>
    <div class="tw:flex tw:justify-between tw:flex-wrap tw:gap-2">
        <x-filter name="vminfo" :fields="$filterFields" :initial="$filter" :reload="true"/>
        <x-date-range-picker :start="$from" :end="$to" :reload="true"></x-date-range-picker>
    </div>
    <hr class="tw:my-5"/>

    @if(empty($tiles))
        <p class="text-muted">{{ __('No virtual machines with graphs found.') }}</p>
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
        <x-pagination-footer :paginator="$vms" :options="\App\View\Components\VminfoGraphs::PER_PAGE_OPTIONS"/>
    </x-slot>
</x-panel>
