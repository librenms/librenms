<?php

use App\Models\IsisAdjacency;

echo '
<div>
  <div class="panel panel-default">
    <div class="panel-body">
      <table class="table table-condensed table-hover" style="border-collapse:collapse;">
        <thead>
          <tr>
            <th>&nbsp;</th>
            <th>Local Device</th>
            <th>Local Interface</th>
            <th>Adjacent</th>
            <th>System ID</th>
            <th>Area</th>
            <th>System Type</th>
            <th>Admin</th>
            <th>State</th>
            <th>Last Uptime</th>
          </tr>
        </thead>';

foreach (IsisAdjacency::where('device_id', $device['device_id'])->with('port')->get() as $adj) {
    if ($adj->isisISAdjState == 'up') {
        $color = 'green';
    } else {
        $color = 'red';
    }
    $interface_name = $adj->port->ifName;

    echo '
        <tbody>
        <tr>
            <td></td>
            <td>' . generate_device_link($device, 0, ['tab' => 'routing', 'proto' => 'isis']) . '</td>
             <td>' . \LibreNMS\Util\Url::portLink($adj->port) . '</td>
            <td>' . $adj->isisISAdjIPAddrAddress . '</td>
            <td>' . $adj->isisISAdjNeighSysID . '</td>
            <td>' . $adj->isisISAdjAreaAddress . '</td>
            <td>' . $adj->isisISAdjNeighSysType . '</td>
            <td>' . $adj->isisCircAdminState . '</td>
            <td><strong><span style="color: ' . $color . ';">' . $adj->isisISAdjState . '</span></strong></td>
            <td>' . \LibreNMS\Util\Time::formatInterval($adj->isisISAdjLastUpTime) . '</td>
        </tr>
        </tbody>';
}
echo '</table>
    </div>
  </div>
</div>';
