<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Http\Request;

class SensorsMetrics
{
    use MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        $totalQ = Sensor::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $lines[] = '# HELP librenms_sensors_total Total number of sensors';
        $lines[] = '# TYPE librenms_sensors_total gauge';
        $lines[] = "librenms_sensors_total {$total}";

        // Group outputs by rrd_type
        $gauge_value_lines = [];
        $gauge_limit_warn_lines = [];
        $gauge_limit_crit_lines = [];
        $counter_value_lines = [];
        $counter_limit_warn_lines = [];
        $counter_limit_crit_lines = [];

        $deviceIdsQuery = Sensor::select('device_id')->distinct();
        $deviceIdsQuery = $this->applyDeviceFilter($deviceIdsQuery, $filters['device_ids']);
        $deviceIds = $deviceIdsQuery->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $sensorQuery = Sensor::select('sensor_id', 'device_id', 'sensor_class', 'sensor_type', 'sensor_descr', 'sensor_current', 'sensor_divisor', 'sensor_multiplier', 'sensor_limit_warn', 'sensor_limit', 'group', 'rrd_type');
        $sensorQuery = $this->applyDeviceFilter($sensorQuery, $filters['device_ids']);
        foreach ($sensorQuery->cursor() as $s) {
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

            // Apply multiplier/divisor
            $mult = (int) ($s->sensor_multiplier ?: 1);
            $div = (int) ($s->sensor_divisor ?: 1);
            $value = $s->sensor_current !== null ? ((float) $s->sensor_current * $mult / max(1, $div)) : null;

            $rrd = strtoupper((string) ($s->rrd_type ?? 'GAUGE'));
            if ($rrd === 'GAUGE') {
                $gauge_value_lines[] = "librenms_sensor_value{{$labels}} " . ($value !== null ? $value : 0);
                $gauge_limit_warn_lines[] = "librenms_sensor_limit_warn{{$labels}} " . ((float) ($s->sensor_limit_warn ?? 0));
                $gauge_limit_crit_lines[] = "librenms_sensor_limit_crit{{$labels}} " . ((float) ($s->sensor_limit ?? 0));
            } else {
                // export counter-like sensors with a different metric name to avoid TYPE conflicts
                $counter_value_lines[] = "librenms_sensor_value_counter{{$labels}} " . ($value !== null ? $value : 0);
                $counter_limit_warn_lines[] = "librenms_sensor_limit_warn_counter{{$labels}} " . ((float) ($s->sensor_limit_warn ?? 0));
                $counter_limit_crit_lines[] = "librenms_sensor_limit_crit_counter{{$labels}} " . ((float) ($s->sensor_limit ?? 0));
            }
        }

        // Append gauge sensors
        if (! empty($gauge_value_lines)) {
            $lines[] = '# HELP librenms_sensor_value Current sensor value (units vary by sensor)';
            $lines[] = '# TYPE librenms_sensor_value gauge';
            $lines = array_merge($lines, $gauge_value_lines);

            $lines[] = '# HELP librenms_sensor_limit_warn Sensor warning threshold';
            $lines[] = '# TYPE librenms_sensor_limit_warn gauge';
            $lines = array_merge($lines, $gauge_limit_warn_lines);

            $lines[] = '# HELP librenms_sensor_limit_crit Sensor critical threshold';
            $lines[] = '# TYPE librenms_sensor_limit_crit gauge';
            $lines = array_merge($lines, $gauge_limit_crit_lines);
        }

        // Append counter-like sensors
        if (! empty($counter_value_lines)) {
            $lines[] = '# HELP librenms_sensor_value_counter Current sensor value (counter-like)';
            $lines[] = '# TYPE librenms_sensor_value_counter counter';
            $lines = array_merge($lines, $counter_value_lines);

            $lines[] = '# HELP librenns_sensor_limit_warn_counter Sensor warning threshold (counter-like)';
            $lines[] = '# TYPE librenns_sensor_limit_warn_counter counter';
            $lines = array_merge($lines, $counter_limit_warn_lines);

            $lines[] = '# HELP librenns_sensor_limit_crit_counter Sensor critical threshold (counter-like)';
            $lines[] = '# TYPE librenns_sensor_limit_crit_counter counter';
            $lines = array_merge($lines, $counter_limit_crit_lines);
        }

        return implode("\n", $lines) . "\n";
    }
}
