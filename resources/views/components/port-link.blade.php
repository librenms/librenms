<x-popup>
    @include('components.port-link_basic')
    <x-slot name="title">
        <div class="tw-text-xl tw-font-bold">{{ $port->device?->displayName() }} - {{ $label }}</div>
        <div>{{ $description }}</div>
    </x-slot>
    <x-slot name="body">
        <div>
            @foreach($graphs as $graph)
                <x-graph-row loading="lazy" :port="$port" :type="$graph['type'] ?? 'port_bits'" :title="$graph['title'] ?? null" :graphs="$fillDefaultVars($graph['vars'] ?? [])"></x-graph-row>
            @endforeach
        </div>
    </x-slot>
</x-popup>
