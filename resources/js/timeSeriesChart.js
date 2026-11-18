// Loaded on demand. Draws a graph from GraphDataController with uPlot.

/**
 * @typedef {object} GraphData App\Data\Graphing\GraphData
 * @property {number[]} timestamps unix time each point's interval ends
 * @property {{label: string, min: ?number, max: ?number, base: number}} axis min and max are limits when set
 * @property {GraphSeries[]} series
 */

/**
 * @typedef {object} GraphSeries App\Data\Graphing\GraphDataSeries, values line up with the timestamps
 * @property {string} key
 * @property {string} label
 * @property {string} unit
 * @property {string} color
 * @property {?string} fillColor the area below the line, or the stacked area
 * @property {?string} maxFillColor the area below the maximum of a line
 * @property {boolean} stacked
 * @property {boolean} invert drawn below the axis
 * @property {number} drawScale multiplies the drawn value
 * @property {Array<?number>} avg null where there is no data
 * @property {?Array<?number>} min null when the same as avg, as it is when each point is a single stored value
 * @property {?Array<?number>} max null when the same as avg
 */

/** @typedef {{from: number, to: number}} View unix times */

/**
 * @typedef {object} ChartOptions
 * @property {View} view
 * @property {Set<string>} hidden keys of series not drawn
 * @property {boolean} compact for small screens
 * @property {(view: View, moving: boolean) => void} onView zoomed or panned, moving while more is likely to follow
 * @property {() => void} onReset
 * @property {(position: ?{index: number, left: number}) => void} onHover the point whose interval the cursor is in
 */

/**
 * @typedef {object} Chart
 * @property {(graph: GraphData) => void} update new data for the same series
 * @property {(view: View) => void} setView
 * @property {() => void} resize to the element
 * @property {() => void} destroy
 */

import uPlot from 'uplot';
import 'uplot/dist/uPlot.min.css';
import LibreNMSDate from './datetime.js';
import LibreNMSNumber from './number.js';

const GRID = 'rgba(128, 128, 128, 0.2)';
const SELECTION = 'rgba(128, 128, 128, 0.3)';
const MIN_ZOOM_PIXELS = 5;
const WHEEL_ZOOM = 0.8;
const TAP_PIXELS = 10;
const DOUBLE_TAP_MS = 350;
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

// a point is the average of the interval ending at its time, drawn level like the image graphs
const INTERVALS = uPlot.paths.stepped({ align: -1, alignGaps: -1 });

export function formatValue(value, unit = '', base = 1000) {
    if (value === null) {
        return '-';
    }

    if (unit === '%') {
        return `${Number(value.toFixed(2))} %`;
    }

    const format = base === 1024 ? LibreNMSNumber.formatBi : LibreNMSNumber.formatSi;

    return format(value, 2, 0, unit).trim();
}

/**
 * Legend values of points start to end (exclusive). Min and max use those of each point, keeping consolidated peaks.
 *
 * @param {GraphSeries} series
 */
export function summarize(series, start, end) {
    const mins = series.min ?? series.avg;
    const maxes = series.max ?? series.avg;
    let now = null;
    let min = Infinity;
    let max = -Infinity;
    let sum = 0;
    let count = 0;

    for (let index = start; index < end; index++) {
        const value = series.avg[index];
        if (value !== null) {
            now = value;
            min = Math.min(min, mins[index] ?? value);
            max = Math.max(max, maxes[index] ?? value);
            sum += value;
            count++;
        }
    }

    return count === 0
        ? { now: null, min: null, max: null, avg: null }
        : { now, min, max, avg: sum / count };
}

function drawnValues(series, values) {
    const factor = series.drawScale * (series.invert ? -1 : 1);

    return factor === 1 ? values : values.map((value) => (value === null ? null : value * factor));
}

// a gap in every series stays a gap
function stack(lists) {
    const totals = [];

    return lists.map((values) => values.map((value, index) => {
        if (value !== null) {
            totals[index] = (totals[index] ?? 0) + value;
        }

        return totals[index] ?? null;
    }));
}

// Back to front: max ranges, fills, stacked areas from the top down, lines.
// Layers depend only on which series there are, so new data can replace the old.
function layersFor(series) {
    const stacked = series.filter((item) => item.stacked);
    const lines = series.filter((item) => ! item.stacked);
    const average = (item) => (data) => drawnValues(data[item.key], data[item.key].avg);

    return [
        // points that cover more than one stored value show their range
        ...lines.map((item) => ({
            fill: item.maxFillColor ?? `${item.color}26`,
            values: (data) => (data[item.key].max === null ? null : drawnValues(data[item.key], data[item.key].max)),
        })),
        ...lines.filter((item) => item.fillColor).map((item) => ({ fill: item.fillColor, values: average(item) })),
        ...stacked.map((item) => ({
            fill: item.fillColor,
            stroke: item.color,
            values: (data, totals) => totals[item.key],
        })).reverse(),
        ...lines.map((item) => ({ stroke: item.color, values: average(item) })),
    ];
}

// from zero unless the graph sets a limit, like the image graphs, with room where the data sets it
function valueRange(axis) {
    const floor = axis.min ?? 0;

    return (plot, dataMin, dataMax) => {
        if (dataMin === null || dataMax === null) {
            return [floor, axis.max ?? floor + 1];
        }

        const min = Math.min(dataMin, floor);
        const max = Math.max(dataMax, axis.max ?? dataMax);
        const room = (max - min || 1) * 0.05;

        return [min < floor ? min - room : min, max === axis.max ? max : max + room];
    };
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
export default function createChart(element, graph, { view, hidden, compact, onView, onReset, onHover }) {
    const label = compact ? '' : graph.axis.label;
    const visible = graph.series.filter((item) => ! hidden.has(item.key));
    const layers = layersFor(visible);
    const unit = commonUnit(graph.series);
    const zone = LibreNMSDate.zone();
    // read per draw, the site style can change
    const text = () => getComputedStyle(element).color;
    const size = () => ({ width: element.clientWidth, height: element.clientHeight });
    let shown = view;

    const dataFor = (source) => {
        const byKey = Object.fromEntries(source.series.map((item) => [item.key, item]));
        const stacked = visible.filter((item) => item.stacked).map((item) => byKey[item.key]);
        const totals = stack(stacked.map((item) => drawnValues(item, item.avg)));
        const totalsByKey = Object.fromEntries(stacked.map((item, index) => [item.key, totals[index]]));
        const empty = source.timestamps.map(() => null);

        return [source.timestamps, ...layers.map((layer) => layer.values(byKey, totalsByKey) ?? empty)];
    };

    // the first point ending at or after the time
    const intervalIndex = (plot, closest, time) => (
        plot.data[0][closest] < time && closest < plot.data[0].length - 1 ? closest + 1 : closest
    );

    const startPan = (event) => {
        const start = { ...shown, x: event.clientX };
        const secondsPerPixel = (shown.to - shown.from) / plot.over.clientWidth;
        const move = (moved) => {
            const shift = (start.x - moved.clientX) * secondsPerPixel;
            onView({ from: start.from + shift, to: start.to + shift }, true);
        };
        const stop = () => {
            document.removeEventListener('mousemove', move);
            document.removeEventListener('mouseup', stop);
            plot.over.style.cursor = 'grab';
        };

        event.preventDefault();
        plot.over.style.cursor = 'grabbing';
        document.addEventListener('mousemove', move);
        document.addEventListener('mouseup', stop);

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
            y: { range: valueRange(graph.axis) },
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
                incrs: graph.axis.base === 1024 ? BINARY_INCREMENTS : undefined,
            },
        ],
        series: [
            {},
            ...layers.map((layer) => ({
                stroke: layer.stroke,
                fill: layer.fill,
                fillTo: 0,
                width: layer.stroke ? 1.25 : 0,
                paths: INTERVALS,
                points: { show: false },
            })),
        ],
        cursor: {
            y: false,
            points: { size: (self, index) => (layers[index - 1]?.stroke ? 7 : 0) },
            dataIdx: (self, seriesIndex, closest, time) => intervalIndex(self, closest, time),
            // the page zooms to a selection, not uPlot
            drag: { x: true, y: false, setScale: false, dist: MIN_ZOOM_PIXELS },
            bind: {
                mousedown: (self, target, handler) => (event) => (event.shiftKey ? handler(event) : startPan(event)),
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
                onHover(idx === null || idx === undefined || left < 0
                    ? null
                    : { index: intervalIndex(self, idx, self.posToVal(left, 'x')), left: self.over.offsetLeft + left });
            }],
        },
    }, dataFor(graph), element);

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
        update: (next) => plot.setData(dataFor(next)),
        setView: (next) => {
            shown = next;
            plot.setScale('x', { min: shown.from, max: shown.to });
        },
        resize: () => plot.setSize(size()),
        destroy: () => {
            style.disconnect();
            plot.destroy();
        },
    };
}
