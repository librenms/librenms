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
            @foreach($graphs as $text => $inner)
                <div class="border-b">
                    {{ $text }}
                </div>
                <div class="flex flex-row flex-wrap">
                    @foreach($inner as $graph)
                        <x-graph loading="defer" :port="$port" :type="$graph['type'] ?? 'port_bits'" :vars="$fillDefaultVars($graph['vars'] ?? [])"/>
                    @endforeach
                </div>
            @endforeach
        </div>
    </x-slot>
</x-popup>
