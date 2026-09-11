@if($device->transceivers->isNotEmpty())
    <x-device.overview.panel :title="__('port.tabs.transceivers')" icon="fa fa-exchange-alt"
        :href="route('device', ['device' => $device->device_id, 'tab' => 'ports', 'vars' => 'transceivers'])">
        <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
            @foreach($device->transceivers as $transceiver)
                <div>
                    <div class="tw:bg-neutral-100 tw:px-2 tw:py-1 tw:dark:bg-dark-gray-200">
                        @if($transceiver->port)<x-port-link :port="$transceiver->port" :vars="['view' => 'transceiver']" />@endif
                        <x-icons.transceiver /> {{ $transceiver->vendor }} {{ $transceiver->type }}
                    </div>
                    <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
                    @foreach($transceiverSensors as $sensor)
                        @if($shouldShow($sensor, $transceiver))
                            <div class="tw:flex tw:items-center tw:gap-3 tw:px-2 tw:py-1 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                                <span class="tw:min-w-0 tw:flex-1 tw:truncate">{{ $sensor->sensor_descr }}</span>
                                <div class="tw:hidden tw:w-25 tw:shrink-0 tw:justify-end tw:sm:flex">
                                    <x-graph :type="'sensor_' . $sensor->sensor_class" :vars="['id' => $sensor->sensor_id]" width="100" height="24" popup
                                             :popup-title="$device->display . ' - ' . $sensor->sensor_descr" />
                                </div>
                                <div class="tw:flex tw:w-28 tw:shrink-0 tw:justify-end">
                                    <x-label :status="$sensor->currentStatus()">{{ $sensor->formatValue() }}</x-label>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </x-device.overview.panel>
@endif
