<?php
$no_refresh = true;
?>
<table id="port-arp" class="table table-condensed table-hover table-striped">
    <thead>
        <tr>
            <th data-column-id="mac_address" data-formatter="tooltip">MAC address</th>
<?php
if (\LibreNMS\Config::get('mac_oui.enabled') === true) {
    echo '            <th data-column-id="mac_oui" data-sortable="false" data-width="150px" data-visible="false" data-formatter="tooltip">Vendor</th>';
}
?>
            <th data-column-id="ipv4_address" data-formatter="tooltip">IPv4 address</th>
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
    url: "ajax_table.php"
});
</script>

