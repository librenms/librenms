@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" subtitle="{{ __('Graylog') }}">
        <x-device.log-tabs :device="$device" tab="graylog" />

        <x-panel title="{{ __('Graylog') }}">
            <div class="table-responsive">
                <table id="graylog" class="table table-hover table-condensed graylog">
                    <thead>
                    <tr>
                        <th data-column-id="severity" data-sortable="false"></th>
                        <th data-column-id="origin">Origin</th>
                        <th data-column-id="timestamp" data-formatter="browserTime">Timestamp</th>
                        <th data-column-id="level">Level</th>
                        <th data-column-id="source">Source</th>
                        <th data-column-id="message" data-sortable="false">Message</th>
                        <th data-column-id="facility">Facility</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </x-panel>
    </x-device.page>
@endsection

@section('scripts')
    <script>
        var graylog_grid = $("#graylog").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            formatters: {
                "browserTime": function (column, row) {
                        let timezone = @json($timezone);

                        if (timezone) {
                            return row.timestamp;
                        }

                        return moment.parseZone(row.timestamp).local().format("YYYY-MM-DD HH:mm:ss");
                    }
            },
            post: function () {
                return {
                    stream: @json($stream),
                    device: {{ $device->device_id }},
                    range: @json($range),
                    loglevel: @json($loglevel),
                };
            },
            url: "{{ route('table.graylog', ) }}",
        });
        $('.actionBar').append(
            '<div class="pull-left">' +
                '<form method="GET" action="" class="form-inline" role="form" id="result_form">' +
                '<div class="form-group">' +
                    '<select name="stream" id="stream" class="form-control">' +
                    '</select>&nbsp;&nbsp;' +
                '</div>' +

                '<div class="form-group">' +
                    '<select name="loglevel" id="loglevel" class="form-control">' +
                        '<option value="" disabled selected>All LogLevels</option>' +
                        '<option value="0">(0) {{ __("syslog.severity.0") }}</option>' +
                        '<option value="1">(1) {{ __("syslog.severity.1") }}</option>' +
                        '<option value="2">(2) {{ __("syslog.severity.2") }}</option>' +
                        '<option value="3">(3) {{ __("syslog.severity.3") }}</option>' +
                        '<option value="4">(4) {{ __("syslog.severity.4") }}</option>' +
                        '<option value="5">(5) {{ __("syslog.severity.5") }}</option>' +
                        '<option value="6">(6) {{ __("syslog.severity.6") }}</option>' +
                        '<option value="7">(7) {{ __("syslog.severity.7") }}</option>' +
                        '</select>&nbsp;&nbsp;</div>' +
                '<div class="form-group">' +
                    '<select id="range" name="range" class="form-control">' +
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
                    '</select>&nbsp;&nbsp;</div>' +
                '<button type="submit" class="btn btn-default">Filter</button>' +
                '</form>' +
            '</div>'
        );
        init_select2("#stream", "graylog", function(params) {
            return {
                field: "stream",
                device: {{ $device->device_id }},
                term: params.term,
                page: params.page || 1
            }
        }, @json($stream),'All Messages');
    </script>
@endsection
