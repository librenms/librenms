<?php

use App\Models\OspfInstance;

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
$data = OspfInstance::where('ospfAdminStat', 'enabled')
    ->with('device')->get();

/** @var OspfInstance $instance */
foreach ($data as $instance) {
    $area_count = dbFetchCell("SELECT COUNT(*) FROM `ospf_areas` WHERE `device_id` = '" . $instance->device_id . "'");
    $port_count = dbFetchCell("SELECT COUNT(*) FROM `ospf_ports` WHERE `device_id` = '" . $instance->device_id . "'");
    $port_count_enabled = dbFetchCell("SELECT COUNT(*) FROM `ospf_ports` WHERE `ospfIfAdminStat` = 'enabled' AND `device_id` = '" . $instance->device_id . "'");
    $nbr_count = dbFetchCell("SELECT COUNT(*) FROM `ospf_nbrs` WHERE `device_id` = '" . $instance->device_id . "'");

    $status_color = $instance->ospfAdminStat == 'enabled' ? 'success' : 'default';
    $abr_status_color = $instance->ospfAreaBdrRtrStatus == 'true' ? 'success' : 'default';
    $asbr_status_color = $instance->ospfASBdrRtrStatus == 'true' ? 'success' : 'default';

    echo '
        <tbody>
          <tr>
            <td></td>
            <td>' . ($instance->device ? \LibreNMS\Util\Url::deviceLink($instance->device, null, ['tab' => 'routing', 'proto' => 'ospf']) : 'unknown') . '</td>
            <td>' . $instance->ospfRouterId . '</td>
            <td><span class="label label-' . $status_color . '">' . $instance->ospfAdminStat . '</span></td>
            <td><span class="label label-' . $abr_status_color . '">' . $instance->ospfAreaBdrRtrStatus . '</span></td>
            <td><span class="label label-' . $asbr_status_color . '">' . $instance->ospfASBdrRtrStatus . '</span></td>
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
