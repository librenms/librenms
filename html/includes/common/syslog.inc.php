<?php

$common_output[] = '
<div class="table-responsive">
    <table id="syslog" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="priority">&nbsp;</th>
                <th data-column-id="timestamp" data-order="desc">Datetime</th>
                <th data-column-id="device_id">Hostname</th>
                <th data-column-id="program">Program</th>
                <th data-column-id="msg">Message</th>
                <th data-column-id="status">Message</th>
            </tr>
        </thead>
    </table>
</div>
<script>

var syslog_grid = $("#syslog").bootgrid({
    ajax: true,
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
    statusMappings: {
        // Nagios style
        0: "text-muted",
        1: "warning",
        2: "danger",
        3: "info"
    }
});

</script>
';
