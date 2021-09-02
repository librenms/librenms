<?php

$no_refresh = true;

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'nac',
];
$pagetitle[] = 'NAC';

//manage the column visibility depending on OS
$vlan_visibility = ' data-visible="false"';
$t_elapsed_visibility = ' data-visible="false"';
$t_left_visibility = ' data-visible="false"';
$timeout_visibility = ' data-visible="false"';
$mode_visibility = ' data-visible="false"';
if ($device['os'] === 'vrp') {
    $vlan_visibility = '';
    $t_elapsed_visibility = '';
} else {
    $t_left_visibility = '';
    $timeout_visibility = '';
    $mode_visibility = '';
}

?>
<br/>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Network Access Controls</h3>
        </div>
        <div class="panel-body" style="padding: 15px 0">
            <table id="nac-grid" data-toggle="bootgrid" class="table table-condensed table-responsive table-striped">
                <thead>
                <tr>
                    <th data-column-id="port_id" data-width="100px">Port</th>
                    <th data-column-id="mac_address" data-width="150px" data-formatter="tooltip">MAC Address</th>
<?php
if (\LibreNMS\Config::get('mac_oui.enabled') === true) {
    echo '                    <th data-column-id="mac_oui" data-sortable="false" data-width="130px" data-visible="false" data-formatter="tooltip">Vendor</th>';
}
?>
                    <th data-column-id="ip_address" data-width="140px" data-formatter="tooltip">IP Address</th>
                    <th data-column-id="vlan" data-width="60px" data-formatter="tooltip"<?php echo $vlan_visibility ?>>Vlan</th>
                    <th data-column-id="domain" data-formatter="nac_domain" data-formatter="tooltip">Domain</th>
                    <th data-column-id="host_mode"<?php echo $mode_visibility ?>data-formatter="nac_mode">Mode</th>
                    <th data-column-id="username" data-width="250px" data-formatter="tooltip">Username</th>
                    <th data-column-id="authz_by" data-visible="false" data-formatter="tooltip">Auth By</th>
                    <th data-column-id="timeout"<?php echo $timeout_visibility ?> data-formatter="time_interval">Timeout</th>
                    <th data-column-id="time_elapsed"<?php echo $t_elapsed_visibility ?> data-formatter="time_interval">Elapsed time</th>
                    <th data-column-id="time_left"<?php echo $t_left_visibility ?> data-formatter="time_interval">Time left</th>
                    <th data-column-id="authc_status" data-formatter="nac_authc" data-formatter="tooltip">AuthC</th>
                    <th data-column-id="authz_status" data-width="76px" data-formatter="nac_authz">AuthZ</th>
                    <th data-column-id="method" data-width="100px" data-formatter="nac_method">Method</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('#nac-grid').bootgrid({
        ajax: true,
        rowCount: [25, 50, 100, -1],
        url: "<?php echo url('/ajax/table/port-nac'); ?>",
        post: function () {
            return {
                device_id: <?php echo $device['device_id'] ?>
            };
        },
        formatters: {
            "time_interval": function (column, row) {
                var value = row[column.id];
                var res = humanize_duration(value);
                var res_light = res.split(' ').slice(0, 2).join(' ');
                return "<span title=\'" + res.trim() + "\' data-toggle=\'tooltip\'>" + res_light + "</span>";
            },
            "tooltip": function (column, row) {
                var value = row[column.id];
                var vendor = '';
                if (column.id == 'mac_address' && ((vendor = row['mac_oui']) != '' )) {
                    return "<span title=\'" + value + " (" + vendor + ")\' data-toggle=\'tooltip\'>" + value + "</span>";
                }
                return "<span title=\'" + value + "\' data-toggle=\'tooltip\'>" + value + "</span>";
            },
            "nac_authz": function (column, row) {
                var value = row[column.id];

                if (value === 'authorizationSuccess' || value === 'sussess') { 
                    //typo in huawei MIB so we must keep sussess
                    return "<i class=\"fa fa-check-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:green;\"></i>";
                } else if (value === 'authorizationFailed') {
                    return "<i class=\"fa fa-times-circle fa-lg icon-theme\" aria-hidden=\"true\" style=\"color:red;\"></i>";
                } else {
                    return "<span class=\'label label-default\' title=\'" + value + "\' data-toggle=\'tooltip\'>" + value + "</span>";
                }
            },
            "nac_domain": function (column, row) {
                var value = row[column.id];
                if (value === 'voice') {
                    return "<i class=\"fa fa-phone fa-lg icon-theme\"  aria-hidden=\"true\"></i>";
                } else if (value === 'data') {
                    return "<i class=\"fa fa-desktop fa-lg icon-theme\"  aria-hidden=\"true\"></i>";
                } else if (value === 'Disabled') {
                    return "<i class=\"fa fa-desktop fa-lg icon-theme\"  aria-hidden=\"true\"></i>";
                } else {
                    return "<span class=\'label label-default\' title=\'" + value + "\' data-toggle=\'tooltip\'>" + value + "</span>";
                }
            },
            "nac_authc": function (column, row) {
                var value = row[column.id];
                if (value === 'notRun') {
                    return "<span class=\"label label-primary\">notRun</span>";
                } else if (value === 'running') {
                    return "<span class=\"label label-primary\">running</span>";
                } else if (value === 'failedOver') {
                    return "<i class=\"fa fa-times-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:red;\"></i>";
                } else if (value === 'authcSuccess') {
                    return "<i class=\"fa fa-check-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:green;\">";
                } else if (value === 'authcFailed') {
                    return "<i class=\"fa fa-times-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:red;\"></i>";
                } else if (value === '6') {
                    return "<i class=\"fa fa-times-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:red;\"></i>";
                } else {
                    return "<span class=\'label label-default\' title=\'" + value + "\' data-toggle=\'tooltip\'>" + value + "</span>";
                }
            },
            "nac_method": function (column, row) {
                var value = row[column.id];
                if (value === 'dot1x') {
                    return "<span class=\"label label-success\">802.1x</span>";
                } else if (value === 'macAuthBypass') {
                    return "<span class=\"label label-primary\">MAB</span>";
                } else if (value === 'other') {
                    return "<span class=\"label label-danger\">Disabled</span>";
                } else {
                    return "<span class=\'label label-default\' title=\'" + value + "\' data-toggle=\'tooltip\'>" + value + "</span>";
                }
            }
        }
    });
</script>
