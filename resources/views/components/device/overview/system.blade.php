<x-device.overview.panel :title="$title" icon="fa fa-id-card">
    <div class="tw:divide-y tw:divide-gray-300 tw:dark:divide-zinc-800">
        @foreach($rows as $row)
            <div class="tw:grid tw:grid-cols-3 tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                <div class="tw:font-medium">{{ $row['label'] }}</div>
                <div class="tw:col-span-2 tw:min-w-0 tw:break-words">{{ $row['value'] }}</div>
            </div>
        @endforeach
        @if($device->inserted)
            <div class="tw:grid tw:grid-cols-3 tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                <div class="tw:font-medium">{{ __('Device Added') }}</div>
                <div class="tw:col-span-2" title="{{ $device->inserted }}">{{ $device->inserted->diffForHumans() }}</div>
            </div>
        @endif
        @if($device->last_discovered)
            <div class="tw:grid tw:grid-cols-3 tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                <div class="tw:font-medium">{{ __('Last Discovered') }}</div>
                <div class="tw:col-span-2" title="{{ $device->last_discovered }}">{{ $device->last_discovered->diffForHumans() }}</div>
            </div>
        @endif
        @if($device->location)
            <div class="tw:grid tw:grid-cols-3 tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300">
                <div class="tw:font-medium">{{ __('Location') }}</div>
                <div class="tw:col-span-2">{{ $device->location->display() }}</div>
            </div>
            @if($device->location->coordinatesValid())
                <x-geo-map class="tw:w-full!" height="180px" :lat="$device->location->lat" :lng="$device->location->lng" :zoom="14" readonly />
            @endif
        @endif
    </div>
</x-device.overview.panel>
