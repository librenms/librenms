@props(['src', 'range', 'options' => new App\Data\Graphing\GraphViewOptions, 'view' => ['compare' => null, 'trend' => false]])

{{--
    range: ['last' => seconds] up to now, or ['from' => time, 'to' => time].
    options: App\Data\Graphing\GraphViewOptions, the size is set by dragging the bottom corners.
    view: compare with an earlier time (period, day or week, null for none), and trend lines.
    Use it with $el.timeSeriesGraph: setRange(range), refresh(), zoom(steps), pan(steps), reset(), setOptions(options),
    image() for a PNG Blob and csv() for the data shown.
    Dispatches graph-range-changed with what is shown, graph-state-changed with {loading, refreshing, zoomed, features},
    and graph-resized with {width, height} once dragged, nulls when back to the automatic size.
--}}
@php
    $columns = collect(App\Data\Graphing\GraphViewOptions::COLUMNS)->mapWithKeys(fn (string $column) => [$column => __("graphs.legend.$column")]);
    $sortButton = 'tw:inline-flex tw:items-center tw:gap-1 tw:p-0 tw:border-0 tw:bg-transparent tw:font-semibold tw:text-inherit tw:cursor-pointer tw:whitespace-nowrap';
    $handle = 'tw:absolute tw:bottom-0 tw:z-10 tw:w-4 tw:h-4 tw:touch-none tw:opacity-30 tw:group-hover:opacity-100 tw:transition-opacity
               tw:after:absolute tw:after:bottom-0.5 tw:after:w-2.5 tw:after:h-2.5 tw:after:border-b-2 tw:after:border-gray-500 tw:dark:after:border-gray-400';
@endphp
<div x-data="timeSeriesGraph(@js($src), @js($range), @js($options), @js($view))"
     x-effect="$dispatch('graph-state-changed', { loading, refreshing, zoomed, features })" {{ $attributes }}>
    <div x-show="caption" x-cloak class="tw:sm:hidden tw:mb-1 tw:text-sm tw:font-semibold tw:truncate" x-text="caption"></div>

    {{-- a dragged size replaces the automatic one --}}
    <div x-ref="box" :style="boxStyle()"
         @if ($options->height !== null) style="width: {{ round(($options->width ?? 1) * 100, 1) }}%; height: {{ $options->height }}px; min-height: 0; max-height: none; aspect-ratio: auto" @endif
         class="tw:group tw:relative tw:mx-auto tw:w-full tw:aspect-[3/1] tw:min-h-[240px] tw:max-h-[60vh] tw:text-gray-700 tw:dark:text-gray-300">
        <div x-ref="plot" class="tw:absolute tw:inset-0" :class="error && 'tw:invisible'"></div>

        <div x-show="loading" class="tw:absolute tw:inset-0 tw:flex tw:items-center tw:justify-center">
            <i class="fa-solid fa-spinner fa-spin fa-2x" aria-hidden="true"></i>
            <span class="tw:sr-only">{{ __('graphs.loading') }}</span>
        </div>

        <div x-show="error" x-cloak x-text="error" role="alert"
             class="tw:absolute tw:inset-0 tw:flex tw:items-center tw:justify-center tw:text-lg tw:text-red-600 tw:dark:text-red-400"></div>

        <div x-show="tooltip" x-cloak
             class="tw:absolute tw:z-10 tw:pointer-events-none tw:px-3 tw:py-2 tw:rounded tw:shadow-lg tw:text-sm tw:whitespace-nowrap tw:bg-white tw:dark:bg-dark-gray-400 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100"
             :style="tooltip?.style">
            <div class="tw:font-semibold tw:mb-1" x-text="tooltip?.time"></div>
            <template x-for="row in tooltip?.rows ?? []" :key="row.key">
                <div class="tw:flex tw:items-center tw:gap-2">
                    <span class="tw:inline-block tw:w-3 tw:h-3 tw:rounded-sm tw:shrink-0" :style="{ backgroundColor: row.color }"></span>
                    <span class="tw:grow tw:min-w-0 tw:max-w-64 tw:truncate" x-text="row.label"></span>
                    <span class="tw:font-mono tw:tabular-nums tw:pl-2 tw:shrink-0" x-text="row.value"></span>
                    <span x-show="row.previous !== null" class="tw:font-mono tw:tabular-nums tw:shrink-0 tw:text-gray-500 tw:dark:text-gray-400"
                          :title="@js(__('graphs.previous'))" x-text="`(${row.previous})`"></span>
                </div>
            </template>
            <div x-show="tooltip?.more" class="tw:text-gray-500 tw:dark:text-gray-400">+<span x-text="tooltip?.more"></span> {{ __('graphs.more') }}</div>
        </div>

        <div class="{{ $handle }} tw:right-0 tw:cursor-nwse-resize tw:after:right-0.5 tw:after:border-r-2"
             title="{{ __('graphs.resize') }}" x-on:pointerdown="startResize($event, 1)" x-on:dblclick="autoSize()"></div>
        <div class="{{ $handle }} tw:left-0 tw:cursor-nesw-resize tw:after:left-0.5 tw:after:border-l-2"
             title="{{ __('graphs.resize') }}" x-on:pointerdown="startResize($event, -1)" x-on:dblclick="autoSize()"></div>
    </div>

    <div class="tw:overflow-x-auto tw:mt-2" x-show="legend.length" x-cloak>
        <table class="tw:mx-auto tw:text-xs tw:sm:text-sm">
            <thead>
                <tr class="tw:text-gray-500 tw:dark:text-gray-400">
                    <th class="tw:text-left tw:font-semibold" :aria-sort="sort?.column === 'label' ? (sort.descending ? 'descending' : 'ascending') : 'none'">
                        <button type="button" class="{{ $sortButton }}" x-on:click="sortBy('label')">
                            {{ __('graphs.legend.series') }} <i class="fa-solid tw:text-xs" :class="sortIcon('label')" aria-hidden="true"></i>
                        </button>
                    </th>
                    <template x-for="column in options.columns" :key="column">
                        <th class="tw:text-right tw:font-semibold tw:px-1.5 tw:sm:px-3"
                            :aria-sort="sort?.column === column ? (sort.descending ? 'descending' : 'ascending') : 'none'">
                            <button type="button" class="{{ $sortButton }} tw:ml-auto" x-on:click="sortBy(column)">
                                <span x-text="@js($columns)[column]"></span> <i class="fa-solid tw:text-xs" :class="sortIcon(column)" aria-hidden="true"></i>
                            </button>
                        </th>
                    </template>
                </tr>
            </thead>
            <tbody>
                <template x-for="item in sortedLegend()" :key="item.key">
                    <tr class="tw:cursor-pointer tw:select-none tw:hover:bg-gray-100 tw:dark:hover:bg-dark-gray-300"
                        :class="hidden.includes(item.key) && 'tw:opacity-40'"
                        x-on:click="toggle(item.key, $event)">
                        <td class="tw:pr-2 tw:sm:pr-6 tw:py-1.5 tw:sm:py-0.5">
                            {{-- the click reaches the row --}}
                            <button type="button" :aria-pressed="! hidden.includes(item.key)"
                                    class="tw:flex tw:items-center tw:gap-2 tw:p-0 tw:border-0 tw:bg-transparent tw:text-inherit tw:text-left tw:cursor-pointer">
                                <span class="tw:inline-block tw:w-3 tw:h-3 tw:rounded-sm tw:shrink-0" :style="{ backgroundColor: item.color }"></span>
                                <span class="tw:max-w-[30vw] tw:sm:max-w-none tw:truncate" x-text="item.label"></span>
                            </button>
                        </td>
                        <template x-for="column in options.columns" :key="column">
                            <td class="tw:text-right tw:font-mono tw:tabular-nums tw:whitespace-nowrap tw:px-1.5 tw:sm:px-3" x-text="item[column]"></td>
                        </template>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

</div>

@pushOnce('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const MAX_TOOLTIP_ROWS = 12;
        const TOOLTIP_GAP = 32; // from the cursor
        const TOOLTIP_MARGIN = 8; // from the window edges
        const SETTLE_DELAY = 200; // a move loads data once it pauses
        const MARGIN = 0.5; // of the view, loaded past both ends
        const PAN_STEP = 0.25; // of the view
        const ZOOM_STEP = 0.5; // of the view
        const MIN_SPAN = 120;
        const MAX_SPAN = 10 * 365 * 86400;
        const MAX_DETAIL_CHANGE = 2; // zooming further than this from the loaded detail loads again
        const REFRESH_AFTER = 60; // seconds of newer data a view can miss before it loads again
        const WIDE_SCREEN = window.matchMedia('(min-width: 640px)'); // the sm: classes above
        const TOUCH = window.matchMedia('(pointer: coarse)');
        const MIN_HEIGHT = {{ App\Data\Graphing\GraphViewOptions::MIN_HEIGHT }};
        const MAX_HEIGHT = {{ App\Data\Graphing\GraphViewOptions::MAX_HEIGHT }};
        const MIN_WIDTH = {{ App\Data\Graphing\GraphViewOptions::MIN_WIDTH }}; // of the space for it

        const DRAWN_OPTIONS = ['range', 'mirror', 'stacking', 'scale', 'trend']; // the others are the legend, size and compare
        const COMPARE_SECONDS = { day: 86400, week: 604800 }; // a period is the span shown

        const now = () => Math.floor(Date.now() / 1000);
        const clamp = (value, low, high) => Math.min(Math.max(value, low), high);

        window.Alpine.data('timeSeriesGraph', (src, range, options, viewOptions) => {
            // not reactive
            let charts = null; // the chart module
            let chart = null;
            let graph = null;
            let view = null; // {from, to} zoomed or panned to
            let earlier = null; // the data of the time compared with, moved to line up with the graph
            let loaded = null; // {from, to, detail, offset} of the data, detail in seconds per pixel, offset back to the earlier data
            let request = null;
            let timer = null;
            let legendFrame = null;
            let resizes = null;

            const shown = () => view ?? ('last' in range ? { from: now() - range.last, to: now() } : range);
            const keys = (data) => data.series.map((series) => series.key).join('\n');

            return {
                loading: true,
                refreshing: false,
                error: null,
                legend: [],
                hidden: [],
                tooltip: null,
                zoomed: false,
                caption: '',
                // which options change this graph
                features: { mirror: false, stacking: false },
                options: { ...options, ...viewOptions },
                // column and direction, null for the order of the graph
                sort: null,

                init() {
                    this.$root.timeSeriesGraph = {
                        setRange: (next) => this.setRange(next),
                        refresh: () => this.load(),
                        zoom: (steps) => this.zoom(steps),
                        pan: (steps) => this.pan(steps),
                        reset: () => this.reset(),
                        setOptions: (next) => this.setOptions(next),
                        image: () => chart?.image() ?? Promise.resolve(null),
                        csv: () => (graph === null ? null : charts.toCsv(graph, shown(), new Set(this.hidden))),
                    };
                    resizes = new ResizeObserver(() => chart?.resize());
                    resizes.observe(this.$refs.plot);
                    this.load();
                },

                destroy() {
                    clearTimeout(timer);
                    cancelAnimationFrame(legendFrame);
                    resizes?.disconnect();
                    request?.abort();
                    chart?.destroy();
                },

                setRange(next) {
                    range = next;
                    this.show(null);
                },

                reset() {
                    if (this.zoomed) {
                        this.show(null);
                    }
                },

                // negative for earlier
                pan(steps) {
                    const { from, to } = shown();
                    const shift = (to - from) * PAN_STEP * steps;

                    this.show({ from: from + shift, to: to + shift });
                },

                // negative for out
                zoom(steps) {
                    const { from, to } = shown();
                    const middle = (from + to) / 2;
                    const half = (to - from) * ZOOM_STEP ** steps / 2;

                    this.show({ from: middle - half, to: middle + half });
                },

                // the drawing ones draw again, the legend ones only change the legend
                setOptions(next) {
                    const drawn = DRAWN_OPTIONS.some((key) => key in next && next[key] !== this.options[key]);
                    const compared = 'compare' in next && next.compare !== this.options.compare;

                    this.options = { ...this.options, ...next };
                    if (drawn && graph !== null) {
                        this.draw();
                    }
                    if (compared) {
                        this.load();
                    }
                },

                // how far back the time compared with is, 0 for none
                compareSeconds() {
                    const { from, to } = shown();

                    return this.options.compare === 'period' ? to - from : COMPARE_SECONDS[this.options.compare] ?? 0;
                },

                boxStyle() {
                    const { width, height } = this.options;

                    return height === null
                        ? { width: '', height: '', minHeight: '', maxHeight: '', aspectRatio: '' }
                        : { width: `${(width ?? 1) * 100}%`, height: `${height}px`, minHeight: '0', maxHeight: 'none', aspectRatio: 'auto' };
                },

                // side: 1 for the right corner, -1 for the left, the box stays in the middle
                startResize(event, side) {
                    const handle = event.currentTarget;
                    const box = this.$refs.box;
                    const space = box.parentElement.clientWidth;
                    const start = { x: event.clientX, y: event.clientY, width: box.offsetWidth, height: box.offsetHeight };
                    const move = (moved) => {
                        const width = clamp(start.width + side * (moved.clientX - start.x) * 2, space * MIN_WIDTH, space);
                        this.options.width = Math.round(width / space * 1000) / 1000;
                        this.options.height = Math.round(clamp(start.height + moved.clientY - start.y, MIN_HEIGHT, MAX_HEIGHT));
                    };
                    const stop = () => {
                        handle.removeEventListener('pointermove', move);
                        handle.removeEventListener('pointerup', stop);
                        handle.removeEventListener('pointercancel', stop);
                        this.$dispatch('graph-resized', { width: this.options.width, height: this.options.height });
                    };

                    event.preventDefault();
                    handle.setPointerCapture(event.pointerId);
                    handle.addEventListener('pointermove', move);
                    handle.addEventListener('pointerup', stop);
                    handle.addEventListener('pointercancel', stop);
                },

                autoSize() {
                    this.options.width = null;
                    this.options.height = null;
                    this.$dispatch('graph-resized', { width: null, height: null });
                },

                // Shows what is loaded right away, and loads the rest once moving stops. null is back to the range.
                show(next, moving = false) {
                    if (next !== null) {
                        const span = Math.min(Math.max(next.to - next.from, MIN_SPAN), MAX_SPAN);
                        const middle = (next.from + next.to) / 2;
                        next = { from: Math.round(middle - span / 2), to: Math.round(middle + span / 2) };
                    }

                    view = next;
                    this.zoomed = view !== null;
                    this.tooltip = null;
                    chart?.setView(shown());
                    legendFrame ??= requestAnimationFrame(() => {
                        legendFrame = null;
                        this.updateLegend();
                    });

                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        this.$dispatch('graph-range-changed', view ?? range);
                        if (this.needsData()) {
                            this.load();
                        } else {
                            // the loaded data covers it, a load for an earlier view would replace that
                            request?.abort();
                            this.refreshing = false;
                        }
                    }, moving ? SETTLE_DELAY : 0);
                },

                // not laid out while hidden
                plotWidth() {
                    return this.$refs.plot.clientWidth || window.innerWidth;
                },

                // the loaded data is missing a part of the view, newer data, or is too coarse or fine for it
                needsData() {
                    const { from, to } = shown();
                    const detail = (to - from) / this.plotWidth();

                    return loaded === null
                        || from < loaded.from
                        || Math.min(to, now()) > loaded.to + REFRESH_AFTER
                        || detail > loaded.detail * MAX_DETAIL_CHANGE
                        || detail < loaded.detail / MAX_DETAIL_CHANGE
                        || this.compareSeconds() !== loaded.offset;
                },

                async load() {
                    const { from, to } = shown();
                    const margin = Math.round((to - from) * MARGIN);
                    const until = Math.max(to, Math.min(to + margin, now()));
                    const params = {
                        from: from - margin,
                        to: until,
                        // the same detail as the view alone
                        width: Math.round(this.plotWidth() * (until - from + margin) / (to - from)),
                    };

                    const offset = this.compareSeconds();

                    request?.abort();
                    const current = request = new AbortController();
                    this.refreshing = true;

                    try {
                        const [module, response, before] = await Promise.all([
                            window.LibreNMS.loadTimeSeriesChart(),
                            window.axios.get(src, { params, signal: current.signal }),
                            offset === 0
                                ? null
                                : window.axios.get(src, { params: { ...params, from: params.from - offset, to: params.to - offset }, signal: current.signal })
                                    // no earlier data is no comparison, not a broken graph
                                    .catch((error) => (current.signal.aborted ? Promise.reject(error) : null)),
                        ]);

                        const replaced = graph;
                        charts = module;
                        graph = response.data;
                        earlier = before === null ? null : { ...before.data, timestamps: before.data.timestamps.map((time) => time + offset) };
                        loaded = { from: params.from, to: params.to, detail: (params.to - params.from) / params.width, offset };
                        this.caption = graph.axis.label;
                        this.features = { mirror: graph.series.some((series) => series.invert), stacking: graph.series.length > 1 };
                        this.error = null;

                        if (chart && keys(replaced) === keys(graph) && chart.comparing === (earlier !== null)) {
                            chart.update(graph, earlier);
                        } else {
                            // series came or went, keep the choices for the rest
                            this.hidden = this.hidden.filter((key) => graph.series.some((series) => series.key === key));
                            this.draw();
                        }
                        this.updateLegend();
                    } catch (error) {
                        if (current.signal.aborted) {
                            return; // replaced by a newer load
                        }
                        loaded = null; // try again on the next move
                        if (! error.response) {
                            console.error(error); // a fault in drawing
                        }
                        this.error = error.response?.data?.message ?? error.message;
                    }

                    this.loading = false;
                    this.refreshing = false;
                },

                draw() {
                    chart?.destroy();
                    this.tooltip = null;
                    chart = charts.default(this.$refs.plot, graph, {
                        view: shown(),
                        hidden: new Set(this.hidden),
                        compact: ! WIDE_SCREEN.matches,
                        draw: Object.fromEntries(DRAWN_OPTIONS.map((key) => [key, this.options[key]])),
                        previous: earlier,
                        onView: (next, moving) => this.show(next, moving),
                        onReset: () => this.reset(),
                        onHover: (position) => this.hover(position),
                    });
                },

                // of what is shown, not the margins
                updateLegend() {
                    if (graph === null) {
                        return;
                    }

                    const [start, end] = charts.shownPoints(graph.timestamps, shown());

                    this.legend = graph.series.map((series) => {
                        const format = (value) => charts.formatValue(value, series.unit, graph.axis.base);
                        const summary = charts.summarize(series, start, end);

                        return {
                            key: series.key,
                            label: series.label,
                            color: series.color,
                            now: format(summary.now),
                            min: format(summary.min),
                            max: format(summary.max),
                            avg: format(summary.avg),
                            p95: format(summary.p95),
                            values: summary, // to sort by
                        };
                    });
                },

                // in the order of the graph unless sorted, series without a value last
                sortedLegend() {
                    if (this.sort === null) {
                        return this.legend;
                    }

                    const { column, descending } = this.sort;
                    const value = (item) => (column === 'label' ? item.label : item.values[column]);

                    return [...this.legend].sort((a, b) => {
                        const [first, second] = [value(a), value(b)];
                        if (first === null || second === null) {
                            return (first === null) - (second === null);
                        }
                        const order = column === 'label' ? first.localeCompare(second) : first - second;

                        return descending ? -order : order;
                    });
                },

                // labels first a to z, values first the highest, then the other way, then the order of the graph
                sortBy(column) {
                    const first = column !== 'label';

                    if (this.sort?.column !== column) {
                        this.sort = { column, descending: first };
                    } else if (this.sort.descending === first) {
                        this.sort = { column, descending: ! first };
                    } else {
                        this.sort = null;
                    }
                },

                sortIcon(column) {
                    if (this.sort?.column !== column) {
                        return 'fa-sort tw:opacity-30';
                    }

                    return this.sort.descending ? 'fa-sort-down' : 'fa-sort-up';
                },

                // Only this series, or all again. With ctrl or shift, show or hide this one.
                toggle(key, event) {
                    const others = graph.series.map((series) => series.key).filter((other) => other !== key);

                    if (event.ctrlKey || event.metaKey || event.shiftKey) {
                        this.hidden = this.hidden.includes(key) ? this.hidden.filter((other) => other !== key) : [...this.hidden, key];
                    } else {
                        const alone = ! this.hidden.includes(key) && others.every((other) => this.hidden.includes(other));
                        this.hidden = alone ? [] : others;
                    }

                    this.draw();
                },

                hover(position) {
                    if (position === null) {
                        this.tooltip = null;

                        return;
                    }

                    const format = (series, value) => charts.formatValue(value, series.unit, graph.axis.base);
                    const rows = graph.series
                        .filter((series) => ! this.hidden.includes(series.key))
                        .map((series) => ({ series, now: series.avg[position.index] ?? null, before: position.previous[series.key] ?? null }))
                        .filter(({ now, before }) => now !== null || before !== null)
                        .map(({ series, now, before }) => ({
                            key: series.key,
                            label: series.label,
                            color: series.color,
                            amount: now ?? before,
                            value: format(series, now),
                            previous: before === null ? null : format(series, before),
                        }));

                    // the busiest ones
                    const listed = rows.length > MAX_TOOLTIP_ROWS
                        ? [...rows].sort((a, b) => b.amount - a.amount).slice(0, MAX_TOOLTIP_ROWS)
                        : rows;

                    this.tooltip = {
                        time: LibreNMS.Date.display(position.time, { dateStyle: 'medium', timeStyle: 'short', hourCycle: 'h23' }),
                        rows: listed,
                        more: rows.length - listed.length,
                        style: this.tooltipStyle(position.left),
                    };
                },

                // Beside the cursor on the side with more room, for touch in the top corner away from the finger
                tooltipStyle(left) {
                    const plot = this.$refs.plot.getBoundingClientRect();
                    const touch = TOUCH.matches;
                    const before = left > plot.width / 2;
                    const room = before ? left - TOOLTIP_GAP : plot.width - left - TOOLTIP_GAP;
                    const inset = touch ? '0' : `${plot.width - room}px`;
                    const fromLeft = touch ? before : ! before;
                    // kept between the sticky menu bar and the bottom of the window
                    const menuBar = document.querySelector('.navbar-sticky-top')?.getBoundingClientRect().bottom ?? 0;
                    const highest = menuBar + TOOLTIP_MARGIN - plot.top;
                    const lowest = window.innerHeight - TOOLTIP_MARGIN - plot.top;

                    return {
                        top: '0',
                        left: fromLeft ? inset : 'auto',
                        right: fromLeft ? 'auto' : inset,
                        maxWidth: touch ? '75%' : `${room}px`,
                        // 100% is the tooltip height
                        transform: `translateY(max(${highest}px, min(0px, calc(${lowest}px - 100%))))`,
                    };
                },
            };
        });
    });
</script>
@endPushOnce
