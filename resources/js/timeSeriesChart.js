// Loaded on demand. Draws a graph from GraphDataController with uPlot.

/** @typedef {import('./graphs/seriesData.js').GraphData} GraphData */
/** @typedef {import('./graphs/plotData.js').DrawOptions} DrawOptions */
/** @typedef {import('./graphs/seriesData.js').Threshold} Threshold */
/** @typedef {import('./graphs/seriesData.js').View} View */

/**
 * @typedef {object} ChartOptions
 * @property {View} view
 * @property {Set<string>} hidden keys of series not drawn
 * @property {boolean} compact for small screens
 * @property {DrawOptions} draw
 * @property {?GraphData} previous an earlier time moved to line up with the graph, drawn as dashed lines
 * @property {Threshold[]} thresholds the ones to draw, always inside the value axis
 * @property {(view: View, moving: boolean) => void} onView zoomed or panned, moving while more is likely to follow
 * @property {() => void} onReset
 * @property {(position: ?{index: number, time: number, left: number, previous: Object<string, ?number>, trends: Object<string, ?number>}) => void} onHover
 *     the point whose interval the cursor is in, and the earlier values and trends there. Past the data, only earlier values and trends are drawn.
 */

/**
 * @typedef {object} Chart
 * @property {(graph: GraphData, previous: ?GraphData) => void} update new data for the same series, previous when it has it
 * @property {(view: View) => void} setView
 * @property {() => void} resize to the element
 * @property {() => Promise<Blob>} image a PNG of the plot
 * @property {boolean} comparing drawn with previous data
 * @property {() => void} destroy
 */

import uPlot from 'uplot';
import 'uplot/dist/uPlot.min.css';
import LibreNMSDate from './datetime.js';
import {createPlotData, thresholdLines} from './graphs/plotData.js';
import {formatValue} from './graphs/seriesData.js';

const GRID = 'rgba(128, 128, 128, 0.2)';
const SELECTION = 'rgba(128, 128, 128, 0.3)';
const MIN_ZOOM_PIXELS = 5;
const WHEEL_ZOOM = 0.8;
const TAP_PIXELS = 10;
const DOUBLE_TAP_MS = 350;
const LOG_TICK_PIXELS = 28;
const BINARY_INCREMENTS = Array.from({ length: 70 }, (unused, power) => 2 ** power);

// [increment, default, year, month, day, hour, minute, second rollover, mode]
const TIME_AXIS = [
    [3600 * 24 * 365, '{YYYY}', null, null, null, null, null, null, 1],
    [3600 * 24 * 28, '{MMM}', '\n{YYYY}', null, null, null, null, null, 1],
    [3600 * 24, '{D} {MMM}', '\n{YYYY}', null, null, null, null, null, 1],
    [3600, '{HH}:{mm}', '\n{D} {MMM} {YYYY}', null, '\n{D} {MMM}', null, null, null, 1],
    [60, '{HH}:{mm}', '\n{D} {MMM} {YYYY}', null, '\n{D} {MMM}', null, null, null, 1],
    [1, '{HH}:{mm}:{ss}', '\n{D} {MMM} {YYYY}', null, '\n{D} {MMM}', null, null, null, 1],
];

// a point is the average of the interval ending at its time, drawn level in the stepped style
const LINE_PATHS = {
    stepped: uPlot.paths.stepped({ align: -1, alignGaps: -1 }),
    linear: uPlot.paths.linear(),
    smooth: uPlot.paths.spline(),
};

// from zero unless the graph sets a limit or a margin, like the image graphs, with room where the data sets it
function valueRange(axis) {
    if (axis.margin > 0 && axis.min === null && axis.max === null) {
        return fittedRange;
    }

    const floor = axis.min ?? 0;

    return (plot, dataMin, dataMax) => {
        if (dataMin === null || dataMax === null) {
            return [floor, axis.max ?? floor + 1];
        }

        const min = Math.min(dataMin, floor);
        // zero stays in view for values below the axis
        const max = Math.max(dataMax, axis.max ?? 0);
        const room = (max - min || 1) * 0.05;

        return [min < floor ? min - room : min, max === axis.max ? max : max + room];
    };
}

const THRESHOLD_ROOM = 0.05; // of the spread of the thresholds, between them and the edge of the axis

/**
 * The lowest and highest values the axis has to show so every threshold is inside it, away from its edges.
 * The same rule as GraphOptions::thresholdBounds() for the image graphs.
 *
 * @param {Threshold[]} thresholds
 * @param {boolean} mirror series marked invert are drawn below the axis
 * @returns {?number[]} null without thresholds
 */
function thresholdBounds(thresholds, mirror) {
    if (thresholds.length === 0) {
        return null;
    }

    const values = thresholds.flatMap((threshold) => thresholdLines(threshold, mirror));
    const low = Math.min(...values);
    const high = Math.max(...values);
    const room = (high - low || Math.abs(high) || 1) * THRESHOLD_ROOM;

    return [
        low > 0 && low - room < 0 ? 0 : low - room,
        high < 0 && high + room > 0 ? 0 : high + room,
    ];
}

// a range that also reaches the bounds
function including(range, bounds) {
    if (bounds === null) {
        return range;
    }

    return (plot, dataMin, dataMax) => {
        const [min, max] = range(plot, dataMin, dataMax);

        return [Math.min(min, bounds[0]), Math.max(max, bounds[1])];
    };
}

// only the data, the limits of the graph are left out
function fittedRange(plot, dataMin, dataMax) {
    if (dataMin === null || dataMax === null) {
        return [0, 1];
    }

    const room = (dataMax - dataMin || Math.abs(dataMax) || 1) * 0.05;
    // no room below zero when there is nothing there
    const min = dataMin >= 0 && dataMin - room < 0 ? 0 : dataMin - room;

    return [min, dataMax + room];
}

// Powers of ten on both sides of zero, as many as fit. Asinh draws the values between -1 and 1 in a line, so from 1 up.
function logSplits(plot, axisIndex, min, max) {
    const top = Math.max(Math.abs(min), Math.abs(max));
    if (top < 1) {
        return [0];
    }

    const highest = Math.ceil(Math.log10(top));
    const sides = (min < 0 ? 1 : 0) + (max > 0 ? 1 : 0);
    const fit = Math.max(2, Math.floor(plot.bbox.height / devicePixelRatio / LOG_TICK_PIXELS));
    const every = Math.ceil((highest + 1) * sides / fit);
    const powers = [];
    for (let power = highest; power >= 0; power -= every) {
        powers.unshift(10 ** power);
    }

    return [...powers.map((value) => -value).reverse(), 0, ...powers].filter((value) => value >= min && value <= max);
}

// the first color behind the element that is not see through
function backgroundOf(element) {
    for (let current = element; current; current = current.parentElement) {
        const color = getComputedStyle(current).backgroundColor;
        if (color && color !== 'transparent' && ! /rgba\(.*,\s*0\)$/.test(color)) {
            return color;
        }
    }

    return '#fff';
}

function commonUnit(series) {
    const units = new Set(series.map((item) => item.unit));

    return units.size === 1 ? series[0].unit : '';
}

/**
 * The view may be a part of the data or reach past it: the page moves it first and loads data after.
 * Answer onView with setView.
 *
 * @param {HTMLElement} element
 * @param {GraphData} graph
 * @param {ChartOptions} options
 * @returns {Chart}
 */
export default function createChart(element, graph, { view, hidden, compact, draw, previous, thresholds, onView, onReset, onHover }) {
    const label = compact ? '' : graph.axis.label;
    const comparing = previous !== null;
    const { layers, dataFor } = createPlotData(graph, { hidden, draw, thresholds, comparing });
    const lineStyle = draw.line_style ?? 'stepped';
    const log = draw.scale === 'log';
    const logBase = graph.axis.base === 1024 ? 2 : 10;
    const bounds = thresholdBounds(thresholds, draw.mirror);
    const unit = commonUnit(graph.series);
    const zone = LibreNMSDate.zone();
    // read per draw, the site style can change
    const text = () => getComputedStyle(element).color;
    const size = () => ({ width: element.clientWidth, height: element.clientHeight });
    let shown = view;
    let current = { graph, previous };
    // the values of the previous time at each point, for hovering
    let data = dataFor(graph, previous, shown);

    // Stepped lines show the interval covering the cursor. Linear lines snap to the nearest sample.
    const sampleIndex = (plot, closest, time) => (
        lineStyle === 'stepped' && plot.data[0][closest] < time && closest < plot.data[0].length - 1 ? closest + 1 : closest
    );

    let stopPan = null;
    const startPan = (event) => {
        stopPan?.();
        const start = { ...shown, x: event.clientX };
        const secondsPerPixel = (shown.to - shown.from) / plot.over.clientWidth;
        const move = (moved) => {
            const shift = (start.x - moved.clientX) * secondsPerPixel;
            onView({ from: start.from + shift, to: start.to + shift }, true);
        };
        stopPan = () => {
            document.removeEventListener('mousemove', move);
            document.removeEventListener('mouseup', stopPan);
            plot.over.style.cursor = 'grab';
            stopPan = null;
        };

        plot.over.style.cursor = 'grabbing';
        document.addEventListener('mousemove', move);
        document.addEventListener('mouseup', stopPan);

        return null;
    };

    // plain scrolling is left to the page
    const onWheel = (event) => {
        const span = shown.to - shown.from;
        const sideways = Math.abs(event.deltaX) > Math.abs(event.deltaY) ? event.deltaX : (event.shiftKey ? event.deltaY : 0);

        if (event.ctrlKey || event.metaKey) {
            const factor = event.deltaY < 0 ? WHEEL_ZOOM : 1 / WHEEL_ZOOM;
            const anchor = plot.posToVal(event.clientX - plot.over.getBoundingClientRect().left, 'x');

            event.preventDefault();
            onView({ from: anchor - (anchor - shown.from) * factor, to: anchor + (shown.to - anchor) * factor }, true);
        } else if (sideways !== 0) {
            const shift = (sideways / plot.over.clientWidth) * span;

            event.preventDefault();
            onView({ from: shown.from + shift, to: shown.to + shift }, true);
        }
    };

    // one finger pans, two zoom, a double tap resets
    let gesture = null;
    let lastTap = 0;
    const touchPoints = (event) => {
        const left = plot.over.getBoundingClientRect().left;

        return [...event.touches].map((touch) => touch.clientX - left);
    };
    const startGesture = (event, moved) => {
        gesture = event.touches.length === 0 ? null : { view: shown, points: touchPoints(event), moved };
    };
    const onTouchMove = (event) => {
        if (gesture === null || event.touches.length !== gesture.points.length) {
            return;
        }

        const points = touchPoints(event);
        const width = plot.over.clientWidth;
        const { from, to } = gesture.view;

        if (points.length === 1) {
            if (! gesture.moved && Math.abs(points[0] - gesture.points[0]) < TAP_PIXELS) {
                return;
            }
            const shift = (gesture.points[0] - points[0]) / width * (to - from);

            onView({ from: from + shift, to: to + shift }, true);
        } else {
            // the time between the fingers stays there
            const spread = (ends) => Math.max(Math.abs(ends[0] - ends[1]), 1);
            const middle = (ends) => (ends[0] + ends[1]) / 2;
            const anchor = from + middle(gesture.points) / width * (to - from);
            const span = (to - from) * spread(gesture.points) / spread(points);
            const start = anchor - middle(points) / width * span;

            onView({ from: start, to: start + span }, true);
        }
        gesture.moved = true;
    };
    const onTouchEnd = (event) => {
        if (event.touches.length === 0 && gesture !== null && ! gesture.moved) {
            const tapped = Date.now();
            if (tapped - lastTap < DOUBLE_TAP_MS) {
                onReset();
            }
            lastTap = tapped;
        }

        // a finger that stays is no longer a tap
        startGesture(event, true);
    };

    const plot = new uPlot({
        ...size(),
        tzDate: (timestamp) => uPlot.tzDate(new Date(timestamp * 1000), zone),
        legend: { show: false },
        scales: {
            // set by the page, not the data
            x: { auto: false, range: () => [shown.from, shown.to] },
            // asinh is logarithmic away from zero and also draws values at or below it
            y: log
                ? { distr: 4, log: logBase, range: bounds === null ? undefined : (plot, min, max) => uPlot.rangeAsinh(Math.min(min ?? bounds[0], bounds[0]), Math.max(max ?? bounds[1], bounds[1]), logBase, false) }
                : { range: including(draw.scale === 'fit' ? fittedRange : valueRange(graph.axis), bounds) },
        },
        axes: [
            { stroke: text, grid: { stroke: GRID }, ticks: { stroke: GRID }, values: TIME_AXIS },
            {
                stroke: text,
                grid: { stroke: GRID },
                ticks: { stroke: GRID },
                label: label || undefined,
                // inverted series are below the axis, not negative
                values: (self, ticks) => ticks.map((value) => formatValue(Math.abs(value), unit, graph.axis.base)),
                size: (self, values) => Math.max(...(values ?? ['']).map((value) => value.length)) * 7 + (label ? 40 : 16),
                // ticks at 4 GiB, not 3.73 GiB
                incrs: graph.axis.base === 1024 && ! log ? BINARY_INCREMENTS : undefined,
                splits: log ? logSplits : undefined,
                // the ticks are already few, the log default would leave some out
                filter: log ? (self, splits) => splits : undefined,
            },
        ],
        series: [
            {},
            ...layers.map((layer) => ({
                // uPlot strokes in black by default
                stroke: layer.stroke ?? 'transparent',
                fill: layer.fill,
                fillTo: 0,
                width: layer.stroke ? (layer.constant === undefined ? 1.25 : 1.5) : 0,
                dash: layer.dash,
                // trends and constants are straight, not drawn level for each interval
                paths: layer.trend !== undefined || layer.constant !== undefined ? undefined : LINE_PATHS[lineStyle],
                points: { show: false },
            })),
        ],
        // the top layer of a range is filled down to the bottom one
        bands: layers.flatMap((layer, index) => (layer.range ? [{ series: [index + 1, index + 2], fill: layer.range }] : [])),
        cursor: {
            y: false,
            points: { size: (self, index) => (layers[index - 1]?.stroke && layers[index - 1].constant === undefined ? 7 : 0) },
            dataIdx: (self, seriesIndex, closest, time) => sampleIndex(self, closest, time),
            // the page zooms to a selection, not uPlot
            drag: { x: true, y: false, setScale: false, dist: MIN_ZOOM_PIXELS },
            bind: {
                mousedown: (self, target, handler) => (event) => {
                    if (event.shiftKey) {
                        return handler(event);
                    }

                    return event.button === 0 ? startPan(event) : null;
                },
                dblclick: () => () => {
                    onReset();

                    return null;
                },
            },
        },
        hooks: {
            setSelect: [(self) => {
                const { left, width } = self.select;
                if (width >= MIN_ZOOM_PIXELS) {
                    onView({ from: self.posToVal(left, 'x'), to: self.posToVal(left + width, 'x') }, false);
                }
                self.setSelect({ left: 0, top: 0, width: 0, height: 0 }, false);
            }],
            setCursor: [(self) => {
                const { idx, left } = self.cursor;
                const index = idx === null || idx === undefined || left < 0 ? null : sampleIndex(self, idx, self.posToVal(left, 'x'));
                // past the data there are only trends and earlier values
                const past = index !== null && index >= current.graph.timestamps.length;
                const at = (columns) => Object.fromEntries(Object.entries(columns).map(([key, values]) => [key, values[index]]));
                onHover(index === null || (past && ! comparing && ! draw.trend)
                    ? null
                    : {
                        index,
                        time: self.data[0][index],
                        left: self.over.offsetLeft + left,
                        previous: at(data.earlier),
                        trends: at(data.trends),
                    });
            }],
        },
    }, data.columns, element);

    plot.over.style.cursor = 'grab';
    plot.over.style.touchAction = 'pan-y';
    plot.root.querySelector('.u-select').style.background = SELECTION;
    plot.over.addEventListener('wheel', onWheel, { passive: false });
    plot.over.addEventListener('touchstart', (event) => startGesture(event, false), { passive: true });
    plot.over.addEventListener('touchmove', onTouchMove, { passive: true });
    plot.over.addEventListener('touchend', onTouchEnd, { passive: true });
    plot.over.addEventListener('touchcancel', () => {
        gesture = null;
    }, { passive: true });

    // the site style is a class on the document
    const style = new MutationObserver(() => plot.redraw(false));
    style.observe(document.documentElement, { attributeFilter: ['class'] });

    return {
        comparing,
        update: (next, before) => {
            current = { graph: next, previous: before };
            data = dataFor(next, before, shown);
            plot.setData(data.columns);
        },
        setView: (next) => {
            shown = next;
            // a trend is of what is shown
            if (draw.trend) {
                data = dataFor(current.graph, current.previous, shown);
                plot.setData(data.columns);
            }
            plot.setScale('x', { min: shown.from, max: shown.to });
        },
        resize: () => plot.setSize(size()),
        image: () => {
            const source = plot.ctx.canvas;
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            canvas.width = source.width;
            canvas.height = source.height;
            context.fillStyle = backgroundOf(element);
            context.fillRect(0, 0, canvas.width, canvas.height);
            context.drawImage(source, 0, 0);

            return new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
        },
        destroy: () => {
            stopPan?.();
            style.disconnect();
            plot.destroy();
        },
    };
}
