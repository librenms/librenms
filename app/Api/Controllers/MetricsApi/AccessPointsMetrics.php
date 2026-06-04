<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\AccessPoint;
use App\Models\Device;
use Illuminate\Http\Request;

class AccessPointsMetrics
{
    use Traits\MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Determine scope (global vs detail) and parse filters
        $scope = $this->parseScope($request);
        $includeDetail = $scope === 'detail';

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        $totalQ = AccessPoint::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_access_points_total', 'Total number of access points', 'gauge', ["librenms_access_points_total {$total}"]);

        // Default to global metrics only; detailed per-access-point metrics are opt-in via ?scope=detail
        if (! $includeDetail) {
            return implode("\n", $lines) . "\n";
        }

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

        // Preload AP + device metadata once, then join with Redis snapshots in memory.
        $apQuery = AccessPoint::select('accesspoint_id', 'device_id', 'name', 'radio_number', 'type', 'mac_addr', 'deleted', 'channel', 'txpow', 'radioutil', 'numasoclients', 'nummonclients', 'numactbssid', 'nummonbssid', 'interference');
        $apQuery = $this->applyDeviceFilter($apQuery, $filters['device_ids']);
        $aps = $apQuery->get();

        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')
            ->whereIn('device_id', $aps->pluck('device_id')->unique()->values())
            ->get()
            ->keyBy('device_id');

        // AP metrics are currently written with vendor/legacy measurement names.
        $payloads = $this->readRedisPollerPayloads($filters['device_ids']);
        $snapshot = [];
        foreach ($payloads as $payload) {
            $measurement = $payload['measurement'] ?? null;
            if (! in_array($measurement, ['accesspoint', 'arubaap', 'aruba'], true)) {
                continue;
            }

            $deviceId = isset($payload['device_id']) ? (int) $payload['device_id'] : 0;
            if ($deviceId <= 0) {
                continue;
            }

            $timestamp = isset($payload['timestamp']) ? (int) $payload['timestamp'] : 0;
            $tags = is_array($payload['tags'] ?? null) ? $payload['tags'] : [];
            $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];
            if (empty($fields)) {
                continue;
            }

            $accessPointId = isset($tags['accesspoint_id']) ? (int) $tags['accesspoint_id'] : 0;
            if ($accessPointId > 0) {
                $snapshotKey = 'id:' . $accessPointId;
            } else {
                $name = (string) ($tags['name'] ?? '');
                $radioNumber = (string) ($tags['radionum'] ?? ($tags['radio_number'] ?? ''));
                if ($name === '' || $radioNumber === '') {
                    continue;
                }

                $snapshotKey = 'key:' . $deviceId . ':' . $name . ':' . $radioNumber;
            }

            if (! isset($snapshot[$snapshotKey]) || $timestamp >= $snapshot[$snapshotKey]['timestamp']) {
                $snapshot[$snapshotKey] = [
                    'timestamp' => $timestamp,
                    'fields' => $fields,
                ];
            }
        }

        foreach ($aps as $ap) {
            $snapshotByIdKey = 'id:' . $ap->accesspoint_id;
            $snapshotByCompositeKey = 'key:' . $ap->device_id . ':' . (string) $ap->name . ':' . (string) $ap->radio_number;
            if (isset($snapshot[$snapshotByIdKey])) {
                $apFields = $snapshot[$snapshotByIdKey]['fields'];
            } elseif (isset($snapshot[$snapshotByCompositeKey])) {
                $apFields = $snapshot[$snapshotByCompositeKey]['fields'];
            } else {
                continue;
            }

            $field = static fn (array $fields, string $name): int => is_numeric($fields[$name] ?? null) ? (int) $fields[$name] : 0;

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

            $deleted_lines[] = "librenms_access_points_deleted{{$labels}} " . ($ap->deleted ? '1' : '0');
            $channel_lines[] = "librenms_access_points_channel{{$labels}} " . $field($apFields, 'channel');
            $txpow_lines[] = "librenms_access_points_txpow{{$labels}} " . $field($apFields, 'txpow');
            $radioutil_lines[] = "librenms_access_points_radioutil{{$labels}} " . $field($apFields, 'radioutil');
            $numasoclients_lines[] = "librenms_access_points_numasoclients{{$labels}} " . $field($apFields, 'numasoclients');
            $nummonclients_lines[] = "librenms_access_points_nummonclients{{$labels}} " . $field($apFields, 'nummonclients');
            $numactbssid_lines[] = "librenms_access_points_numactbssid{{$labels}} " . $field($apFields, 'numactbssid');
            $nummonbssid_lines[] = "librenms_access_points_nummonbssid{{$labels}} " . $field($apFields, 'nummonbssid');
            $interference_lines[] = "librenms_access_points_interference{{$labels}} " . $field($apFields, 'interference');
        }

        // Append per-access-point metrics
        $this->appendMetricBlock($lines, 'librenms_access_points_deleted', 'Whether an access point is deleted (1) or not (0)', 'gauge', $deleted_lines);
        $this->appendMetricBlock($lines, 'librenms_access_points_channel', 'Access point channel', 'gauge', $channel_lines);
        $this->appendMetricBlock($lines, 'librenms_access_points_txpow', 'Access point transmit power', 'gauge', $txpow_lines);
        $this->appendMetricBlock($lines, 'librenms_access_points_radioutil', 'Access point radio utilization', 'gauge', $radioutil_lines);
        $this->appendMetricBlock($lines, 'librenms_access_points_numasoclients', 'Number of associated clients (active)', 'gauge', $numasoclients_lines);
        $this->appendMetricBlock($lines, 'librenms_access_points_nummonclients', 'Number of monitored clients', 'gauge', $nummonclients_lines);
        $this->appendMetricBlock($lines, 'librenms_access_points_numactbssid', 'Number of active BSSIDs', 'gauge', $numactbssid_lines);
        $this->appendMetricBlock($lines, 'librenms_access_points_nummonbssid', 'Number of monitored BSSIDs', 'gauge', $nummonbssid_lines);
        $this->appendMetricBlock($lines, 'librenms_access_points_interference', 'Interference level reported for access point', 'gauge', $interference_lines);

        return implode("\n", $lines) . "\n";
    }
}
