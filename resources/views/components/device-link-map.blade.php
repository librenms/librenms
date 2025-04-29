<div>
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
    @foreach($graphs as $graph)
        @isset($graph['text'], $graph['graph'])
            <x-graph-row loading="lazy" :device="$device" :type="$graph['graph']" :title="$graph['text']" :graphs="[['from' => '-1d'], ['from' => '-7d']]"></x-graph-row>
        @endisset
    @endforeach
</div>
