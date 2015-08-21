<?php

$common_output[] = '
<table id="syslog" class="table table-hover table-condensed table-striped">
    <thead>
        <tr>
            <th data-column-id="timestamp" data-order="desc">Datetime</th>
            <th data-column-id="device_id">Hostname</th>
            <th data-column-id="program">Program</th>
            <th data-column-id="msg">Message</th>
        </tr>
    </thead>
</table>

<script>

var syslog_grid = $("#syslog").bootgrid({
    ajax: true,
    post: function ()
    {
        return {
            id: "syslog",
            device: "' .mres($vars['device']) .'",
        };
    },
    url: "ajax_table.php"
});

</script>
';
