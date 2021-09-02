<x-popup>
    <a class="{{ $linkClass() }}" href="{{ $link }}" {{ $attributes }}>
        {{ $slot->isNotEmpty() ? $slot : $label }}
    </a>
    <x-slot name="title">
        <div class="text-xl font-bold">{{ $port->device->displayName() }} - {{ $label }}</div>
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
