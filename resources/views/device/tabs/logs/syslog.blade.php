@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" subtitle="{{ __('Syslog') }}">
        <x-device.log-tabs :device="$device" tab="syslog"/>

        <x-panel title="{{ __('Syslog') }}">
            <div class="table-responsive">
                <table id="syslog" class="table table-hover table-condensed table-striped">
                    <thead>
                    <tr>
                        <th data-column-id="label"></th>
                        <th data-column-id="timestamp" data-order="desc">Timestamp</th>
                        <th data-column-id="level">Level</th>
                        <th data-column-id="device_id">Hostname</th>
                        <th data-column-id="program">Program</th>
                        <th data-column-id="msg">Message</th>
                        <th data-column-id="priority">Priority</th>
                    </tr>
                    </thead>
                </table>
            </div>

            @if(! $filter_device)
                <input type="hidden" name="device" id="device" value="{{ $device->device_id }}">
            @endif
        </x-panel>
    </x-device.page>
@endsection

@section('scripts')
    <script>
        var syslog_grid = $("#syslog").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            post: function ()
            {
                return {
                    device: {{ $device->device_id }},
                    program: @json($program),
                    priority: @json($priority),
                    to: @json($to),
                    from: @json($from),
                };
            },
            url: "{{ route('table.syslog') }}"
        });

        $('.actionBar').append(
            '<div class="pull-left">' +
            '<form method="GET" action="" class="form-inline" role="form" id="result_form">' +
            '<div class="form-group">' +
            @if($filter_device)
                '<select name="device" id="device" class="form-control">' +
                '<option value="">All Devices&nbsp;&nbsp;</option>' +
                @if($device)
                    '<option value={{ $device->device_id }}>' + @json($device->displayName()) + '</option>' +
                @endif
                '</select>' +
            @endif
            '</div>' +
            '&nbsp;&nbsp;<div class="form-group">' +
            '<select name="program" id="program" class="form-control">' +
                '<option value="">All Programs&nbsp;&nbsp;</option>' +
            @if($program)
                '<option value=\"' + @json($program) + '\">' + @json($program) + '</option>' +";
            @endif
            '</select>' +
            '</div>' +
            '&nbsp;&nbsp;<div class="form-group">' +
            '<select name="priority" id="priority" class="form-control">' +
                '<option value="">All Priorities</option>' +
            @if($priority)
                '<option value=\"' + @json($priority) + '\">' + @json($priority) + '</option>' +";
            @endif
            '</select>' +
            '</div>' +
            '&nbsp;&nbsp;<div class="form-group">' +
            '<input name="from" type="text" class="form-control" id="dtpickerfrom" maxlength="16" value="' + @json($from) + '" placeholder="From" data-date-format="YYYY-MM-DD HH:mm">' +
            '</div>' +
            '<div class="form-group">' +
            '&nbsp;&nbsp;<input name="to" type="text" class="form-control" id="dtpickerto" maxlength="16" value="' + @json($to) + '" placeholder="To" data-date-format="YYYY-MM-DD HH:mm">' +
            '</div>' +
            '&nbsp;&nbsp;<button type="submit" class="btn btn-default">Filter</button>' +
            '</form>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>'
        );

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
            defaultDate: '{{ $default_date }}'
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
            }
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
            $("#dtpickerto").data("DateTimePicker").maxDate('{{ $now }}');
        }

        init_select2("select#device", "device", {limit: 100}, "{{ $device->device_id }}");
        init_select2("#program", "syslog", function(params) {
            return {
                field: "program",
                device: $('#device').val(),
                term: params.term,
                page: params.page || 1
            }
        }, @json($program));
        init_select2("#priority", "syslog", function(params) {
            return {
                field: "priority",
                device: $('#device').val(),
                term: params.term,
                page: params.page || 1
            }
        }, @json($priority));
    </script>
@endsection
