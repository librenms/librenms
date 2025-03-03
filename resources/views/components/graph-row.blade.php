@isset($title)
    <div class="tw-border-b tw-font-semibold">
        {{ $title }}
    </div>
@endisset
<div class="tw-flex tw-flex-wrap" @if(! $responsive) style="width: {{ $rowWidth }}px;" @endif {{ $attributes->filter(fn ($value) => ! is_array($value)) }}>
    @foreach($graphs as $graph)
        <x-graph
                :type="$type"
                :loading="$loading"
                :aspect="$aspect"
                :port="$port"
                :device="$device"
                :legend="$attributes->get('legend', 'no')"
                :height="$attributes->get('height', 150)"
                :vars="array_merge($graph, $attributes->get('vars', []))"
                {{ $attributes->class(['lg:tw-w-1/4 sm:tw-w-1/2 tw-w-full' => $responsive]) }}></x-graph>
    @endforeach
</div>
