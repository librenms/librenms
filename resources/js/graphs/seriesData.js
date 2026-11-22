import LibreNMSNumber from '../number.js';

/**
 * @typedef {object} GraphData App\Data\Graphing\GraphData
 * @property {number[]} timestamps unix time each point's interval ends
 * @property {{label: string, min: ?number, max: ?number, base: number, margin: number}} axis min and max are limits when set,
 *     without them a margin above zero asks to fit the axis to the data
 * @property {GraphSeries[]} series
 * @property {Threshold[]} thresholds reference lines, such as alert limits, shown ones are drawn until the viewer hides them
 * @property {number[]} percentiles a legend column of each for every series, series with a percentileColor also draw them
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
 * @property {?string} percentileColor the percentile lines of the graph are drawn for this series in this color, none when null
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
 * The nearest rank percentile of sorted values, null without values
 *
 * @param {number[]} sorted lowest first
 * @param {number} percentile 0 to 100
 * @returns {?number}
 */
export function percentileOf(sorted, percentile) {
    return sorted.length === 0 ? null : sorted[Math.max(0, Math.ceil(sorted.length * percentile / 100) - 1)];
}

/**
 * The averages of points start to end (exclusive) that have data, lowest first
 *
 * @param {GraphSeries} series
 * @returns {number[]}
 */
export function sortedAverages(series, start, end) {
    return series.avg.slice(start, end).filter((value) => value !== null).sort((a, b) => a - b);
}

/**
 * The percentile of a legend column, such as 95 for p95, null for a column of another value
 *
 * @param {string} column
 * @returns {?number}
 */
export function columnPercentile(column) {
    return /^p\d/.test(column) ? Number(column.slice(1)) : null;
}

/**
 * The legend columns the viewer can show, in order: the columns of every graph, then a column of each percentile it offers
 * or the graph has, lowest first.
 *
 * @param {string[]} columns of every graph, such as now and p95
 * @param {number[]} percentiles of the graph
 * @returns {string[]}
 */
export function columnChoices(columns, percentiles) {
    const offered = columns.map(columnPercentile).filter((percentile) => percentile !== null);
    const all = [...new Set([...offered, ...percentiles])].sort((a, b) => a - b);

    return [...columns.filter((column) => columnPercentile(column) === null), ...all.map((percentile) => `p${percentile}`)];
}

/**
 * Whether a legend column is shown: a percentile of the graph unless the viewer hid it, any other when the viewer chose it
 *
 * @param {string} column
 * @param {string[]} chosen columns the viewer chose
 * @param {number[]} percentiles of the graph
 * @param {number[]} hidden percentiles of graphs the viewer hid
 */
export function columnShown(column, chosen, percentiles, hidden) {
    const percentile = columnPercentile(column);

    return percentile !== null && percentiles.includes(percentile) ? ! hidden.includes(percentile) : chosen.includes(column);
}

/**
 * Legend values of points start to end (exclusive). Min and max use those of each point, keeping consolidated peaks.
 * Percentiles are of the averages, like the image graphs, keyed by their column, such as p95.
 *
 * @param {GraphSeries} series
 * @param {number[]} percentiles
 */
export function summarize(series, start, end, percentiles = []) {
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

    values.sort((a, b) => a - b);
    const columns = Object.fromEntries(percentiles.map((percentile) => [`p${percentile}`, percentileOf(values, percentile)]));

    if (values.length === 0) {
        return { now: null, min: null, max: null, avg: null, ...columns };
    }

    return { now, min, max, avg: sum / values.length, ...columns };
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
