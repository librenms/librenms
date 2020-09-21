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
foreach (dbFetchRows("SELECT * FROM `ospf_instances` WHERE `ospfAdminStat` = 'enabled'") as $instance) {
    $device = device_by_id_cache($instance['device_id']);
    $area_count = dbFetchCell("SELECT COUNT(*) FROM `ospf_areas` WHERE `device_id` = '" . $device['device_id'] . "'");
    $port_count = dbFetchCell("SELECT COUNT(*) FROM `ospf_ports` WHERE `device_id` = '" . $device['device_id'] . "'");
    $port_count_enabled = dbFetchCell("SELECT COUNT(*) FROM `ospf_ports` WHERE `ospfIfAdminStat` = 'enabled' AND `device_id` = '" . $device['device_id'] . "'");
    $nbr_count = dbFetchCell("SELECT COUNT(*) FROM `ospf_nbrs` WHERE `device_id` = '" . $device['device_id'] . "'");

    $status_color = $abr_status_color = $asbr_status_color = 'default';

    if ($instance['ospfAdminStat'] == 'enabled') {
        $status_color = 'success';
    }

    if ($instance['ospfAreaBdrRtrStatus'] == 'true') {
        $abr_status_color = 'success';
    }

    if ($instance['ospfASBdrRtrStatus'] == 'true') {
        $asbr_status_color = 'success';
    }

    echo '
        <tbody>
          <tr>
            <td></td>
            <td>' . generate_device_link($device, 0, ['tab' => 'routing', 'proto' => 'ospf']) . '</td>
            <td>' . $instance['ospfRouterId'] . '</td>
            <td><span class="label label-' . $status_color . '">' . $instance['ospfAdminStat'] . '</span></td>
            <td><span class="label label-' . $abr_status_color . '">' . $instance['ospfAreaBdrRtrStatus'] . '</span></td>
            <td><span class="label label-' . $asbr_status_color . '">' . $instance['ospfASBdrRtrStatus'] . '</span></td>
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
