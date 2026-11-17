@foreach($sensors as $sensor)
    <x-panel>
        <x-slot:heading class="tw:flex tw:flex-wrap tw:justify-between tw:gap-2">
            <h3 class="panel-title">{{ $sensor->poller_type == 'ipmi' ? \LibreNMS\Util\Rewrite::ipmiSensorName($device->hardware, $sensor->sensor_descr) : $sensor->sensor_descr }}</h3>
            <span>
                <x-label :status="$sensor->currentStatus()">{{ $sensor->formatValue() }}</x-label>
                @foreach(['sensor_limit_low' => 'low', 'sensor_limit_low_warn' => 'low_warn', 'sensor_limit_warn' => 'high_warn', 'sensor_limit' => 'high'] as $field => $text)
                    @if($sensor->$field !== null)
                        <x-label>{{ $text }}: {{ $sensor->formatValue($field) }}</x-label>
                    @endif
                @endforeach
            </span>
        </x-slot:heading>
        <x-graph-row :type="$sensor->getGraphType()" :vars="['id' => $sensor->sensor_id]" columns="responsive" />
    </x-panel>
@endforeach
