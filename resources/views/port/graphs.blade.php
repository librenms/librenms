<div class="@if($hideFilter) tw:hidden @else tw:flex tw:justify-between @endif">
    <x-filter name="ports" :fields="$filterFields" id="port-filter" :hide="$hideFilter" :initial="$filter" :reload="true"/>
    <x-date-range-picker :start="$graphTemplate['from'] ?? null" :end="$graphTemplate['to'] ?? null" :reload="true"></x-date-range-picker>
</div>
<hr class="tw:my-5 @if($hideFilter) tw:hidden @endif"/>

    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:lg:grid-cols-4 tw:gap-4">
        @foreach($ports as $port)
            @php
                /** @var \App\Models\Port $port */
                $graph_array = $graphTemplate;
                $graph_array['id'] = $port->port_id;
                $link = \LibreNMS\Util\Url::graphPageUrl($graph_array['type'], Arr::except($graph_array, ['height', 'width', 'legend', 'title']));
                $graphTag = \LibreNMS\Util\Url::lazyGraphTag($graph_array, 'tw:w-full tw:h-auto');
                $portLinkOptions = ['port_id' => $port->port_id, 'params' => ['type' => $graph_array['type']]];
            @endphp

            <div>
                <a href="{{ $link }}" class="{{ \LibreNMS\Util\Url::portLinkDisplayClass($port) }}" x-data="portLink(@js($portLinkOptions))">
                    {!! $graphTag !!}
                </a>
            </div>
        @endforeach
    </div>

<x-slot name="footer">
    <x-pagination-footer :paginator="$ports" :options="$paginationOptions" :per-page="$perPage"/>
</x-slot>


