<?php

use App\Models\OspfArea;
use App\Models\OspfInstance;
use App\Models\OspfNbr;
use App\Models\OspfPort;

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

$device_ids = $data->pluck('device_id');
$area_counts = OspfArea::selectRaw('device_id, COUNT(*) as total')
    ->whereIn('device_id', $device_ids)
    ->groupBy('device_id')
    ->pluck('total', 'device_id');
$port_counts = OspfPort::selectRaw('device_id, COUNT(*) as total')
    ->whereIn('device_id', $device_ids)
    ->groupBy('device_id')
    ->pluck('total', 'device_id');
$port_counts_enabled = OspfPort::selectRaw('device_id, COUNT(*) as total')
    ->where('ospfIfAdminStat', 'enabled')
    ->whereIn('device_id', $device_ids)
    ->groupBy('device_id')
    ->pluck('total', 'device_id');
$nbr_counts = OspfNbr::selectRaw('device_id, COUNT(*) as total')
    ->whereIn('device_id', $device_ids)
    ->groupBy('device_id')
    ->pluck('total', 'device_id');

/** @var OspfInstance $instance */
foreach ($data as $instance) {
    $area_count = $area_counts[$instance->device_id] ?? 0;
    $port_count = $port_counts[$instance->device_id] ?? 0;
    $port_count_enabled = $port_counts_enabled[$instance->device_id] ?? 0;
    $nbr_count = $nbr_counts[$instance->device_id] ?? 0;

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
