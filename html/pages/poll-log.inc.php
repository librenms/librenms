<?php
$no_refresh = true;
$pagetitle[] = 'Poll Log';
if (isset($vars['filter'])) {
    $type = $vars['filter'];
}
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

searchbar = "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\">"+
            "<div class=\"col-sm-9 actionBar\"><span class=\"pull-left\">"+
            "<a href='<?php echo generate_url(array('page' => 'poll-log')); ?>' class='btn btn-primary btn-sm'>All devices</a> "+
            "<a href='<?php echo generate_url(array('page' => 'poll-log', 'filter' => 'unpolled')); ?>' class='btn btn-danger btn-sm'>Unpolled devices</a>"+
            "</div><div class=\"col-sm-3 actionBar\"><p class=\"{{css.search}}\"></p><p class=\"{{css.actions}}\"></p></div>";

var grid = $("#poll-log").bootgrid({
    ajax: true,
    rowCount: [50,100,250,-1],
    columnSelection: false,
    templates: {
        header: searchbar
    },
    post: function ()
    {
        return {
            id: "poll-log",
            type: "<?php echo $type;?>"
        };
    },
    url: "ajax_table.php"
});

</script>
