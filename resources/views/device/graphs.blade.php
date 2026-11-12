<div class="@if($hideFilter) tw:hidden @else tw:flex tw:justify-between @endif">
    <x-filter name="devices" :fields="$filterFields" id="device-filter" :hide="$hideFilter" :initial="$filter" :reload="true"/>
    <x-date-range-picker :start="$graphTemplate['from'] ?? null" :end="$graphTemplate['to'] ?? null" :reload="true"></x-date-range-picker>
</div>
<hr class="tw:my-5 @if($hideFilter) tw:hidden @endif"/>

<div>
    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:lg:grid-cols-4 tw:gap-4">
        @foreach($deviceGraphs as $graph)
            <div>
                <a href="{{ $graph['link'] }}" x-data="deviceLink(@js($graph['deviceLinkOptions']))">
                    {!! $graph['graphTag'] !!}
                </a>
            </div>
        @endforeach
    </div>
</div>

<x-slot name="footer">
    <x-pagination-footer :paginator="$devices" :options="$paginationOptions" :per-page="$perPage"/>
</x-slot>


