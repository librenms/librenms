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

            @if(! $filter_device)
                <input type="hidden" name="device" id="device" value="{{ $device->device_id }}">
            @endif
        </x-panel>
    </x-device.page>
@endsection

@section('scripts')
<script>
    let searchbar = "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}}\"><div class=\"row\">"+
            "<div class=\"col-sm-8\"><form method=\"GET\" action=\"\" class=\"form-inline\">"+
                    "Filter: " +
                    "<div class=\"form-group\"><select name=\"stream\" id=\"stream\" class=\"form-control\" data-placeholder=\"All Messages\">"+
                    @if($stream)
                        "<option value=\"" + @json($stream) + "\">" + @json($stream) + "</option>" +
                    @endif
                        "</select>&nbsp;</div>"+

                    @if($filter_device)
                        "<div class=\"form-group\"><select name=\"device\" id=\"device\" class=\"form-control\" data-placeholder=\"All Devices\">"+
                        @if($device)
                        "<option value=\"{{ $device->device_id }}\">" + @json($device->displayName()) + "</option>" +
                        @endif
                        "</select>&nbsp;</div>"+
                    @endif

                    "<div class=\"form-group\">"+
                        "<select name=\"loglevel\" id=\"loglevel\" class=\"form-control\">"+
                            "<option value=\"\" disabled selected>Log Level</option>"+
                            "<option value=\"0\">(0) {{ __('syslog.severity.0') }}</option>"+
                            "<option value=\"1\">(1) {{ __('syslog.severity.1') }}</option>"+
                            "<option value=\"2\">(2) {{ __('syslog.severity.2') }}</option>"+
                            "<option value=\"3\">(3) {{ __('syslog.severity.3') }}</option>"+
                            "<option value=\"4\">(4) {{ __('syslog.severity.4') }}</option>"+
                            "<option value=\"5\">(5) {{ __('syslog.severity.5') }}</option>"+
                            "<option value=\"6\">(6) {{ __('syslog.severity.6') }}</option>"+
                            "<option value=\"7\">(7) {{ __('syslog.severity.7') }}</option>"+
                            "</select>&nbsp;</div>"+
                    "<div class=\"form-group\"><select id=\"range\" name=\"range\" class=\"form-control\">"+
                            "<option value=\"0\">Search all time</option>"+
                            "<option value=\"300\">Search last 5 minutes</option>"+
                            "<option value=\"900\">Search last 15 minutes</option>"+
                            "<option value=\"1800\">Search last 30 minutes</option>"+
                            "<option value=\"3600\">Search last 1 hour</option>"+
                            "<option value=\"7200\">Search last 2 hours</option>"+
                            "<option value=\"28800\">Search last 8 hours</option>"+
                            "<option value=\"86400\">Search last 1 day</option>"+
                            "<option value=\"172800\">Search last 2 days</option>"+
                            "<option value=\"432000\">Search last 5 days</option>"+
                            "<option value=\"604800\">Search last 7 days</option>"+
                            "<option value=\"1209600\">Search last 14 days</option>"+
                            "<option value=\"2592000\">Search last 30 days</option>"+
                            "</select>&nbsp;</div>"+
                    "<button type=\"submit\" class=\"btn btn-success\">Filter</button>&nbsp;"+
                    "</form></div>"+
            "<div class=\"col-sm-4 actionBar\"><p class=\"@{{css.search}}\"></p><p class=\"@{{css.actions}}\"></p></div></div></div>";

    var graylog_grid = $("#graylog").bootgrid({
        ajax: true,
        rowCount: [{{ $range }}, 25, 50, 100, 250, -1],
        formatters: {
            "browserTime": function (column, row) {
                    let timezone = @json($timezone);

                    if (timezone) {
                        return row.timestamp;
                    }

                    return moment.parseZone(row.timestamp).local().format("YYYY-MM-DD HH:mm:ss");
                }
        },

    @if($show_form)
        templates: {
            header: searchbar
        },
    @endif

        post: function () {
            return {
                stream: $("#stream").val(),
                device: $("#device").val(),
                range: "{{ $range }}",
                loglevel: "{{ $loglevel }}",
            };
        },
        url: "{{ route('table.graylog', ) }}",
    });

    init_select2("#stream", "graylog-streams", {}, @json($stream));
    init_select2("select#device", "device", {limit: 100}, "{{ $device->device_id }}");
</script>
@endsection
