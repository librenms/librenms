<?php
$no_refresh = true;
?>
<table id="ports-fdb" class="table table-condensed table-hover table-striped">
    <thead>
        <tr>
            <th data-column-id="mac_address" data-width="150px">MAC Address</th>
            <th data-column-id="ipv4_address" data-sortable="false">IPv4 Address</th>
            <th data-column-id="interface">Port</th>
            <th data-column-id="description">Description</th>
            <th data-column-id="vlan" data-width="60px">Vlan</th>
            <th data-column-id="dnsname" data-sortable="false">DNS Name</th>
        </tr>
    </thead>
</table>

<script>

var grid = $("#ports-fdb").bootgrid({
    ajax: true,
    post: function ()
    {
        return {
            device_id: "<?php echo $device['device_id']; ?>"
        };
    },
    url: "ajax/table/fdb-tables"
});
</script>

