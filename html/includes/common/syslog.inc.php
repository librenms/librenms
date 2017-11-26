<?php

$common_output[] = '
<div class="table-responsive">
    <table id="syslog" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="priority"></th>
                <th data-column-id="timestamp" data-order="desc">Timestamp</th>
                <th data-column-id="device_id">Hostname</th>
                <th data-column-id="program">Program</th>
                <th data-column-id="msg">Message</th>
                <th data-column-id="status">Priority</th>
            </tr>
        </thead>
    </table>
</div>
<script>

var syslog_grid = $("#syslog").bootgrid({
    ajax: true,
    rowCount: [50, 100, 250, -1],
    post: function ()
    {
        return {
            id: "syslog",
            device: "'.mres($vars['device']) .'",
            program: "'.mres($vars['program']).'",
            priority: "'.mres($vars['priority']).'",
            to: "'.mres($vars['to']).'",
            from: "'.mres($vars['from']).'",
        };
    },
    url: "ajax_table.php",
});
</script>
';
