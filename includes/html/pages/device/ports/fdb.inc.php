<?php
$no_refresh = true;
?>
<table id="ports-fdb" class="table table-condensed table-hover table-striped">
    <thead>
        <tr>
            <th data-column-id="mac_address" data-width="150px" data-formatter="tooltip">MAC Address</th>
<?php
if (\LibreNMS\Config::get('mac_oui.enabled') === true) {
    echo '            <th data-column-id="mac_oui" data-sortable="false" data-width="150px" data-visible="false" data-formatter="tooltip">Vendor</th>';
}
?>
            <th data-column-id="ipv4_address" data-sortable="false" data-formatter="tooltip">IPv4 Address</th>
            <th data-column-id="interface">Port</th>
            <th data-column-id="description" data-formatter="tooltip">Description</th>
            <th data-column-id="vlan" data-width="60px">Vlan</th>
            <th data-column-id="dnsname" data-sortable="false" data-visible="false" data-formatter="tooltip">DNS Name</th>
            <th data-column-id="first_seen" data-width="165px">First seen</th>
            <th data-column-id="last_seen" data-width="165px">Last seen</th>
        </tr>
    </thead>
</table>

<script>

var grid = $("#ports-fdb").bootgrid({
    ajax: true,
    post: function ()
    {
        return {
            device_id: "<?php echo $device['device_id']; ?>",
            dns: $("#ports-fdb").bootgrid("getColumnSettings").find(col => col.id === "dnsname").visible,
        };
    },
    formatters: {
        "tooltip": function (column, row) {
                var value = row[column.id];
                var vendor = '';
                if (column.id == 'mac_address' && ((vendor = row['mac_oui']) != '' )) {
                    return "<span title=\'" + value + " (" + vendor + ")\' data-toggle=\'tooltip\'>" + value + "</span>";
                }
                return "<span title=\'" + value + "\' data-toggle=\'tooltip\'>" + value + "</span>";
            },
    },
    url: "<?php echo url('/ajax/table/fdb-tables') ?>"
});
</script>

