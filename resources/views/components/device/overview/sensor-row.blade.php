@props(['sensor'])

<div class="tw:flex tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
    <div class="tw:w-36 tw:min-w-150 tw:shrink-0 tw:whitespace-nowrap">
        <x-popup>
            <a href="{{ $sensor->graph_link }}">{{ $sensor->sensor_descr }}</a>
            <x-slot name="title">{{ DeviceCache::getPrimary()->displayName() . ' - ' . $sensor->sensor_descr }}</x-slot>
            <x-slot name="body">
                <x-graph-row loading="lazy" :vars="['id' => $sensor->sensor_id]" :type="'sensor_' . $sensor->sensor_class"></x-graph-row>
            </x-slot>
        </x-popup>
    </div>
    <div class="tw:flex tw:min-w-0 tw:flex-1 tw:justify-center">
        <x-popup>
            <x-graph :vars="['id' => $sensor->sensor_id]" :type="'sensor_' . $sensor->sensor_class" width="100" height="24" loading="lazy"></x-graph>
            <x-slot name="title">{{ DeviceCache::getPrimary()->displayName() . ' - ' . $sensor->sensor_descr }}</x-slot>
            <x-slot name="body">
                <x-graph-row loading="lazy" :vars="['id' => $sensor->sensor_id]" :type="'sensor_' . $sensor->sensor_class"></x-graph-row>
            </x-slot>
        </x-popup>
    </div>
    <div>
        <x-popup>
            <a href="{{ $sensor->graph_link }}">
                <x-label :status="$sensor->currentStatus()">{{ $sensor->formatValue() }}</x-label>
            </a>
            <x-slot name="title">{{ DeviceCache::getPrimary()->displayName() . ' - ' . $sensor->sensor_descr }}</x-slot>
            <x-slot name="body">
                <x-graph-row loading="lazy" :vars="['id' => $sensor->sensor_id]" :type="'sensor_' . $sensor->sensor_class"></x-graph-row>
            </x-slot>
        </x-popup>
    </div>
</div>
