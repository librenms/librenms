<?php

use App\Models\Ospfv3Instance;

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
        </thead>
        <tbody>';
$instances = Ospfv3Instance::where('ospfv3AdminStatus', 'enabled')
    ->with('device')->withCount(['device.ospfv3Areas', 'device.ospfv3Ports', 'device.ospfv3Nbr'])->get();
foreach ($instances as $instance) {
    $port_count_enabled = $instance->device->ospfv3Ports()->where('ospfv3IfAdminStatus', 'enabled')->count();

    $status_color = $instance->ospfv3AdminStatus == 'enabled' ? 'success' : 'default';
    $abr_status_color = $instance->ospfv3AreaBdrRtrStatus == 'true' ? 'success' : 'default';
    $asbr_status_color = $instance->ospfv3ASBdrRtrStatus == 'true' ? 'success' : 'default';

    echo '<tr>
            <td></td>
            <td>' . \LibreNMS\Util\Url::deviceUrl($instance->device, ['tab' => 'routing', 'proto' => 'ospfv3']) . '</td>
            <td>' . long2ip($instance->ospfv3RouterId) . '</td>
            <td><span class="label label-' . $status_color . '">' . $instance->ospfv3AdminStatus . '</span></td>
            <td><span class="label label-' . $abr_status_color . '">' . $instance->ospfv3AreaBdrRtrStatus . '</span></td>
            <td><span class="label label-' . $asbr_status_color . '">' . $instance->ospfv3ASBdrRtrStatus . '</span></td>
            <td>' . $instance->device->ospfv3AreasCount . '</td>
            <td>' . $instance->device->ospfv3PortsCount . '(' . $port_count_enabled . ')</td>
            <td>' . $instance->device->ospfv3NbrsCount . '</td>
          </tr>';
}
echo '</tbody>
    </table>
    </div>
  </div>
</div>';
