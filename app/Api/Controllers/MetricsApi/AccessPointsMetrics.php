<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\AccessPoint;
use App\Models\Device;
use Illuminate\Http\Request;
use Traits\MetricsHelpers;

class AccessPointsMetrics
{

    public function render(Request $request): string
    {
        $lines = [];

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        $totalQ = AccessPoint::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_access_points_total', 'Total number of access points', 'gauge', "librenms_access_points_total {$total}");

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
        $deviceIdsQuery = AccessPoint::select('device_id')->distinct();
        $deviceIdsQuery = $this->applyDeviceFilter($deviceIdsQuery, $filters['device_ids']);
        $deviceIds = $deviceIdsQuery->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $apQuery = AccessPoint::select('accesspoint_id', 'device_id', 'name', 'radio_number', 'type', 'mac_addr', 'deleted', 'channel', 'txpow', 'radioutil', 'numasoclients', 'nummonclients', 'numactbssid', 'nummonbssid', 'interference');
        $apQuery = $this->applyDeviceFilter($apQuery, $filters['device_ids']);
        foreach ($apQuery->cursor() as $ap) {
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
        $this->appendMetricBlock($lines, 'librenms_access_point_deleted', 'Whether an access point is deleted (1) or not (0)', 'gauge', $deleted_lines);
        $this->appendMetricBlock($lines, 'librenms_access_point_channel', 'Access point channel', 'gauge', $channel_lines);
        $this->appendMetricBlock($lines, 'librenms_access_point_txpow', 'Access point transmit power', 'gauge', $txpow_lines);
        $this->appendMetricBlock($lines, 'librenms_access_point_radioutil', 'Access point radio utilization', 'gauge', $radioutil_lines);
        $this->appendMetricBlock($lines, 'librenms_access_point_numasoclients', 'Number of associated clients (active)', 'gauge', $numasoclients_lines);
        $this->appendMetricBlock($lines, 'librenms_access_point_nummonclients', 'Number of monitored clients', 'gauge', $nummonclients_lines);
        $this->appendMetricBlock($lines, 'librenms_access_point_numactbssid', 'Number of active BSSIDs', 'gauge', $numactbssid_lines);
        $this->appendMetricBlock($lines, 'librenms_access_point_nummonbssid', 'Number of monitored BSSIDs', 'gauge', $nummonbssid_lines);
        $this->appendMetricBlock($lines, 'librenms_access_point_interference', 'Interference level reported for access point', 'gauge', $interference_lines);

        return implode("\n", $lines) . "\n";
    }
}
