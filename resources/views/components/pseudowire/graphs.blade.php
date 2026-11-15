@props(['port'])

@if($port)
    @foreach(['port_bits', 'port_upkts', 'port_errors'] as $graphType)
        <x-port-link :port="$port" basic>
            <x-graph :port="$port" :type="$graphType" :link="false" from="-1d" width="150" height="30" />
        </x-port-link>
    @endforeach
@endif
