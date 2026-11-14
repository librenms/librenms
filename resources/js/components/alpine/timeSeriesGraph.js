import LibreNMSDate from '../../datetime.js';
import {formatValue, summarize} from '../../graphs/seriesMath.js';

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
// narrower screens get the label of the value axis above the graph, see the sm: classes of the view
const WIDE_SCREEN = '(min-width: 640px)';

const now = () => Math.floor(Date.now() / 1000);

/**
 * A graph drawn in the browser.
 *
 * It shows a time range: {last: seconds} for a period up to now, or {from, to} in unix time.
 * The page sets another range with $el.timeSeriesGraph.setRange(range). The user can zoom and pan
 * away from the range and reset back to it. Each change dispatches graph-range-changed with what is shown now.
 *
 * @param {string} src the graph data endpoint of the graph
 * @param {{last: number}|{from: number, to: number}} range the range to start with
 */
export default function timeSeriesGraph(src, range) {
    // kept out of the component data, Alpine must not make these reactive
    let createChart = null;
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

        /**
         * Show what is already loaded of a view right away, then tell the page and load the view in full detail
         *
         * @param {{from: number, to: number}|null} next null to go back to the range
         * @param {boolean} moving more moves are likely to follow, so wait for them before loading
         */
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
                    import('../../graphs/timeSeriesChart.js'),
                    window.axios.get(src, { params, signal: current.signal }),
                ]);

                createChart = module.default;
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
            chart = createChart(this.$refs.plot, graph, {
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
                const format = (value) => formatValue(value, series.unit, graph.axis.base);
                const summary = summarize(series, Math.max(start, 0), end);

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
                    value: formatValue(series.avg[position.index], series.unit, graph.axis.base),
                }));

            // with many series, the busiest ones are the interesting ones
            const listed = rows.length > MAX_TOOLTIP_ROWS
                ? [...rows].sort((a, b) => b.amount - a.amount).slice(0, MAX_TOOLTIP_ROWS)
                : rows;

            this.tooltip = {
                time: LibreNMSDate.display(graph.timestamps[position.index], { dateStyle: 'medium', timeStyle: 'short', hourCycle: 'h23' }),
                rows: listed,
                more: rows.length - listed.length,
                style: this.tooltipStyle(position.left),
            };
        },

        /**
         * The tooltip goes beside the cursor, far enough to leave the graph around the cursor in sight,
         * on the side with the most room. A finger would cover it there, so for touch it goes in the corner
         * away from the finger. It starts at the top of the graph and moves as needed to stay inside the window.
         *
         * @param {number} left where the cursor is in the graph
         */
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
}
