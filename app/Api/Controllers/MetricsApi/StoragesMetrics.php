<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use App\Models\Storage;
use Illuminate\Http\Request;

class StoragesMetrics
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
        $totalQ = Storage::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_storages_total', 'Total number of storage entries', 'gauge', ["librenms_storages_total {$total}"]);

        // Default to global metrics only; detailed per-access-point metrics are opt-in via ?scope=detail
        if (! $includeDetail) {
            return implode("\n", $lines) . "\n";
        }

        // Prepare per-storage arrays
        $used_lines = [];
        $free_lines = [];
        $total_lines = [];
        $perc_lines = [];

        // Gather per-storage values from Redis poller snapshots
        $payloads = $this->readRedisPollerPayloads($filters['device_ids']);
        $snapshot = [];
        foreach ($payloads as $payload) {
            if (($payload['measurement'] ?? null) !== 'storage') {
                continue;
            }

            $deviceId = isset($payload['device_id']) ? (int) $payload['device_id'] : 0;
            if ($deviceId <= 0) {
                continue;
            }

            $timestamp = isset($payload['timestamp']) ? (int) $payload['timestamp'] : 0;
            $tags = is_array($payload['tags'] ?? null) ? $payload['tags'] : [];
            $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];

            $used = $fields['used'] ?? null;
            $free = $fields['free'] ?? null;
            if (! is_numeric($used) && ! is_numeric($free)) {
                continue;
            }

            $storageId = isset($tags['storage_id']) ? (int) $tags['storage_id'] : 0;
            $key = $storageId > 0
                ? $deviceId . ':' . $storageId
                : $deviceId . ':' . ($tags['type'] ?? '') . ':' . ($tags['descr'] ?? '');

            if (! isset($snapshot[$key]) || $timestamp >= $snapshot[$key]['timestamp']) {
                $snapshot[$key] = [
                    'timestamp' => $timestamp,
                    'used' => (float) ($used ?? 0),
                    'free' => (float) ($free ?? 0),
                ];
            }
        }

        if (empty($snapshot)) {
            return implode("\n", $lines) . "\n";
        }

        $deviceIds = collect(array_values(array_unique(array_map(fn (string $key) => (int) explode(':', $key)[0], array_keys($snapshot)))));
        $devices = Device::select('device_id', 'hostname', 'sysName')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $storageQuery = Storage::select('storage_id', 'device_id', 'type', 'storage_descr');
        $storageQuery = $this->applyDeviceFilter($storageQuery, $filters['device_ids']);
        foreach ($storageQuery->cursor() as $s) {
            $snapshotKey = $s->device_id . ':' . $s->storage_id;
            if (! isset($snapshot[$snapshotKey])) {
                $snapshotKey = $s->device_id . ':' . $s->type . ':' . $s->storage_descr;
            }
            if (! isset($snapshot[$snapshotKey])) {
                continue;
            }

            $used = (float) $snapshot[$snapshotKey]['used'];
            $free = (float) $snapshot[$snapshotKey]['free'];
            $totalBytes = $used + $free;
            $percent = $totalBytes > 0 ? (($used / $totalBytes) * 100) : 0;

            $dev = $devices->get($s->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';

            $labels = sprintf('storage_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",storage_descr="%s"',
                $s->storage_id,
                $s->device_id,
                $device_hostname,
                $device_sysName,
                $this->escapeLabel((string) $s->storage_descr)
            );

            $used_lines[] = "librenms_storages_used_bytes{{$labels}} {$used}";
            $free_lines[] = "librenms_storages_free_bytes{{$labels}} {$free}";
            $total_lines[] = "librenms_storages_total_bytes{{$labels}} {$totalBytes}";
            $perc_lines[] = "librenms_storages_used_percent{{$labels}} {$percent}";
        }

        // Append per-storage metrics
        $this->appendMetricBlock($lines, 'librenms_storages_used_bytes', 'Used bytes in storage', 'gauge', $used_lines);
        $this->appendMetricBlock($lines, 'librenms_storages_free_bytes', 'Free bytes in storage', 'gauge', $free_lines);
        $this->appendMetricBlock($lines, 'librenms_storages_total_bytes', 'Total bytes in storage', 'gauge', $total_lines);
        $this->appendMetricBlock($lines, 'librenms_storages_used_percent', 'Percent used in storage', 'gauge', $perc_lines);

        return implode("\n", $lines) . "\n";
    }
}
