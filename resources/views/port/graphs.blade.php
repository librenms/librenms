<div class="@if($hideFilter) tw:hidden @else tw:flex tw:justify-between @endif">
    <x-filter name="ports" :fields="$filterFields" id="port-filter" :hide="$hideFilter" :initial="$filter" :reload="true"/>
    <x-date-range-picker :start="$graphTemplate['from'] ?? null" :end="$graphTemplate['to'] ?? null"></x-date-range-picker>
</div>
<hr class="tw:my-5 @if($hideFilter) tw:hidden @endif"/>

<div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:lg:grid-cols-4 tw:gap-4">
    @foreach($portGraphs as $graph)
        <div>
            <a href="{{ $graph['link'] }}" x-data="portLink(@js($graph['portLinkOptions']))">
                <img
                    class="graph-image tw:w-full tw:h-auto"
                    style="display:none"
                    src="{{ route('graph', $graph['params']) }}"
                    onerror="this.closest('div').style.display='none'"
                    onload="this.style.display=''"
                />
            </a>
        </div>
    @endforeach
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
        <div>{{ $ports->appends(request()->all())->links() }}</div>
    </div>
</x-slot>

@push('scripts')
<script>
    window.addEventListener('date-range-changed', (event) => {
        const { relativeStartSeconds, relativeEndSeconds, start, end } = event.detail;
        const url = new URL(window.location.href);

        const setOrDelete = (param, value) =>
            value ? url.searchParams.set(param, value) : url.searchParams.delete(param);

        const prevFrom = url.searchParams.get('from');
        const prevTo = url.searchParams.get('to');

        const fromOffset = relativeStartSeconds && LibreNMS.Date.toShortOffset(relativeStartSeconds);
        setOrDelete('from', fromOffset === '-1d' ? null : fromOffset || (start && LibreNMS.Date.toUrl(start)));
        setOrDelete('to', relativeEndSeconds ? LibreNMS.Date.toShortOffset(relativeEndSeconds) : end && LibreNMS.Date.toUrl(end));

        if (url.searchParams.get('from') !== prevFrom || url.searchParams.get('to') !== prevTo) {
            window.location.href = url.toString();
        }
    });
</script>
@endpush
