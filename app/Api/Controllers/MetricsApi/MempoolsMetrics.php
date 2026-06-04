<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use App\Models\Mempool;
use Illuminate\Http\Request;

class MempoolsMetrics
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
        $totalQ = Mempool::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_mempools_total', 'Total number of mempools', 'gauge', "librenms_mempools_total {$total}");

        // Default to global metrics only; detailed per-access-point metrics are opt-in via ?scope=detail
        if (! $includeDetail) {
            return implode("\n", $lines) . "\n";
        }

        // Prepare per-mempool arrays
        $used_lines = [];
        $free_lines = [];
        $total_lines = [];
        $perc_lines = [];

        // Gather per-mempool metrics from Redis poller snapshots
        $payloads = $this->readRedisPollerPayloads($filters['device_ids']);
        $snapshot = [];
        foreach ($payloads as $payload) {
            if (($payload['measurement'] ?? null) !== 'mempool') {
                continue;
            }

            $deviceId = isset($payload['device_id']) ? (int) $payload['device_id'] : 0;
            if ($deviceId <= 0) {
                continue;
            }

            $timestamp = isset($payload['timestamp']) ? (int) $payload['timestamp'] : 0;
            $tags = is_array($payload['tags'] ?? null) ? $payload['tags'] : [];
            $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];

            $mempoolType = (string) ($tags['mempool_type'] ?? '');
            $mempoolClass = (string) ($tags['mempool_class'] ?? '');
            $mempoolIndex = (string) ($tags['mempool_index'] ?? '');
            if ($mempoolType === '' || $mempoolClass === '' || $mempoolIndex === '') {
                continue;
            }

            $snapshotKey = $deviceId . ':' . $mempoolType . ':' . $mempoolClass . ':' . $mempoolIndex;
            if (! isset($snapshot[$snapshotKey]) || $timestamp >= $snapshot[$snapshotKey]['timestamp']) {
                $snapshot[$snapshotKey] = [
                    'timestamp' => $timestamp,
                    'fields' => $fields,
                ];
            }
        }

        if (empty($snapshot)) {
            return implode("\n", $lines) . "\n";
        }

        $deviceIds = collect(array_values(array_unique(array_map(fn (string $key) => (int) explode(':', $key)[0], array_keys($snapshot)))))
            ->map(static fn (int $id): string => (string) $id);
        $devices = $this->gatherDevicesForIds($deviceIds);

        $mpQuery = Mempool::select('mempool_id', 'device_id', 'mempool_descr', 'mempool_class', 'mempool_type', 'mempool_index');
        $mpQuery = $this->applyDeviceFilter($mpQuery, $filters['device_ids']);
        foreach ($mpQuery->cursor() as $m) {
            $snapshotKey = $m->device_id . ':' . $m->mempool_type . ':' . $m->mempool_class . ':' . $m->mempool_index;
            if (! isset($snapshot[$snapshotKey])) {
                continue;
            }

            $fields = $snapshot[$snapshotKey]['fields'];
            $used = (float) ($fields['used'] ?? 0);
            $free = (float) ($fields['free'] ?? 0);
            $total = $used + $free;
            $percent = $total > 0 ? (($used / $total) * 100) : 0;

            $dev = $devices->get($m->device_id);
            $labelsArr = [
                'mempool_id' => (string) $m->mempool_id,
                'device_id' => (string) $m->device_id,
                'device_hostname' => $dev ? $this->escapeLabel((string) $dev->hostname) : '',
                'device_sysName' => $dev ? $this->escapeLabel((string) $dev->sysName) : '',
                'mempool_descr' => $this->escapeLabel((string) $m->mempool_descr),
                'mempool_class' => $this->escapeLabel((string) $m->mempool_class),
            ];

            $labels = $this->formatLabels($labelsArr);

            $used_lines[] = "librenms_mempools_used_bytes{{$labels}} {$used}";
            $free_lines[] = "librenms_mempools_free_bytes{{$labels}} {$free}";
            $total_lines[] = "librenms_mempools_total_bytes{{$labels}} {$total}";
            $perc_lines[] = "librenms_mempools_used_percent{{$labels}} {$percent}";
        }

        // Append per-mempool metrics
        $this->appendMetricBlock($lines, 'librenms_mempools_used_bytes', 'Used bytes in mempool', 'gauge', $used_lines);
        $this->appendMetricBlock($lines, 'librenms_mempools_free_bytes', 'Free bytes in mempool', 'gauge', $free_lines);
        $this->appendMetricBlock($lines, 'librenms_mempools_total_bytes', 'Total bytes in mempool', 'gauge', $total_lines);
        $this->appendMetricBlock($lines, 'librenms_mempools_used_percent', 'Percent used', 'gauge', $perc_lines);

        return implode("\n", $lines) . "\n";
    }
}
