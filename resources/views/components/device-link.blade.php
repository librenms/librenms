<x-popup>
    <a class="{{ $linkClass() }}" href="{{ route('device', ['device' => $device->device_id, 'tab' => $tab, 'section' => $section]) }}">
        {{ $slot->isNotEmpty() ? $slot : $device->displayName() }}
    </a>
    <x-slot name="title">
        <span class="text-nowrap pr-1">
            <span class="text-xl font-bold">{{ $device->displayName() }}</span>
            {{ $device->hardware }}
        </span>
        <span class="text-nowrap pl-2 pr-1">
            @if($device->os){{ \LibreNMS\Config::getOsSetting($device->os, 'text') }}@endif
            {{ $device->version }}
        </span>
        <span class="text-nowrap pl-2">
            @if($device->feature)({{ $device->features }})@endif
            @if($device->location)[{{ $device->location }}]@endif
        </span>
    </x-slot>
    <x-slot name="body">
        @foreach($graphs as $graph)
            @isset($graph['text'], $graph['graph'])
                <x-graph-row loading="lazy" :device="$device" :type="$graph['graph']" :title="$graph['text']" :graphs="[['from' => '-1d'], ['from' => '-1w']]"></x-graph-row>
            @endisset
        @endforeach
    </x-slot>
</x-popup>
