<x-panel body-class="tw:p-0!">
    <table id="ports-arp" class="table table-condensed table-hover table-striped tw:mt-1 tw:mb-0!">
        <thead>
        <tr>
            <th data-column-id="interface">Port</th>
            <th data-column-id="mac_address" data-formatter="tooltip">MAC address</th>
            @config('mac_oui.enabled')
                <th data-column-id="mac_oui" data-sortable="false" data-width="150px" data-visible="false" data-formatter="tooltip">Vendor</th>
            @endconfig
            <th data-column-id="ipv4_address" data-formatter="tooltip">IPv4 address</th>
            <th data-column-id="remote_device" data-sortable="false">Remote device</th>
            <th data-column-id="remote_interface" data-sortable="false">Remote interface</th>
        </tr>
        </thead>
    </table>
</x-panel>

<script>
    var grid = $("#ports-arp").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function ()
        {
            return {
                id: "arp-search",
                device_id: "{{ $device->device_id }}"
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
