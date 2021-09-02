@isset($title)
    <div class="border-b font-semibold">
        {{ $title }}
    </div>
@endisset
<div class="flex flex-row flex-wrap">
    @foreach($graphs as $graph)
        <x-graph :type="$type" :loading="$loading" :port="$port" :device="$device" :vars="$graph"></x-graph>
    @endforeach
</div>
