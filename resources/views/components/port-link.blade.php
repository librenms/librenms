<x-popup {{ $attributes }}>
    @include('components.port-link_basic')
    <x-slot name="title">
        <div class="tw:text-xl tw:font-bold">{{ $port->device?->displayName() }} - {{ $label }}</div>
        <div>{{ $description }}</div>
    </x-slot>
    <x-slot name="body">
        <div>
            @foreach($graphs as $graph)
                <x-graph-row loading="lazy" :port="$port" :type="$graph['type'] ?? 'port_bits'" :title="$graph['title'] ?? null"
                             :graphs="$fillDefaultVars($graph['vars'] ?? [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']])"/>
            @endforeach
        </div>
    </x-slot>
</x-popup>
