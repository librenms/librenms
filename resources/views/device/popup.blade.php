<x-panel class="tw:p-0">
    <x-slot name="heading" class="tw:p-0">
    <div class="tw:opacity-90 tw:p-3 tw:mb-0 tw:border-b-2 tw:border-solid tw:border-gray-200 tw:dark:border-dark-gray-200 tw:rounded-t-lg">
        <span class="tw:text-nowrap tw:pr-1">
            <a href="{{ $href }}" class="tw:text-xl tw:font-bold">{{ $device->displayName() }}</a>
            @if($device->hardware)
                <span class="tw:ml-2">{{ $device->hardware }}</span>
            @endif
        </span>
        <span class="tw:text-nowrap tw:pl-2 tw:pr-1">
            @if($osText)
                <span>{{ $osText }}</span>
            @endif
            @if($device->version)
                <span class="tw:ml-1">{{ $device->version }}</span>
            @endif
        </span>
        <span class="tw:text-nowrap tw:pl-2">
            @if($device->features)
                <span>({{ $device->features }})</span>
            @endif
            @if($device->location)
                <span class="tw:ml-1">[{{ $device->location }}]</span>
            @endif
        </span>
    </div>
    </x-slot>
    <div>
        @forelse($graphs as $graph)
            <x-graph-row :device="$device->device_id" :type="$graph['type']" :title="$graph['title']" :graphs="$graph['graphs']" />
        @empty
            <div class="tw:text-center tw:py-4 tw:text-gray-500">
                {{ __('No graphs available for this device') }}
            </div>
        @endforelse
    </div>
</x-panel>
