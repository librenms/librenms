import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import test from 'node:test';
import {runInNewContext} from 'node:vm';
import {createPlotData} from '../../resources/js/graphs/plotData.js';
import {limitRange, panRange, resolveRange, zoomRange} from '../../resources/js/graphs/range.js';
import {formatValue, shownPoints, summarize, toCsv} from '../../resources/js/graphs/seriesData.js';

const timestamps = [100, 200, 300];
const view = { from: 100, to: 300 };

function series(key, values = {}) {
    return {
        key, label: key, unit: 'bps', color: '#abcdef', fillColor: null, drawScale: 1,
        stacked: false, invert: false, avg: [1, 2, 3], min: null, max: null, ...values,
    };
}

function graphComponent(viewPath, name) {
    const script = readFileSync(new URL('../../resources/views/' + viewPath, import.meta.url), 'utf8');
    let component;
    runInNewContext(script.trim().slice('<script>'.length, -'</script>'.length), {
        document: { addEventListener: (_event, initialize) => initialize() },
        window: {
            LibreNMS: { Graphs: { resolveRange, formatValue, shownPoints, summarize } },
            Alpine: {
                data: (registered, create) => {
                    if (registered === name) {
                        component = create({ range: view });
                    }
                },
            },
        },
    });

    return component;
}

function timeSeriesGraph() {
    const component = graphComponent('components/time-series-graph-script.blade.php', 'timeSeriesGraph');
    component.draw = () => {};

    return component;
}

test('only plotted series can be hidden or isolated through the legend', () => {
    const component = timeSeriesGraph();
    component.updateGraph({
        axis: { base: 1000 },
        timestamps,
        series: [series('in'), series('out'), series('total', { legendOnly: true, color: null })],
        thresholds: [],
    });

    const total = component.legend.find(({ key }) => key === 'total');
    assert.equal(total.toggleable, false);
    assert.equal(total.max, '3 bps');
    assert.equal(component.legend.find(({ key }) => key === 'in').toggleable, true);

    component.toggle('total', {});
    assert.deepEqual(Array.from(component.hidden), []);
    component.toggle('total', { ctrlKey: true });
    assert.deepEqual(Array.from(component.hidden), []);

    component.toggle('in', {});
    assert.deepEqual(Array.from(component.hidden), ['out']);
    component.toggle('in', {});
    assert.deepEqual(Array.from(component.hidden), []);

    component.toggle('out', { ctrlKey: true });
    assert.deepEqual(Array.from(component.hidden), ['out']);
});

test('a refresh clears hidden state when a series becomes legend-only', () => {
    const component = timeSeriesGraph();
    const graph = { axis: { base: 1000 }, timestamps, thresholds: [] };
    const plotted = [series('in'), series('out')];
    component.updateGraph({ ...graph, series: [...plotted, series('total')] });
    component.toggle('total', { ctrlKey: true });
    assert.deepEqual(Array.from(component.hidden), ['total']);

    component.updateGraph({ ...graph, series: [...plotted, series('total', { legendOnly: true, color: null })] });

    assert.deepEqual(Array.from(component.hidden), []);
});

test('the only plotted series stays visible and has a read-only legend entry', () => {
    const component = timeSeriesGraph();
    const graph = { axis: { base: 1000 }, timestamps, thresholds: [] };
    const incoming = series('in');
    const total = series('total', { legendOnly: true, color: null });
    component.updateGraph({ ...graph, series: [incoming, series('out'), total] });
    component.toggle('in', { ctrlKey: true });

    component.updateGraph({ ...graph, series: [incoming, total] });
    assert.deepEqual(Array.from(component.hidden), []);
    assert.equal(component.legend.find(({ key }) => key === 'in').toggleable, false);

    for (const event of [{}, { ctrlKey: true }, { metaKey: true }, { shiftKey: true }]) {
        component.toggle('in', event);
        assert.deepEqual(Array.from(component.hidden), []);
    }
});

test('the settings menu uses viewport positioning only when the actions wrap', () => {
    const component = graphComponent('graphs/page-script.blade.php', 'graphSettingsMenu');
    let actionsTop = 50;
    component.$refs = {
        graphPickerGroup: { getBoundingClientRect: () => ({ bottom: 85 }) },
        graphActions: { getBoundingClientRect: () => ({ top: actionsTop }) },
    };

    component.placeMenu();
    assert.deepEqual({ ...component.menuStyle }, {});

    actionsTop = 93;
    component.placeMenu();
    assert.equal(component.menuStyle.position, 'fixed');
    assert.equal(component.menuStyle.inset, '0');
    assert.equal(component.menuStyle.maxHeight, 'calc(100dvh - 2rem)');

    actionsTop = 50;
    component.placeMenu();
    assert.deepEqual({ ...component.menuStyle }, {});
});

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

test('trends are given in the units of each series for hovering, also past the data', () => {
    const graph = { timestamps, series: [series('in', { avg: [2, 4, 6], drawScale: 2 }), series('out', { invert: true, stacked: true })] };
    const draw = { trend: true, mirror: true };
    const plot = createPlotData(graph, { hidden: new Set(), draw, thresholds: [], comparing: false });
    const { columns, trends } = plot.dataFor(graph, null, { from: 0, to: 400 });

    assert.deepEqual(columns[0], [100, 200, 300, 400]);
    assert.deepEqual(trends, { in: [2, 4, 6, 8], out: [1, 2, 3, 4] });
    // drawn scaled and below the axis
    assert.deepEqual(columns.slice(-2), [[4, 8, 12, 16], [-1, -2, -3, -4]]);
});
