@props([
    'ports',
    'graph' => null,
])
@if($graph)
    <div class="tw:flex tw:flex-wrap tw:gap-1">
        @foreach($ports as $port)
            <div class="tw:block tw:p-0.5 tw:m-0.5 tw:w-[139px] tw:h-[85px] tw:text-center tw:bg-[#e9e9e9] dark:tw:bg-dark-gray-300 tw:rounded">
                <div class="tw:font-bold">{{ $port->getShortLabel() }}</div>
                <x-port-link :port="$port">
                    <x-graph :port="$port" :type="'port_' . $graph" from="-2d" width="132" height="40" legend="no" />
                </x-port-link>
                <div class="tw:text-[9px] tw:truncate">{{ $port->ifAlias }}</div>
            </div>
        @endforeach
    </div>
@else
    @foreach($ports as $port)
        <x-port-link :port="$port">{{ $port->getShortLabel() }}</x-port-link>@if(! $loop->last), @endif
    @endforeach
@endif
