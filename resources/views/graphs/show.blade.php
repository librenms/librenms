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
                          x-data @change="window.location = withShownRange($event.target.value)"></x-select>
            @endif
        </div>
    </x-slot:heading>
</x-panel>

{{-- A graph drawn in the browser changes its range without reloading the page, the thumbnails and date picker follow it --}}
<div @if ($drawInBrowser) x-data="graphsPage(@js($graphRange['last'] ?? null))"
     x-on:graph-range-changed="rangeChanged($event.detail)" x-on:date-range-changed="picked($event.detail)" @endif>

<div id="period-thumbs" class="tw:overflow-x-auto tw:scroll-px-2 tw:pb-2 tw:dark:scheme-dark">
    <div class="tw:flex tw:flex-nowrap tw:gap-1">
    @foreach ($periodThumbs as $thumb)
        <a href="{{ $thumb['link'] }}"
           @if ($thumb['active']) aria-current="true" @endif
           @if ($drawInBrowser) x-bind:aria-current="isPeriod({{ $thumb['period'] }}) ? 'true' : false" x-on:click="showPeriod({{ $thumb['period'] }}, $event)" @endif
           style="width: {{ $thumb['vars']['width'] }}px; min-width: {{ (int) ($thumb['vars']['width'] * 0.6) }}px;"
           class="tw:flex tw:flex-col tw:items-center tw:gap-0.5 tw:p-1 tw:rounded-lg tw:border tw:no-underline tw:transition-colors
                  @if ($loop->first) tw:ml-auto @elseif ($loop->last) tw:mr-auto @endif
                  tw:border-transparent tw:hover:border-gray-300 tw:hover:bg-gray-100 tw:dark:hover:bg-gray-700
                  tw:aria-[current=true]:border-blue-500 tw:aria-[current=true]:bg-blue-50 tw:dark:aria-[current=true]:bg-gray-700
                  tw:aria-[current=true]:hover:border-blue-500 tw:aria-[current=true]:hover:bg-blue-50 tw:dark:aria-[current=true]:hover:bg-gray-700">
            <span class="tw:font-medium tw:whitespace-nowrap tw:text-gray-700 tw:dark:text-gray-200 tw:overflow-hidden tw:text-ellipsis tw:w-full tw:text-center">{{ $thumb['text'] }}</span>
            <img class="graph-image tw:block tw:w-full tw:h-auto tw:object-cover tw:rounded tw:border-0"
                 src="{{ route('graph', $thumb['vars']) }}"
                 width="{{ $thumb['vars']['width'] }}"
                 height="{{ $thumb['vars']['height'] }}"
                 fetchpriority="low"
                 @config('enable_lazy_load') loading="lazy" @endconfig />
        </a>
    @endforeach
    </div>
</div>

<div class="tw:w-[48ch] tw:max-w-full tw:mx-auto tw:mt-3 tw:mb-2">
    <x-date-range-picker :start="$graphFrom" :end="$graphTo" :reload="! $drawInBrowser"
                         class="tw:w-full tw:text-center tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-md tw:bg-gray-100! tw:hover:bg-gray-200! tw:dark:bg-white! tw:dark:hover:bg-gray-200!"></x-date-range-picker>
</div>
<div class="tw:flex tw:flex-wrap tw:items-center tw:justify-center tw:gap-x-4 tw:gap-y-1">
    @if ($canDrawInBrowser)
        <x-toggle size="sm" :label="__('graphs.interactive')" :checked="$drawInBrowser" x-data
                  x-on:change="axios.post('{{ route('preferences.store') }}', { pref: '{{ $rendererPreference }}', value: $event.target.checked ? 'js' : 'default' }).then(() => location.reload())"></x-toggle>
    @endif
    <div>
        @foreach ($toggles as $toggle)
            @if (! $loop->first) | @endif
            <a href="{{ $toggle['link'] }}">{{ $toggle['text'] }}</a>
        @endforeach
        @if ($trendHint) | {{ __('To show trend, set to future date') }} @endif
    </div>
</div>

<div class="tw:w-full tw:mt-4">
@if ($drawInBrowser)
    <x-time-series-graph :src="$graphDataUrl" :range="$graphRange" :height="$mainGraphVars['height']"></x-time-series-graph>
@elseif ($isDynamicGraph)
    <img class="graph graph-image img-responsive tw:w-full tw:h-auto tw:border-0" data-src-template="{{ $dynamicGraphSrcTemplate }}" fetchpriority="high" />
@else
    <img class="graph-image img-responsive tw:w-full tw:h-auto tw:border-0"
         src="{{ route('graph', $mainGraphVars) }}"
         fetchpriority="high" />
@endif
</div>

</div>

@isset($graphDescr)
<x-panel class="tw:mt-4">
    <i class="fa-solid fa-circle-info fa-lg icon-theme" aria-hidden="true"></i>
    {{ $graphDescr }}
</x-panel>
@endisset

@if ($showCommand && $rrdCommand)
<div class="infobox tw:text-gray-900 tw:dark:text-gray-900">
    <p class="tw:text-lg tw:font-bold">{{ __('RRDTool Command') }}</p>
    <pre class="rrd-pre">{{ $rrdCommand }}</pre>
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

    // A link made when the page loaded, with the time range that is shown now
    function withShownRange(href) {
        const url = new URL(href, window.location.href);
        const shown = new URL(window.location.href).searchParams;

        ['from', 'to'].forEach((key) => {
            if (shown.has(key)) url.searchParams.set(key, shown.get(key)); else url.searchParams.delete(key);
        });

        return url.toString();
    }

    @if ($drawInBrowser)
        // Keeps the page in step with the graph that is drawn in the browser, without reloading the page:
        // the period thumbnails and the date picker set the range of the graph, and the address,
        // the date picker and the highlighted thumbnail follow what the graph shows.
        document.addEventListener('alpine:init', () => {
            // last is the seconds up to now the graph shows, null when it shows a fixed range
            window.Alpine.data('graphsPage', (last) => ({
                last,
                // the date picker is being set to what the graph shows, which is not a new range to show
                syncing: false,

                graph() {
                    return this.$root.querySelector('[x-data^="timeSeriesGraph"]')?.timeSeriesGraph;
                },

                picker() {
                    return this.$root.querySelector('[x-data="dateRangePicker"]')?.dateRangePicker;
                },

                // a period is the one shown when it is within 5% of it
                isPeriod(seconds) {
                    return this.last !== null && Math.abs(this.last - seconds) <= 0.05 * seconds;
                },

                // A period thumbnail was clicked, modified clicks still open its link
                showPeriod(seconds, event) {
                    if (event.ctrlKey || event.metaKey || event.shiftKey || ! this.graph()) {
                        return;
                    }

                    event.preventDefault();
                    this.graph().setRange({ last: seconds });
                },

                // A range was picked in the date picker
                picked({ start, end, relativeStartSeconds, relativeEndSeconds }) {
                    if (this.syncing || ! start) {
                        return;
                    }

                    const now = Math.floor(Date.now() / 1000);
                    const range = relativeStartSeconds !== null && relativeEndSeconds === null
                        ? { last: relativeStartSeconds }
                        : {
                            from: relativeStartSeconds !== null ? now - relativeStartSeconds : LibreNMS.Date.toUrl(start),
                            to: relativeEndSeconds !== null ? now - relativeEndSeconds : (end ? LibreNMS.Date.toUrl(end) : now),
                        };

                    this.syncing = true;
                    this.graph()?.setRange(range);
                    this.syncing = false;
                },

                // The graph shows another range
                rangeChanged(range) {
                    const from = 'last' in range ? LibreNMS.Date.toShortOffset(range.last) : String(range.from);
                    const to = 'last' in range ? null : String(range.to);
                    const url = new URL(window.location.href);

                    url.searchParams.set('from', from);
                    if (to === null) url.searchParams.delete('to'); else url.searchParams.set('to', to);
                    window.history.replaceState(window.history.state, '', url);

                    this.last = range.last ?? null;

                    if (! this.syncing) {
                        this.syncing = true;
                        this.picker()?.clear();
                        this.picker()?.set(from, to);
                        this.syncing = false;
                    }
                },
            }));
        });
    @endif

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
