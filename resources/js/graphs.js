import {limitRange, now, panRange, resolveRange, zoomRange} from './graphs/range.js';
import {formatValue, joinGraph, shownPoints, sliceGraph, summarize, toCsv} from './graphs/seriesData.js';

// Shared by the graph page and the time-series Blade component.
window.LibreNMS ??= {};
window.LibreNMS.Graphs = {
    limitRange, now, panRange, resolveRange, zoomRange,
    formatValue, joinGraph, shownPoints, sliceGraph, summarize, toCsv,
    loadChart: () => import('./timeSeriesChart.js'),
};
