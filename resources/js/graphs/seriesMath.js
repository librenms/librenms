// Calculations on the series of a graph, see GraphDataResource for the shape of a series

import LibreNMSNumber from '../number.js';

/**
 * Format a value of a series for display, for example 1500000 bps as 1.5 Mbps
 */
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
 * The legend values of a series: the latest value, the lowest, the highest and the average.
 * Lowest and highest come from the min and max of each point, so peaks survive consolidation.
 *
 * @param {object} series
 * @param {number} start the first point to include
 * @param {number} end the point after the last one to include
 */
export function summarize(series, start = 0, end = series.avg.length) {
    const known = (values) => values.slice(start, end).filter((value) => value !== null);
    const avg = known(series.avg);

    if (avg.length === 0) {
        return { now: null, min: null, max: null, avg: null };
    }

    return {
        now: avg[avg.length - 1],
        min: Math.min(...known(series.min)),
        max: Math.max(...known(series.max)),
        avg: avg.reduce((sum, value) => sum + value, 0) / avg.length,
    };
}

/**
 * Whether points cover more than one stored value, so the min and max differ from the average
 */
export function isConsolidated(series) {
    return series.avg.some((value, index) => value !== null && series.max[index] !== null && series.max[index] !== value);
}

/**
 * The value a series is drawn at: scaled for the drawing and below the axis when inverted
 */
export function drawnValues(series, values) {
    const factor = series.draw_scale * (series.invert ? -1 : 1);

    return factor === 1 ? values : values.map((value) => (value === null ? null : value * factor));
}

/**
 * Add each list of values on top of the ones before it, a gap in every series stays a gap
 */
export function stack(lists) {
    const totals = [];

    return lists.map((values) => values.map((value, index) => {
        if (value !== null) {
            totals[index] = (totals[index] ?? 0) + value;
        }

        return totals[index] ?? null;
    }));
}
