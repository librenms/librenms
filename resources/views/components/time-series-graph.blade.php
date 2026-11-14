@props(['src', 'range', 'height' => 300])

{{--
    A graph drawn in the browser from a graph data url.

    It shows a time range: ['last' => seconds] for a period up to now, or ['from' => time, 'to' => time].
    The page sets another range with $el.timeSeriesGraph.setRange(range), as {last} or {from, to}.
    The user can zoom and pan away from the range and reset back to it.
    Each change dispatches graph-range-changed with what is shown now.
--}}
@php
    $button = 'tw:h-8 tw:min-w-8 tw:px-2 tw:inline-flex tw:items-center tw:justify-center tw:gap-1 tw:rounded tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:text-gray-700 tw:dark:text-gray-200 tw:hover:bg-gray-100 tw:dark:hover:bg-dark-gray-300 tw:disabled:opacity-40 tw:disabled:pointer-events-none';
    $kbd = 'tw:px-1.5 tw:py-0.5 tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-100 tw:bg-white tw:dark:bg-dark-gray-300 tw:border tw:border-gray-300 tw:dark:border-dark-gray-100 tw:rounded tw:shadow-2xs';

    // the help by section: what can be done and the ways to do it, each way the keys to combine
    $help = [
        __('graphs.help.time_range') => [
            [__('graphs.help.pan'), [[__('graphs.help.drag')], ['←'], ['→'], ['Shift', __('graphs.help.scroll')]]],
            [__('graphs.help.zoom'), [['+'], ['-'], ['Ctrl', __('graphs.help.scroll')]]],
            [__('graphs.help.zoom_range'), [['Shift', __('graphs.help.drag')]]],
            [__('graphs.help.reset'), [['R'], [__('graphs.help.double_click')]]],
            [__('graphs.help.values'), [[__('graphs.help.hover')]]],
        ],
        __('graphs.help.series') => [
            [__('graphs.help.only'), [[__('graphs.help.click')]]],
            [__('graphs.help.toggle'), [['Ctrl', __('graphs.help.click')]]],
        ],
        __('graphs.help.touch') => [
            [__('graphs.help.pan'), [[__('graphs.help.drag')]]],
            [__('graphs.help.zoom'), [[__('graphs.help.pinch')]]],
            [__('graphs.help.values'), [[__('graphs.help.tap')]]],
            [__('graphs.help.reset'), [[__('graphs.help.double_tap')]]],
        ],
        __('graphs.help.general') => [
            [__('graphs.help.open'), [['?']]],
            [__('graphs.help.close'), [['Esc']]],
        ],
    ];
@endphp
<div x-data="timeSeriesGraph(@js($src), @js($range))" x-on:keydown.window="keydown($event)" {{ $attributes }}>
    <div class="tw:flex tw:items-center tw:justify-end tw:gap-1 tw:mb-1 tw:text-sm">
        <span class="tw:sm:hidden tw:mr-auto tw:font-semibold tw:truncate" x-text="caption"></span>
        <i x-show="refreshing && ! loading" x-cloak class="fa-solid fa-spinner fa-spin tw:mr-2 tw:text-gray-500 tw:dark:text-gray-400" aria-hidden="true"></i>
        <button type="button" class="{{ $button }}" x-on:click="zoom(-1)" :disabled="loading" title="{{ __('graphs.zoom_out') }}" aria-label="{{ __('graphs.zoom_out') }}">
            <i class="fa-solid fa-magnifying-glass-minus" aria-hidden="true"></i>
        </button>
        <button type="button" class="{{ $button }}" x-on:click="zoom(1)" :disabled="loading" title="{{ __('graphs.zoom_in') }}" aria-label="{{ __('graphs.zoom_in') }}">
            <i class="fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i>
        </button>
        <button type="button" class="{{ $button }} tw:ml-2" x-on:click="pan(-1)" :disabled="loading" title="{{ __('graphs.earlier') }}" aria-label="{{ __('graphs.earlier') }}">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </button>
        <button type="button" class="{{ $button }}" x-on:click="pan(1)" :disabled="loading" title="{{ __('graphs.later') }}" aria-label="{{ __('graphs.later') }}">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
        <button type="button" class="{{ $button }} tw:ml-2" x-on:click="reset" :disabled="! zoomed" title="{{ __('graphs.reset_title') }}">
            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i> {{ __('graphs.reset') }}
        </button>
        <button type="button" x-on:click="showHelp = true" title="{{ __('graphs.help.title') }}" aria-label="{{ __('graphs.help.title') }}"
                class="tw:h-8 tw:w-8 tw:inline-flex tw:items-center tw:justify-center tw:rounded-full tw:text-gray-500 tw:dark:text-gray-400 tw:hover:text-gray-800 tw:dark:hover:text-gray-100">
            <i class="fa-regular fa-circle-question fa-xl" aria-hidden="true"></i>
        </button>
    </div>

    <div class="tw:relative tw:text-gray-700 tw:dark:text-gray-300" style="height: {{ (int) $height }}px">
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
                </div>
            </template>
            <div x-show="tooltip?.more" class="tw:text-gray-500 tw:dark:text-gray-400">+<span x-text="tooltip?.more"></span> {{ __('graphs.more') }}</div>
        </div>
    </div>

    <div class="tw:overflow-x-auto tw:mt-2" x-show="legend.length" x-cloak>
        <table class="tw:mx-auto tw:text-xs tw:sm:text-sm">
            <thead>
                <tr class="tw:text-gray-500 tw:dark:text-gray-400">
                    <th></th>
                    @foreach (['now', 'min', 'max', 'avg'] as $column)
                        <th class="tw:text-right tw:font-semibold tw:px-1.5 tw:sm:px-3">{{ __("graphs.legend.$column") }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <template x-for="item in legend" :key="item.key">
                    <tr class="tw:cursor-pointer tw:select-none tw:hover:bg-gray-100 tw:dark:hover:bg-dark-gray-300"
                        :class="hidden.includes(item.key) && 'tw:opacity-40'"
                        x-on:click="toggle(item.key, $event)">
                        <td class="tw:pr-2 tw:sm:pr-6 tw:py-1.5 tw:sm:py-0.5">
                            <span class="tw:flex tw:items-center tw:gap-2">
                                <span class="tw:inline-block tw:w-3 tw:h-3 tw:rounded-sm tw:shrink-0" :style="{ backgroundColor: item.color }"></span>
                                <span class="tw:max-w-[30vw] tw:sm:max-w-none tw:truncate" x-text="item.label"></span>
                            </span>
                        </td>
                        @foreach (['now', 'min', 'max', 'avg'] as $column)
                            <td class="tw:text-right tw:font-mono tw:tabular-nums tw:whitespace-nowrap tw:px-1.5 tw:sm:px-3" x-text="item.{{ $column }}"></td>
                        @endforeach
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <x-modal show="showHelp">
        <x-slot name="heading">
            <h4 class="tw:m-0 tw:text-base tw:font-semibold tw:flex tw:items-center tw:gap-2">
                <i class="fa fa-keyboard-o tw:text-blue-500" aria-hidden="true"></i>
                {{ __('graphs.help.title') }}
            </h4>
        </x-slot>

        @foreach ($help as $section => $rows)
            <div>
                <h5 class="tw:font-semibold tw:uppercase tw:tracking-wider tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-2">{{ $section }}</h5>
                <div class="tw:grid tw:grid-cols-1 tw:gap-2">
                    @foreach ($rows as [$description, $ways])
                        <div class="tw:flex tw:items-center tw:justify-between tw:gap-3 tw:p-2 tw:rounded-md tw:bg-gray-50 tw:dark:bg-dark-gray-400">
                            <span class="tw:text-gray-600 tw:dark:text-dark-white-300">{{ $description }}</span>
                            <div class="tw:flex tw:flex-wrap tw:items-center tw:justify-end tw:gap-x-3 tw:gap-y-1.5">
                                @foreach ($ways as $keys)
                                    <span class="tw:flex tw:items-center tw:gap-1 tw:whitespace-nowrap">
                                        @foreach ($keys as $key)
                                            @if (! $loop->first)<span class="tw:text-gray-400">+</span>@endif
                                            <kbd class="{{ $kbd }}">{{ $key }}</kbd>
                                        @endforeach
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </x-modal>
</div>

@pushOnce('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const MAX_TOOLTIP_ROWS = 12;
        // how far the tooltip stays from the cursor, and from the edges of the window
        const TOOLTIP_GAP = 32;
        const TOOLTIP_MARGIN = 8;
        // a move that is still going on, like scrolling or dragging, loads data once it pauses
        const SETTLE_DELAY = 200;
        // data is loaded this much of the view past both ends, so a pan shows data right away
        const MARGIN = 0.5;
        // how much of the view a step earlier or later moves, and how much of it a step in shows
        const PAN_STEP = 0.25;
        const ZOOM_STEP = 0.5;
        // the view is kept between two minutes and ten years wide
        const MIN_SPAN = 120;
        const MAX_SPAN = 10 * 365 * 86400;
        // narrower screens get the label of the value axis above the graph, see the sm: classes above
        const WIDE_SCREEN = '(min-width: 640px)';

        const now = () => Math.floor(Date.now() / 1000);

        window.Alpine.data('timeSeriesGraph', (src, range) => {
            // kept out of the component data, Alpine must not make these reactive
            let draw = null; // formatValue, summarize and createChart of the chart module, once it is loaded
            let chart = null;
            let graph = null;
            let view = null; // the {from, to} zoomed or panned to, null while showing the range
            let request = null;
            let timer = null;

            const shown = () => view ?? ('last' in range ? { from: now() - range.last, to: now() } : range);

            return {
                loading: true,
                refreshing: false,
                error: null,
                legend: [],
                hidden: [],
                tooltip: null,
                zoomed: false,
                showHelp: false,
                caption: '',

                init() {
                    this.$root.timeSeriesGraph = { setRange: (next) => this.setRange(next) };
                    new ResizeObserver(() => chart?.resize()).observe(this.$refs.plot);
                    this.load();
                },

                destroy() {
                    clearTimeout(timer);
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

                // Move a number of steps, negative for earlier
                pan(steps) {
                    const { from, to } = shown();
                    const shift = (to - from) * PAN_STEP * steps;

                    this.show({ from: from + shift, to: to + shift });
                },

                // Zoom a number of steps, negative for out
                zoom(steps) {
                    const { from, to } = shown();
                    const middle = (from + to) / 2;
                    const half = (to - from) * ZOOM_STEP ** steps / 2;

                    this.show({ from: middle - half, to: middle + half });
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

                    if (! (event.key in actions) || event.ctrlKey || event.metaKey || event.altKey || this.loading
                        || (this.showHelp && event.key !== '?')
                        || event.target.closest?.('input, textarea, select, [contenteditable]')) {
                        return;
                    }

                    event.preventDefault();
                    actions[event.key]();
                },

                // Show what is already loaded of a view right away, then tell the page and load the view in full detail.
                // next is null to go back to the range. More moves are likely to follow one that is moving, so loading waits for them.
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
                    this.updateLegend();

                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        this.$dispatch('graph-range-changed', view ?? range);
                        this.load();
                    }, moving ? SETTLE_DELAY : 0);
                },

                async load() {
                    const { from, to } = shown();
                    const margin = Math.round((to - from) * MARGIN);
                    const until = Math.max(to, Math.min(to + margin, now()));
                    const params = {
                        from: from - margin,
                        to: until,
                        // the same detail as loading only the view
                        width: Math.round(this.$refs.plot.clientWidth * (until - from + margin) / (to - from)),
                    };

                    request?.abort();
                    const current = request = new AbortController();
                    this.refreshing = true;

                    try {
                        const [module, response] = await Promise.all([
                            window.LibreNMS.loadTimeSeriesChart(),
                            window.axios.get(src, { params, signal: current.signal }),
                        ]);

                        draw = module;
                        graph = response.data;
                        // a min and max that equal the average are left out by the server
                        graph.series.forEach((series) => {
                            series.min ??= series.avg;
                            series.max ??= series.avg;
                        });
                        this.caption = graph.axis.label;
                        this.error = null;

                        if (chart) {
                            chart.update(graph);
                        } else {
                            this.draw();
                        }
                        this.updateLegend();
                    } catch (error) {
                        if (current.signal.aborted) {
                            return; // replaced by a newer load
                        }
                        if (! error.response) {
                            console.error(error); // not an answer from the server, so a fault in drawing
                        }
                        this.error = error.response?.data?.message ?? error.message;
                    }

                    this.loading = false;
                    this.refreshing = false;
                },

                draw() {
                    chart?.destroy();
                    this.tooltip = null;
                    chart = draw.default(this.$refs.plot, graph, {
                        view: shown(),
                        hidden: new Set(this.hidden),
                        compact: ! window.matchMedia(WIDE_SCREEN).matches,
                        onView: (next, moving) => this.show(next, moving),
                        onReset: () => this.reset(),
                        onHover: (position) => this.hover(position),
                    });
                },

                // The legend values are those of what is shown, not of the margins loaded around it
                updateLegend() {
                    if (graph === null) {
                        return;
                    }

                    const { from, to } = shown();
                    const start = graph.timestamps.findIndex((timestamp) => timestamp >= from);
                    const end = start === -1 ? 0 : graph.timestamps.findLastIndex((timestamp) => timestamp <= to) + 1;

                    this.legend = graph.series.map((series) => {
                        const format = (value) => draw.formatValue(value, series.unit, graph.axis.base);
                        const summary = draw.summarize(series, Math.max(start, 0), end);

                        return {
                            key: series.key,
                            label: series.label,
                            color: series.color,
                            now: format(summary.now),
                            min: format(summary.min),
                            max: format(summary.max),
                            avg: format(summary.avg),
                        };
                    });
                },

                // Show only one series, or all again when it is the only one. With ctrl or shift, show or hide just that one.
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

                    const rows = graph.series
                        .filter((series) => ! this.hidden.includes(series.key) && series.avg[position.index] !== null)
                        .map((series) => ({
                            key: series.key,
                            label: series.label,
                            color: series.color,
                            amount: series.avg[position.index],
                            value: draw.formatValue(series.avg[position.index], series.unit, graph.axis.base),
                        }));

                    // with many series, the busiest ones are the interesting ones
                    const listed = rows.length > MAX_TOOLTIP_ROWS
                        ? [...rows].sort((a, b) => b.amount - a.amount).slice(0, MAX_TOOLTIP_ROWS)
                        : rows;

                    this.tooltip = {
                        time: LibreNMS.Date.display(graph.timestamps[position.index], { dateStyle: 'medium', timeStyle: 'short', hourCycle: 'h23' }),
                        rows: listed,
                        more: rows.length - listed.length,
                        style: this.tooltipStyle(position.left),
                    };
                },

                // The tooltip goes beside the cursor, far enough to leave the graph around the cursor in sight,
                // on the side with the most room. A finger would cover it there, so for touch it goes in the corner
                // away from the finger. It starts at the top of the graph and moves as needed to stay inside the window.
                tooltipStyle(left) {
                    const plot = this.$refs.plot.getBoundingClientRect();
                    const touch = window.matchMedia('(pointer: coarse)').matches;
                    const before = left > plot.width / 2;
                    const room = before ? left - TOOLTIP_GAP : plot.width - left - TOOLTIP_GAP;
                    const inset = touch ? '0' : `${plot.width - room}px`;
                    // after the cursor or in the left corner it is placed from the left, else from the right
                    const fromLeft = touch ? before : ! before;
                    // it stays between the menu bar, which sticks to the top of the window, and the bottom of the window
                    const menuBar = document.querySelector('.navbar-sticky-top')?.getBoundingClientRect().bottom ?? 0;
                    const highest = menuBar + TOOLTIP_MARGIN - plot.top;
                    const lowest = window.innerHeight - TOOLTIP_MARGIN - plot.top;

                    return {
                        top: '0',
                        left: fromLeft ? inset : 'auto',
                        right: fromLeft ? 'auto' : inset,
                        maxWidth: touch ? '75%' : `${room}px`,
                        // 100% is the height of the tooltip
                        transform: `translateY(max(${highest}px, min(0px, calc(${lowest}px - 100%))))`,
                    };
                },
            };
        });
    });
</script>
@endPushOnce
