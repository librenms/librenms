<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use App\Models\Processor;
use Illuminate\Http\Request;

class ProcessorsMetrics
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
        $totalQ = Processor::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_processors_total', 'Total number of processors', 'gauge', ["librenms_processors_total {$total}"]);

        // Default to global metrics only; detailed per-access-point metrics are opt-in via ?scope=detail
        if (! $includeDetail) {
            return implode("\n", $lines) . "\n";
        }

        $usage_lines = [];

        // Gather per-processor metrics from Redis poller snapshots
        $payloads = $this->readRedisPollerPayloads($filters['device_ids']);
        $snapshot = [];
        foreach ($payloads as $payload) {
            if (($payload['measurement'] ?? null) !== 'processors') {
                continue;
            }

            $deviceId = isset($payload['device_id']) ? (int) $payload['device_id'] : 0;
            if ($deviceId <= 0) {
                continue;
            }

            $timestamp = isset($payload['timestamp']) ? (int) $payload['timestamp'] : 0;
            $tags = is_array($payload['tags'] ?? null) ? $payload['tags'] : [];
            $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];

            $processorType = (string) ($tags['processor_type'] ?? '');
            $processorIndex = (string) ($tags['processor_index'] ?? '');
            if ($processorType === '' || $processorIndex === '') {
                continue;
            }

            $snapshotKey = $deviceId . ':' . $processorType . ':' . $processorIndex;
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

        $deviceIds = collect(array_values(array_unique(array_map(fn (string $key) => (int) explode(':', $key)[0], array_keys($snapshot)))));
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $procQuery = Processor::select('processor_id', 'device_id', 'processor_descr', 'processor_index', 'processor_type');
        $procQuery = $this->applyDeviceFilter($procQuery, $filters['device_ids']);
        foreach ($procQuery->cursor() as $p) {
            $snapshotKey = $p->device_id . ':' . $p->processor_type . ':' . $p->processor_index;
            if (! isset($snapshot[$snapshotKey])) {
                continue;
            }

            $fields = $snapshot[$snapshotKey]['fields'];
            $usage = (float) ($fields['usage'] ?? $fields['processor_usage'] ?? 0);

            $dev = $devices->get($p->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';

            $labels = sprintf('processor_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",processor_descr="%s",processor_index="%s",processor_type="%s"',
                $p->processor_id,
                $p->device_id,
                $device_hostname,
                $device_sysName,
                $this->escapeLabel((string) $p->processor_descr),
                $this->escapeLabel((string) $p->processor_index),
                $this->escapeLabel((string) $p->processor_type)
            );

            $usage_lines[] = "librenms_processors_usage_percent{{$labels}} {$usage}";
        }

        // Append per-processor metrics
        $this->appendMetricBlock($lines, 'librenms_processors_usage_percent', 'Processor usage percent (0-100)', 'gauge', $usage_lines);

        return implode("\n", $lines) . "\n";
    }
}
