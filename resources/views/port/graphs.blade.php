
    <div>
        <x-filter :fields="$filterFields" id="port-filter" :hide="$hideFilter" :reload="true" class="tw:mb-3"/>
    </div>

        <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:lg:grid-cols-4 tw:gap-4">
            @foreach($ports as $port)
                @php
                    $graph_array = $graphTemplate;
                    $graph_array['id'] = $port->port_id;
                    $link = \LibreNMS\Util\Url::graphPageUrl($graph_array['type'], Arr::except($graph_array, ['height', 'width', 'legend', 'title']));
                    $graphTag = \LibreNMS\Util\Url::lazyGraphTag($graph_array, 'tw:w-full tw:h-auto');
                @endphp

                <div>
                    {!! \LibreNMS\Util\Url::portLink($port, $graphTag, $graph_array['type'], url: $link) !!}
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
