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
    <div class="tw:p-3 tw:flex tw:items-center tw:justify-between tw:flex-wrap tw:gap-4">
        <div class="tw:flex tw:items-center tw:gap-2 tw:text-sm">
            <label for="per_page" class="tw:whitespace-nowrap">{{ __("Per page") }}</label>
            <select id="per_page" class="form-control input-sm tw:w-auto" onchange="window.location.href = $(this).val()">
                @foreach($paginationOptions as $option)
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => $option, 'page' => 1]) }}" {{ $perPage == $option ? ' selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div>{{ $devices->appends(request()->all())->links() }}</div>
    </div>
</x-slot>


