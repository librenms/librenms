<?php

echo '
<div>
  <div class="panel panel-default">
    <div class="panel-body">
      <table class="table table-condensed" style="border-collapse:collapse;">
        <thead>
          <tr>
            <th>&nbsp;</th>
            <th>Device</th>
            <th>Router ID</th>
            <th>Status</th>
            <th>ABR</th>
            <th>ASBR</th>
            <th>Areas</th>
            <th>Ports(Enabled)</th>
            <th>Neighbours</th>
          </tr>
        </thead>';
foreach (dbFetchRows("SELECT * FROM `ospfv3_instances` WHERE `ospfv3AdminStatus` = 'enabled'") as $instance) {
    $device = device_by_id_cache($instance['device_id']);
    $area_count = dbFetchCell("SELECT COUNT(*) FROM `ospfv3_areas` WHERE `device_id` = '" . $device['device_id'] . "'");
    $port_count = dbFetchCell("SELECT COUNT(*) FROM `ospfv3_ports` WHERE `device_id` = '" . $device['device_id'] . "'");
    $port_count_enabled = dbFetchCell("SELECT COUNT(*) FROM `ospfv3_ports` WHERE `ospfv3IfAdminStatus` = 'enabled' AND `device_id` = '" . $device['device_id'] . "'");
    $nbr_count = dbFetchCell("SELECT COUNT(*) FROM `ospfv3_nbrs` WHERE `device_id` = '" . $device['device_id'] . "'");

    $status_color = $abr_status_color = $asbr_status_color = 'default';

    if ($instance['ospfv3AdminStatus'] == 'enabled') {
        $status_color = 'success';
    }

    if ($instance['ospfv3AreaBdrRtrStatus'] == 'true') {
        $abr_status_color = 'success';
    }

    if ($instance['ospfv3ASBdrRtrStatus'] == 'true') {
        $asbr_status_color = 'success';
    }

    echo '
        <tbody>
          <tr>
            <td></td>
            <td>' . generate_device_link($device, 0, ['tab' => 'routing', 'proto' => 'ospfv3']) . '</td>
            <td>' . long2ip($instance['ospfv3RouterId']) . '</td>
            <td><span class="label label-' . $status_color . '">' . $instance['ospfv3AdminStatus'] . '</span></td>
            <td><span class="label label-' . $abr_status_color . '">' . $instance['ospfv3AreaBdrRtrStatus'] . '</span></td>
            <td><span class="label label-' . $asbr_status_color . '">' . $instance['ospfv3ASBdrRtrStatus'] . '</span></td>
            <td>' . $area_count . '</td>
            <td>' . $port_count . '(' . $port_count_enabled . ')</td>
            <td>' . $nbr_count . '</td>
          </tr>
        </tbody>';
}
echo '</table>
    </div>
  </div>
</div>';
