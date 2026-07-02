@extends('layouts.librenmsv1')

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
<div class="row">
<div class="col-md-12">

<x-panel>
    <x-slot:heading>
        <div class="tw:flex tw:flex-col tw:items-start tw:gap-2 tw:sm:flex-row tw:sm:items-center tw:sm:justify-between">
            <div>
                @if ($port)
                    <x-device-link :device="$device"></x-device-link> :: {{ __('Port') }} <x-port-link :port="$port"></x-port-link>
                @elseif ($device)
                    <x-device-link :device="$device"></x-device-link>
                @endif
                {{ $subtitle }}
            </div>
            @if (count($subtypeOptions) > 1)
                <x-select name="graph_subtype" :options="$subtypeOptions" :selected="$subtypeSelected"
                          class="tw:[&_select]:px-3 tw:[&_select]:py-1.5"
                          x-data @change="window.location = $event.target.value"></x-select>
            @endif
        </div>
    </x-slot:heading>
</x-panel>

@unless ($showCommand)
{{-- Thumbnail row: always a single centered row. Each thumbnail grows up to its
     normal rendered width (--thumb-w) and shrinks no smaller than 60% of it; once
     even that no longer fits, the row scrolls horizontally instead of wrapping. --}}
<div id="period-thumbs"
     class="tw:flex tw:overflow-x-auto tw:scroll-px-2 tw:pb-2
            tw:[scrollbar-width:auto] tw:[scrollbar-color:rgb(107_114_128_/_0.9)_transparent] tw:dark:[scrollbar-color:rgb(172_182_191_/_0.8)_transparent]
            tw:[&::-webkit-scrollbar]:h-5
            tw:[&::-webkit-scrollbar-track]:bg-transparent
            tw:[&::-webkit-scrollbar-thumb]:rounded-full tw:[&::-webkit-scrollbar-thumb]:border-4 tw:[&::-webkit-scrollbar-thumb]:border-transparent tw:[&::-webkit-scrollbar-thumb]:bg-clip-content
            tw:[&::-webkit-scrollbar-thumb]:bg-gray-500 tw:[&::-webkit-scrollbar-thumb:hover]:bg-gray-600
            tw:dark:[&::-webkit-scrollbar-thumb]:bg-dark-white-400/85 tw:dark:[&::-webkit-scrollbar-thumb:hover]:bg-dark-white-200">
<div class="tw:mx-auto tw:flex tw:flex-nowrap tw:gap-1">
@foreach ($periodThumbs as $thumb)
    <a href="{{ $thumb['link'] }}"
       @if ($thumb['active']) aria-current="true" @endif
       style="--thumb-w: {{ $thumb['vars']['width'] }}px; --thumb-ar: {{ $thumb['vars']['width'] }} / {{ $thumb['vars']['height'] }}"
       class="tw:flex tw:min-w-[calc(var(--thumb-w)*0.6)] tw:max-w-[var(--thumb-w)] tw:flex-[1_1_var(--thumb-w)] tw:flex-col tw:items-center tw:gap-0.5 tw:p-1 tw:rounded-lg tw:border tw:no-underline tw:transition-colors
              @if ($thumb['active']) tw:border-blue-500 tw:bg-blue-50 tw:dark:bg-gray-700 @else tw:border-transparent tw:hover:border-gray-300 tw:hover:bg-gray-100 tw:dark:hover:bg-gray-700 @endif">
        <span class="tw:font-medium tw:whitespace-nowrap tw:text-gray-700 tw:dark:text-gray-200 tw:overflow-hidden tw:text-ellipsis tw:w-full tw:text-center">{{ $thumb['text'] }}</span>
        <img class="graph-image tw:block tw:w-[var(--thumb-w)] tw:max-w-full tw:aspect-[var(--thumb-ar)] tw:object-cover tw:rounded tw:border-0"
             src="{{ route('graph', $thumb['vars']) }}"
             width="{{ $thumb['vars']['width'] }}"
             height="{{ $thumb['vars']['height'] }}"
             @config('enable_lazy_load') loading="lazy" @endconfig />
    </a>
@endforeach
</div>
</div>
{{-- Runs synchronously as the parser reaches it (before first paint), so the active
     period is already in view with no visible scroll jump. Image width/height attrs
     mean layout is correct even before the thumbnails load. --}}
<script>
    (function () {
        var active = document.getElementById('period-thumbs')?.querySelector('[aria-current="true"]');
        if (active) active.scrollIntoView({ block: 'nearest', inline: 'center' });
    })();
</script>
@endunless

<div class="tw:w-[48ch] tw:max-w-full tw:mx-auto tw:mt-3 tw:mb-2">
    <x-date-range-picker :start="$graphFrom" :end="$graphTo"
                         class="tw:w-full tw:text-center tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-md tw:bg-gray-100! tw:hover:bg-gray-200! tw:dark:bg-white! tw:dark:hover:bg-gray-200!"></x-date-range-picker>
</div>
<div class="tw:text-center">
@foreach ($toggles as $toggle)
    @if (! $loop->first) | @endif
    <a href="{{ $toggle['link'] }}">{{ $toggle['text'] }}</a>
@endforeach
@if ($trendHint) | {{ __('To show trend, set to future date') }} @endif
</div>

<script type="text/javascript" language="JavaScript">
    document.graphFrom = {{ is_numeric($mainGraphVars['from']) ? $mainGraphVars['from'] : 0 }};
    document.graphTo = {{ is_numeric($mainGraphVars['to']) ? $mainGraphVars['to'] : 0 }};
    document.graphWidth = {{ is_numeric($mainGraphVars['width']) ? $mainGraphVars['width'] : 0 }};
    document.graphHeight = {{ is_numeric($mainGraphVars['height']) ? $mainGraphVars['height'] : 0 }};
    document.graphLegend = '{{ str_replace("'", '', $mainGraphVars['legend'] ?? '') }}';
</script>

<div class="tw:text-center tw:[&_img]:mx-auto tw:[&_img]:h-auto tw:[&_img]:max-w-full">
@if ($isDynamicGraph)
    <script src="{{ asset('js/RrdGraphJS/q-5.0.2.min.js') }}"></script>
    <script src="{{ asset('js/RrdGraphJS/moment-timezone-with-data.js') }}"></script>
    <script src="{{ asset('js/RrdGraphJS/rrdGraphPng.js') }}"></script>
    <script type="text/javascript">
        q.ready(function(){
            var graphs = [];
            q('.graph').forEach(function(item){
                graphs.push(
                    q(item).rrdGraphPng({
                        canvasPadding: 120,
                        initialStart: {{ is_numeric($mainGraphVars['from']) ? $mainGraphVars['from'] : '(new Date()).getTime() / 1000 - 24*3600' }},
                        initialRange: {{ is_numeric($mainGraphVars['to']) ? $mainGraphVars['to'] - $mainGraphVars['from'] : '24*3600' }}
                    })
                );
            });
        });
        window.onload = function(){ window.dispatchEvent(new Event('resize')); }
    </script>
    <img style="width:{{ $dynamicGraphWidth }}px" class="graph graph-image img-responsive tw:h-full tw:border-0" data-src-template="{{ $dynamicGraphSrcTemplate }}" />
@else
    <img class="graph-image img-responsive tw:mx-auto tw:border-0"
         src="{{ route('graph', $mainGraphVars) }}"
         @config('enable_lazy_load') loading="lazy" @endconfig />
@endif
</div>

@if ($graphDescr !== null)
<x-panel class="tw:mt-4">
    <x-slot:heading>
        <div class="tw:flex tw:items-center tw:gap-3">
            <i class="fa-solid fa-circle-info fa-lg icon-theme" aria-hidden="true"></i>
            <div>{{ $graphDescr }}</div>
        </div>
    </x-slot:heading>
</x-panel>
@endif

@if ($showCommand && $rrdCommand)
<div class="infobox">
    <p class="tw:text-lg tw:font-bold">{{ __('RRDTool Command') }}</p>
    <pre class="rrd-pre">{{ $rrdCommand }}</pre>
</div>
@endif

</div>
</div>
</div>

<x-refresh-timer :refresh="$refresh"></x-refresh-timer>
@endsection

@push('scripts')
<script>
    window.addEventListener('date-range-changed', (event) => {
        const { relativeStartSeconds, relativeEndSeconds, start, end } = event.detail;
        const url = new URL(window.location.href);

        const setOrDelete = (param, value) =>
            value ? url.searchParams.set(param, value) : url.searchParams.delete(param);

        const prevFrom = url.searchParams.get('from');
        const prevTo = url.searchParams.get('to');

        const fromOffset = relativeStartSeconds && LibreNMS.Date.toShortOffset(relativeStartSeconds);
        setOrDelete('from', fromOffset === '-1d' ? null : fromOffset || (start && LibreNMS.Date.toUrl(start)));
        setOrDelete('to', relativeEndSeconds ? LibreNMS.Date.toShortOffset(relativeEndSeconds) : end && LibreNMS.Date.toUrl(end));

        if (url.searchParams.get('from') !== prevFrom || url.searchParams.get('to') !== prevTo) {
            window.location.href = url.toString();
        }
    });
</script>
@endpush
