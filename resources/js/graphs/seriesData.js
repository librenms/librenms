import LibreNMSNumber from '../number.js';

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
export function firstAfter(timestamps, time) {
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

// the values of the series at the points start to end (exclusive), a min or max that is the average stays null
function slicedSeries(series, start, end) {
    const slice = (values) => (values === null ? null : values.slice(start, end));

    return { ...series, avg: slice(series.avg), min: slice(series.min), max: slice(series.max) };
}

/**
 * The points of next replace those of the graph from its first time to its last, the others are kept.
 * Both have the same series.
 *
 * @param {GraphData} graph
 * @param {GraphData} next
 * @returns {GraphData}
 */
export function joinGraph(graph, next) {
    if (next.timestamps.length === 0) {
        return graph;
    }

    const start = firstAfter(graph.timestamps, next.timestamps[0] - 1);
    const end = firstAfter(graph.timestamps, next.timestamps.at(-1));
    const join = (values, added) => [...values.slice(0, start), ...added, ...values.slice(end)];
    const series = next.series.map((item) => {
        const old = graph.series.find((other) => other.key === item.key);
        const bound = (name) => (old[name] === null && item[name] === null ? null : join(old[name] ?? old.avg, item[name] ?? item.avg));

        return { ...item, avg: join(old.avg, item.avg), min: bound('min'), max: bound('max') };
    });

    return { ...next, timestamps: join(graph.timestamps, next.timestamps), series };
}

/**
 * The points of the graph in a time
 *
 * @param {GraphData} graph
 * @param {View} view
 * @returns {GraphData}
 */
export function sliceGraph(graph, { from, to }) {
    const [start, end] = shownPoints(graph.timestamps, { from, to });

    return { ...graph, timestamps: graph.timestamps.slice(start, end), series: graph.series.map((item) => slicedSeries(item, start, end)) };
}
