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
                <div class="font-semibold">{{ $graph['text'] }}</div>
                <div class="flex flex-row flex-wrap">
                    <x-graph :device="$device" start="-1d" :type="$graph['graph']" loading="lazy" trim="1" />
                    <x-graph :device="$device" start="-1w" :type="$graph['graph']" loading="lazy" trim="1" />
                </div>
            @endisset
        @endforeach
    </x-slot>
</x-popup>
