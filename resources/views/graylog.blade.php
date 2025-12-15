@extends('layouts.librenmsv1')

@section('title', __('Graylog'))

@section('content')
    <div class="container-fluid">
        <x-panel body-class="tw:p-0!">
            <x-slot name="heading">
                <h3 class="panel-title">@lang('Graylog')</h3>
            </x-slot>
            <div class="table-responsive">
                <table id="graylog" class="table table-hover table-condensed graylog"
                    data-url="{{ route('table.graylog') }}" data-export="false">
                    <thead>
                    <tr>
                        <th data-column-id="severity" data-width="20" data-sortable="false"></th>
                        <th data-column-id="timestamp" data-width="160" data-order="desc">@lang('Timestamp')</th>
                        <th data-column-id="level">@lang('Level')</th>
                        <th data-column-id="origin">@lang('Origin')</th>
                        <th data-column-id="source">@lang('Source')</th>
                        <th data-column-id="message" data-sortable="false">@lang('Message')</th>
                        <th data-column-id="facility">@lang('Facility')</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </x-panel>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const graylog_grid = $("#graylog").bootgrid({
                ajax: true,
                rowCount: [20, 50, 100, 250],
                templates: {
                    header: '<div id="@{{ctx.id}}" class="@{{css.header}} tw:flex tw:flex-wrap tw:items-center">' +
                        '<form class="tw:flex tw:flex-wrap tw:items-center" role="form" id="graylog_filter">' +
                            '{!! addslashes(csrf_field()) !!}' +
                            '<div class="tw:flex tw:items-baseline tw:ml-2">' +
                                '<select name="device" id="device" class="form-control"></select>' +
                            '</div>' +
                            '<div class="tw:flex tw:items-baseline tw:ml-2">' +
                                '<select name="graylog-streams" id="graylog-streams" class="form-control"></select>' +
                            '</div>' +
                            '<div class="tw:flex tw:items-baseline tw:ml-2">' +
                                '<select name="loglevel" id="loglevel" class="form-control" ' +
                                    'data-toggle="tooltip" data-placement="top" ' +
                                    'title="@lang("Maximum severity (7) Debug shows all")">' +
                                    '<option value="0">(0) {{ __("syslog.severity.0") }}</option>' +
                                    '<option value="1">(1) {{ __("syslog.severity.1") }}</option>' +
                                    '<option value="2">(2) {{ __("syslog.severity.2") }}</option>' +
                                    '<option value="3">(3) {{ __("syslog.severity.3") }}</option>' +
                                    '<option value="4">(4) {{ __("syslog.severity.4") }}</option>' +
                                    '<option value="5">(5) {{ __("syslog.severity.5") }}</option>' +
                                    '<option value="6" selected>(6) {{ __("syslog.severity.6") }}</option>' +
                                    '<option value="7">(7) {{ __("syslog.severity.7") }}</option>' +
                                '</select>' +
                            '</div>' +
                            '<div class="tw:flex tw:items-baseline tw:ml-2">' +
                                '<select id="range" name="range" class="form-control" ' +
                                    'data-toggle="tooltip" data-placement="top" ' +
                                    'title="@lang("Relative time range overrides the absolute range. Absolute “Start/End” is only used when the relative range is set to All")">' +
                                    '<option value="0">All Time Ranges</option>' +
                                    '<option value="300">Last 5 minutes</option>' +
                                    '<option value="900">Last 15 minutes</option>' +
                                    '<option value="1800">Last 30 minutes</option>' +
                                    '<option value="3600">Last 1 hour</option>' +
                                    '<option value="7200">Last 2 hours</option>' +
                                    '<option value="28800">Last 8 hours</option>' +
                                    '<option value="86400">Last 1 day</option>' +
                                    '<option value="172800">Last 2 days</option>' +
                                    '<option value="432000">Last 5 days</option>' +
                                    '<option value="604800">Last 7 days</option>' +
                                    '<option value="1209600">Last 14 days</option>' +
                                    '<option value="2592000">Last 30 days</option>' +
                                '</select>' +
                            '</div>' +
                            '<div class="tw:flex tw:relative tw:items-baseline tw:ml-2">' +
                                '<input name="from" type="text" class="form-control" id="dtpickerfrom" maxlength="16" value="' + @json($filter['from']) + '" placeholder="From" data-date-format="YYYY-MM-DD HH:mm" ' +
                                    'data-toggle="tooltip" data-placement="top" ' +
                                    'title="@lang("Start of the absolute time range (ignored when a relative range is selected)")">' +
                            '</div>' +
                            '<div class="tw:flex tw:relative tw:items-baseline tw:ml-2">' +
                                '<input name="to" type="text" class="form-control" id="dtpickerto" maxlength="16" value="' + @json($filter['to']) + '" placeholder="To" data-date-format="YYYY-MM-DD HH:mm" ' +
                                    'data-toggle="tooltip" data-placement="top" ' +
                                    'title="@lang("End of the absolute time range (ignored when a relative range is selected)")">' +
                            '</div>' +
                            '<button type="submit" class="btn btn-default tw:ml-2">@lang("Filter")</button>' +
                            '<button type="button" class="btn btn-default tw:ml-2" id="graylog_clear">@lang("Clear")</button>' +
                        '</form>' +
                        '<div class="actionBar tw:ml-auto tw:relative">' +
                            '<div class="@{{css.search}}"></div>' +
                            '<div class="@{{css.actions}}"></div>' +
                        '</div>' +
                    '</div>'
                },
                post: function () {
                    return {
                        device: $('#device').val() || '',
                        stream: $('#graylog-streams').val() || '',
                        source: $('#graylog-source').val() || '',
                        range: $('#range').val() || '',
                        loglevel: $('#loglevel').val() || '',
                        from: $('#dtpickerfrom').val() || '',
                        to: $('#dtpickerto').val() || '',
                    };
                },
            });

            const dtIcons = {
                time: 'fa fa-clock-o',
                date: 'fa fa-calendar',
                up: 'fa fa-chevron-up',
                down: 'fa fa-chevron-down',
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-calendar-check-o',
                clear: 'fa fa-trash-o',
                close: 'fa fa-close'
            };
            $("#graylog").on("loaded.rs.jquery.bootgrid", function () {
                $("#dtpickerfrom").datetimepicker({
                    icons: dtIcons,
                    defaultDate: '{{ $filter['default_date'] }}'
                });
                $("#dtpickerfrom").on("dp.change", function (e) {
                    $("#dtpickerto").data("DateTimePicker").minDate(e.date);
                });
                $("#dtpickerto").datetimepicker({
                    icons: dtIcons,
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
                    $("#dtpickerto").data("DateTimePicker").maxDate('{{ $filter['now'] }}');
                }
                $("#graylog_filter").on("submit", function (e) {
                    e.preventDefault();
                    graylog_grid.bootgrid("reload", true);
                });

                init_select2("#device", "device", {limit: 100}, @json($device) , "@lang('All Devices')");
                init_select2("#graylog-streams", "graylog-streams", @json($graylog_filter), @json($filter['stream']),'All Streams');
                $("#graylog_clear").on("click", function () {
                    $("#device").val(null).trigger("change");
                    $("#graylog-streams").val(null).trigger("change");
                    $("#loglevel").val('6').trigger("change");
                    $("#range").val('0').trigger("change");

                    $("#graylog").find(".search-field").val("");
                    graylog_grid.bootgrid("search", "");

                    const fromPicker = $("#dtpickerfrom").data("DateTimePicker");
                    const toPicker   = $("#dtpickerto").data("DateTimePicker");

                    const now   = moment();
                    const from  = moment(now).subtract(7, 'day');

                    fromPicker.minDate(false);
                    fromPicker.maxDate(now);
                    toPicker.minDate(from);
                    toPicker.maxDate(now);

                    fromPicker.date(from);
                    toPicker.date(now);

                    graylog_grid.bootgrid("reload", true);
                });
                $('[data-toggle="tooltip"]').tooltip();
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        #graylog-header .actionBar {
            display: flex;
            align-items: center;
        }
        #graylog-header .actionBar .search {
            margin-right: .5rem;
            float: none;
        }
        #graylog-header .actionBar > .actions {
            display: flex;
        }
        #graylog-header .actionBar > .actions > * {
            float: none;
        }
    </style>
@endpush