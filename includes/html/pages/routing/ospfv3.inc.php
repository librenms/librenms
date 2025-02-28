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
    ->with(['device' => function ($query) {
        return $query->withCount(['ospfv3Areas', 'ospfv3Ports', 'ospfv3Nbrs']);
    }])->get();
foreach ($instances as $instance) {
    $port_count_enabled = $instance->device->ospfv3Ports()->where('ospfv3IfAdminStatus', 'enabled')->count();

    $status_color = $instance->ospfv3AdminStatus == 'enabled' ? 'success' : 'default';
    $abr_status_color = $instance->ospfv3AreaBdrRtrStatus == 'true' ? 'success' : 'default';
    $asbr_status_color = $instance->ospfv3ASBdrRtrStatus == 'true' ? 'success' : 'default';

    echo '<tr>
            <td></td>
            <td>' . \LibreNMS\Util\Url::deviceLink($instance->device, vars: ['tab' => 'routing', 'proto' => 'ospfv3']) . '</td>
            <td>' . $instance->ospfv3RouterId . '</td>
            <td><span class="label label-' . $status_color . '">' . $instance->ospfv3AdminStatus . '</span></td>
            <td><span class="label label-' . $abr_status_color . '">' . $instance->ospfv3AreaBdrRtrStatus . '</span></td>
            <td><span class="label label-' . $asbr_status_color . '">' . $instance->ospfv3ASBdrRtrStatus . '</span></td>
            <td>' . $instance->device->ospfv3_areas_count . '</td>
            <td>' . $instance->device->ospfv3_ports_count . '(' . $port_count_enabled . ')</td>
            <td>' . $instance->device->ospfv3_nbrs_count . '</td>
          </tr>';
}
echo '</tbody>
    </table>
    </div>
  </div>
</div>';
