@extends('layouts.librenmsv1')

@section('title', $pageTitle)

@section('content')
<div class="container-fluid lnms-graphs">
<div class="row">
<div class="col-md-12">

<x-panel class="lnms-graphs-panel">
    <x-slot:heading>
        <div class="lnms-graphs-heading tw:flex tw:flex-col tw:items-start tw:gap-2 tw:sm:flex-row tw:sm:items-center tw:sm:justify-between">
            <div class="lnms-graphs-title">
                @if ($port)
                    <x-device-link :device="$device"></x-device-link> :: {{ __('Port') }} <x-port-link :port="$port"></x-port-link>
                 @elseif ($device)
                    <x-device-link :device="$device"></x-device-link>
                @endif
                {{ $subtitle }}
            </div>
            @if (count($subtypeOptions) > 1)
                <x-select name="graph_subtype" :options="$subtypeOptions" :selected="$subtypeSelected"
                          class="lnms-graphs-subtype tw:[&_select]:px-3 tw:[&_select]:py-1.5"
                          x-data @change="window.location = $event.target.value"></x-select>
            @endif
        </div>
    </x-slot:heading>
</x-panel>

<div id="period-thumbs" class="lnms-graphs-thumbs tw:overflow-x-auto tw:scroll-px-2 tw:pb-2 tw:dark:scheme-dark">
    <div class="lnms-graphs-thumbs__row tw:flex tw:flex-nowrap tw:gap-1">
    @foreach ($periodThumbs as $thumb)
        <a href="{{ $thumb['link'] }}"
           @if ($thumb['active']) aria-current="true" @endif
           style="width: {{ $thumb['vars']['width'] }}px; min-width: {{ (int) ($thumb['vars']['width'] * 0.6) }}px;"
           class="lnms-graphs-thumb tw:flex tw:flex-col tw:items-center tw:gap-0.5 tw:p-1 tw:rounded-lg tw:border tw:no-underline tw:transition-colors
                  @if ($loop->first) tw:ml-auto @elseif ($loop->last) tw:mr-auto @endif
                  @if ($thumb['active']) lnms-graphs-thumb--active @endif">
            <span class="lnms-graphs-thumb__label tw:font-medium tw:whitespace-nowrap tw:overflow-hidden tw:text-ellipsis tw:w-full tw:text-center">{{ $thumb['text'] }}</span>
            <img class="graph-image lnms-graphs-thumb__img tw:block tw:w-full tw:h-auto tw:object-cover tw:rounded tw:border-0"
                 src="{{ route('graph', $thumb['vars']) }}"
                 width="{{ $thumb['vars']['width'] }}"
                 height="{{ $thumb['vars']['height'] }}"
                 fetchpriority="low"
                 @config('enable_lazy_load') loading="lazy" @endconfig />
        </a>
    @endforeach
    </div>
</div>

<div class="lnms-graphs-daterange tw:w-[48ch] tw:max-w-full tw:mx-auto tw:mt-3 tw:mb-2">
    <x-date-range-picker :start="$graphFrom" :end="$graphTo" :reload="true"
                         class="lnms-graphs-daterange__control tw:w-full tw:text-center tw:px-3 tw:py-2 tw:border tw:rounded-md"></x-date-range-picker>
</div>
<div class="lnms-graphs-toggles tw:text-center">
    @foreach ($toggles as $toggle)
        @if (! $loop->first) <span class="lnms-graphs-toggles__sep" aria-hidden="true">|</span> @endif
        <a href="{{ $toggle['link'] }}">{{ $toggle['text'] }}</a>
    @endforeach
    @if ($trendHint) <span class="lnms-graphs-toggles__sep" aria-hidden="true">|</span> <span class="lnms-graphs-toggles__hint">{{ __('To show trend, set to future date') }}</span> @endif
</div>

<div class="lnms-graphs-main tw:w-full tw:mt-4">
@if ($isDynamicGraph)
    <img class="graph graph-image img-responsive tw:w-full tw:h-auto tw:border-0" data-src-template="{{ $dynamicGraphSrcTemplate }}" fetchpriority="high" />
@else
    <img class="graph-image img-responsive tw:w-full tw:h-auto tw:border-0"
         src="{{ route('graph', $mainGraphVars) }}"
         fetchpriority="high" />
@endif
</div>

@isset($graphDescr)
<x-panel class="lnms-graphs-descr tw:mt-4">
    <i class="fa-solid fa-circle-info fa-lg icon-theme" aria-hidden="true"></i>
    {{ $graphDescr }}
</x-panel>
@endisset

@if ($showCommand && $rrdCommand)
<div class="infobox lnms-graphs-rrd">
    <p class="lnms-graphs-rrd__title tw:text-lg tw:font-bold">{{ __('RRDTool Command') }}</p>
    <pre class="rrd-pre lnms-graphs-rrd__pre">{{ $rrdCommand }}</pre>
</div>
@endif

</div>
</div>
</div>

<x-refresh-timer :refresh="$refresh"></x-refresh-timer>
@endsection

@section('javascript')
    @if ($isDynamicGraph)
        <script src="{{ asset('js/RrdGraphJS/q-5.0.2.min.js') }}"></script>
        <script src="{{ asset('js/RrdGraphJS/moment-timezone-with-data.js') }}"></script>
        <script src="{{ asset('js/RrdGraphJS/rrdGraphPng.js') }}"></script>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        document.getElementById('period-thumbs')?.querySelector('[aria-current="true"]')?.scrollIntoView({ block: 'nearest', inline: 'center' });
    })();

    @if ($isDynamicGraph)
        q.ready(function(){
            var graphs = [];
            q('.graph').forEach(function(item){
                graphs.push(
                    q(item).rrdGraphPng({
                        canvasPadding: 120,
                        initialStart: {{ is_numeric($mainGraphVars['from']) ? $mainGraphVars['from'] : '(new Date()).getTime() / 1000 - 24*3600' }},
                        initialRange: {{ isset($mainGraphVars['to']) ? $mainGraphVars['to'] - $mainGraphVars['from'] : time() - $mainGraphVars['from'] }}
                    })
                );
            });
        });
        window.onload = function(){ window.dispatchEvent(new Event('resize')); }
    @endif

</script>
@endpush
