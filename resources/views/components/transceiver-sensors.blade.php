@props(['transceiver'])

@foreach($groupedSensors as $class => $sensors)
    @if($loop->first)
        <div {{ $attributes->merge(['class' => 'tw:grid tw:grid-cols-[min-content_min-content_1fr] tw:gap-x-4']) }}>
    @endif
    @foreach($sensors as $sensor)
        <div class="tw:whitespace-nowrap tw:text-right">
            {{ $sensor->sensor_descr }}
        </div>
        <div>
            <x-label :status="$sensor->currentStatus()">{{ $sensor->formatValue() }}</x-label>
        </div>
        <div style="height: 26px;">
            <x-popup>
                <div class="tw:border-2">
                    <x-graph :type="'sensor_' . $class" :vars="['id' => $sensor->sensor_id]" legend="yes" width="100" height="20"></x-graph>
                </div>
                <x-slot name="title">{{ $transceiver->port?->getLabel() }}</x-slot>
                <x-slot name="body">
                    <x-graph-row loading="lazy" :type="'sensor_' . $class" :vars="['id' => $sensor->sensor_id]" legend="yes"></x-graph-row>
                </x-slot>
            </x-popup>
        </div>
    @endforeach
    @if($loop->last)
        </div>
    @endif
@endforeach
