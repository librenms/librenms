@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-panel title="{{ __('Device Alerts') }}">
        <div style="margin: 0 auto; width: 99%;">
            <script src="{{ asset('js/vis-network.min.js') }}"></script>
            <script src="{{ asset('js/vis-data.min.js') }}"></script>
            <script src="{{ asset('js/vis-timeline-graph2d.min.js') }}"></script>
            <div id="visualization" style="margin-bottom: -120px;"></div>
            <script type="text/javascript">
                var container = document.getElementById('visualization');
                var groups = new vis.DataSet();
                @foreach($data['groups'] as $group)
                    groups.add({id: @json($group), content: @json($group)});
                @endforeach

                var items = @json($data['items']);
                var dataset = new vis.DataSet(items);
                var options = {
                    style: 'bar',
                    barChart: { width: 50, align: 'right', sideBySide: true },
                    drawPoints: false,
                    legend: { left: { position: "bottom-left" } },
                    dataAxis: {
                        icons: true,
                        showMajorLabels: true,
                        showMinorLabels: true,
                    },
                    zoomMin: 86400,
                    zoomMax: {{ $data['zoom_max'] }},
                    orientation: 'top'
                };
                var graph2d = new vis.Graph2d(container, items, groups, options);
            </script>
        </div>
    </x-panel>
</x-device.page>
@endsection
