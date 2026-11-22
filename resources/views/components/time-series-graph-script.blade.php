<script>
    document.addEventListener('alpine:init', () => {
        const { limitRange, now, panRange, resolveRange, zoomRange, columnChoices, columnPercentile, columnShown, formatValue, joinGraph, shownPoints, sliceGraph, summarize, toCsv, loadChart } = window.LibreNMS.Graphs;
        const MAX_TOOLTIP_ROWS = 12;
        const TOOLTIP_GAP = 32; // from the cursor
        const TOOLTIP_MARGIN = 8; // from the window edges
        const SETTLE_DELAY = 200; // a move loads data once it pauses
        const MAX_SPAN = 10 * 365 * 86400;
        const MAX_DETAIL_CHANGE = 2; // zooming further than this from the loaded detail loads again
        const REFRESH_AFTER = 60; // seconds of newer data a view can miss before it loads again
        const MIN_POINTS = 10; // the fewest the server gives
        const KEPT_SPANS = 2; // of the view, the loaded data kept on each side of it

        const DRAWN_OPTIONS = ['range', 'mirror', 'stacking', 'scale', 'line_style', 'trend', 'percentile_lines', 'hidden_percentiles']; // the others are the legend, size and compare
        const COMPARE_SECONDS = { day: 86400, week: 604800 }; // a period is the span shown

        const clamp = (value, low, high) => Math.min(Math.max(value, low), high);

        // What the graph does when its options are left as the graph defines: stacking of the drawn series all, none
        // or some of them, null for a single one; the scale fit to the data, from zero or between limits; percentile lines.
        function ownDrawing({ axis, percentiles, series }, drawn) {
            const stacked = drawn.filter((item) => item.stacked).length;
            const fits = axis.margin > 0 && axis.min === null && axis.max === null;

            return {
                mirror: drawn.some((item) => item.invert),
                stacking: drawn.length < 2 ? null : (stacked === drawn.length ? 'all' : (stacked === 0 ? 'none' : 'some')),
                scale: fits ? 'fit' : ((axis.min ?? 0) === 0 && axis.max === null ? 'zero' : 'limits'),
                percentileLines: percentiles.length > 0 && series.some((item) => item.percentileColor),
            };
        }

        function graphDefinition({ axis, thresholds, percentiles, series }) {
            return JSON.stringify([axis, thresholds, percentiles, series.map(({ avg, min, max, ...definition }) => definition)]);
        }

        window.Alpine.data('timeSeriesGraph', ({ src, range, options, columns, columnLabels, percentileLabel, minWidth, minHeight, maxHeight }) => {
            // Keep the plot and its sample arrays outside Alpine reactivity.
            let createChart;
            let chart = null;
            let graph = null;
            let drawnKeys = [];
            let view = null; // {from, to} zoomed or panned to
            let earlier = null; // the data of the time compared with, moved to line up with the graph
            let loaded = null; // {from, to, fresh, detail, offset} of the data, detail in seconds per pixel, fresh is the newest time it can hold, offset back to the earlier data
            let request = null;
            let timer = null;
            let legendFrame = null;
            let resizes = null;
            let unsized = false; // a load waits for the plot to be laid out

            const shown = () => view ?? resolveRange(range);

            return {
                loading: true,
                refreshing: false,
                error: null,
                legend: [],
                percentiles: [], // of the graph, each a legend column
                thresholds: [],
                toggledThresholds: [], // indexes the viewer turned on or off
                hidden: [],
                tooltip: null,
                zoomed: false,
                caption: '',
                features: null,
                options,
                // column and direction, null for the order of the graph
                sort: null,
                columnsMenuStyle: {},

                init() {
                    this.$root.timeSeriesGraph = {
                        setRange: (next) => this.setRange(next),
                        refresh: () => this.load(true),
                        zoom: (steps) => this.zoom(steps),
                        pan: (steps) => this.pan(steps),
                        reset: () => this.reset(),
                        setOptions: (next) => this.setOptions(next),
                        image: () => chart?.image() ?? Promise.resolve(null),
                        csv: () => (graph === null ? null : toCsv(graph, shown(), new Set(this.hidden))),
                    };
                    resizes = new ResizeObserver(() => {
                        if (unsized && this.$refs.plot.clientWidth > 0) {
                            this.load();
                        }
                        chart?.resize();
                    });
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

                pan(steps) {
                    this.show(panRange(shown(), steps));
                },

                zoom(steps) {
                    this.show(zoomRange(shown(), steps));
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
                        const width = clamp(start.width + side * (moved.clientX - start.x) * 2, space * minWidth, space);
                        this.options.width = Math.round(width / space * 1000) / 1000;
                        this.options.height = Math.round(clamp(start.height + moved.clientY - start.y, minHeight, maxHeight));
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
                    view = next === null ? null : limitRange(next, MAX_SPAN);
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
                        || Math.min(to, now()) > loaded.fresh + REFRESH_AFTER
                        || detail > loaded.detail * MAX_DETAIL_CHANGE
                        || detail < loaded.detail / MAX_DETAIL_CHANGE
                        || this.compareSeconds() !== loaded.offset;
                },

                // The part of the view to join to the loaded data: before it, after its last values, which may still be filled in, or both.
                // null when the view needs new data, it is at another detail or apart from the loaded data.
                missingPart() {
                    const { from, to } = shown();
                    const detail = (to - from) / this.plotWidth();
                    if (loaded === null || graph === null
                        || detail > loaded.detail * MAX_DETAIL_CHANGE
                        || detail < loaded.detail / MAX_DETAIL_CHANGE
                        || this.compareSeconds() !== loaded.offset
                        || to < loaded.from || from > loaded.to) {
                        return null;
                    }

                    const valued = graph.timestamps.findLastIndex((time, index) => graph.series.some((series) => series.avg[index] !== null));
                    const lastValue = valued === -1 ? loaded.from : graph.timestamps[valued];
                    const before = from < loaded.from;
                    const after = Math.min(to, now()) > loaded.fresh + REFRESH_AFTER;
                    if (! before && ! after) {
                        return null;
                    }

                    return {
                        from: before ? from : Math.max(from, Math.min(lastValue, loaded.fresh)),
                        to: after ? to : Math.min(to, loaded.from),
                    };
                },

                // all of the view, or the part it is missing at the detail of the loaded data
                async load(all = false) {
                    // a hidden tab or panel has no width to fit the data to yet
                    unsized = this.$refs.plot.clientWidth === 0;
                    if (unsized) {
                        return;
                    }

                    const part = all ? null : this.missingPart();
                    const { from, to } = part ?? shown();
                    const requestedAt = now();
                    const width = part === null ? this.plotWidth() : Math.round((to - from) / loaded.detail);
                    const params = { from, to, width: Math.max(MIN_POINTS, width) };

                    const offset = this.compareSeconds();

                    request?.abort();
                    const current = request = new AbortController();
                    this.refreshing = true;

                    try {
                        const [module, response, before] = await Promise.all([
                            loadChart(),
                            window.axios.get(src, { params, signal: current.signal }),
                            offset === 0
                                ? null
                                : window.axios.get(src, { params: { ...params, from: params.from - offset, to: params.to - offset }, signal: current.signal })
                                    // no earlier data is no comparison, not a broken graph
                                    .catch(() => null),
                        ]);

                        if (current.signal.aborted) {
                            return;
                        }
                        createChart = module.default;
                        const moved = before && { ...before.data, timestamps: before.data.timestamps.map((time) => time + offset) };
                        const range = { from: params.from, to: params.to, fresh: Math.min(params.to, requestedAt) };
                        if (part !== null && graphDefinition(graph) !== graphDefinition(response.data)) {
                            return this.load(true); // the graph changed, a part of it does not join
                        }
                        if (part !== null) {
                            earlier = earlier && moved ? joinGraph(earlier, moved) : (earlier ?? moved);
                            loaded = {
                                ...loaded,
                                from: Math.min(loaded.from, range.from),
                                to: Math.max(loaded.to, range.to),
                                fresh: Math.max(loaded.fresh, range.fresh),
                            };
                            this.updateGraph(this.kept(joinGraph(graph, response.data)));
                        } else {
                            earlier = moved;
                            loaded = { ...range, detail: (params.to - params.from) / params.width, offset };
                            this.updateGraph(response.data);
                        }
                        this.error = null;
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

                // the loaded data near the view, the rest is dropped
                kept(next) {
                    const { from, to } = shown();
                    const span = to - from;
                    const near = { from: Math.max(loaded.from, from - span * KEPT_SPANS), to: Math.min(loaded.to, to + span * KEPT_SPANS) };

                    loaded = { ...loaded, ...near, fresh: Math.min(loaded.fresh, near.to) };
                    earlier = earlier && sliceGraph(earlier, near);

                    return sliceGraph(next, near);
                },

                updateGraph(next) {
                    // A refresh can change thresholds, units or styles even when all series keys still match.
                    const canUpdate = chart && chart.comparing === (earlier !== null)
                        && graphDefinition(graph) === graphDefinition(next);
                    graph = next;
                    const drawn = graph.series.filter((series) => ! series.legendOnly);
                    drawnKeys = drawn.map((series) => series.key);
                    this.caption = graph.axis.label;
                    this.features = ownDrawing(graph, drawn);
                    if (canUpdate) {
                        chart.update(graph, earlier);
                    } else {
                        this.hidden = drawnKeys.length < 2 ? [] : this.hidden.filter((key) => drawnKeys.includes(key));
                        this.draw();
                    }
                    this.updateLegend();
                },

                draw() {
                    chart?.destroy();
                    chart = null;
                    this.tooltip = null;
                    chart = createChart(this.$refs.plot, graph, {
                        view: shown(),
                        hidden: new Set(this.hidden),
                        compact: window.matchMedia('(max-width: 639px)').matches,
                        draw: this.options,
                        previous: earlier,
                        thresholds: graph.thresholds.filter((threshold, index) => this.thresholdShown(index)),
                        onView: (next, moving) => this.show(next, moving),
                        onReset: () => this.reset(),
                        onHover: (position) => this.hover(position),
                    });
                },

                // as the graph sets it, unless the viewer turned it on or off
                thresholdShown(index) {
                    return graph?.thresholds[index]?.shown !== this.toggledThresholds.includes(index);
                },

                toggleThreshold(index) {
                    this.toggledThresholds = this.toggledThresholds.includes(index)
                        ? this.toggledThresholds.filter((other) => other !== index)
                        : [...this.toggledThresholds, index];

                    this.draw();
                },

                // of what is shown, the loaded data can reach past it
                updateLegend() {
                    if (graph === null) {
                        return;
                    }

                    const [start, end] = shownPoints(graph.timestamps, shown());

                    this.thresholds = graph.thresholds;
                    this.percentiles = graph.percentiles;
                    // every percentile column the viewer can show
                    const percentiles = this.menuColumns().map(columnPercentile).filter((percentile) => percentile !== null);
                    this.legend = graph.series.map((series) => {
                        const format = (value) => formatValue(value, series.unit, graph.axis.base);
                        const summary = summarize(series, start, end, percentiles);

                        return {
                            key: series.key,
                            label: series.label,
                            color: series.color,
                            toggleable: drawnKeys.length > 1 && ! series.legendOnly,
                            ...Object.fromEntries(Object.entries(summary).map(([column, value]) => [column, format(value)])),
                            values: summary, // to sort by
                        };
                    });
                },

                // the columns the viewer can show or hide
                menuColumns() {
                    return columnChoices(columns, this.percentiles);
                },

                isColumnShown(column) {
                    return columnShown(column, this.options.columns, this.percentiles, this.options.hidden_percentiles);
                },

                shownColumns() {
                    return this.menuColumns().filter((column) => this.isColumnShown(column));
                },

                // the header of a column, a percentile such as 25th
                columnLabel(column) {
                    return columnLabels[column] ?? percentileLabel.replace('%p', column.slice(1));
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

                // A percentile of the graph is hidden or shown again, other columns are chosen keeping their order. Sorting by a hidden one stops.
                toggleColumn(column) {
                    const percentile = columnPercentile(column);
                    if (percentile !== null && this.percentiles.includes(percentile)) {
                        const hidden = this.options.hidden_percentiles;
                        // its line goes with it
                        this.setOptions({ hidden_percentiles: hidden.includes(percentile) ? hidden.filter((other) => other !== percentile) : [...hidden, percentile] });
                    } else {
                        this.options.columns = this.options.columns.includes(column)
                            ? this.options.columns.filter((other) => other !== column)
                            : columns.filter((other) => other === column || this.options.columns.includes(other));
                    }

                    if (this.sort !== null && this.sort.column !== 'label' && ! this.isColumnShown(this.sort.column)) {
                        this.sort = null;
                    }
                    this.$dispatch('graph-columns-changed', { columns: this.options.columns, hidden_percentiles: this.options.hidden_percentiles });
                },

                placeColumnsMenu() {
                    const button = this.$refs.columnsButton.getBoundingClientRect();

                    this.columnsMenuStyle = { top: `${button.bottom + 4}px`, right: `${document.documentElement.clientWidth - button.right}px` };
                },

                // it would stay put while the button moves
                closeColumnsMenu() {
                    if (this.$refs.columnsMenu.matches(':popover-open')) {
                        this.$refs.columnsMenu.hidePopover();
                    }
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
                    if (drawnKeys.length < 2 || ! drawnKeys.includes(key)) {
                        return;
                    }

                    if (event.ctrlKey || event.metaKey || event.shiftKey) {
                        this.hidden = this.hidden.includes(key) ? this.hidden.filter((other) => other !== key) : [...this.hidden, key];
                    } else {
                        const others = drawnKeys.filter((other) => other !== key);
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

                    const format = (series, value) => formatValue(value, series.unit, graph.axis.base);
                    const rows = graph.series
                        .filter((series) => ! this.hidden.includes(series.key))
                        .map((series) => ({
                            series,
                            now: series.avg[position.index] ?? null,
                            before: position.previous[series.key] ?? null,
                            trend: position.trends[series.key] ?? null,
                        }))
                        .filter(({ now, before, trend }) => now !== null || before !== null || trend !== null)
                        .map(({ series, now, before, trend }) => ({
                            key: series.key,
                            label: series.label,
                            color: series.color,
                            amount: now ?? before ?? trend,
                            value: format(series, now),
                            previous: before === null ? null : format(series, before),
                            trend: trend === null ? null : format(series, trend),
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
                    const touch = window.matchMedia('(pointer: coarse)').matches;
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
