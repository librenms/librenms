<div class="table-responsive">
    <table id="sensors" class="table table-hover table-condensed storage">
        <thead>
            <tr>
                <th data-column-id="hostname">Device</th>
                <th data-column-id="sensor_descr">Sensor</th>
                <th data-column-id="graph" data-sortable="false" data-searchable="false"></th>
                <th data-column-id="alert" data-sortable="false" data-searchable="false"></th>
                <th data-column-id="sensor_current">Current</th>
                <th data-column-id="sensor_limit_low" data-searchable="false">Low Limit</th>
                <th data-column-id="sensor_limit" data-searchable="false">High Limit</th>
            </tr>
        </thead>
    </table>
</div>

<script>
    var grid = $("#sensors").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function ()
        {
            return {
                id:         'wireless-sensors',
                view:       '<?php echo $vars['view']; ?>',
                graph_type: '<?php echo $graph_type; ?>',
                unit:       '<?php echo $unit; ?>',
                class:      '<?php echo $class; ?>'
            };
        },
        url: "ajax_table.php"
    });
</script>
