@extends('layouts.librenmsv1')

@section('title', __('Outages'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default panel-condensed">
                <div class="panel-heading">
                    <strong>Outages</strong>
                </div>

                <div class="table-responsive">
                    <table id="outages" class="table table-hover table-condensed table-striped"
                           data-url="{{ route('table.outages') }}">
                        <thead>
                        <tr>
                            <th data-column-id="status" data-sortable="false"></th>
                            <th data-column-id="going_down" data-order="desc">Start</th>
                            <th data-column-id="up_again">End</th>
                            <th data-column-id="device_id">Hostname</th>
                            <th data-column-id="duration" data-sortable="false">Duration</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var outages_grid = $("#outages").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        templates: {
            search: ""
        },
        post: function ()
        {
            return {
                device: $('#device').val(),
                to: $("#dtpickerto").val(),
                from: $("#dtpickerfrom").val(),
            };
        },
    });

    $('.actionBar').append(
        '<div class="pull-left">' +
        '<form method="get" action="{{ route('outages') }}" class="form-inline" role="form" id="result_form">' +
        '<div class="form-group">' +
        @if($show_device_list)
        '<select name="device" id="device" class="form-control">' +
        '<option value="">All Devices</option>' +
            @if($device)
            '<option value="{{ $device->device_id }}" selected>{{ $device->displayName() }}</option>' +
            @endif
        '</select>' +
        @else
        '&nbsp;&nbsp;<input type="hidden" name="device" id="device" value="{{ $device?->device_id }}">' +
        @endif
        '</div>' +
        '&nbsp;&nbsp;<div class="form-group">' +
        '<input name="from" type="text" class="form-control" id="dtpickerfrom" maxlength="16" value="{{ $from }}" placeholder="From" data-date-format="YYYY-MM-DD HH:mm">' +
        '</div>' +
        '<div class="form-group">' +
        '&nbsp;&nbsp;<input name="to" type="text" class="form-control" id="dtpickerto" maxlength="16" value="{{ $to }}" placeholder="To" data-date-format="YYYY-MM-DD HH:mm">' +
        '</div>' +
        '&nbsp;&nbsp;<button type="submit" class="btn btn-default">Filter</button>' +
        '</form>' +
        '</div>'
    );

    $(function () {
        $("#dtpickerfrom").datetimepicker({
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
            },
            defaultDate: '{{ $default_start_date }}'
        });
        $("#dtpickerfrom").on("dp.change", function (e) {
            $("#dtpickerto").data("DateTimePicker").minDate(e.date);
        });
        $("#dtpickerto").datetimepicker({
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
            },
            defaultDate: '{{ $default_end_date }}'
        });
        $("#dtpickerto").on("dp.change", function (e) {
            $("#dtpickerfrom").data("DateTimePicker").maxDate(e.date);
        });
        if ($("#dtpickerfrom").val() != "") {
            $("#dtpickerto").data("DateTimePicker").minDate($("#dtpickerfrom").val());
        }
        if ($("#dtpickerto").val() != "") {
            $("#dtpickerfrom").data("DateTimePicker").maxDate($("#dtpickerto").val());
        } else {
            $("#dtpickerto").data("DateTimePicker").maxDate('{{ $default_end_date }}');
        }
    });

    @if($show_device_list)
    init_select2("#device", "device", {}, {{ \Illuminate\Support\Js::from($selected_device) }} , "All Devices");
    $('#device').on('change', () => outages_grid.bootgrid('reload'));
    @endif
</script>
@endsection
