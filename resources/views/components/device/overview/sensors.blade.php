@props(['device', 'sensorGroups'])

@foreach($sensorGroups as $sensorData)
    <x-device.overview.panel :title="$sensorData['sensor']->label()" :icon="'fa fa-' . $sensorData['sensor']->icon()"
        :href="route('device', ['device' => $device->device_id, 'tab' => 'health', 'vars' => 'metric=' . $sensorData['sensor']->value])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @foreach($sensorData['groups'] as $group => $sensors)
                @if(filled($group))
                    <div class="tw:bg-neutral-100 tw:px-2 tw:py-1 tw:font-bold tw:dark:bg-dark-gray-300">{{ $group }}</div>
                @endif
                @foreach($sensors as $data)
                    <div class="tw:flex tw:items-center tw:gap-3 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                        <div class="tw:min-w-0 tw:flex-1 tw:truncate" title="{{ $data['description'] }}">
                            <x-popup>
                                <a href="{{ $data['graphLink'] }}">{{ $data['description'] }}</a>
                                <x-slot name="title">{{ $device->display }} - {{ $data['description'] }}</x-slot>
                                <x-slot name="body"><x-graph-row loading="lazy" :type="'sensor_' . $data['sensor']->sensor_class" :vars="['id' => $data['sensor']->sensor_id]" /></x-slot>
                            </x-popup>
                        </div>
                        <div class="tw:hidden tw:w-25 tw:shrink-0 tw:justify-end tw:sm:flex">
                            <x-graph :type="'sensor_' . $data['sensor']->sensor_class" :vars="['id' => $data['sensor']->sensor_id]" width="100" height="24" popup
                                     :popup-title="$device->display . ' - ' . $data['description']" />
                        </div>
                        <div class="tw:flex tw:w-28 tw:shrink-0 tw:justify-end">
                            <x-popup>
                                <a href="{{ $data['graphLink'] }}"><x-label :status="$data['sensor']->currentStatus()">{{ $data['sensor']->formatValue() }}</x-label></a>
                                <x-slot name="title">{{ $device->display }} - {{ $data['description'] }}</x-slot>
                                <x-slot name="body"><x-graph-row loading="lazy" :type="'sensor_' . $data['sensor']->sensor_class" :vars="['id' => $data['sensor']->sensor_id]" /></x-slot>
                            </x-popup>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </x-device.overview.panel>
@endforeach
