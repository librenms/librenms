<x-panel class="tw:p-0">
    <x-slot name="heading" class="tw:p-0">
        <div class="tw:opacity-90 tw:p-3 tw:mb-0 tw:border-b-2 tw:border-solid tw:border-gray-200 tw:dark:border-dark-gray-200 tw:rounded-t-lg">
            <span class="tw:text-nowrap tw:pr-1">
                <a href="{{ $href }}" class="tw:text-xl tw:font-bold">{{ $device?->displayName() }} - {{ $label }}</a>
            </span>
            @if($description && $description != $label)
                <div class="tw:text-sm tw:text-gray-600 tw:dark:text-gray-400">{{ $description }}</div>
            @endif
        </div>
    </x-slot>
    <div>
        @foreach($graphs as $graph)
            <x-graph-row loading="lazy" :port="$port" :type="$graph['type']" :title="$graph['title']" :graphs="$graph['graphs']" />
        @endforeach
    </div>
</x-panel>
