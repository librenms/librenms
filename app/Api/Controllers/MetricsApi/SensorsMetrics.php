<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Http\Request;

class SensorsMetrics
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
        $totalQ = Sensor::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_sensors_total', 'Total number of sensors', 'gauge', ["librenms_sensors_total {$total}"]);

        // Default to global metrics only; detailed per-access-point metrics are opt-in via ?scope=detail
        if (! $includeDetail) {
            return implode("\n", $lines) . "\n";
        }

        // Group outputs by rrd_type
        $gauge_value_lines = [];
        $gauge_limit_warn_lines = [];
        $gauge_limit_crit_lines = [];
        $counter_value_lines = [];
        $counter_limit_warn_lines = [];
        $counter_limit_crit_lines = [];

        // Gather per-sensor values from Redis poller snapshots
        $payloads = $this->readRedisPollerPayloads($filters['device_ids']);
        $snapshot = [];
        foreach ($payloads as $payload) {
            if (($payload['measurement'] ?? null) !== 'sensor') {
                continue;
            }

            $deviceId = isset($payload['device_id']) ? (int) $payload['device_id'] : 0;
            if ($deviceId <= 0) {
                continue;
            }

            $timestamp = isset($payload['timestamp']) ? (int) $payload['timestamp'] : 0;
            $tags = is_array($payload['tags'] ?? null) ? $payload['tags'] : [];
            $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];
            $value = $fields['sensor'] ?? null;
            if (! is_numeric($value)) {
                continue;
            }

            $sensorId = isset($tags['sensor_id']) ? (int) $tags['sensor_id'] : 0;
            $key = $sensorId > 0
                ? $deviceId . ':' . $sensorId
                : $deviceId . ':' . ($tags['sensor_class'] ?? '') . ':' . ($tags['sensor_type'] ?? '') . ':' . ($tags['sensor_index'] ?? '');

            if (! isset($snapshot[$key]) || $timestamp >= $snapshot[$key]['timestamp']) {
                $snapshot[$key] = [
                    'timestamp' => $timestamp,
                    'value' => (float) $value,
                ];
            }
        }

        if (empty($snapshot)) {
            return implode("\n", $lines) . "\n";
        }

        $deviceIds = collect(array_values(array_unique(array_map(fn (string $key) => (int) explode(':', $key)[0], array_keys($snapshot)))));
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $sensorQuery = Sensor::select('sensor_id', 'device_id', 'sensor_class', 'sensor_type', 'sensor_descr', 'sensor_index', 'sensor_limit_warn', 'sensor_limit', 'group', 'rrd_type');
        $sensorQuery = $this->applyDeviceFilter($sensorQuery, $filters['device_ids']);
        foreach ($sensorQuery->cursor() as $s) {
            $snapshotKey = $s->device_id . ':' . $s->sensor_id;
            if (! isset($snapshot[$snapshotKey])) {
                $snapshotKey = $s->device_id . ':' . $s->sensor_class . ':' . $s->sensor_type . ':' . $s->sensor_index;
            }
            if (! isset($snapshot[$snapshotKey])) {
                continue;
            }

            $value = (float) $snapshot[$snapshotKey]['value'];

            $dev = $devices->get($s->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';

            $labels = sprintf('sensor_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",sensor_class="%s",sensor_type="%s",sensor_descr="%s",group="%s"',
                $s->sensor_id,
                $s->device_id,
                $device_hostname,
                $device_sysName,
                $this->escapeLabel((string) $s->sensor_class),
                $this->escapeLabel((string) $s->sensor_type),
                $this->escapeLabel((string) $s->sensor_descr),
                $this->escapeLabel((string) $s->group)
            );

            $rrd = strtoupper((string) ($s->rrd_type ?? 'GAUGE'));
            if ($rrd === 'GAUGE') {
                $gauge_value_lines[] = "librenms_sensors_value{{$labels}} {$value}";
                $gauge_limit_warn_lines[] = "librenms_sensors_limit_warn{{$labels}} " . ((float) ($s->sensor_limit_warn ?? 0));
                $gauge_limit_crit_lines[] = "librenms_sensors_limit_crit{{$labels}} " . ((float) ($s->sensor_limit ?? 0));
            } else {
                // export counter-like sensors with a different metric name to avoid TYPE conflicts
                $counter_value_lines[] = "librenms_sensors_value_counter{{$labels}} {$value}";
                $counter_limit_warn_lines[] = "librenms_sensors_limit_warn_counter{{$labels}} " . ((float) ($s->sensor_limit_warn ?? 0));
                $counter_limit_crit_lines[] = "librenms_sensors_limit_crit_counter{{$labels}} " . ((float) ($s->sensor_limit ?? 0));
            }
        }

        // Append gauge sensors
        if (! empty($gauge_value_lines)) {
            $this->appendMetricBlock($lines, 'librenms_sensors_value', 'Current sensor value (units vary by sensor)', 'gauge', $gauge_value_lines);
            $this->appendMetricBlock($lines, 'librenms_sensors_limit_warn', 'Sensor warning threshold', 'gauge', $gauge_limit_warn_lines);
            $this->appendMetricBlock($lines, 'librenms_sensors_limit_crit', 'Sensor critical threshold', 'gauge', $gauge_limit_crit_lines);
        }

        // Append counter-like sensors
        if (! empty($counter_value_lines)) {
            $this->appendMetricBlock($lines, 'librenms_sensors_value_counter', 'Current sensor value (counter-like)', 'counter', $counter_value_lines);
            $this->appendMetricBlock($lines, 'librenms_sensors_limit_warn_counter', 'Sensor warning threshold (counter-like)', 'counter', $counter_limit_warn_lines);
            $this->appendMetricBlock($lines, 'librenms_sensors_limit_crit_counter', 'Sensor critical threshold (counter-like)', 'counter', $counter_limit_crit_lines);
        }

        return implode("\n", $lines) . "\n";
    }
}
