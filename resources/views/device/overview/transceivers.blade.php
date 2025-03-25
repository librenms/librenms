<div class="row">
    <div class="col-md-12">
        <x-panel class="device-overview panel-condensed">
            <x-slot name="heading" class="tw:mb-6">
                <x-icons.transceiver></x-icons.transceiver>
                <strong><a href="{{ $transceivers_link }}">{{ __('port.tabs.transceivers') }}</a></strong>
            </x-slot>

            @foreach($transceivers as $transceiver)
                <x-panel body-class="tw:p-0!">
                    <x-slot name="heading">
                        @if($transceiver->port)
                        <x-port-link :port="$transceiver->port" :vars="['view' => 'transceiver']"></x-port-link>
                        @endif
                        <x-icons.transceiver></x-icons.transceiver> {{ $transceiver->vendor }} {{ $transceiver->type }}
                    </x-slot>
                    <table class="table table-hover table-condensed table-striped tw:mb-0!">
                        @foreach($sensors as $sensor)
                            @if($sensor->entPhysicalIndex !== null && $sensor->entPhysicalIndex == $transceiver->entity_physical_index && $filterSensors($sensor))
                            <tr>
                                <td>
                                    <div style="display: grid; grid-gap: 10px; grid-template-columns: 3fr 1fr 1fr;">
                                        <div>{{ $sensor->sensor_descr }}</div>
                                        <div><x-graph loading="lazy" popup="true" :popup-title="DeviceCache::getPrimary()->displayName() . ' - ' . $sensor->sensor_descr" type="sensor_{{ $sensor->sensor_class }}" width="100" height="24" :vars="['id' => $sensor->sensor_id]"></x-graph></div>
                                        <div><x-label :status="$sensor->currentStatus()">{{ $sensor->formatValue() }}</x-label></div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </table>
                </x-panel>
            @endforeach
        </x-panel>
    </div>
</div>
