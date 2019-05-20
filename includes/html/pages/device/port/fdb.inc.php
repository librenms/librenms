<?php
$no_refresh = true;
?>
<table id="port-fdb" class="table table-condensed table-hover table-striped">
    <thead>
        <tr>
            <th data-column-id="mac_address" data-width="150px">MAC Address</th>
            <th data-column-id="ipv4_address" data-sortable="false">IPv4 Address</th>
            <th data-column-id="interface">Port</th>
            <th data-column-id="vlan" data-width="60px">Vlan</th>
            <th data-column-id="dnsname" data-sortable="false" data-visible="false">DNS Name</th>
            <th data-column-id="first_seen" data-width="165px">First seen</th>
            <th data-column-id="last_seen" data-width="165px">Last seen</th>
        </tr>
    </thead>
</table>

<script>

var grid = $("#port-fdb").bootgrid({
    ajax: true,
    post: function ()
    {
        return {
            port_id: "<?php echo $port['port_id']; ?>",
            dns: $("#port-fdb").bootgrid("getColumnSettings")[4].visible
        };
    },
    url: "<?php echo url('/ajax/table/fdb-tables'); ?>"
});
</script>

