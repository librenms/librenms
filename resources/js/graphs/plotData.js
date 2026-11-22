import {firstAfter, percentileOf, shownPoints, sortedAverages} from './seriesData.js';

/** @typedef {import('./seriesData.js').GraphSeries} GraphSeries */
/** @typedef {import('./seriesData.js').Threshold} Threshold */

/**
 * @typedef {object} DrawOptions App\Data\Graphing\GraphViewOptions
 * @property {boolean} range shade between the min and max of each line
 * @property {boolean} mirror inverted series below the axis, else above it with the rest
 * @property {'graph'|'stack'|'overlay'} stacking as the graph defines, all on a side of the axis, or none
 * @property {'graph'|'fit'|'log'} scale as the graph defines, fitted to the data, or logarithmic
 * @property {'stepped'|'linear'|'smooth'} line_style how adjacent samples are connected
 * @property {boolean} trend a straight line fitted to what is shown of each series
 * @property {boolean} percentile_lines the percentiles of the graph drawn for the series that draw them
 * @property {number[]} hidden_percentiles percentiles of the graph left out of the legend, and not drawn
 */

const PREVIOUS_DASH = [6, 4];
const TREND_DASH = [2, 4];
const THRESHOLD_DASH = [8, 4];
const PERCENTILE_DASH = [3, 3];
const MAX_TREND_POINTS = 1000; // past the data, to the end of the view
// what drawing multiplies the values of a series by
function drawFactor(series) {
    return series.drawScale * (series.invert ? -1 : 1);
}

function drawnValues(series, values) {
    const factor = drawFactor(series);

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

/**
 * The values of each point of the other times, null where they have none.
 * A point is the one whose interval covers the time, from the point before it, the first as long as the second.
 */
function alignedTo(timestamps, others, values) {
    const interval = (index) => (index > 0 ? others[index] - others[index - 1] : (others[1] ?? Infinity) - others[0]);

    return timestamps.map((time) => {
        const index = firstAfter(others, time - 1);

        return index < others.length && others[index] - time < interval(index) ? values[index] : null;
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

// Back to front: min to max ranges, fills, stacked areas from the top down, lines, percentiles, thresholds, earlier lines, trends.
// Layers depend only on which series there are and what is drawn, so new data can replace the old.
// A range is two layers, its top and its bottom, the fill is between them.
// Values get the drawn data {series, totals} of now and of the previous time, null without it.
// A constant layer has the same value at every time. A trend layer has the key of its series.
// A percentile layer has the key of its series and the percentile, drawn level at the percentile of what is shown.
function layersFor(series, percentileSeries, percentiles, thresholds, { range: showRange, trend, mirror }, comparing) {
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
        if (values.max === null && values.min === null) {
            return null;
        }

        const bound = top !== values.invert ? values.max : values.min;

        return drawnValues(values, bound ?? values.avg);
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
        ...percentileSeries.flatMap((item) => percentiles.map((percentile) => ({
            stroke: item.percentileColor, dash: PERCENTILE_DASH, percentile: { key: item.key, percentile }, values: () => null,
        }))),
        ...thresholds.flatMap((threshold) => thresholdLines(threshold, mirror).map((value) => ({
            stroke: threshold.color, dash: THRESHOLD_DASH, constant: value, values: () => null,
        }))),
        ...(comparing ? series : []).map((item) => ({
            stroke: `${item.color}b3`,
            dash: PREVIOUS_DASH,
            previous: true,
            values: (now, previous) => (previous === null ? null : drawn(item, previous)),
        })),
        ...(trend ? series : []).map((item) => ({ stroke: item.color, dash: TREND_DASH, trend: item.key, values: (now) => drawn(item, now) })),
    ];
}

/**
 * Where the line of a threshold is drawn, as Threshold::drawnValues() for the image graphs
 *
 * @param {Threshold} threshold
 * @param {boolean} mirror series marked invert are drawn below the axis
 * @returns {number[]}
 */
export function thresholdLines({ value, invert, mirror: both }, mirror) {
    if (! mirror) {
        return [value];
    }

    return both ? [value, -value] : [invert ? -value : value];
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
    const invert = mirror && item.invert;
    if (stacking === 'stack') {
        return { ...item, invert, stacked: true, fillColor: item.fillColor ?? item.color };
    }
    if (stacking === 'overlay') {
        const filled = item.stacked || item.fillColor !== null;
        return { ...item, invert, stacked: false, fillColor: filled ? `${item.color}26` : null, maxFillColor: null, rangeColor: null };
    }

    return invert === item.invert ? item : { ...item, invert };
}

// uPlot consumes one column per layer. Reuse the layer order when only the samples change.
export function createPlotData(graph, { hidden, draw, thresholds, comparing }) {
    const shownSeries = graph.series.filter((item) => ! hidden.has(item.key) && ! item.legendOnly);
    const visible = shownSeries.map((item) => restyled(item, draw));
    // legend only series, such as totals, draw their percentiles too, those of hidden legend columns are left out
    const percentileSeries = draw.percentile_lines === false ? [] : graph.series
        .filter((item) => item.percentileColor && ! hidden.has(item.key))
        .map((item) => restyled(item, draw));
    const percentiles = graph.percentiles.filter((percentile) => ! draw.hidden_percentiles.includes(percentile));
    const layers = layersFor(visible, percentileSeries, percentiles, thresholds, draw, comparing);
    const stackedKeys = visible.filter((item) => item.stacked).map((item) => item.key);

    const drawnData = (source) => {
        const series = Object.fromEntries(source.series.map((item) => [item.key, restyled(item, draw)]));

        const totals = {};
        const sides = { above: [], below: [] };

        for (const key of stackedKeys) {
            const item = series[key];
            if (! item) {
                continue; // a series may be absent from the comparison period
            }

            const side = item.invert ? sides.below : sides.above;
            totals[key] = drawnValues(item, item.avg).map((value, index) => {
                if (value !== null) {
                    side[index] = (side[index] ?? 0) + value;
                }

                return side[index] ?? null;
            });
        }

        return { series, totals };
    };

    // Trends and the earlier time reach the end of the view, past the data.
    const timesFor = (timestamps, shown) => {
        const last = timestamps.at(-1);
        const step = timestamps.length > 1 ? last - timestamps.at(-2) : 0;
        if (! (draw.trend || comparing) || step <= 0 || shown.to <= last) {
            return timestamps;
        }

        const count = Math.min(Math.ceil((shown.to - last) / step), MAX_TREND_POINTS);

        return [...timestamps, ...Array.from({ length: count }, (unused, index) => last + step * (index + 1))];
    };

    function dataFor(source, before, shown) {
        const now = drawnData(source);
        const then = before === null ? null : drawnData(before);
        const times = timesFor(source.timestamps, shown);
        const empty = times.map(() => null);
        const lined = (values) => [...values, ...empty.slice(values.length)];

        const earlier = before === null ? {} : Object.fromEntries(before.series.map((item) => [
            item.key, alignedTo(times, before.timestamps, item.avg),
        ]));

        // in the units of each series, a stacked one its own share of the total its line is drawn at
        const trends = Object.fromEntries(layers.filter((layer) => layer.trend !== undefined).map((layer) => {
            const line = trendOf(source.timestamps, now.series[layer.trend]?.avg ?? [], shown);

            return [layer.trend, line === null ? empty : times.map((time) => line(time))];
        }));

        const [start, end] = shownPoints(source.timestamps, shown);
        const columns = [times, ...layers.map((layer) => {
            if (layer.constant !== undefined) {
                return times.map(() => layer.constant);
            }
            if (layer.percentile !== undefined) {
                const item = now.series[layer.percentile.key];
                const value = item === undefined ? null : percentileOf(sortedAverages(item, start, end), layer.percentile.percentile);

                return value === null ? empty : times.map(() => value * drawFactor(item));
            }
            const values = layer.values(now, then);
            if (values === null) {
                return empty;
            }
            if (layer.trend !== undefined) {
                const line = trendOf(source.timestamps, values, shown);

                return line === null ? empty : times.map((time) => line(time));
            }

            const samples = layer.previous ? alignedTo(times, before.timestamps, values) : lined(values);
            return (draw.line_style ?? 'stepped') === 'stepped' ? withFirstInterval(samples) : samples;
        })];

        return { columns, earlier, trends };
    }

    return { layers, dataFor };
}
