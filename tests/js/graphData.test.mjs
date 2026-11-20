import assert from 'node:assert/strict';
import test from 'node:test';
import { createPlotData } from '../../resources/js/graphs/plotData.js';
import { limitRange, panRange, resolveRange, zoomRange } from '../../resources/js/graphs/range.js';
import { shownPoints, summarize, toCsv } from '../../resources/js/graphs/seriesData.js';

const timestamps = [100, 200, 300];
const view = { from: 100, to: 300 };

function series(key, values = {}) {
    return {
        key, label: key, unit: 'bps', color: '#abcdef', fillColor: null, drawScale: 1,
        stacked: false, invert: false, avg: [1, 2, 3], min: null, max: null, ...values,
    };
}

test('relative and fixed ranges use the same toolbar steps and stay centered at the zoom limit', () => {
    const relative = resolveRange({ last: 200 }, 300);
    assert.deepEqual(relative, view);
    assert.deepEqual(panRange(relative, -1), { from: 50, to: 250 });
    assert.deepEqual(zoomRange(view, -1), { from: 0, to: 400 });
    assert.deepEqual(limitRange(zoomRange(view, 1)), { from: 140, to: 260 });
    assert.deepEqual(limitRange(zoomRange(view, -1), 300), { from: 50, to: 350 });
});

test('legend and CSV include the intervals overlapping the view and preserve gaps', () => {
    const incoming = series('in', { avg: [10, null, 30], min: [2, null, 5] });
    const graph = { timestamps, series: [incoming, series('out')] };
    const partial = { from: 100, to: 250 };
    const [start, end] = shownPoints(timestamps, partial);

    assert.deepEqual(summarize(incoming, start, end), { now: 30, min: 5, max: 30, avg: 30, p95: 30 });
    assert.equal(toCsv(graph, partial, new Set(['out'])),
        '"time","in (bps)"\n1970-01-01T00:03:20.000Z,\n1970-01-01T00:05:00.000Z,30\n');
});

test('a range uses the average for the omitted maximum, including on a mirrored series', () => {
    for (const invert of [true, false]) {
        const graph = { timestamps, series: [series('in', { invert, min: [0.5, 1, 2] })] };
        const draw = { range: true, mirror: true };
        const plot = createPlotData(graph, { hidden: new Set(), draw, thresholds: [], comparing: false });
        const { columns } = plot.dataFor(graph, null, view);

        assert.deepEqual(columns[1], invert ? [-0.5, -1, -2] : [1, 2, 3]);
        assert.deepEqual(columns[2], invert ? [-1, -2, -3] : [0.5, 1, 2]);
    }
});

test('a stacked comparison tolerates series missing from the earlier period', () => {
    const graph = { timestamps, series: [series('in'), series('out')] };
    const previous = { timestamps, series: [series('out', { avg: [4, 5, 6] })] };
    const draw = { stacking: 'stack', mirror: true };
    const plot = createPlotData(graph, { hidden: new Set(), draw, thresholds: [], comparing: true });
    const { columns, earlier } = plot.dataFor(graph, previous, view);
    const comparison = columns.slice(-2);

    assert.deepEqual(comparison, [[null, null, null], [4, 5, 6]]);
    assert.deepEqual(earlier, { out: [4, 5, 6] });
});

test('only stepped lines extend the first sample back to the start of its interval', () => {
    const graph = { timestamps, series: [series('in', { avg: [null, 2, 3] })] };
    for (const [line_style, expected] of [['stepped', [2, 2, 3]], ['linear', [null, 2, 3]], ['smooth', [null, 2, 3]]]) {
        const plot = createPlotData(graph, { hidden: new Set(), draw: { line_style }, thresholds: [], comparing: true });
        const { columns } = plot.dataFor(graph, graph, view);

        assert.deepEqual(columns, [timestamps, expected, expected]);
        assert.deepEqual(graph.series[0].avg, [null, 2, 3]);
    }
});
