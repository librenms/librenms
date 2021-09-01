<x-popup>
    <a class="{{ $linkClass() }}" href="{{ $link }}" {{ $attributes }}>
        {{ $slot->isNotEmpty() ? $slot : $label }}
    </a>
    <x-slot name="title">
        <div class="text-xl font-bold">{{ $port->device->displayName() }} - {{ $label }}</div>
        <div>{{ $description }}</div>
    </x-slot>
    <x-slot name="body">
        @foreach($graphs as $graph)
            <x-graph :vars="$vars" :type="$graph" from="-1d" width="340" height="100" legend="yes"></x-graph>
            <x-graph :vars="$vars" :type="$graph" from="-1w" width="340" height="100" legend="yes"></x-graph>
            <x-graph :vars="$vars" :type="$graph" from="-1m" width="340" height="100" legend="yes"></x-graph>
            <x-graph :vars="$vars" :type="$graph" from="-1y" width="340" height="100" legend="yes"></x-graph>
        @endforeach
    </x-slot>
</x-popup>
