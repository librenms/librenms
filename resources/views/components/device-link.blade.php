<x-popup>
    <a class="tw:font-bold @if($status=='disabled') tw:text-gray-400 tw:visited:text-gray-400 @elseif($status=='down') tw:text-red-600 tw:visited:text-red-600 @else tw:text-blue-900 tw:visited:text-blue-900 tw:dark:text-dark-white-100 tw:dark:visited:text-dark-white-100 @endif" href="{{ route('device', ['device' => $device->device_id ?? 1, 'tab' => $tab, 'section' => $section]) }}">
        {{ $slot->isNotEmpty() ? $slot : $device->displayName() }}
    </a>
    <x-slot name="title">
        <span class="tw:text-nowrap tw:pr-1">
            <span class="tw:text-xl tw:font-bold">{{ $device->displayName() }}</span>
            {{ $device->hardware }}
        </span>
        <span class="tw:text-nowrap tw:pl-2 tw:pr-1">
            @if($device->os){{ \LibreNMS\Config::getOsSetting($device->os, 'text') }}@endif
            {{ $device->version }}
        </span>
        <span class="tw:text-nowrap tw:pl-2">
            @if($device->feature)({{ $device->features }})@endif
            @if($device->location)[{{ $device->location }}]@endif
        </span>
    </x-slot>
    <x-slot name="body">
        <template x-if="loadGraphs" x-data="{loadGraphs: false}" x-init="$watch('popupShow', shown => {if(shown) loadGraphs = true})">
            <div>
            @foreach($graphs as $graph)
                @isset($graph['text'], $graph['graph'])
                    <x-graph-row loading="lazy" :device="$device" :type="$graph['graph']" :title="$graph['text']" :graphs="[['from' => '-1d'], ['from' => '-7d']]"></x-graph-row>
                @endisset
            @endforeach
            </div>
        </template>
    </x-slot>
</x-popup>
