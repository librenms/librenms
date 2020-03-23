@extends('device.index')

@section('tab')
    <div class="panel panel-default">
        <div class="panel-heading">
            <form method="post" role="form" id="map" class="form-inline">
                @csrf
                <div style="position: relative">
                    <div class="form-group">
                        <label for="dtpickerfrom">From</label>
                        <input type="text" class="form-control" id="dtpickerfrom" name="dtpickerfrom" maxlength="16"
                               value="{{ $data['dtpickerfrom'] }}" data-date-format="YYYY-MM-DD HH:mm">
                    </div>
                    <div class="form-group">
                        <label for="dtpickerto">To</label>
                        <input type="text" class="form-control" id="dtpickerto" name="dtpickerto" maxlength=16
                               value="{{ $data['dtpickerto'] }} " data-date-format="YYYY-MM-DD HH:mm">
                    </div>
                    <input type="submit" class="btn btn-default" id="submit" value="Update">
                </div>
            </form>
        </div>
        <div class="panel-body">
            <div id="performance"></div>
        </div>
    </div>
@endsection

@section('javascript')
    <script src="{{ url('js/vis.min.js') }}"></script>
@endsection

@push('scripts')
    <script type="text/javascript">
        var container = document.getElementById('performance');
        var names = ['Loss', 'Min latency', 'Max latency', 'Avg latency'];
        var groups = new vis.DataSet();
        groups.add({
            id: 0,
            content: names[0],
            options: {
                drawPoints: {
                    style: 'circle'
                },
                shaded: {
                    orientation: 'bottom'
                }
            }
        });

        groups.add({
            id: 1,
            content: names[1],
            options: {
                drawPoints: {
                    style: 'circle'
                },
                shaded: {
                    orientation: 'bottom'
                }
            }
        });

        groups.add({
            id: 2,
            content: names[2],
            options: {
                drawPoints: {
                    style: 'circle'
                },
                shaded: {
                    orientation: 'bottom'
                }
            }
        });

        groups.add({
            id: 3,
            content: names[3],
            options: {
                drawPoints: {
                    style: 'circle'
                },
                shaded: {
                    orientation: 'bottom'
                }
            }
        });

        var items = @json($data['perfdata']);
        var dataset = new vis.DataSet(items);
        var options = {
            barChart: {width: 50, align: 'right'}, // align: left, center, right
            drawPoints: false,
            legend: {left: {position: "bottom-left"}},
            dataAxis: {
                icons: true,
                showMajorLabels: true,
                showMinorLabels: true,
            },
            zoomMin: 86400, //24hrs
            zoomMax: {{ $data['duration'] }},
            orientation: 'top'
        };
        var graph2d = new vis.Graph2d(container, dataset, groups, options);

        $(function () {
            $("#dtpickerfrom").datetimepicker({
                useCurrent: true,
                sideBySide: true,
                useStrict: false,
                icons: {
                    time: 'fa fa-clock-o',
                    date: 'fa fa-calendar',
                    up: 'fa fa-chevron-up',
                    down: 'fa fa-chevron-down',
                    previous: 'fa fa-chevron-left',
                    next: 'fa fa-chevron-right',
                    today: 'fa fa-calendar-check-o',
                    clear: 'fa fa-trash-o',
                    close: 'fa fa-close'
                }
            });
            $("#dtpickerto").datetimepicker({
                useCurrent: true,
                sideBySide: true,
                useStrict: false,
                icons: {
                    time: 'fa fa-clock-o',
                    date: 'fa fa-calendar',
                    up: 'fa fa-chevron-up',
                    down: 'fa fa-chevron-down',
                    previous: 'fa fa-chevron-left',
                    next: 'fa fa-chevron-right',
                    today: 'fa fa-calendar-check-o',
                    clear: 'fa fa-trash-o',
                    close: 'fa fa-close'
                }
            });
        });
    </script>
@endpush