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
                <div class="flex flex-wrap sm:flex-nowrap">
                    <x-graph :port="$port" :type="$graph" from="-1d" width="340" height="100" legend="yes" />
                    <x-graph :port="$port" :type="$graph" from="-1w" width="340" height="100" legend="yes" />
                </div>
                <div class="flex flex-wrap sm:flex-nowrap">
                    <x-graph :port="$port" :type="$graph" from="-1m" width="340" height="100" legend="yes" />
                    <x-graph :port="$port" :type="$graph" from="-1y" width="340" height="100" legend="yes" />
                </div>
            @endforeach
        </div>
    </x-slot>
</x-popup>
