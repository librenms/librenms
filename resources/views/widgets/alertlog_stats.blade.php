<div id="alertlog_stats_container-{{ $id }}" data-reload="false">
    <div class="table-responsive">
        <table id="alertlog_stats-{{ $id }}" class="table table-hover table-condensed table-striped">
            <thead>
            <tr>
                <th data-column-id="count">{{ __('Count') }}</th>
                <th data-column-id="hostname">{{ __('Device') }}</th>
                <th data-column-id="alert_rule">{{ __('Alert rule') }}</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<script>
    (function () {
        var grid = $("#alertlog_stats-{{ $id }}").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            navigation: ! {{ $hidenavigation }},
            post: function () {
                return {
                    id: "alertlog-stats",
                    device_id: "",
                    min_severity: '{{ $min_severity }}',
                    time_interval: '{{ $time_interval }}'
                };
            },
            url: "ajax_table.php"
        });

        $('#alertlog_stats_container-{{ $id }}').on('refresh', function (event) {
            grid.bootgrid('reload');
        });
        $('#alertlog_stats_container-{{ $id }}').on('destroy', function (event) {
            grid.bootgrid('destroy');
            delete grid;
        });
    })();
</script>
