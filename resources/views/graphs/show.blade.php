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

<div x-data="graphsPage(@js($graphRange), @js($graphOptions), @js($drawInBrowser), @js($graphView))"
     x-on:keydown.window="keydown($event)"
     @if ($drawInBrowser)
         x-on:graph-range-changed="rangeChanged($event.detail)" x-on:date-range-changed="picked($event.detail)"
         x-on:graph-state-changed="state = $event.detail" x-on:graph-resized="resized($event.detail)"
     @endif>

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

@include('graphs.toolbar')

<div class="tw:w-full tw:mt-2">
@if ($drawInBrowser)
    <x-time-series-graph :src="$graphDataUrl" :range="$graphRange" :options="$graphOptions" :view="$graphView"></x-time-series-graph>
@elseif ($isDynamicGraph)
    <img x-ref="image" class="graph graph-image img-responsive tw:w-full tw:h-auto tw:border-0" data-src-template="{{ $dynamicGraphSrcTemplate }}" fetchpriority="high" />
@else
    <img x-ref="image" class="graph-image img-responsive tw:w-full tw:h-auto tw:border-0"
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

<x-refresh-timer :refresh="$refresh" :callback="$drawInBrowser ? 'refreshGraph' : 'location.reload'"></x-refresh-timer>
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

    // links were made at page load, the range may have changed
    function withShownRange(href) {
        const url = new URL(href, window.location.href);
        const shown = new URL(window.location.href).searchParams;

        ['from', 'to'].forEach((key) => {
            if (shown.has(key)) url.searchParams.set(key, shown.get(key)); else url.searchParams.delete(key);
        });

        return url.toString();
    }

    @if ($drawInBrowser)
        // the refresh timer loads new data, keeping what the graph shows
        function refreshGraph() {
            document.querySelector('[x-data^="timeSeriesGraph"]')?.timeSeriesGraph?.refresh();
        }
    @endif

    // The toolbar moves the graph drawn in the browser, or loads the page again with a new image.
    // Thumbnails and date picker set the range of a graph in the browser; address, picker and highlight follow it.
    document.addEventListener('alpine:init', () => {
        const PAN_STEP = 0.25; // of the view, as the graph in the browser
        const ZOOM_STEP = 0.5;
        const MIN_SPAN = 120;
        const SAVE_DELAY = 500;

        const now = () => Math.floor(Date.now() / 1000);
        const viewOf = (range) => ('last' in range ? { from: now() - range.last, to: now() } : range);

        const download = (blob, name) => {
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = name;
            link.click();
            setTimeout(() => URL.revokeObjectURL(link.href), 0);
        };

        const DEFAULTS = @js(new App\Data\Graphing\GraphViewOptions);
        const ADDRESS = @js(App\Data\Graphing\GraphViewOptions::ADDRESS); // shared in the address, the others are the viewer's
        const COMPARE = { period: 'yes', day: 'day', week: 'week' }; // the previous parameter

        // range: of the page, options: App\Data\Graphing\GraphViewOptions, interactive: drawn in the browser,
        // view: {compare, trend} only for this view, in the address but not saved
        window.Alpine.data('graphsPage', (range, options, interactive, view) => {
            let saving = null;
            // the menus have their own data, where $root and $refs are theirs
            let root = null;
            let image = null;

            return {
                // seconds up to now, null for a fixed range
                last: range.last ?? null,
                options: { ...options, ...view },
                // features: the options that change the graph shown
                state: { loading: interactive, refreshing: false, zoomed: false, features: { mirror: false, stacking: false } },
                showHelp: false,
                // the picker is being set to what the graph shows
                syncing: false,

                init() {
                    root = this.$root;
                    image = this.$refs.image ?? null;
                    if (interactive) {
                        this.updateAddress();
                    }
                },

                // An address shows what is drawn: drawing options that are not the defaults, and the view.
                updateAddress() {
                    const url = new URL(window.location.href);
                    const value = (key) => (typeof this.options[key] === 'boolean' ? (this.options[key] ? '1' : '0') : this.options[key]);

                    ADDRESS.forEach((key) => {
                        if (this.options[key] === DEFAULTS[key]) url.searchParams.delete(key); else url.searchParams.set(key, value(key));
                    });
                    if (this.options.compare) url.searchParams.set('previous', COMPARE[this.options.compare]); else url.searchParams.delete('previous');
                    if (this.options.trend) url.searchParams.set('trend', '1'); else url.searchParams.delete('trend');

                    window.history.replaceState(window.history.state, '', url);
                },

                graph() {
                    return root.querySelector('[x-data^="timeSeriesGraph"]')?.timeSeriesGraph;
                },

                picker() {
                    return root.querySelector('[x-data="dateRangePicker"]')?.dateRangePicker;
                },

                // an image graph is drawn again for the new range
                navigate(view) {
                    const span = Math.max(view.to - view.from, MIN_SPAN);
                    const middle = (view.from + view.to) / 2;
                    const url = new URL(window.location.href);

                    url.searchParams.set('from', String(Math.round(middle - span / 2)));
                    url.searchParams.set('to', String(Math.round(middle + span / 2)));
                    window.location = url.toString();
                },

                // negative for out
                zoom(steps) {
                    if (interactive) {
                        this.graph()?.zoom(steps);

                        return;
                    }

                    const { from, to } = viewOf(range);
                    const middle = (from + to) / 2;
                    const half = (to - from) * ZOOM_STEP ** steps / 2;
                    this.navigate({ from: middle - half, to: middle + half });
                },

                // negative for earlier
                pan(steps) {
                    if (interactive) {
                        this.graph()?.pan(steps);

                        return;
                    }

                    const { from, to } = viewOf(range);
                    const shift = (to - from) * PAN_STEP * steps;
                    this.navigate({ from: from + shift, to: to + shift });
                },

                reset() {
                    this.graph()?.reset();
                },

                keydown(event) {
                    const actions = {
                        '?': () => { this.showHelp = ! this.showHelp; },
                        ArrowLeft: () => this.pan(-1),
                        ArrowRight: () => this.pan(1),
                        '+': () => this.zoom(1),
                        '=': () => this.zoom(1),
                        '-': () => this.zoom(-1),
                        r: () => this.reset(),
                        R: () => this.reset(),
                    };

                    if (! (event.key in actions) || event.ctrlKey || event.metaKey || event.altKey || this.state.loading
                        || (this.showHelp && event.key !== '?')
                        || event.target.closest?.('input, textarea, select, [contenteditable]')) {
                        return;
                    }

                    event.preventDefault();
                    actions[event.key]();
                },

                saveRenderer(interactiveNext) {
                    window.axios.post(@js(route('preferences.store')), { pref: @js($rendererPreference), value: interactiveNext ? 'js' : 'default' })
                        .then(() => window.location.reload());
                },

                // kept for the next graph, saved once changes pause
                saveOptions() {
                    clearTimeout(saving);
                    saving = setTimeout(() => {
                        const saved = Object.fromEntries(Object.keys(DEFAULTS).map((key) => [key, this.options[key]]));
                        window.axios.post(@js(route('preferences.store')), { pref: @js($optionsPreference), value: saved })
                            .catch((error) => toastr.error(error.response?.data?.message ?? error.message));
                    }, SAVE_DELAY);
                },

                setOption(key, value) {
                    this.options[key] = value;
                    this.graph()?.setOptions({ [key]: value });
                    this.updateAddress();
                    if (key in DEFAULTS) {
                        this.saveOptions();
                    }
                },

                toggleColumn(column) {
                    const columns = this.options.columns.includes(column)
                        ? this.options.columns.filter((other) => other !== column)
                        : @js(App\Data\Graphing\GraphViewOptions::COLUMNS).filter((other) => other === column || this.options.columns.includes(other));
                    this.setOption('columns', columns);
                },

                resized({ width, height }) {
                    this.options.width = width;
                    this.options.height = height;
                    this.saveOptions();
                },

                autoSize() {
                    this.graph()?.setOptions({ width: null, height: null });
                    this.resized({ width: null, height: null });
                },

                async exportImage() {
                    const picture = interactive
                        ? await this.graph()?.image()
                        : await window.axios.get(image.currentSrc || image.src, { responseType: 'blob' }).then((response) => response.data);
                    if (picture) {
                        // image graphs may be SVG
                        download(picture, `${@js($fileName)}.${picture.type === 'image/svg+xml' ? 'svg' : 'png'}`);
                    }
                },

                async exportCsv() {
                    let csv = this.graph()?.csv();
                    if (! interactive) {
                        const view = viewOf(range);
                        const [charts, response] = await Promise.all([
                            window.LibreNMS.loadTimeSeriesChart(),
                            window.axios.get(@js($graphDataUrl), { params: { ...view, width: image.clientWidth || window.innerWidth } }),
                        ]);
                        csv = charts.toCsv(response.data, view);
                    }
                    if (csv) {
                        download(new Blob([csv], { type: 'text/csv' }), @js($fileName . '.csv'));
                    }
                },

                isPeriod(seconds) {
                    return this.last !== null && Math.abs(this.last - seconds) <= 0.05 * seconds;
                },

                // modified clicks still open the link
                showPeriod(seconds, event) {
                    if (event.ctrlKey || event.metaKey || event.shiftKey || ! this.graph()) {
                        return;
                    }

                    event.preventDefault();
                    this.graph().setRange({ last: seconds });
                },

                picked({ start, end, relativeStartSeconds, relativeEndSeconds }) {
                    if (this.syncing) {
                        return;
                    }

                    if (! start) {
                        // cleared: the default period
                        this.graph()?.setRange({ last: 86400 });

                        return;
                    }

                    const time = now();
                    const next = relativeStartSeconds !== null && relativeEndSeconds === null
                        ? { last: relativeStartSeconds }
                        : {
                            from: relativeStartSeconds !== null ? time - relativeStartSeconds : LibreNMS.Date.toUrl(start),
                            to: relativeEndSeconds !== null ? time - relativeEndSeconds : (end ? LibreNMS.Date.toUrl(end) : time),
                        };

                    this.syncing = true;
                    this.graph()?.setRange(next);
                    this.syncing = false;
                },

                rangeChanged(shown) {
                    const from = 'last' in shown ? LibreNMS.Date.toShortOffset(shown.last) : String(shown.from);
                    const to = 'last' in shown ? null : String(shown.to);
                    const url = new URL(window.location.href);

                    url.searchParams.set('from', from);
                    if (to === null) url.searchParams.delete('to'); else url.searchParams.set('to', to);
                    window.history.replaceState(window.history.state, '', url);

                    range = shown;
                    this.last = shown.last ?? null;

                    if (! this.syncing) {
                        this.syncing = true;
                        this.picker()?.clear();
                        this.picker()?.set(from, to);
                        this.syncing = false;
                    }
                },
            };
        });
    });

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
