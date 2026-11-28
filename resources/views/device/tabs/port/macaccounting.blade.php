@if($subview == 'top10')
    @php
        $periods = ['1d' => [__('Day'), '-1d'], '2d' => [__('Two Day'), '-2d'], '1w' => [__('Week'), '-1w'], '1m' => [__('Month'), '-1mo'], '1y' => [__('Year'), '-1y']];
        $from = $periods[$period][1];
        $top = fn (array $vars) => route('device.port', ['device' => $port->device_id, 'port' => $port->port_id, 'view' => 'macaccounting', 'subview' => 'top10'] + $vars + ['graph' => $graph, 'sort' => $sort, 'period' => $period]);
        $graphUrl = fn (array $vars) => route('graph', $vars + ['type' => 'port_mac_acc_total', 'id' => $port->port_id, 'stat' => $graph, 'sort' => $sort, 'from' => $from, 'width' => 150, 'height' => 50]);
    @endphp
    <div class="tw:flex tw:flex-wrap tw:gap-4">
        <div class="tw:flex tw:flex-col tw:gap-2">
            @foreach($periods as $key => [$text, $periodFrom])
                <a href="{{ $top(['period' => $key]) }}" @class(['tw:p-1', 'pagemenu-selected' => $key == $period])>
                    <div class="tw:font-semibold">{{ $text }}</div>
                    <img src="{{ $graphUrl(['from' => $periodFrom]) }}" alt="{{ $text }}">
                </a>
            @endforeach
        </div>
        <div>
            <img src="{{ $graphUrl(['width' => 745, 'height' => 300]) }}" alt="{{ __('Top 10') }}" class="tw:max-w-full">
        </div>
        <div class="tw:flex tw:flex-col tw:gap-2">
            @foreach(['graph' => ['bits' => __('Traffic'), 'pkts' => __('Packets')], 'sort' => ['in' => __('Top Input'), 'out' => __('Top Output'), 'both' => __('Top Aggregate')]] as $var => $choices)
                @foreach($choices as $value => $text)
                    <a href="{{ $top([$var => $value]) }}" @class(['tw:p-1', 'pagemenu-selected' => $$var == $value])>
                        <div class="tw:font-semibold">{{ $text }}</div>
                        <img src="{{ $graphUrl([$var == 'graph' ? 'stat' : 'sort' => $value]) }}" alt="{{ $text }}">
                    </a>
                @endforeach
            @endforeach
        </div>
    </div>
@elseif($subview == 'minigraphs')
    <div class="tw:flex tw:flex-wrap tw:gap-2">
        @foreach($macs as $entry)
            <div class="tw:text-center">
                <div>{{ $entry['ip'] }} - {{ $entry['as'] ?? __('No Session') }}</div>
                <x-graph :type="'macaccounting_' . $graph" :vars="['id' => $entry['mac']->ma_id]" from="-2d" :width="213" :height="45" popup :popup-title="$entry['ip'] . ' - ' . ($entry['as'] ?? __('No Session'))" />
            </div>
        @endforeach
    </div>
@else
    @foreach($macs as $entry)
        <x-panel>
            <x-slot:heading class="tw:flex tw:flex-wrap tw:gap-x-6">
                <span class="tw:font-semibold">{{ \LibreNMS\Util\Mac::parse($entry['mac']->mac)->readable() }}</span>
                <span>{{ $entry['ip'] }}</span>
                @if($entry['port'])
                    <span><x-device-link :device="$entry['port']->device" /> <x-port-link :port="$entry['port']" /></span>
                @endif
                @if($entry['as'])
                    <span>{{ $entry['as'] }} {{ $entry['astext'] }}</span>
                @endif
                <span>{{ __('In') }}: {{ \LibreNMS\Util\Number::formatSi($entry['mac']->bps_in, 2, 3, 'bps') }}</span>
                <span>{{ __('Out') }}: {{ \LibreNMS\Util\Number::formatSi($entry['mac']->bps_out, 2, 3, 'bps') }}</span>
            </x-slot:heading>
            <x-graph-row :type="'macaccounting_' . $graph" :vars="['id' => $entry['mac']->ma_id]" columns="responsive" />
        </x-panel>
    @endforeach
@endif
