@extends('layouts.librenmsv1')

@php
    $ranges = [
        0 => 'all time',
        300 => 'last 5 minutes',
        900 => 'last 15 minutes',
        1800 => 'last 30 minutes',
        3600 => 'last 1 hour',
        7200 => 'last 2 hours',
        28800 => 'last 8 hours',
        86400 => 'last 1 day',
        172800 => 'last 2 days',
        432000 => 'last 5 days',
        604800 => 'last 7 days',
        1209600 => 'last 14 days',
        2592000 => 'last 30 days',
    ];
    $rangeSelected = (string) $range;
    $loglevelSelected = (string) $loglevel;
    $columnLabel = function (string $field): string {
        if ($field === 'severity') {
            return '';
        }
        return ucwords(str_replace(['_', '-'], ' ', $field));
    };
@endphp

@section('content')
    <x-device.page :device="$device" subtitle="{{ __('Graylog') }}">
        <x-device.log-tabs :device="$device" tab="graylog" />

        <x-panel title="{{ __('Graylog') }}">
            <div class="table-responsive">
                <table id="graylog" class="table table-hover table-condensed graylog">
                    <thead>
                    <tr>
                        @if(in_array('severity', $fields, true))
                            <th data-column-id="severity" data-sortable="false"></th>
                        @endif
                        <th data-column-id="timestamp" data-formatter="browserTime">{{ __('Timestamp') }}</th>
                        @foreach($fields as $field)
                            @if($field !== 'severity')
                                <th data-column-id="{{ $field }}" data-sortable="false">{{ $columnLabel($field) }}</th>
                            @endif
                        @endforeach
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
                    @if($stream_selected)
                        "<option value=\"" + @json($stream_selected['id']) + "\">" + @json($stream_selected['text']) + "</option>" +
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
                            "<option value=\"\" @if($loglevelSelected === '') selected @endif>Any Log Level</option>"+
                            @for($i = 0; $i <= 7; $i++)
                                "<option value=\"{{ $i }}\" @if($loglevelSelected === (string) $i) selected @endif>({{ $i }}) {{ __('syslog.severity.' . $i) }}</option>"+
                            @endfor
                            "</select>&nbsp;</div>"+
                    "<div class=\"form-group\"><select id=\"range\" name=\"range\" class=\"form-control\">"+
                            @foreach($ranges as $value => $label)
                                "<option value=\"{{ $value }}\" @if($rangeSelected === (string) $value) selected @endif>Search {{ $label }}</option>"+
                            @endforeach
                            "</select>&nbsp;</div>"+
                    "<button type=\"submit\" class=\"btn btn-success\">Filter</button>&nbsp;"+
                    "</form></div>"+
            "<div class=\"col-sm-4 actionBar\"><p class=\"@{{css.search}}\"></p><p class=\"@{{css.actions}}\"></p></div></div></div>";

    var graylog_grid = $("#graylog").bootgrid({
        ajax: true,
        rowCount: [{{ \App\Facades\LibrenmsConfig::get('graylog.device-page.rowCount', 10) }}, 25, 50, 100, 250, -1],
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

    var graylog_row_details = {};

    graylog_grid.on("loaded.rs.jquery.bootgrid", function () {
        var rows = graylog_grid.bootgrid("getCurrentRows") || [];
        graylog_row_details = {};
        graylog_grid.find("tbody > tr").each(function (i) {
            var idx = $(this).attr("data-row-id");
            if (idx === undefined) {
                idx = String(i);
            }
            var row = rows[idx];
            if (row && row._detail_html) {
                graylog_row_details[idx] = row._detail_html;
                $(this).addClass("graylog-clickable");
            }
        });
    });

    graylog_grid.find("tbody").on("click", "tr", function (e) {
        var $row = $(this);
        if ($row.hasClass("graylog-detail-row")) {
            return;
        }
        if ($(e.target).closest("a, button, input, select").length) {
            return;
        }
        var $next = $row.next(".graylog-detail-row");
        if ($next.length) {
            $next.remove();
            $row.removeClass("graylog-detail-open");
            return;
        }
        var idx = $row.attr("data-row-id");
        var detailHtml = graylog_row_details[idx];
        if (! detailHtml) {
            return;
        }
        var colCount = $row.children("td").length;
        $row.addClass("graylog-detail-open");
        $row.after('<tr class="graylog-detail-row"><td colspan="' + colCount + '">' + detailHtml + '</td></tr>');
    });

    init_select2("#stream", "graylog-streams", {}, @json($stream_selected), 'All Streams');
    init_select2("select#device", "device", {limit: 100}, @json(['id' => $device->device_id, 'text' => $device->displayName()]), 'All Devices');
</script>
@endsection

@push('styles')
    <style>
        #graylog tbody > tr.graylog-clickable {
            cursor: pointer;
        }
        tr.graylog-detail-open > td {
            background-color: #f5f5f5;
        }
        tr.graylog-detail-row > td {
            background-color: #fafafa;
            padding: 6px 10px;
        }
        dl.graylog-row-detail {
            display: grid;
            grid-template-columns: max-content 1fr;
            column-gap: 16px;
            row-gap: 2px;
            margin: 0;
            font-family: monospace;
            font-size: 12px;
        }
        dl.graylog-row-detail dt {
            font-weight: 600;
            word-break: break-word;
        }
        dl.graylog-row-detail dd {
            margin: 0;
            word-break: break-word;
            white-space: pre-wrap;
        }
        html.dark tr.graylog-detail-open > td {
            background-color: #2a2a2a;
        }
        html.dark tr.graylog-detail-row > td {
            background-color: #1f1f1f;
            color: #ddd;
        }
        html.dark dl.graylog-row-detail dt {
            color: #8ab4f8;
        }
    </style>
@endpush
