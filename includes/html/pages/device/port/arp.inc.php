<?php
$no_refresh = true;
?>
<table id="port-arp" class="table table-condensed table-hover table-striped">
    <thead>
        <tr>
            <th data-column-id="mac_address">MAC address</th>
            <th data-column-id="ipv4_address">IPv4 address</th>
            <th data-column-id="remote_device" data-sortable="false">Remote device</th>
            <th data-column-id="remote_interface" data-sortable="false">Remote interface</th>
        </tr>
    </thead>
</table>

<script>

var grid = $("#port-arp").bootgrid({
    ajax: true,
    rowCount: [50, 100, 250, -1],
    post: function ()
    {
        return {
            id: "arp-search",
            port_id: "<?php echo $port['port_id']; ?>"
        };
    },
    url: "ajax_table.php"
});
</script>

