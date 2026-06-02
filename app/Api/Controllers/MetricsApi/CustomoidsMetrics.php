<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Customoid;
use App\Models\Device;
use Illuminate\Http\Request;

class CustomoidsMetrics
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
        $totalQ = Customoid::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_customoids_total', 'Total number of customoids', 'gauge', ["librenms_customoids_total {$total}"]);

        // Default to global metrics only; detailed per-access-point metrics are opt-in via ?scope=detail
        if (! $includeDetail) {
            return implode("\n", $lines) . "\n";
        }

        // Prepare per-customoid metrics arrays grouped by datatype
        $gauge_value_lines = [];
        $counter_value_lines = [];
        $gauge_limit_warn_lines = [];
        $gauge_limit_crit_lines = [];
        $counter_limit_warn_lines = [];
        $counter_limit_crit_lines = [];

        // Gather per-customoid metrics from Redis poller snapshots
        $payloads = $this->readRedisPollerPayloads($filters['device_ids']);
        $snapshot = [];
        foreach ($payloads as $payload) {
            if (($payload['measurement'] ?? null) !== 'customoid') {
                continue;
            }

            $deviceId = isset($payload['device_id']) ? (int) $payload['device_id'] : 0;
            if ($deviceId <= 0) {
                continue;
            }

            $timestamp = isset($payload['timestamp']) ? (int) $payload['timestamp'] : 0;
            $tags = is_array($payload['tags'] ?? null) ? $payload['tags'] : [];
            $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];

            $customoidId = isset($tags['customoid_id']) ? (int) $tags['customoid_id'] : 0;
            $snapshotKey = $customoidId > 0
                ? $deviceId . ':' . $customoidId
                : $deviceId . ':descr:' . (string) ($tags['descr'] ?? '');
            if ($snapshotKey === $deviceId . ':descr:') {
                continue;
            }
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

        $coQuery = Customoid::select('customoid_id', 'device_id', 'customoid_descr', 'customoid_limit_warn', 'customoid_limit', 'customoid_datatype');
        $coQuery = $this->applyDeviceFilter($coQuery, $filters['device_ids']);
        foreach ($coQuery->cursor() as $c) {
            $snapshotKey = $c->device_id . ':' . $c->customoid_id;
            if (! isset($snapshot[$snapshotKey])) {
                $snapshotKey = $c->device_id . ':descr:' . $c->customoid_descr;
            }
            if (! isset($snapshot[$snapshotKey])) {
                continue;
            }

            $fields = $snapshot[$snapshotKey]['fields'];
            $value = (float) ($fields['oid_value'] ?? 0);

            $dev = $devices->get($c->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';

            $labels = sprintf('customoid_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",customoid_descr="%s"',
                $c->customoid_id,
                $c->device_id,
                $device_hostname,
                $device_sysName,
                $this->escapeLabel((string) $c->customoid_descr)
            );

            // Determine datatype; treat non-GAUGE as counter-like
            $datatype = strtoupper((string) ($c->customoid_datatype ?? 'GAUGE'));
            if ($datatype === 'GAUGE') {
                $gauge_value_lines[] = "librenms_customoids_value{{$labels}} {$value}";
                $gauge_limit_warn_lines[] = "librenms_customoids_limit_warn{{$labels}} " . ((float) ($c->customoid_limit_warn ?? 0));
                $gauge_limit_crit_lines[] = "librenms_customoids_limit_crit{{$labels}} " . ((float) ($c->customoid_limit ?? 0));
            } else {
                // treat as counter
                $counter_value_lines[] = "librenms_customoids_value_counter{{$labels}} {$value}";
                $counter_limit_warn_lines[] = "librenms_customoids_limit_warn_counter{{$labels}} " . ((float) ($c->customoid_limit_warn ?? 0));
                $counter_limit_crit_lines[] = "librenms_customoids_limit_crit_counter{{$labels}} " . ((float) ($c->customoid_limit ?? 0));
            }
        }

        // Append gauge-type customoids
        if (! empty($gauge_value_lines)) {
            $this->appendMetricBlock($lines, 'librenms_customoids_value', 'Custom oid current value', 'gauge', $gauge_value_lines);
            $this->appendMetricBlock($lines, 'librenms_customoids_limit_warn', 'Customoid warning threshold', 'gauge', $gauge_limit_warn_lines);
            $this->appendMetricBlock($lines, 'librenms_customoids_limit_crit', 'Customoid critical threshold', 'gauge', $gauge_limit_crit_lines);
        }

        // Append counter-type customoids (use distinct metric names to avoid TYPE conflicts)
        if (! empty($counter_value_lines)) {
            $this->appendMetricBlock($lines, 'librenms_customoids_value_counter', 'Custom oid current value (counter-like)', 'counter', $counter_value_lines);
            $this->appendMetricBlock($lines, 'librenms_customoids_limit_warn_counter', 'Customoid warning threshold (counter-like)', 'counter', $counter_limit_warn_lines);
            $this->appendMetricBlock($lines, 'librenms_customoids_limit_crit_counter', 'Customoid critical threshold (counter-like)', 'counter', $counter_limit_crit_lines);
        }

        return implode("\n", $lines) . "\n";
    }
}
