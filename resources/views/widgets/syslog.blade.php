<div class="table-responsive">
    <table id="syslog" class="table table-hover table-condensed table-striped">
        <thead>
        <tr>
            <th data-column-id="label"></th>
            <th data-column-id="timestamp" data-order="desc">Timestamp</th>
            <th data-column-id="level">Level</th>
            <th data-column-id="device_id">Hostname</th>
            <th data-column-id="program">Program</th>
            <th data-column-id="msg">Message</th>
            <th data-column-id="priority">Priority</th>
        </tr>
        </thead>
    </table>
</div>
<script type="application/javascript">
    $("#syslog").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function ()
        {
            return {
                device: '{{ $device ?: '' }}'
            };
        },
        url: "ajax/table/syslog"
    });
</script>
