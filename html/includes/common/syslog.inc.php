<?php
$common_output[] = '
<table id="sysloglog" class="table table-hover table-condensed table-striped">
    <thead>
        <tr>
            <th data-column-id="datetime" data-order="desc">Datetime</th>
            <th data-column-id="hostname">Hostname</th>
            <th data-column-id="program">Program</th>
            <th data-column-id="message">Message</th>
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
            type: "' .mres($vars['type']) .'",
        };
    },
    url: "ajax_table.php"
});
</script>
';
