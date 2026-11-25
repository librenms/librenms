{{-- $stats from AlertLog::severityStats() --}}
<x-panel title="{{ __('Alert Stats') }}">
    <div id="alert-stats-chart" style="margin: 0 auto; width: 99%; margin-bottom: -120px;"></div>
</x-panel>

@push('scripts')
    <script src="{{ asset('js/vis-network.min.js') }}"></script>
    <script src="{{ asset('js/vis-data.min.js') }}"></script>
    <script src="{{ asset('js/vis-timeline-graph2d.min.js') }}"></script>
    <script>
        (function () {
            var stats = @js($stats);
            var groups = new vis.DataSet(stats.groups.map(group => ({id: group, content: group})));

            new vis.Graph2d(document.getElementById('alert-stats-chart'), stats.items, groups, {
                style: 'bar',
                barChart: {width: 50, align: 'right', sideBySide: true},
                drawPoints: false,
                legend: {left: {position: "bottom-left"}},
                dataAxis: {
                    icons: true,
                    showMajorLabels: true,
                    showMinorLabels: true,
                },
                zoomMin: 86400,
                zoomMax: stats.zoom_max,
                orientation: 'top'
            });
        })();
    </script>
@endpush
