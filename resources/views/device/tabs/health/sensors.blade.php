@foreach($sensors as $sensor)
    <x-panel>
        <x-slot:heading>
            <h3 class="panel-title">
                {{ $sensor->poller_type == 'ipmi' ? \LibreNMS\Util\Rewrite::ipmiSensorName($device->hardware, $sensor->sensor_descr) : $sensor->sensor_descr }}
                <span class="pull-right">
                    <x-label :status="$sensor->currentStatus()">{{ __('Current') }}: {{ $sensor->formatValue() }}</x-label>
                    @foreach(['sensor_limit_low' => __('Low Limit'), 'sensor_limit_low_warn' => __('Low Warning'), 'sensor_limit_warn' => __('High Warning'), 'sensor_limit' => __('High Limit')] as $field => $text)
                        @if($sensor->$field !== null)
                            <x-label>{{ $text }}: {{ $sensor->formatValue($field) }}</x-label>
                        @endif
                    @endforeach
                </span>
            </h3>
        </x-slot:heading>
        <x-graph-row :type="$sensor->getGraphType()" :device="$device" :vars="['id' => $sensor->sensor_id]" columns="responsive" />
    </x-panel>
@endforeach
