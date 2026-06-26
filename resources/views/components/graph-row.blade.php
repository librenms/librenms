@isset($title)
    <div class="tw:border-b tw:font-semibold">
        {{ $title }}
    </div>
@endisset
<div @if(! $responsive) style="width: {{ $rowWidth }}px;" @endif {{ $attributes->filter(fn ($value) => ! is_array($value)) }}
     class="{{ $responsive ? 'tw:grid tw:grid-cols-1 tw:sm:grid-cols-2 tw:lg:grid-cols-4 tw:gap-2' : 'tw:flex tw:flex-wrap' }}">
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
                :img-class="$responsive ? 'tw:w-full tw:h-auto' : null"
        ></x-graph>
    @endforeach
</div>
