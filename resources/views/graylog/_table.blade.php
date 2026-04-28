<div class="table-responsive">
    <table id="graylog" class="table table-hover table-condensed graylog">
        <thead>
        <tr>
            <th data-column-id="_id" data-visible="false" data-identifier="true"></th>
            @foreach($columns as $col)
                <th data-column-id="{{ $col['field'] }}"
                    @if($col['field'] === 'timestamp') data-formatter="browserTime" @endif
                    data-sortable="false">{{ $col['label'] }}</th>
            @endforeach
        </tr>
        </thead>
    </table>
</div>

@push('scripts')
<script>
    let searchbar = "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}}\"><div class=\"row\">"+
            "<div class=\"col-sm-8\"><form method=\"GET\" action=\"\" class=\"form-inline\">"+
                    "Filter: " +
                    "<div class=\"form-group\"><select name=\"stream\" id=\"stream\" class=\"form-control\" data-placeholder=\"All Messages\"></select>&nbsp;</div>"+
                    "<div class=\"form-group\"><select name=\"device\" id=\"device\" class=\"form-control\" data-placeholder=\"All Devices\"></select>&nbsp;</div>"+
                    "<div class=\"form-group\">"+
                        "<select name=\"loglevel\" id=\"loglevel\" class=\"form-control\">"+
                            "<option value=\"\" @if($loglevel_selected === '') selected @endif>Any Log Level</option>"+
                            @for($i = 0; $i <= 7; $i++)
                                "<option value=\"{{ $i }}\" @if($loglevel_selected === (string) $i) selected @endif>({{ $i }}) {{ __('syslog.severity.' . $i) }}</option>"+
                            @endfor
                            "</select>&nbsp;</div>"+
                    "<div class=\"form-group\"><select id=\"range\" name=\"range\" class=\"form-control\">"+
                            @foreach($ranges as $value => $label)
                                "<option value=\"{{ $value }}\" @if($range_selected === (string) $value) selected @endif>Search {{ $label }}</option>"+
                            @endforeach
                            "</select>&nbsp;</div>"+
                    "<button type=\"submit\" class=\"btn btn-success\">Filter</button>&nbsp;"+
                    "</form></div>"+
            "<div class=\"col-sm-4 actionBar\"><p class=\"@{{css.search}}\"></p><p class=\"@{{css.actions}}\"></p></div></div></div>";

    var graylog_grid = $("#graylog").bootgrid({
        ajax: true,
        identifier: "_id",
        rowCount: [{{ $row_count_default }}, 25, 50, 100, 250, -1],
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

        url: @json($table_url),
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
            $row.children("td").removeClass("tw:bg-gray-100 tw:dark:bg-dark-gray-300");
            return;
        }
        var rowId = $row.attr("data-row-id");
        var row = (graylog_grid.bootgrid("getCurrentRows") || [])
            .find(function (r) { return String(r._id) === String(rowId); });
        if (! row || ! row._detail_html) {
            return;
        }
        var colCount = $row.children("td").length;
        $row.children("td").addClass("tw:bg-gray-100 tw:dark:bg-dark-gray-300");
        $row.after('<tr class="graylog-detail-row"><td colspan="' + colCount + '" class="tw:bg-gray-50 tw:dark:bg-dark-gray-400 tw:px-3 tw:py-2">' + row._detail_html + '</td></tr>');
    });

    init_select2("#stream", "graylog-streams", {}, @json($stream_selected), 'All Streams');
    init_select2("select#device", "device", {limit: 100}, @json($device_selected), 'All Devices');
</script>
@endpush
