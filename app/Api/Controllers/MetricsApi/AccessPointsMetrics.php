<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\AccessPoint;
use App\Models\Device;
use Illuminate\Http\Request;

class AccessPointsMetrics
{
    use MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Gather global metrics
        $total = AccessPoint::count();

        // Append global metrics
        $lines[] = '# HELP librenms_access_points_total Total number of access points';
        $lines[] = '# TYPE librenms_access_points_total gauge';
        $lines[] = "librenms_access_points_total {$total}";

        // Prepare per-access-point metrics arrays
        $deleted_lines = [];
        $channel_lines = [];
        $txpow_lines = [];
        $radioutil_lines = [];
        $numasoclients_lines = [];
        $nummonclients_lines = [];
        $numactbssid_lines = [];
        $nummonbssid_lines = [];
        $interference_lines = [];

        // Gather device info mapping for labels
        $deviceIds = AccessPoint::select('device_id')->distinct()->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        foreach (AccessPoint::select('accesspoint_id', 'device_id', 'name', 'radio_number', 'type', 'mac_addr', 'deleted', 'channel', 'txpow', 'radioutil', 'numasoclients', 'nummonclients', 'numactbssid', 'nummonbssid', 'interference')->cursor() as $ap) {
            $dev = $devices->get($ap->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';
            $device_type = $dev ? $this->escapeLabel((string) $dev->type) : '';
            $labels = sprintf('accesspoint_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",device_type="%s",name="%s",radio_number="%s",type="%s",mac_addr="%s"',
                $ap->accesspoint_id,
                $ap->device_id,
                $device_hostname,
                $device_sysName,
                $device_type,
                $this->escapeLabel((string) $ap->name),
                $this->escapeLabel((string) $ap->radio_number),
                $this->escapeLabel((string) $ap->type),
                $this->escapeLabel((string) $ap->mac_addr)
            );

            $deleted_lines[] = "librenms_access_point_deleted{{$labels}} " . ($ap->deleted ? '1' : '0');
            $channel_lines[] = "librenms_access_point_channel{{$labels}} " . ((int) $ap->channel ?: 0);
            $txpow_lines[] = "librenms_access_point_txpow{{$labels}} " . ((int) $ap->txpow ?: 0);
            $radioutil_lines[] = "librenms_access_point_radioutil{{$labels}} " . ((int) $ap->radioutil ?: 0);
            $numasoclients_lines[] = "librenms_access_point_numasoclients{{$labels}} " . ((int) $ap->numasoclients ?: 0);
            $nummonclients_lines[] = "librenms_access_point_nummonclients{{$labels}} " . ((int) $ap->nummonclients ?: 0);
            $numactbssid_lines[] = "librenms_access_point_numactbssid{{$labels}} " . ((int) $ap->numactbssid ?: 0);
            $nummonbssid_lines[] = "librenms_access_point_nummonbssid{{$labels}} " . ((int) $ap->nummonbssid ?: 0);
            $interference_lines[] = "librenms_access_point_interference{{$labels}} " . ((int) $ap->interference ?: 0);
        }

        // Append per-access-point metrics
        $lines[] = '# HELP librenms_access_point_deleted Whether an access point is deleted (1) or not (0)';
        $lines[] = '# TYPE librenms_access_point_deleted gauge';
        $lines = array_merge($lines, $deleted_lines);

        $lines[] = '# HELP librenms_access_point_channel Access point channel';
        $lines[] = '# TYPE librenms_access_point_channel gauge';
        $lines = array_merge($lines, $channel_lines);

        $lines[] = '# HELP librenms_access_point_txpow Access point transmit power';
        $lines[] = '# TYPE librenms_access_point_txpow gauge';
        $lines = array_merge($lines, $txpow_lines);

        $lines[] = '# HELP librenms_access_point_radioutil Access point radio utilization';
        $lines[] = '# TYPE librenms_access_point_radioutil gauge';
        $lines = array_merge($lines, $radioutil_lines);

        $lines[] = '# HELP librenms_access_point_numasoclients Number of associated clients (active)';
        $lines[] = '# TYPE librenms_access_point_numasoclients gauge';
        $lines = array_merge($lines, $numasoclients_lines);

        $lines[] = '# HELP librenms_access_point_nummonclients Number of monitored clients';
        $lines[] = '# TYPE librenms_access_point_nummonclients gauge';
        $lines = array_merge($lines, $nummonclients_lines);

        $lines[] = '# HELP librenms_access_point_numactbssid Number of active BSSIDs';
        $lines[] = '# TYPE librenms_access_point_numactbssid gauge';
        $lines = array_merge($lines, $numactbssid_lines);

        $lines[] = '# HELP librenms_access_point_nummonbssid Number of monitored BSSIDs';
        $lines[] = '# TYPE librenms_access_point_nummonbssid gauge';
        $lines = array_merge($lines, $nummonbssid_lines);

        $lines[] = '# HELP librenms_access_point_interference Interference level reported for access point';
        $lines[] = '# TYPE librenms_access_point_interference gauge';
        $lines = array_merge($lines, $interference_lines);

        return implode("\n", $lines) . "\n";
    }
}
