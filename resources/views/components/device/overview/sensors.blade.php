@props(['device', 'sensorGroups'])

@foreach($sensorGroups as $sensorData)
    <x-device.overview.panel :title="$sensorData['sensor']->label()" :icon="'fa fa-' . $sensorData['sensor']->icon()"
        :href="route('device', ['device' => $device->device_id, 'tab' => 'health', 'vars' => 'metric=' . $sensorData['sensor']->value])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @foreach($sensorData['groups'] as $group => $sensors)
                @if(filled($group))
                    <div class="tw:bg-neutral-100 tw:px-3 tw:py-2 tw:font-bold tw:dark:bg-dark-gray-300">{{ $group }}</div>
                @endif
                @foreach($sensors as $sensor)
                    <div class="tw:flex tw:items-center tw:gap-3 tw:px-3 tw:py-2">
                        <span class="tw:w-36 tw:truncate" title="{{ $sensor->sensor_descr }}">{{ $sensor->sensor_descr }}</span>
                        <x-graph :type="'sensor_' . $sensor->sensor_class" :vars="['id' => $sensor->sensor_id]" width="100" height="24" popup
                                 :popup-title="$device->display . ' - ' . $sensor->sensor_descr" />
                        <x-label class="tw:ml-auto" :status="$sensor->currentStatus()">{{ $sensor->formatValue() }}</x-label>
                    </div>
                @endforeach
            @endforeach
        </div>
    </x-device.overview.panel>
@endforeach
