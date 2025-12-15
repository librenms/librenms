@extends('layouts.librenmsv1')

@section('title', __('Eventlog'))

@section('content')
    <div class="container-fluid">
        <x-panel body-class="tw:p-0!">
            <x-slot name="heading">
                <h3 class="panel-title">@lang('Eventlog')</h3>
            </x-slot>
            <div class="table-responsive">
                <table id="eventlog" class="table table-hover table-condensed table-striped"
                    data-url="{{ route('table.eventlog') }}">
                    <thead>
                    <tr>
                        <th data-column-id="label" data-width="20" data-sortable="false"></th>
                        <th data-column-id="datetime" data-width="160" data-order="desc">@lang('Timestamp')</th>
                        <th data-column-id="device_id" data-order="asc">@lang('Device')</th>
                        <th data-column-id="type">@lang('Type')</th>
                        <th data-column-id="message" data-sortable="false">@lang('Message')</th>
                        <th data-column-id="username">@lang('User')</th>
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
            const eventlog_grid = $("#eventlog").bootgrid({
                ajax: true,
                rowCount: [20, 50, 100, 250],
                templates: {
                    header: '<div id="@{{ctx.id}}" class="@{{css.header}} tw:flex tw:flex-wrap tw:items-center">' +
                        '<form class="tw:flex tw:flex-wrap tw:items-center" role="form" id="eventlog_filter">' +
                            '{!! addslashes(csrf_field()) !!}' +
                            '<div class="tw:flex tw:items-baseline tw:ml-2">' +
                                '<select name="device" id="device" class="form-control"></select>' +
                            '</div>' +
                            '<div class="tw:flex tw:items-baseline tw:ml-2">' +
                                '<select name="eventtype" id="eventtype" class="form-control"></select>' +
                            '</div>' +
                            '<div class="tw:flex tw:relative tw:items-baseline tw:ml-2">' +
                                '<input name="from" type="text" class="form-control" id="dtpickerfrom" maxlength="16" value="' + @json($filter['from']) + '" placeholder="From" data-date-format="YYYY-MM-DD HH:mm">' +
                            '</div>' +
                            '<div class="tw:flex tw:relative tw:items-baseline tw:ml-2">' +
                                '<input name="to" type="text" class="form-control" id="dtpickerto" maxlength="16" value="' + @json($filter['to']) + '" placeholder="To" data-date-format="YYYY-MM-DD HH:mm">' +
                            '</div>' +
                            '<button type="submit" class="btn btn-default tw:ml-2">@lang("Filter")</button>' +
                            '<button type="button" class="btn btn-default tw:ml-2" id="eventlog_clear">@lang("Clear")</button>' +
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
                        eventtype: $('#eventtype').val() || '',
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
            $("#eventlog").on("loaded.rs.jquery.bootgrid", function () {
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
                if ($("#dtpickerfrom").val() !== '') {
                    $("#dtpickerto").data("DateTimePicker").minDate($("#dtpickerfrom").val());
                }
                if ($("#dtpickerto").val() !== '') {
                    $("#dtpickerfrom").data("DateTimePicker").maxDate($("#dtpickerto").val());
                } else {
                    $("#dtpickerto").data("DateTimePicker").maxDate('{{ $filter['now'] }}');
                }
                $("#eventlog_filter").on("submit", function (e) {
                    e.preventDefault();
                    eventlog_grid.bootgrid("reload", true);
                });
                init_select2("#device", "device", {limit: 100}, @json($device) , "@lang('All Devices')");
                init_select2("#eventtype", "eventlog", @json($eventlog_filter), @json($filter['eventtype']), "@lang('All Types')");
                $("#eventlog_clear").on("click", function () {
                    $("#device").val(null).trigger("change");
                    $("#eventtype").val(null).trigger("change");

                    $("#eventlog").find(".search-field").val("");
                    eventlog_grid.bootgrid("search", "");

                    const fromPicker = $("#dtpickerfrom").data("DateTimePicker");
                    const toPicker   = $("#dtpickerto").data("DateTimePicker");

                    const now   = moment();
                    const from  = moment(now).subtract(1, 'day');

                    fromPicker.minDate(false);
                    fromPicker.maxDate(now);
                    toPicker.minDate(from);
                    toPicker.maxDate(now);

                    fromPicker.date(from);
                    toPicker.date(now);

                    eventlog_grid.bootgrid("reload", true);
                });
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        #eventlog-header .actionBar {
            display: flex;
            align-items: center;
        }
        #eventlog-header .actionBar .search {
            margin-right: .5rem;
            float: none;
        }
        #eventlog-header .actionBar > .actions {
            display: flex;
        }
        #eventlog-header .actionBar > .actions > * {
            float: none;
        }
    </style>
@endpush