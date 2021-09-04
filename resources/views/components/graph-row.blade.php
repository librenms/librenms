@isset($title)
    <div class="tw-border-b tw-font-semibold">
        {{ $title }}
    </div>
@endisset
<div class="tw-flex tw-flex-wrap" @if(! $responsive) style="width: {{ $rowWidth }}px;" @endif {{ $attributes }}>
    @foreach($graphs as $graph)
        <x-graph :type="$type" :loading="$loading" :aspect="$aspect" :port="$port" :device="$device" :vars="$graph" class="@if($responsive) tw-xl:w-1/4 tw-lg:w-1/2 tw-sm:w-full tw-flex-auto @endif"></x-graph>
    @endforeach
</div>
