// Draws a graph with uPlot. This is the only module that knows about uPlot,
// it is loaded on demand so pages without a graph do not pay for it.
// See GraphDataResource for the shape of a graph and its series.

import uPlot from 'uplot';
import 'uplot/dist/uPlot.min.css';
import LibreNMSDate from '../datetime.js';
import {drawnValues, formatValue, isConsolidated, stack} from './seriesMath.js';

const GRID = 'rgba(128, 128, 128, 0.2)';
const MIN_ZOOM_PIXELS = 5;
const WHEEL_ZOOM = 0.8;
// a finger that moved less than this tapped, two taps within the time are a double tap
const TAP_PIXELS = 10;
const DOUBLE_TAP_MS = 350;
const BINARY_INCREMENTS = Array.from({ length: 70 }, (unused, power) => 2 ** power);

// 24 hour times and day before month, so the axis reads the same for everyone
// [tick increment, default, year, month, day, hour, minute, second rollover formats, concat mode]
const TIME_AXIS = [
    [3600 * 24 * 365, '{YYYY}', null, null, null, null, null, null, 1],
    [3600 * 24 * 28, '{MMM}', '\n{YYYY}', null, null, null, null, null, 1],
    [3600 * 24, '{D} {MMM}', '\n{YYYY}', null, null, null, null, null, 1],
    [3600, '{HH}:{mm}', '\n{D} {MMM} {YYYY}', null, '\n{D} {MMM}', null, null, null, 1],
    [60, '{HH}:{mm}', '\n{D} {MMM} {YYYY}', null, '\n{D} {MMM}', null, null, null, 1],
    [1, '{HH}:{mm}:{ss}', '\n{D} {MMM} {YYYY}', null, '\n{D} {MMM}', null, null, null, 1],
];

/**
 * What to draw for the series, in drawing order: the ranges up to the max of each point at the back,
 * then the fills, then the lines, so a fill never hides the line of another series.
 * Stacked series are solid areas, drawn from the top of the stack down.
 * The layers only depend on which series there are, so new data for the same series can replace the old.
 */
function layersFor(series) {
    const stacked = series.filter((item) => item.stacked);
    const lines = series.filter((item) => ! item.stacked);
    const average = (item) => (data) => drawnValues(data[item.key], data[item.key].avg);

    return [
        ...lines.map((item) => ({
            fill: item.max_fill_color ?? `${item.color}26`,
            // nothing to show while every point is a single stored value
            values: (data) => (isConsolidated(data[item.key]) ? drawnValues(data[item.key], data[item.key].max) : null),
        })),
        ...lines.filter((item) => item.fill_color).map((item) => ({ fill: item.fill_color, values: average(item) })),
        ...stacked.map((item) => ({
            fill: item.fill_color,
            stroke: item.color,
            values: (data, totals) => totals[item.key],
        })).reverse(),
        ...lines.map((item) => ({ stroke: item.color, values: average(item) })),
    ];
}

/**
 * The points with no data on either side. A line needs two points, so these are drawn as dots:
 * a single successful poll between failed ones would not show otherwise.
 */
function isolatedPoints(values) {
    return values.flatMap((value, index) => (
        value !== null && (values[index - 1] ?? null) === null && (values[index + 1] ?? null) === null ? [index] : []
    ));
}

/**
 * Keep the limits the graph asks for in view, and leave some room where the data sets the limit.
 * Like the image graphs, the axis starts at zero unless the graph sets another limit.
 */
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

/**
 * The unit shown on the value axis, when all series have the same one
 */
function commonUnit(series) {
    const units = new Set(series.map((item) => item.unit));

    return units.size === 1 ? series[0].unit : '';
}

/**
 * Draw a graph in an element. The chart shows the view it is given, which may be a part of the data
 * or reach past it: the page moves the view right away and loads the data for it afterwards.
 *
 * @param {HTMLElement} element sized by the page, the chart fills it
 * @param {object} graph the response of the graph data endpoint
 * @param {object} options
 * @param {{from: number, to: number}} options.view the time range to show
 * @param {Set<string>} options.hidden keys of the series to leave out
 * @param {boolean} options.compact leave out the label of the value axis, where every pixel of width counts
 * @param {(view: {from: number, to: number}, moving: boolean) => void} options.onView the user zoomed or panned,
 *   moving when more moves are likely to follow right away. The page answers with setView.
 * @param {() => void} options.onReset the user asked for the original view
 * @param {(position: {index: number, left: number, top: number}|null) => void} options.onHover the cursor is at a point, or left
 */
export default function createChart(element, graph, { view, hidden, compact, onView, onReset, onHover }) {
    const label = compact ? '' : graph.axis.label;
    const visible = graph.series.filter((item) => ! hidden.has(item.key));
    const layers = layersFor(visible);
    const unit = commonUnit(graph.series);
    const zone = LibreNMSDate.zone();
    const text = getComputedStyle(element).color;
    const size = () => ({ width: element.clientWidth, height: element.clientHeight });
    let shown = view;

    // The data of the layers, the values of a layer without anything to draw are all missing
    const dataFor = (source) => {
        const byKey = Object.fromEntries(source.series.map((item) => [item.key, item]));
        const stacked = visible.filter((item) => item.stacked).map((item) => byKey[item.key]);
        const totals = stack(stacked.map((item) => drawnValues(item, item.avg)));
        const totalsByKey = Object.fromEntries(stacked.map((item, index) => [item.key, totals[index]]));
        const empty = source.timestamps.map(() => null);

        return [source.timestamps, ...layers.map((layer) => {
            const values = layer.values(byKey, totalsByKey) ?? empty;
            layer.isolated = isolatedPoints(values);

            return values;
        })];
    };

    // Dragging with shift held moves the view instead of selecting a range to zoom to
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
            plot.over.style.cursor = '';
        };

        event.preventDefault();
        plot.over.style.cursor = 'grabbing';
        document.addEventListener('mousemove', move);
        document.addEventListener('mouseup', stop);

        return null;
    };

    // Scrolling sideways (or with shift) pans, scrolling with ctrl zooms around the cursor.
    // Plain scrolling is left alone so the page still scrolls with the cursor over the graph.
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

    // Touch: one finger pans, two fingers zoom around the point between them, two quick taps reset.
    // Scrolling the page up and down is left to the browser (touch-action), a tap reaches uPlot as a click.
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
            // the time between the fingers stays between them while they spread or close
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

        // a finger that stays carries on from here, it is not a tap anymore
        startGesture(event, true);
    };

    const plot = new uPlot({
        ...size(),
        tzDate: (timestamp) => uPlot.tzDate(new Date(timestamp * 1000), zone),
        legend: { show: false },
        scales: {
            // the view is set by the page, not by the data: it may be a part of the data or reach past it
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
                // inverted series are drawn below the axis, their values are not negative
                values: (self, ticks) => ticks.map((value) => formatValue(Math.abs(value), unit, graph.axis.base)),
                size: (self, values) => Math.max(...(values ?? ['']).map((value) => value.length)) * 7 + (label ? 40 : 16),
                // binary units get ticks on binary amounts: 4 GiB, not 3.73 GiB
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
                points: layer.stroke
                    ? { size: 4, fill: layer.stroke, filter: (self, index, show) => (show ? null : layer.isolated) }
                    : { show: false },
            })),
        ],
        cursor: {
            y: false,
            points: { size: (self, index) => (layers[index - 1]?.stroke ? 7 : 0) },
            // a selected range is shown through the view like every other move, not by uPlot itself
            drag: { x: true, y: false, setScale: false, dist: MIN_ZOOM_PIXELS },
            bind: {
                mousedown: (self, target, handler) => (event) => (event.shiftKey ? startPan(event) : handler(event)),
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
                const { idx, left, top } = self.cursor;
                onHover(idx === null || idx === undefined || left < 0
                    ? null
                    : { index: idx, left: self.over.offsetLeft + left, top: self.over.offsetTop + top });
            }],
        },
    }, dataFor(graph), element);

    plot.over.style.touchAction = 'pan-y';
    plot.over.addEventListener('wheel', onWheel, { passive: false });
    plot.over.addEventListener('touchstart', (event) => startGesture(event, false), { passive: true });
    plot.over.addEventListener('touchmove', onTouchMove, { passive: true });
    plot.over.addEventListener('touchend', onTouchEnd, { passive: true });
    plot.over.addEventListener('touchcancel', () => {
        gesture = null;
    }, { passive: true });

    return {
        update: (next) => plot.setData(dataFor(next)),
        setView: (next) => {
            shown = next;
            plot.setScale('x', { min: shown.from, max: shown.to });
        },
        resize: () => plot.setSize(size()),
        destroy: () => plot.destroy(),
    };
}
