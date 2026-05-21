<div class="row">
    <div class="col-md-12">
        <x-panel class="device-overview panel-condensed">
            <x-slot name="heading">
                <i class="fa fa-wifi fa-lg icon-theme" aria-hidden="true"></i>
                <strong><a href="{{ $subscribers_link }}">{{ __('Subscribers') }}</a></strong>
            </x-slot>
            <table class="table table-hover table-condensed table-striped tw:mb-0!">
                <thead>
                    <tr>
                        <th>{{ __('Subscriber') }}</th>
                        <th>{{ __('Distance') }}</th>
                        <th>{{ __('Signal') }}</th>
                        <th>{{ __('MCS') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($sensors->groupBy('sensor_index')->sortKeys() as $index => $smSensors)
                    @php
                        $name     = $subscriberName($smSensors->first()->sensor_descr, (string) $index);
                        $distance = $findSensor($smSensors, 'distance');
                        $rssiDl   = $findSensor($smSensors, 'rssi', 'dl');
                        $mcsDl    = $findSensor($smSensors, 'mcs', 'dl');
                    @endphp
                    <tr>
                        <td><strong>{{ $name }}</strong></td>
                        <td class="text-nowrap">
                            @if($distance)<span class="text-muted">{{ $distance->formatValue() }}</span>@endif
                        </td>
                        <td class="text-nowrap">
                            @if($rssiDl)
                                <x-label :status="$rssiDl->currentStatus()">{{ $rssiDl->sensor_current }} dBm</x-label>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if($mcsDl)
                                <x-label :status="$mcsDl->currentStatus()">{{ $mcsDl->sensor_current }}</x-label>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </x-panel>
    </div>
</div>
