// Loaded on demand. Draws a graph from GraphDataController with uPlot.

/**
 * @typedef {object} GraphData App\Data\Graphing\GraphData
 * @property {number[]} timestamps unix time each point's interval ends
 * @property {{label: string, min: ?number, max: ?number, base: number, margin: number}} axis min and max are limits when set,
 *     without them a margin above zero asks to fit the axis to the data
 * @property {GraphSeries[]} series
 * @property {Threshold[]} thresholds reference lines, such as alert limits, shown ones are drawn until the viewer hides them
 */

/**
 * @typedef {object} GraphSeries App\Data\Graphing\GraphDataSeries, values line up with the timestamps
 * @property {string} key
 * @property {string} label
 * @property {string} unit
 * @property {?string} color null for a legend only series without one
 * @property {?string} fillColor the area below the line, or the stacked area
 * @property {?string} maxFillColor the area below the maximum of a line
 * @property {?string} rangeColor the area between the min and max of a line
 * @property {boolean} legendOnly in the legend, not drawn
 * @property {boolean} stacked
 * @property {boolean} invert drawn below the axis
 * @property {number} drawScale multiplies the drawn value
 * @property {Array<?number>} avg null where there is no data
 * @property {?Array<?number>} min null when the same as avg, as it is when each point is a single stored value
 * @property {?Array<?number>} max null when the same as avg
 */

/**
 * @typedef {object} Threshold
 * @property {number} value
 * @property {string} label
 * @property {string} color
 * @property {boolean} shown drawn until the viewer hides it
 * @property {boolean} invert a limit of the series drawn below the axis, drawn below it too when they are
 * @property {boolean} mirror a limit of the series on both sides of the axis, also drawn below it when series are
 */

/** @typedef {{from: number, to: number}} View unix times */

/**
 * @typedef {object} DrawOptions App\Data\Graphing\GraphViewOptions
 * @property {boolean} range shade between the min and max of each line
 * @property {boolean} mirror inverted series below the axis, else above it with the rest
 * @property {'graph'|'stack'|'overlay'} stacking as the graph defines, all on a side of the axis, or none
 * @property {'graph'|'fit'|'log'} scale as the graph defines, fitted to the data, or logarithmic
 * @property {boolean} trend a straight line fitted to what is shown of each series
 */

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
 * @property {(position: ?{index: number, time: number, left: number, previous: Object<string, ?number>}) => void} onHover
 *     the point whose interval the cursor is in, and the earlier values there. Past the data, only earlier values and trends are drawn.
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
import LibreNMSNumber from './number.js';

const GRID = 'rgba(128, 128, 128, 0.2)';
const SELECTION = 'rgba(128, 128, 128, 0.3)';
const MIN_ZOOM_PIXELS = 5;
const WHEEL_ZOOM = 0.8;
const TAP_PIXELS = 10;
const DOUBLE_TAP_MS = 350;
const LOG_TICK_PIXELS = 28;
const PREVIOUS_DASH = [6, 4];
const TREND_DASH = [2, 4];
const THRESHOLD_DASH = [8, 4];
const MAX_TREND_POINTS = 1000; // past the data, to the end of the view
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

// the first index whose timestamp is after the time
function firstAfter(timestamps, time) {
    let low = 0;
    let high = timestamps.length;
    while (low < high) {
        const middle = (low + high) >> 1;
        if (timestamps[middle] > time) {
            high = middle;
        } else {
            low = middle + 1;
        }
    }

    return low;
}

/**
 * The points of a view, start to end (exclusive). A point covers the interval up to its time,
 * so the last one may end after the view (times are whole seconds).
 *
 * @param {number[]} timestamps
 * @param {View} view
 * @returns {[number, number]}
 */
export function shownPoints(timestamps, { from, to }) {
    return [firstAfter(timestamps, from), Math.min(firstAfter(timestamps, to - 1) + 1, timestamps.length)];
}

/**
 * Legend values of points start to end (exclusive). Min and max use those of each point, keeping consolidated peaks.
 * The 95th percentile is of the averages, like the image graphs.
 *
 * @param {GraphSeries} series
 */
export function summarize(series, start, end) {
    const mins = series.min ?? series.avg;
    const maxes = series.max ?? series.avg;
    const values = [];
    let now = null;
    let min = Infinity;
    let max = -Infinity;
    let sum = 0;

    for (let index = start; index < end; index++) {
        const value = series.avg[index];
        if (value !== null) {
            now = value;
            min = Math.min(min, mins[index] ?? value);
            max = Math.max(max, maxes[index] ?? value);
            sum += value;
            values.push(value);
        }
    }

    if (values.length === 0) {
        return { now: null, min: null, max: null, avg: null, p95: null };
    }

    values.sort((a, b) => a - b);

    return { now, min, max, avg: sum / values.length, p95: values[Math.ceil(values.length * 0.95) - 1] };
}

/**
 * The averages of the points of a view, a column for each series.
 *
 * @param {GraphData} graph
 * @param {View} view
 * @param {Set<string>} hidden keys of series left out
 */
export function toCsv(graph, view, hidden = new Set()) {
    // a spreadsheet would run text that starts like a formula
    const cell = (text) => `"${String(text).replace(/^[=+\-@\t\r]/, "'$&").replaceAll('"', '""')}"`;
    const series = graph.series.filter((item) => ! hidden.has(item.key));
    const [start, end] = shownPoints(graph.timestamps, view);
    const lines = [['time', ...series.map((item) => (item.unit ? `${item.label} (${item.unit})` : item.label))].map(cell).join(',')];

    for (let index = start; index < end; index++) {
        const time = new Date(graph.timestamps[index] * 1000).toISOString();
        lines.push([time, ...series.map((item) => item.avg[index] ?? '')].join(','));
    }

    return `${lines.join('\n')}\n`;
}

function drawnValues(series, values) {
    const factor = series.drawScale * (series.invert ? -1 : 1);

    return factor === 1 ? values : values.map((value) => (value === null ? null : value * factor));
}

// uPlot starts a line at its first point, so give that point the start of its interval: the time before it
function withFirstInterval(values) {
    const first = values.findIndex((value) => value !== null);
    if (first < 1) {
        return values;
    }

    const extended = values.slice();
    extended[first - 1] = values[first];

    return extended;
}

// a gap in every series stays a gap, each side of the axis has its own stack
function stackBySide(stacked) {
    const totals = {};

    for (const invert of [false, true]) {
        const side = stacked.filter((item) => item.invert === invert);
        stack(side.map((item) => drawnValues(item, item.avg))).forEach((values, index) => {
            totals[side[index].key] = values;
        });
    }

    return totals;
}

function stack(lists) {
    const totals = [];

    return lists.map((values) => values.map((value, index) => {
        if (value !== null) {
            totals[index] = (totals[index] ?? 0) + value;
        }

        return totals[index] ?? null;
    }));
}

/**
 * The values of each point of the other times, null where they have none.
 * A point is the one whose interval covers the time.
 */
function alignedTo(timestamps, others, values) {
    const step = others.length > 1 ? others[1] - others[0] : Infinity;

    return timestamps.map((time) => {
        const index = firstAfter(others, time - 1);

        return index < others.length && others[index] - time < step ? values[index] : null;
    });
}

// a straight line through the points shown, least squares
function trendOf(timestamps, values, { from, to }) {
    let count = 0;
    let sumTime = 0;
    let sumValue = 0;
    let sumTimeValue = 0;
    let sumTimeSquared = 0;

    values.forEach((value, index) => {
        const time = timestamps[index] - from; // small numbers keep the precision
        if (value !== null && timestamps[index] > from && timestamps[index] <= to) {
            count++;
            sumTime += time;
            sumValue += value;
            sumTimeValue += time * value;
            sumTimeSquared += time * time;
        }
    });

    const spread = count * sumTimeSquared - sumTime * sumTime;
    if (count < 2 || spread === 0) {
        return null;
    }

    const slope = (count * sumTimeValue - sumTime * sumValue) / spread;
    const start = (sumValue - slope * sumTime) / count;

    return (time) => start + slope * (time - from);
}

// Back to front: min to max ranges, fills, stacked areas from the top down, lines, thresholds, earlier lines, trends.
// Layers depend only on which series there are and what is drawn, so new data can replace the old.
// A range is two layers, its top and its bottom, the fill is between them.
// Values get the drawn data {series, totals} of now and of the previous time, null without it.
// A constant layer has the same value at every time.
function layersFor(series, thresholds, { range: showRange, trend, mirror }, comparing) {
    const stacked = series.filter((item) => item.stacked);
    const lines = series.filter((item) => ! item.stacked);
    const average = (item) => ({ series: data }) => drawnValues(data[item.key], data[item.key].avg);
    // a stacked series is drawn at its total
    const drawn = (item, { series: data, totals }) => {
        if (data[item.key] === undefined) {
            return null; // not in the earlier data
        }

        return item.stacked ? totals[item.key] ?? null : drawnValues(data[item.key], data[item.key].avg);
    };
    // inverted, the min is drawn above the max
    const edge = (item, top) => ({ series: data }) => {
        const values = data[item.key];
        if (values.max === null) {
            return null;
        }

        return drawnValues(values, top !== values.invert ? values.max : (values.min ?? values.avg));
    };

    return [
        // points that cover more than one stored value show their range
        ...(showRange ? lines : []).flatMap((item) => [
            { range: item.rangeColor ?? item.maxFillColor ?? `${item.color}26`, values: edge(item, true) },
            { values: edge(item, false) },
        ]),
        ...lines.filter((item) => item.fillColor).map((item) => ({ fill: item.fillColor, values: average(item) })),
        ...stacked.map((item) => ({
            fill: item.fillColor,
            stroke: item.color,
            values: ({ totals }) => totals[item.key],
        })).reverse(),
        ...lines.map((item) => ({ stroke: item.color, values: average(item) })),
        ...thresholds.flatMap((threshold) => thresholdLines(threshold, mirror).map((value) => ({
            stroke: threshold.color, dash: THRESHOLD_DASH, constant: value, values: () => null,
        }))),
        ...(comparing ? series : []).map((item) => ({
            stroke: `${item.color}b3`,
            dash: PREVIOUS_DASH,
            previous: true,
            values: (now, previous) => (previous === null ? null : drawn(item, previous)),
        })),
        ...(trend ? series : []).map((item) => ({ stroke: item.color, dash: TREND_DASH, trend: true, values: (now) => drawn(item, now) })),
    ];
}

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
 * Where the line of a threshold is drawn, as Threshold::drawnValues() for the image graphs
 *
 * @param {Threshold} threshold
 * @param {boolean} mirror series marked invert are drawn below the axis
 * @returns {number[]}
 */
function thresholdLines({ value, invert, mirror: both }, mirror) {
    if (! mirror) {
        return [value];
    }

    return both ? [value, -value] : [invert ? -value : value];
}

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

/**
 * The series as the options draw them.
 * The image graphs draw areas solid, overlaid they need to show what is behind them.
 *
 * @param {GraphSeries} item
 * @param {DrawOptions} draw
 * @returns {GraphSeries}
 */
function restyled(item, { mirror, stacking }) {
    const filled = item.stacked || item.fillColor !== null;
    const changes = {
        ...(mirror ? {} : { invert: false }),
        ...(stacking === 'stack' ? { stacked: true, fillColor: item.fillColor ?? item.color } : {}),
        // the range too, its default color is see through
        ...(stacking === 'overlay' ? { stacked: false, fillColor: filled ? `${item.color}26` : null, maxFillColor: null, rangeColor: null } : {}),
    };

    return Object.keys(changes).length === 0 ? item : { ...item, ...changes };
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
    const shownSeries = graph.series.filter((item) => ! hidden.has(item.key) && ! item.legendOnly);
    const visible = shownSeries.map((item) => restyled(item, draw));
    const comparing = previous !== null;
    const layers = layersFor(visible, thresholds, draw, comparing);
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
    let earlier = {};

    const drawnData = (source) => {
        const series = Object.fromEntries(source.series.map((item) => [item.key, restyled(item, draw)]));

        return { series, totals: stackBySide(visible.filter((item) => item.stacked).map((item) => series[item.key])) };
    };

    // Trends and the earlier time reach the end of the view, past the data.
    const timesFor = (timestamps) => {
        const last = timestamps.at(-1);
        const step = timestamps.length > 1 ? last - timestamps.at(-2) : 0;
        if (! (draw.trend || comparing) || step <= 0 || shown.to <= last) {
            return timestamps;
        }

        const count = Math.min(Math.ceil((shown.to - last) / step), MAX_TREND_POINTS);

        return [...timestamps, ...Array.from({ length: count }, (unused, index) => last + step * (index + 1))];
    };

    const dataFor = (source, before) => {
        const now = drawnData(source);
        const then = before === null ? null : drawnData(before);
        const times = timesFor(source.timestamps);
        const empty = times.map(() => null);
        const lined = (values) => [...values, ...empty.slice(values.length)];

        earlier = before === null ? {} : Object.fromEntries(before.series.map((item) => [
            item.key, alignedTo(times, before.timestamps, item.avg),
        ]));

        return [times, ...layers.map((layer) => {
            if (layer.constant !== undefined) {
                return times.map(() => layer.constant);
            }
            const values = layer.values(now, then);
            if (values === null) {
                return empty;
            }
            if (layer.previous) {
                return withFirstInterval(alignedTo(times, before.timestamps, values));
            }
            if (layer.trend) {
                const line = trendOf(source.timestamps, values, shown);

                return line === null ? empty : times.map((time) => line(time));
            }

            return lined(withFirstInterval(values));
        })];
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
                paths: layer.trend || layer.constant !== undefined ? undefined : INTERVALS,
                points: { show: false },
            })),
        ],
        // the top layer of a range is filled down to the bottom one
        bands: layers.flatMap((layer, index) => (layer.range ? [{ series: [index + 1, index + 2], fill: layer.range }] : [])),
        cursor: {
            y: false,
            points: { size: (self, index) => (layers[index - 1]?.stroke && layers[index - 1].constant === undefined ? 7 : 0) },
            dataIdx: (self, seriesIndex, closest, time) => intervalIndex(self, closest, time),
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
                const index = idx === null || idx === undefined || left < 0 ? null : intervalIndex(self, idx, self.posToVal(left, 'x'));
                // past the data there are only trends and earlier values
                const past = index !== null && index >= current.graph.timestamps.length;
                onHover(index === null || (past && ! comparing)
                    ? null
                    : {
                        index,
                        time: self.data[0][index],
                        left: self.over.offsetLeft + left,
                        previous: Object.fromEntries(Object.entries(earlier).map(([key, values]) => [key, values[index]])),
                    });
            }],
        },
    }, dataFor(graph, previous), element);

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
            plot.setData(dataFor(next, before));
        },
        setView: (next) => {
            shown = next;
            // a trend is of what is shown
            if (draw.trend) {
                plot.setData(dataFor(current.graph, current.previous));
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
            style.disconnect();
            plot.destroy();
        },
    };
}
