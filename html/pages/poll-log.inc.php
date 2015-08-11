<?php
$no_refresh = true;
?>
<table id="poll-log" class="table table-condensed table-hover table-striped">
    <thead>
        <tr>
            <th data-column-id="hostname">Hostname</th>
            <th data-column-id="last_polled">Last Polled</th>
            <th data-column-id="poller_group">Poller Group</th>
            <th data-column-id="last_polled_timetaken" data-order="desc">Polling Duration (Seconds)</th>
        </tr>
    </thead>
</table>

<script>

var grid = $("#poll-log").bootgrid({
    ajax: true,
    post: function ()
    {
        return {
            id: "poll-log"
        };
    },
    url: "ajax_table.php"
});

</script>
