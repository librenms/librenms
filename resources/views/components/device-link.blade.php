<x-popup>
    <a class="tw-font-bold @if($status=='disabled') tw-text-gray-400 visited:tw-text-gray-400 @elseif($status=='down') tw-text-red-600 visited:tw-text-red-600 @else tw-text-blue-900 visited:tw-text-blue-900 dark:tw-text-dark-white-100 dark:visited:tw-text-dark-white-100 @endif" href="{{ route('device', ['device' => $device->device_id, 'tab' => $tab, 'section' => $section]) }}">
        {{ $slot->isNotEmpty() ? $slot : $device->displayName() }}
    </a>
    <x-slot name="title">
        <span class="tw-text-nowrap tw-pr-1">
            <span class="tw-text-xl tw-font-bold">{{ $device->displayName() }}</span>
            {{ $device->hardware }}
        </span>
        <span class="tw-text-nowrap tw-pl-2 tw-pr-1">
            @if($device->os){{ \LibreNMS\Config::getOsSetting($device->os, 'text') }}@endif
            {{ $device->version }}
        </span>
        <span class="tw-text-nowrap tw-pl-2">
            @if($device->feature)({{ $device->features }})@endif
            @if($device->location)[{{ $device->location }}]@endif
        </span>
    </x-slot>
    <x-slot name="body">
        @foreach($graphs as $graph)
            @isset($graph['text'], $graph['graph'])
                <x-graph-row loading="lazy" :device="$device" :type="$graph['graph']" :title="$graph['text']" :graphs="[['from' => '-1d'], ['from' => '-7d']]"></x-graph-row>
            @endisset
        @endforeach
    </x-slot>
</x-popup>
