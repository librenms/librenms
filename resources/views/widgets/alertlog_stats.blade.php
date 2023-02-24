<div class="table-responsive">
    <table id="alertlog-stats_{{ $id }}" class="table table-hover table-condensed table-striped" data-ajax="true">
        <thead>
        <tr>
            <th data-column-id="count">{{ __('Count') }}</th>
            <th data-column-id="hostname">{{ __('Device') }}</th>
            <th data-column-id="alert_rule">{{ __('Alert rule') }}</th>
        </tr>
        </thead>
    </table>
</div>
<script>
    $("#alertlog-stats_{{ $id }}").bootgrid({
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
</script>
