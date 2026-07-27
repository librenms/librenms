@props(['device'])

@if($device->transceivers->isNotEmpty())
    @php($transceiverSensors = $device->sensors->where('group', 'transceiver'))
    <x-device.overview.panel :title="__('port.tabs.transceivers')" icon="fa fa-exchange-alt"
        :href="route('device', ['device' => $device->device_id, 'tab' => 'ports', 'vars' => 'transceivers'])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @foreach($device->transceivers as $transceiver)
                <div>
                    <div class="tw:bg-neutral-100 tw:px-4 tw:py-2.5 tw:dark:bg-dark-gray-200">
                        @if($transceiver->port)<x-port-link :port="$transceiver->port" :vars="['view' => 'transceiver']" />@endif
                        <x-icons.transceiver /> {{ $transceiver->vendor }} {{ $transceiver->type }}
                    </div>
                    @foreach($transceiverSensors as $sensor)
                        @if($sensor->entPhysicalIndex !== null && $sensor->entPhysicalIndex == $transceiver->entity_physical_index
                            && ($sensor->sensor_class === 'temperature' || ($sensor->sensor_class === 'dbm' && \Illuminate\Support\Str::contains($sensor->sensor_descr, ['rx', 'receive'], true))))
                            <div class="tw:flex tw:items-center tw:gap-3 tw:px-3 tw:py-2">
                                <span class="tw:w-36 tw:truncate">{{ $sensor->sensor_descr }}</span>
                                <x-graph :type="'sensor_' . $sensor->sensor_class" :vars="['id' => $sensor->sensor_id]" width="100" height="24" popup
                                         :popup-title="$device->display . ' - ' . $sensor->sensor_descr" />
                                <x-label class="tw:ml-auto" :status="$sensor->currentStatus()">{{ $sensor->formatValue() }}</x-label>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
    </x-device.overview.panel>
@endif
