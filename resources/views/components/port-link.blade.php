<x-popup>
    <a class="@if($status=='disabled') tw-text-gray-400 visited:tw-text-gray-400 @elseif($status=='down') tw-text-red-600 visited:tw-text-red-600 @else tw-text-blue-900 visited:tw-text-blue-900 dark:tw-text-dark-white-100 dark:visited:tw-text-dark-white-100 @endif"
       href="{{ $link }}"
        {{ $attributes }}>
        {{ $slot->isNotEmpty() ? $slot : $label }}
    </a>
    <x-slot name="title">
        <div class="tw-text-xl tw-font-bold">{{ $port->device->displayName() }} - {{ $label }}</div>
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
