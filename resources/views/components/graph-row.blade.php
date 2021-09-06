@isset($title)
    <div class="tw-border-b tw-font-semibold">
        {{ $title }}
    </div>
@endisset
<div class="tw-flex tw-flex-wrap" @if(! $responsive) style="width: {{ $rowWidth }}px;" @endif {{ $attributes }}>
    @foreach($graphs as $graph)
        <x-graph :type="$type" :loading="$loading" :aspect="$aspect" :port="$port" :device="$device" :vars="$graph" class="@if($responsive) xl:tw-w-1/4 lg:tw-w-1/2 sm:tw-w-full @endif"></x-graph>
    @endforeach
</div>
