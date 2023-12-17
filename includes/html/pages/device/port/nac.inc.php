<?php
$no_refresh = true;

?>
<table id="nac-grid" data-toggle="bootgrid" class="table table-hover table-condensed table-striped">
    <thead>
        <tr>
            <th data-column-id="port_id" data-width="100px">Ports</th>
            <th data-column-id="mac_address" data-formatter="tooltip" data-width="170px">Mac Address</th>
            <th data-column-id="mac_oui" data-formatter="tooltip" data-sortable="false" data-width="130px" data-visible="<?php echo \LibreNMS\Config::get('mac_oui.enabled') ? 'true' : 'false' ?>">Vendor</th>
            <th data-column-id="ip_address" data-formatter="tooltip" data-width="140px">IP Address</th>
            <th data-column-id="vlan" data-formatter="tooltip" data-width="60px">Vlan</th>
            <th data-column-id="domain" data-formatter="nac_domain" data-formatter="tooltip">Domain</th>
            <th data-column-id="host_mode" data-formatter="nac_mode">Host Mode</th>
            <th data-column-id="username" data-formatter="tooltip" data-width="250px">Username</th>
            <th data-column-id="authz_by" data-formatter="tooltip">Auth By</th>
            <th data-column-id="timeout" data-formatter="time_interval">Timeout</th>
            <th data-column-id="time_elapsed" data-formatter="time_interval" >Time Elapsed</th>
            <th data-column-id="time_left" data-formatter="time_interval" data-visible="false">Time Left</th>
            <th data-column-id="authc_status" data-formatter="nac_authc" data-formatter="tooltip">NAC Authc</th>
            <th data-column-id="authz_status" data-formatter="nac_authz">NAC Authz</th>
            <th data-column-id="method" data-formatter="nac_method">NAC Method</th>
            <th data-column-id="created_at" data-formatter="tooltip">First seen</th>
            <th data-column-id="updated_at" data-formatter="tooltip">Last seen</th>

        </tr>
    </thead>
</table>

<script>

var grid = $("#nac-grid").bootgrid({
    ajax: true,
    rowCount: [25, 50, 100, -1],
    url: "<?php echo url('/ajax/table/port-nac'); ?>",
    post: function () {
        var check_showHistorical = document.getElementById('check_showHistorical');
        if (check_showHistorical) {
            var showHistorical = check_showHistorical.checked;
        } else {
            var showHistorical = false;
        }

        return {
            showHistorical: showHistorical,
            device_id: "<?php echo $port['device_id']; ?>",
            port_id: "<?php echo $port['port_id']; ?>",
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
    }
});

var add = $(".actionBar").append(
        '<div class="search form-group pull-left" style="width:auto">' +
        '<?php echo csrf_field() ?>' +
        '<input type="checkbox" name="check_showHistorical" data-size="small" id="check_showHistorical">' +
        '&nbsp;Include historical NAC entries' +
        '</div>');

$("#check_showHistorical").bootstrapSwitch({
    'onSwitchChange': function(event, state){
         updateTable();
    }
});

function updateTable() {
    $('#nac-grid').bootgrid('reload');
};
</script>

